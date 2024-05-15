<?php

namespace Amora\App\Module\Article\App;

use Amora\Core\App\App;
use Amora\Core\Config\S3Config;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Entity\Response\Pagination;
use Amora\Core\Entity\Util\QueryOptions;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\MediaService;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\Logger;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3UploaderApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly MediaService $mediaService,
        private readonly S3Config $s3Config,
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

            $this->logger->logInfo('Getting media...');

            $entries = $this->mediaService->filterMediaBy(
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

            $this->log($totalEntries . ' images processed.');
            $this->log('Average entry process time: ' . $averageTime . ' seconds.');
        });
    }

    private function processMedia(Media $existingMedia): bool
    {
        $this->logger->logInfo('Processing media ID: ' . $existingMedia->id);
        $this->logger->logInfo('Processing media (medium): ' . $existingMedia->getDirWithNameMedium());

        /**
         * List your Amazon S3 buckets.
         *
         * This code expects that you have AWS credentials set up per:
         * https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html
         */

//        putenv("AWS_ACCESS_KEY_ID={$this->s3Config->accessKey}");
//        putenv("AWS_SECRET_ACCESS_KEY={$this->s3Config->accessSecret}");
//
//        echo 'AWS_ACCESS_KEY_ID: ' . getenv('AWS_ACCESS_KEY_ID') . PHP_EOL;
//        echo 'AWS_SECRET_ACCESS_KEY: ' . getenv('AWS_SECRET_ACCESS_KEY') . PHP_EOL;


        try {
            $s3Client = new S3Client(
                [
                    'profile' => 'default',
                    'region' => 'fra1',
                    'endpoint' => $this->s3Config->originEndpoint,
                    'use_path_style_endpoint' => false, // Configures to use subdomain/virtual calling format.
                    'credentials' => [
                        'key' => $this->s3Config->accessKey,
                        'secret' => $this->s3Config->accessSecret,
                    ],
                ],
            );

            $res = $s3Client->putObject(
                [
                    'Bucket' => $this->s3Config->buckedName,
                    'Key' => $this->s3Config->projectFolderName . '/' . $existingMedia->getPathWithNameMedium(true),
                    'Body' => fopen($existingMedia->getDirWithNameMedium(), 'r'),
                    'ACL' => 'public-read',
                ]
            );

            $this->log('Uploaded: ' . $res['ObjectURL']);
        } catch (S3Exception $e) {
            $this->log("There was an error uploading the file" . PHP_EOL . PHP_EOL . $e->getMessage(), true);
            return false;
        }

        return true;
    }
}
