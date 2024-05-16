<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Config\S3Config;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Util\Logger;
use Aws\S3\S3Client;
use Throwable;

readonly class S3Service
{
    private S3Client $s3Client;

    public function __construct(
        private Logger $logger,
        private S3Config $s3Config,
    ) {
        $this->s3Client = new S3Client(
            [
                'profile' => 'default',
                'region' => $this->s3Config->regionName,
                'endpoint' => $this->s3Config->apiEndpoint,
                'use_path_style_endpoint' => false,
            ],
        );
    }

    public function put(string $filename, string $fullPathAndFilename): Feedback
    {
        try {
            if (!file_exists($fullPathAndFilename)) {
                return new Feedback(
                    isSuccess: false,
                    message: 'File not found: ' . $fullPathAndFilename,
                );
            }

            $res = $this->s3Client->putObject(
                [
                    'Bucket' => $this->s3Config->bucketName,
                    'Key' => $this->s3Config->projectFolderName . '/' . $filename,
                    'Body' => fopen($fullPathAndFilename, 'r'),
                    'ACL' => 'public-read',
                ],
            );

            return new Feedback(
                isSuccess: (bool)$res['ObjectURL'],
                response: $res['ObjectURL'] ?? null,
            );
        } catch (Throwable $e) {
            return new Feedback(
                isSuccess: false,
                message: $e->getMessage(),
            );
        }
    }

    public function delete(string $filename): bool
    {
        try {
            $res = $this->s3Client->deleteObject(
                [
                    'Bucket' => $this->s3Config->bucketName,
                    'Key' => $this->s3Config->projectFolderName . '/' . $filename,
                ]
            );

            $statusCode = $res['@metadata']['statusCode'] ?? 0;

            return (int)$statusCode === 204;
        } catch (Throwable $e) {
            $this->logger->logError('Error deleting file from S3: ' . $e->getMessage());
            return false;
        }
    }
}
