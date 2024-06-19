<?php

namespace Amora\App\Module\Article\App;

use Amora\Core\App\App;
use Amora\Core\Core;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Service\S3Service;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Logger;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;
use Throwable;

class S3UploaderApp extends App
{
    const MEDIA_BATCH = 200;
    const DAYS_BEFORE_DELETING_MEDIA_LOCALLY = 30;

    public function __construct(
        Logger $logger,
        private readonly MediaService $mediaService,
        private readonly ImageService $imageService,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'S3 Uploader',
            lockMaxTimeSinceLastSyncSeconds: 300, // 5 minutes
            isPersistent: false,
        );
    }

    public function run(): void
    {
        $this->execute(function () {
            if (!Core::getConfig()->s3Config) {
                $this->log('S3 configuration is missing. Aborting...');
                return;
            }

            $timeBefore = microtime(true);

            $totalEntries = $this->retrieveAndUploadMedia();

            $timeAfter = microtime(true);
            $diffMicroseconds = $timeAfter - $timeBefore;
            $averageTime = $totalEntries ? round($diffMicroseconds / $totalEntries, 3) : 0;
            $this->log($totalEntries . ' images uploaded. Average time per image: ' . $averageTime . ' seconds.');

            $timeBefore = microtime(true);

            $totalEntries = $this->retrieveAndDeleteMedia();

            $timeAfter = microtime(true);
            $diffMicroseconds = $timeAfter - $timeBefore;
            $averageTime = $totalEntries ? round($diffMicroseconds / $totalEntries, 3) : 0;
            $this->log($totalEntries . ' images deleted. Average time per image: ' . $averageTime . ' seconds.');
        });
    }

    private function retrieveAndUploadMedia(): int
    {
        $this->log('Getting media to upload...');

        $entries = $this->mediaService->filterMediaBy(
            typeIds: [MediaType::Image->value],
            statusIds: [MediaStatus::Active->value],
            isUploadedToS3: false,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('id', QueryOrderDirection::ASC)],
                pagination: new Pagination(itemsPerPage: self::MEDIA_BATCH),
            ),
        );

        if (!$entries) {
            return 0;
        }

        $count = 0;

        $s3Service = ArticleCore::getS3Service();

        /** @var Media $entry */
        foreach ($entries as $entry) {
            $res = $this->uploadMedia($entry, $s3Service);

            if (!$res->isSuccess) {
                $this->log(
                    'Error uploading media (ID: ' . $entry->id . '). Aborting... => ' . $res->message,
                    true,
                );
                return $count;
            }

            $count++;
        }

        return $count;
    }

    private function uploadMedia(Media $media, S3Service $s3Service): Feedback
    {
        $this->log('Uploading media ID: ' . $media->id);

        $filenames = $this->getMediaFilenames($media);

        foreach ($filenames as $filename => $pathWithName) {
            $res = $s3Service->put(
                filename: $filename,
                fullPathAndFilename: $pathWithName,
            );

            if (!$res->isSuccess) {
                return $res;
            }
        }

        $res = $this->mediaService->updateMediaFields(
            mediaId: $media->id,
            uploadedToS3At: new DateTimeImmutable(),
        );

        if ($res) {
            $this->log("Media uploaded. ID: $media->id");
        }

        $this->updateExif($media);

        return new Feedback(true);
    }

    private function retrieveAndDeleteMedia(): int
    {
        $this->log('Getting media to delete...');

        $beforeDate = DateUtil::convertStringToDateTimeImmutable(
            '-' . self::DAYS_BEFORE_DELETING_MEDIA_LOCALLY . ' days'
        );

        $entries = $this->mediaService->filterMediaBy(
            uploadedToS3Before: $beforeDate,
            isDeletedLocally: false,
            queryOptions: new QueryOptions(
                orderBy: [new QueryOrderBy('id', QueryOrderDirection::ASC)],
                pagination: new Pagination(itemsPerPage: self::MEDIA_BATCH),
            ),
        );

        $count = 0;

        /** @var Media $entry */
        foreach ($entries as $entry) {
            $res = $this->deleteMedia($entry);

            if (!$res->isSuccess) {
                $this->log(
                    'Error deleting media (ID: ' . $entry->id . '). Aborting... => ' . $res->message,
                    true,
                );
                return $count;
            }

            $count++;
        }

        return $count;
    }

    private function deleteMedia(Media $media): Feedback
    {
        $this->log('Deleting media ID: ' . $media->id);

        $this->updateExif($media);

        $filenames = $this->getMediaFilenames($media);

        foreach ($filenames as $pathWithName) {
            if (file_exists($pathWithName)) {
                $res = unlink($pathWithName);

                if (!$res) {
                    return new Feedback(
                        isSuccess: false,
                        message: 'Error deleting media: ' . $pathWithName,
                    );
                }
            }
        }

        $res = $this->mediaService->updateMediaFields(
            mediaId: $media->id,
            deletedLocallyAt: new DateTimeImmutable(),
        );

        if ($res) {
            $this->log("Media deleted. ID: $media->id");
        }

        return new Feedback(true);
    }

    private function getMediaFilenames(Media $media): array
    {
        $filenames = [
            $media->filename => $media->getDirWithNameOriginal(),
        ];

        if ($media->filenameXSmall) {
            $filenames[$media->filenameXSmall] = $media->getDirWithNameXSmall();
        }

        if ($media->filenameSmall) {
            $filenames[$media->filenameSmall] = $media->getDirWithNameSmall();
        }

        if ($media->filenameMedium) {
            $filenames[$media->filenameMedium] = $media->getDirWithNameMedium();
        }

        if ($media->filenameLarge) {
            $filenames[$media->filenameLarge] = $media->getDirWithNameLarge();
        }

        if ($media->filenameXLarge) {
            $filenames[$media->filenameXLarge] = $media->getDirWithNameXLarge();
        }

        return $filenames;
    }

    private function updateExif(Media $media): void
    {
        if ($media->exif) {
            return;
        }

        try {
            $this->log('Storing EXIF data. Media ID: ' . $media->id);

            $exif = $this->imageService->getExifData($media->getDirWithNameOriginal());
            $this->mediaService->storeMediaExif($media->id, $exif);
        } catch (Throwable) {
            $this->log('Error updating EXIF. Media ID: ' . $media->id);
        }
    }
}
