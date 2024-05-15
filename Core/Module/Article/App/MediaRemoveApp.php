<?php

namespace Amora\App\Module\Article\App;

use Amora\Core\App\App;
use Amora\Core\Entity\Response\Feedback;
use Amora\Core\Module\Album\Value\AlbumStatus;
use Amora\Core\Module\Article\ArticleCore;
use Amora\Core\Module\Article\Datalayer\ArticleDataLayer;
use Amora\Core\Module\Article\Datalayer\MediaDataLayer;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\ArticleStatus;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\Logger;

class MediaRemoveApp extends App
{
    public function __construct(
        Logger $logger,
        private readonly MediaDataLayer $mediaDataLayer,
        private readonly ArticleDataLayer $articleDataLayer,
    ) {
        parent::__construct(
            logger: $logger,
            appName: 'Media Remove App',
            lockMaxTimeSinceLastSyncSeconds: 300, // 5 minutes
            isPersistent: false,
        );
    }

    public function run(): void
    {
        $this->execute(function () {
            $timeBefore = microtime(true);

            $this->deleteArticleHistory();
            $this->deleteDeletedArticles();

            $this->logger->logInfo('Getting media...');
            $entries = $this->getMedia();

            /** @var Media $entry */
            foreach ($entries as $entry) {
                $res = ArticleCore::getDb()->withTransaction(
                    function () use ($entry) {
                        $res = $this->deleteMedia($entry);

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

    private function getMedia(): array
    {
        $entries = $this->mediaDataLayer->getDb()->fetchAll(
            '
                SELECT
                    m.id AS media_id,
                    m.user_id,
                    m.type_id AS media_type_id,
                    m.status_id AS media_status_id,
                    m.width_original AS media_width_original,
                    m.height_original AS media_height_original,
                    m.path AS media_path,
                    m.filename_original AS media_filename_original,
                    m.filename_extra_small AS media_filename_extra_small,
                    m.filename_small AS media_filename_small,
                    m.filename_medium AS media_filename_medium,
                    m.filename_large AS media_filename_large,
                    m.filename_extra_large AS media_filename_extra_large,
                    m.caption_html AS media_caption_html,
                    m.filename_source AS media_filename_source,
                    m.created_at AS media_created_at,
                    m.updated_at AS media_updated_at,

                    u.status_id AS user_status_id,
                    u.language_iso_code AS user_language_iso_code,
                    u.role_id AS user_role_id,
                    u.journey_id AS user_journey_id,
                    u.created_at AS user_created_at,
                    u.updated_at AS user_updated_at,
                    u.email AS user_email,
                    u.name AS user_name,
                    u.password_hash AS user_password_hash,
                    u.bio AS user_bio,
                    u.timezone AS user_timezone,
                    u.change_email_to AS user_change_email_to
                FROM core_media AS m
                    LEFT JOIN core_user AS u ON m.user_id = u.id
                WHERE m.status_id IN (:mediaDeletedStatusId)
                    AND m.type_id IN (:mediaTypeId)
                    AND m.id NOT IN (
                        SELECT main_media_id FROM core_album WHERE status_id NOT IN (:albumDeletedStatusId) AND main_media_id IS NOT NULL
                        UNION
                        SELECT cas.main_media_id FROM core_album_section AS cas INNER JOIN core_album AS ca ON ca.id = cas.album_id WHERE ca.status_id NOT IN (:albumDeletedStatusId) AND cas.main_media_id IS NOT NULL
                        UNION
                        SELECT casm.media_id FROM core_album_section_media AS casm INNER JOIN core_album_section AS cas ON cas.id = casm.album_section_id INNER JOIN core_album AS ca ON ca.id = cas.album_id WHERE ca.status_id NOT IN (:albumDeletedStatusId) AND casm.media_id IS NOT NULL
                        UNION
                        SELECT main_image_id FROM core_article WHERE status_id NOT IN (:articleDeletedStatusId) AND main_image_id IS NOT NULL
                        UNION
                        SELECT media_id FROM core_article_media AS cam INNER JOIN core_article AS ca ON ca.id = cam.article_id WHERE ca.status_id NOT IN (:articleDeletedStatusId)
                        UNION
                        SELECT main_image_id FROM core_article_history WHERE main_image_id IS NOT NULL
                    )
            ',
            [
                ':mediaDeletedStatusId' => MediaStatus::Deleted->value,
                ':mediaTypeId' => MediaType::Image->value,
                ':albumDeletedStatusId' => AlbumStatus::Deleted->value,
                ':articleDeletedStatusId' => ArticleStatus::Deleted->value,
            ]
        );

        $output = [];

        foreach ($entries as $entry) {
            $output[] = Media::fromArray($entry);
        }

        return $output;
    }

    private function deleteMedia(Media $existingMedia): bool
    {
        $this->logger->logInfo('Processing media ID: ' . $existingMedia->id);

        return true;
    }

    private function deleteArticleHistory(): void
    {
        $this->log('Deleting article history...');

        $twoMonthsAgo = DateUtil::convertStringToDateTimeImmutable('-2 months');

        $this->articleDataLayer->getDb()->execute(
            '
                DELETE FROM ' . ArticleDataLayer::ARTICLE_HISTORY_TABLE . '
                WHERE created_at <= :twoMonthsAgo
            ',
            [
                ':twoMonthsAgo' => $twoMonthsAgo->format(DateUtil::MYSQL_DATETIME_FORMAT),
            ],
        );
    }

    private function deleteDeletedArticles(): void
    {
        $this->log('Deleting deleted articles...');

        $twoMonthsAgo = DateUtil::convertStringToDateTimeImmutable('-2 months');

        $this->articleDataLayer->getDb()->execute(
            '
                DELETE ah FROM ' . ArticleDataLayer::ARTICLE_HISTORY_TABLE . ' AS ah
                    INNER JOIN ' . ArticleDataLayer::ARTICLE_TABLE . ' AS a ON a.id = ah.article_id 
                WHERE a.updated_at <= :twoMonthsAgo
                    AND a.status_id = :articleDeletedStatus
            ',
            [
                ':twoMonthsAgo' => $twoMonthsAgo->format(DateUtil::MYSQL_DATETIME_FORMAT),
                ':articleDeletedStatus' => ArticleStatus::Deleted->value,
            ],
        );

        $this->articleDataLayer->getDb()->execute(
            '
                DELETE am FROM ' . ArticleDataLayer::ARTICLE_MEDIA_TABLE . ' AS am
                    INNER JOIN ' . ArticleDataLayer::ARTICLE_TABLE . ' AS a ON a.id = am.article_id 
                WHERE a.updated_at <= :twoMonthsAgo
                    AND a.status_id = :articleDeletedStatus
            ',
            [
                ':twoMonthsAgo' => $twoMonthsAgo->format(DateUtil::MYSQL_DATETIME_FORMAT),
                ':articleDeletedStatus' => ArticleStatus::Deleted->value,
            ],
        );

        $this->articleDataLayer->getDb()->execute(
            '
                DELETE FROM ' . ArticleDataLayer::ARTICLE_TABLE . '
                WHERE updated_at <= :twoMonthsAgo
                    AND status_id = :articleDeletedStatus
            ',
            [
                ':twoMonthsAgo' => $twoMonthsAgo->format(DateUtil::MYSQL_DATETIME_FORMAT),
                ':articleDeletedStatus' => ArticleStatus::Deleted->value,
            ],
        );
    }
}
