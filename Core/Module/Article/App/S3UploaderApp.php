<?php

namespace Amora\App\Module\Article\App;

use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Service\S3Service;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Logger;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

class S3UploaderApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly MediaService $mediaService,
        private readonly S3Service $s3Service,
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
            $timeBefore = microtime(true);

            $this->log('Getting media...');

            $entries = $this->mediaService->filterMediaBy(
                typeIds: [MediaType::Image->value],
                statusIds: [MediaStatus::Active->value],
                isUploadedToS3: false,
                queryOptions: new QueryOptions(
                    orderBy: [new QueryOrderBy('id', QueryOrderDirection::ASC)],
                    pagination: new Pagination(itemsPerPage: 5),
                ),
            );

            /** @var Media $entry */
            foreach ($entries as $entry) {
                $res = ArticleCore::getDb()->withTransaction(
                    function () use ($entry) {
                        return $this->processMedia($entry);
                    }
                );

                if (!$res->isSuccess) {
                    $this->log(
                        'Error processing media (ID: ' . $entry->id . '). Aborting... => ' . $res->message,
                        true,
                    );
                    return;
                }
            }

            $timeAfter = microtime(true);
            $diffMicroseconds = $timeAfter - $timeBefore;
            $totalEntries = count($entries);
            $averageTime = $totalEntries ? round($diffMicroseconds / $totalEntries, 3) : 0;

            $this->log($totalEntries . ' images processed. Average time per image: ' . $averageTime . ' seconds.');
        });
    }

    private function processMedia(Media $existingMedia): Feedback
    {
        $this->log('Processing media ID: ' . $existingMedia->id);

        $filenames = [
            $existingMedia->filename => $existingMedia->getDirWithNameOriginal(),
        ];

        if ($existingMedia->filenameXSmall) {
            $filenames[$existingMedia->filenameXSmall] = $existingMedia->getDirWithNameXSmall();
        }

        if ($existingMedia->filenameSmall) {
            $filenames[$existingMedia->filenameSmall] = $existingMedia->getDirWithNameSmall();
        }

        if ($existingMedia->filenameMedium) {
            $filenames[$existingMedia->filenameMedium] = $existingMedia->getDirWithNameMedium();
        }

        if ($existingMedia->filenameLarge) {
            $filenames[$existingMedia->filenameLarge] = $existingMedia->getDirWithNameLarge();
        }

        if ($existingMedia->filenameXLarge) {
            $filenames[$existingMedia->filenameXLarge] = $existingMedia->getDirWithNameXLarge();
        }

        foreach ($filenames as $filename => $pathWithName) {
            $res = $this->s3Service->put(
                filename: $filename,
                fullPathAndFilename: $pathWithName,
            );

            if (!$res->isSuccess) {
                return $res;
            }
        }

        $res = $this->mediaService->updateMediaFields(
            mediaId: $existingMedia->id,
            uploadedToS3At: new DateTimeImmutable(),
        );

        if ($res) {
            $this->log("Media ($existingMedia->id) uploaded.");
        }

        return new Feedback(true);
    }
}
