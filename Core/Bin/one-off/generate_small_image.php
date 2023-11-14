#!/usr/bin/env php
<?php

namespace Amora\App\Bin;

// change working directory
chdir(dirname(__FILE__));

require_once '../../Core.php';

use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Service\ImageService;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\DateUtil;
use Throwable;
use Amora\Core\Core;

try {
    Core::initiate(realpath(__DIR__ . '/../../..'));
} catch (Throwable $t) {
    echo 'Error initiating application: ' . $t->getMessage() . ' ## Aborting...' . PHP_EOL;
    exit;
}

try {
    $db = ArticleCore::getDb();
    $mediaService = ArticleCore::getMediaService();
    $imageService = ArticleCore::getImageService();
    $logger = ArticleCore::getLogger();

    $logger->logInfo('Getting images...');
    $images = $db->fetchAll(
        '
            SELECT
                m.id AS media_id,
                m.type_id AS media_type_id,
                m.status_id AS media_status_id,
                m.path AS media_path,
                m.filename_original AS media_filename_original,
                m.filename_small AS media_filename_small,
                m.filename_medium AS media_filename_medium,
                m.filename_large AS media_filename_large,
                m.caption_html AS media_caption_html,
                m.filename_source AS media_filename_source,
                m.created_at AS media_created_at,
                m.updated_at AS media_updated_at
            FROM core_media AS m
            WHERE m.filename_small IS NULL
                AND m.status_id IN (:statusActiveId)
                AND m.type_id IN (:typeImageId)
            ORDER BY m.id ASC;
        ',
        [
            ':statusActiveId' => MediaStatus::Active->value,
            ':typeImageId' => MediaType::Image->value,
        ]
    );

    foreach ($images as $image) {
        $media = Media::fromArray($image);

        $logger->logInfo('Media ID: ' . $media->id);

        $extension = $mediaService->getFileExtension($media->filenameOriginal);
        $newMedia = $imageService->generateResizedImage(
            existingMedia: $media,
            newImageSizeConstant: ImageService::IMAGE_SIZE_SMALL,
            imageExtension: $extension,
        );

        if (!$newMedia->filenameSmall) {
          continue;
        }

        $db->execute(
            '
                UPDATE core_media
                SET filename_small = :filenameSmall,
                    updated_at = :updatedAt
                WHERE id = :mediaId
            ',
            [
                ':mediaId' => $media->id,
                ':filenameSmall' => $newMedia->filenameSmall,
                ':updatedAt' => DateUtil::getCurrentDateForMySql(),
            ]
        );

        $logger->logInfo('Media ID: ' . $media->id . ' => Updated: ' . $newMedia->getPathWithNameSmall());
    }
} catch (Throwable $t) {
    Core::getDefaultLogger()->logError(
        'Index error' .
        ' - Error: ' . $t->getMessage() .
        ' - Trace: ' . $t->getTraceAsString()
    );

    header('HTTP/1.1 500 Internal Server Error');
    echo 'There was an unexpected error :(' . PHP_EOL;
}
