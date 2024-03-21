<?php

namespace Amora\App\Module\Article\App;

use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Model\MediaDestroyed;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Logger;
use Amora\Core\Value\QueryOrderDirection;
use DateTimeImmutable;

class ImageResizeApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly MediaService $mediaService,
        private readonly ImageService $imageService,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'Image Resize',
            lockMaxTimeSinceLastSyncSeconds: 300, // 5 minutes
            isPersistent: false,
        );
    }

    public function run(): void
    {
        $this->execute(function () {
            $timeBefore = microtime(true);

            $this->logger->logInfo('Getting media...');

            $entries = $this->mediaService->filterMediaBy(
                typeIds: [MediaType::Image->value],
                statusIds: [MediaStatus::Active->value],
                queryOptions: new QueryOptions(
                    orderBy: [new QueryOrderBy('id', QueryOrderDirection::ASC)],
                ),
            );

            /** @var Media $entry */
            foreach ($entries as $entry) {
                $res = ArticleCore::getDb()->withTransaction(
                    function () use ($entry) {
                        $res = $this->processImage($entry);

                        return new Feedback($res);
                    }
                );

                if (!$res->isSuccess) {
                    $this->log('Something went wrong. Aborting...', true);
                    exit;
                }
            }

            $timeAfter = microtime(true);
            $diffMicroseconds = $timeAfter - $timeBefore;
            $totalEntries = count($entries);
            $averageTime = $totalEntries ? round($diffMicroseconds / $totalEntries, 3) : 0;

            $this->log($totalEntries . ' images processed.');
            $this->log('Average entry process time: ' . $averageTime . ' seconds.');
        });
    }

    private function processImage(Media $existingMedia): bool
    {
        $this->logger->logInfo('Processing media ID: ' . $existingMedia->id);

        if ($existingMedia->widthOriginal) {
            $this->logger->logInfo('Skipping...');
            return true;
        }

        $resizedMedia = $this->imageService->resizeRawImage(
            rawFile: $existingMedia->asRawFile(),
            user: $existingMedia->user,
            captionHtml: $existingMedia->captionHtml,
        );

        $updatedMedia = new Media(
            id: $existingMedia->id,
            type: $existingMedia->type,
            status: $existingMedia->status,
            user: $existingMedia->user,
            widthOriginal: $resizedMedia->widthOriginal,
            heightOriginal: $resizedMedia->heightOriginal,
            path: $resizedMedia->path,
            filenameOriginal: $existingMedia->filenameOriginal,
            filenameXLarge: $resizedMedia->filenameXLarge,
            filenameLarge: $resizedMedia->filenameLarge,
            filenameMedium: $resizedMedia->filenameMedium,
            filenameSmall: $resizedMedia->filenameSmall,
            filenameXSmall: $resizedMedia->filenameXSmall,
            captionHtml: $existingMedia->captionHtml,
            filenameSource: $existingMedia->filenameSource,
            createdAt: $existingMedia->createdAt,
            updatedAt: $resizedMedia->updatedAt,
        );

        $this->mediaService->updateMedia($updatedMedia);

        $now = new DateTimeImmutable();
        $this->mediaService->storeMediaDestroyed(
            new MediaDestroyed(
                id: null,
                mediaId: $existingMedia->id,
                fullPathWithName: $existingMedia->getDirWithNameXSmall(),
                createdAt: $now,
            ),
        );

        $this->mediaService->storeMediaDestroyed(
            new MediaDestroyed(
                id: null,
                mediaId: $existingMedia->id,
                fullPathWithName: $existingMedia->getDirWithNameSmall(),
                createdAt: $now,
            ),
        );

        $this->mediaService->storeMediaDestroyed(
            new MediaDestroyed(
                id: null,
                mediaId: $existingMedia->id,
                fullPathWithName: $existingMedia->getDirWithNameMedium(),
                createdAt: $now,
            ),
        );

        $this->mediaService->storeMediaDestroyed(
            new MediaDestroyed(
                id: null,
                mediaId: $existingMedia->id,
                fullPathWithName: $existingMedia->getDirWithNameLarge(),
                createdAt: $now,
            ),
        );

        $this->mediaService->storeMediaDestroyed(
            new MediaDestroyed(
                id: null,
                mediaId: $existingMedia->id,
                fullPathWithName: $existingMedia->getDirWithNameXLarge(),
                createdAt: $now,
            ),
        );

        $this->logger->logInfo('Media updated. ID: ' . $existingMedia->id);

        return true;
    }
}
