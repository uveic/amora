<?php

namespace Amora\App\Module\Article\App;

use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Entity\Util\QueryOrderBy;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Logger;
use Amora\Core\Value\QueryOrderDirection;
use Aws\S3\S3Client;

class S3UploaderApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly MediaService $mediaService,
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
        $this->listBucket();

        exit;

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

            $this->log($totalEntries . ' images processed.');
            $this->log('Average entry process time: ' . $averageTime . ' seconds.');
        });
    }

    private function processMedia(Media $existingMedia): bool
    {
        $this->logger->logInfo('Processing media ID: ' . $existingMedia->id);

        return true;
    }

    private function listBucket(): void
    {
        /**
         * List your Amazon S3 buckets.
         *
         * This code expects that you have AWS credentials set up per:
         * https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html
         */

        $s3Client = new S3Client(
            [
                'profile' => 'default',
                'region' => 'us-west-2',
                'version' => '2006-03-01'
            ],
        );

        $buckets = $s3Client->listBuckets();
        foreach ($buckets['Buckets'] as $bucket) {
            echo $bucket['Name'] . PHP_EOL;
        }
    }
}
