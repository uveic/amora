<?php

namespace Amora\App\Module\Article\App;

use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Service\S3Service;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Logger;

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
                ids: [147],
                typeIds: [MediaType::Image->value],
                statusIds: [MediaStatus::Active->value],
                queryOptions: new QueryOptions(
                    pagination: new Pagination(itemsPerPage: 1),
                    orderRandomly: true,
                ),
            );

            /** @var Media $entry */
            foreach ($entries as $entry) {
                $res = ArticleCore::getDb()->withTransaction(
                    function () use ($entry) {
                        $res = $this->processMedia($entry);

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

            $this->log($totalEntries . ' images processed. Average time per image: ' . $averageTime . ' seconds.');
        });
    }

    private function processMedia(Media $existingMedia): bool
    {
        $this->log('Processing media ID: ' . $existingMedia->id);

        $res = $this->s3Service->put(
            filename: $existingMedia->filenameMedium,
            fullPathAndFilename: $existingMedia->getDirWithNameMedium(),
        );

        if ($res) {
            $this->log("Media ($existingMedia->id) uploaded: " . $res);

            $this->mediaService->updateMedia(

            );
        }

        return (bool)$res;
    }
}
