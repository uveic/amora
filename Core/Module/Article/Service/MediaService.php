<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Database\Model\TransactionResponse;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\Logger;
use Amora\Core\Model\Util\QueryOptions;
use Amora\Core\Module\Article\Datalayer\MediaDataLayer;
use Amora\Core\Util\StringUtil;
use DateTimeImmutable;
use Throwable;

class MediaService
{
    public function __construct(
        private readonly Logger $logger,
        private readonly MediaDataLayer $mediaDataLayer,
        private readonly ImageResizeService $imageResizeService,
        private readonly string $mediaBaseDir,
        private readonly string $mediaBaseUrl,
    ) {}

    public function getMediaForId(int $id): ?Media
    {
        return $this->mediaDataLayer->getMediaForId($id);
    }

    public function filterMediaBy(
        array $ids = [],
        array $userIds = [],
        array $typeIds = [],
        array $statusIds = [],
        ?QueryOptions $queryOptions = null,
    ): array {
        return $this->mediaDataLayer->filterMediaBy(
            ids: $ids,
            userIds: $userIds,
            typeIds: $typeIds,
            statusIds: $statusIds,
            queryOptions: $queryOptions,
        );
    }

    public function deleteFile(Media $media): bool
    {
        $res = $this->mediaDataLayer->deleteFile($media->id);
        if (empty($res)) {
            $this->logger->logError('Error deleting image. Image ID: ' . $media->id);
            return false;
        }

        if (file_exists($media->getPathWithNameOriginal())) {
            if (!unlink($media->getPathWithNameOriginal())) {
                return false;
            }
        }

        if ($media->filenameMedium) {
            if (file_exists($media->getPathWithNameMedium())) {
                if (!unlink($media->getPathWithNameMedium())) {
                    return false;
                }
            }
        }

        if ($media->filenameLarge) {
            if (file_exists($media->getPathWithNameLarge())) {
                if (!unlink($media->getPathWithNameLarge())) {
                    return false;
                }
            }
        }

        return true;
    }

    public function workflowStoreFile(
        array $rawFile,
        ?User $user,
    ): TransactionResponse {
        return $this->mediaDataLayer->getDb()->withTransaction(
            function () use (
                $rawFile,
                $user,
            ) {
                try {
                    $rawFile = $this->validateAndProcessRawFile($rawFile);
                    if (empty($rawFile)) {
                        return new TransactionResponse(
                            isSuccess: false,
                            message: 'Raw file not valid',
                        );
                    }

                    $processedFile = match ($rawFile->mediaType) {
                        MediaType::Image => $this->processRawFileImage($rawFile, $user),
                        default => $this->processRawFile($rawFile, $user),
                    };

                    if (empty($processedFile)) {
                        return new TransactionResponse(
                            isSuccess: false,
                            message: 'File not valid',
                        );
                    }

                    $output = $this->mediaDataLayer->storeFile($processedFile);

                    return new TransactionResponse(
                        isSuccess: true,
                        response: $output,
                    );
                } catch (Throwable $t) {
                    $this->logger->logError('Error storing file: '
                        . $t->getMessage()
                        . ' - Trace: ' . $t->getTraceAsString()
                    );

                    return new TransactionResponse(
                        isSuccess: false,
                        message: 'Error storing file: ' . $t->getMessage(),
                    );
                }
            }
        );
    }

    private function processRawFileImage(RawFile $rawFile, ?User $user): ?Media
    {
        try {
            return $this->imageResizeService->resizeOriginalImage(
                rawFile: $rawFile,
                user: $user,
            );
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error processing image: '
                . $t->getMessage()
                . ' - Trace: ' . $t->getTraceAsString()
            );

            return null;
        }
    }

    private function processRawFile(RawFile $rawFile, ?User $user): Media
    {
        $now = new DateTimeImmutable();
        return new Media(
            id: null,
            type: MediaType::Image,
            status: MediaStatus::Active,
            user: $user,
            path: $rawFile->path,
            filenameOriginal: $rawFile->name,
            filenameLarge: null,
            filenameMedium: null,
            caption: $rawFile->name,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    private function generateFilename(string $extension): string
    {
        return date('YmdHis') . StringUtil::getRandomString(16) . '.' . $extension;
    }

    private function validateAndProcessRawFile(array $rawFile): ?RawFile
    {
        if (empty($rawFile['files']['name'])) {
            $this->logger->logError('Raw file name is empty');
            return null;
        }

        if (empty($rawFile['files']['tmp_name'])) {
            $this->logger->logError('Raw file tmp_name is empty');
            return null;
        }

        $rawPathWithName = $rawFile['files']['tmp_name'] . $rawFile['files']['name'];
        if (!file_exists($rawPathWithName)) {
            $this->logger->logError('File not not found: ' . $rawPathWithName);
            return null;
        }

        $extension = $this->getFileExtension($rawFile['files']['name']);
        $newName = $this->generateFilename($extension);
        $newPath = rtrim($this->mediaBaseDir, ' /') . '/';
        $targetPath = $newPath . $newName;

        $res = rename($rawPathWithName, $targetPath);
        if (!$res) {
            $this->logger->logError(
                'Error renaming file from ' . $rawPathWithName . ' to ' . $targetPath
            );
            return null;
        }

        $resP = chmod($targetPath, 0644);
        if (!$resP) {
            $this->logger->logError('Error updating file permissions: ' . $rawPathWithName);
        }

        return new RawFile(
            name: $newName,
            path: $newPath,
            extension: $extension,
            mediaType: MediaType::getTypeFromRawFileType($rawFile['files']['type']),
            sizeBytes: (int)$rawFile['files']['size'],
            error: $rawFile['files']['error'],
        );
    }

    public function getFileExtension(string $filename): string
    {
        if (!str_contains($filename, '.')) {
            return '';
        }

        $parts = explode('.', $filename);
        return strtolower(trim($parts[count($parts) - 1]));
    }
}
