<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Core;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Service\ImageSize;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use DateTimeImmutable;

class Media
{
    public function __construct(
        public ?int $id,
        public readonly MediaType $type,
        public readonly MediaStatus $status,
        public readonly ?User $user,
        public readonly ?string $path,
        public readonly string $filenameOriginal,
        public readonly ?string $filenameXLarge,
        public readonly ?string $filenameLarge,
        public readonly ?string $filenameMedium,
        public readonly ?string $filenameSmall,
        public readonly ?string $filenameXSmall,
        public readonly ?string $captionHtml,
        public readonly ?string $filenameSource,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['media_id'],
            type: MediaType::from($data['media_type_id']),
            status: MediaStatus::from($data['media_status_id']),
            user: isset($data['user_id']) ? User::fromArray($data) : null,
            path: $data['media_path'] ?? null,
            filenameOriginal: $data['media_filename_original'],
            filenameXLarge: $data['media_filename_extra_large'] ?? null,
            filenameLarge: $data['media_filename_large'] ?? null,
            filenameMedium: $data['media_filename_medium'] ?? null,
            filenameSmall: $data['media_filename_small'] ?? null,
            filenameXSmall: $data['media_filename_extra_small'] ?? null,
            captionHtml: $data['media_caption_html'] ?? null,
            filenameSource: $data['media_filename_source'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['media_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['media_updated_at']),
        );
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'type_id' => $this->type->value,
            'status_id' => $this->status->value,
            'user_id' => $this->user?->id,
            'user' => $this->user ? $this->user->asArray() : [],
            'path' => $this->path,
            'filename_original' => $this->filenameOriginal,
            'filename_extra_small' => $this->filenameXSmall,
            'filename_small' => $this->filenameSmall,
            'filename_medium' => $this->filenameMedium,
            'filename_large' => $this->filenameLarge,
            'filename_extra_large' => $this->filenameXLarge,
            'caption_html' => $this->captionHtml,
            'filename_source' => $this->filenameSource,
            'created_at' => $this->createdAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'updated_at' => $this->updatedAt->format(DateUtil::MYSQL_DATETIME_FORMAT),
        ];
    }

    public function buildPublicDataArray(): array
    {
        $baseUrl = UrlBuilderUtil::buildBaseUrlWithoutLanguage();
        return [
            'id' => $this->id,
            'pathXSmall' => $this->getPathWithNameXSmall(),
            'fullPathXSmall' => $baseUrl . $this->getPathWithNameXSmall(),
            'pathSmall' => $this->getPathWithNameSmall(),
            'fullPathSmall' => $baseUrl . $this->getPathWithNameSmall(),
            'pathMedium' => $this->getPathWithNameMedium(),
            'fullPathMedium' => $baseUrl . $this->getPathWithNameMedium(),
            'pathLarge' => $this->getPathWithNameLarge(),
            'fullPathLarge' => $baseUrl . $this->getPathWithNameLarge(),
            'pathOriginal' => $this->getPathWithNameOriginal(),
            'fullPathOriginal' => $baseUrl . $this->getPathWithNameOriginal(),
            'caption' => $this->buildAltText(),
            'captionHtml' => $this->captionHtml,
            'name' => $this->type === MediaType::Image
                ? $this->filenameMedium
                : $this->filenameOriginal,
            'sourceName' => $this->filenameSource,
            'createdAt' => $this->createdAt->format('c'),
            'userId' => $this->user?->id,
            'userName' => $this->user?->getNameOrEmail(),
        ];
    }

    public function buildAltText(): string
    {
        if (empty($this->captionHtml)) {
            return '';
        }

        return strip_tags($this->captionHtml);
    }

    public function getDirWithNameOriginal(): string
    {
        return $this->buildDirPath() . $this->filenameOriginal;
    }

    public function getDirWithNameXSmall(): string
    {
        return $this->filenameXSmall
            ? $this->buildDirPath() . $this->filenameXSmall
            : $this->getDirWithNameSmall();
    }

    public function getDirWithNameSmall(): string
    {
        return $this->filenameSmall
            ? $this->buildDirPath() . $this->filenameSmall
            : $this->getDirWithNameMedium();
    }

    public function getDirWithNameMedium(): string
    {
        return $this->filenameMedium
            ? $this->buildDirPath() . $this->filenameMedium
            : $this->getDirWithNameOriginal();
    }

    public function getDirWithNameLarge(): string
    {
        return $this->filenameLarge
            ? $this->buildDirPath() . $this->filenameLarge
            : $this->getDirWithNameMedium();
    }

    public function getDirWithNameXLarge(): string
    {
        return $this->filenameXLarge
            ? $this->buildDirPath() . $this->filenameXLarge
            : $this->getDirWithNameLarge();
    }

    public function getPathWithNameOriginal(): string
    {
        return $this->buildPath() . $this->filenameOriginal;
    }

    public function getPathWithNameXSmall(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameXSmall
            ? $this->buildPath() . $this->filenameXSmall
            : $this->getPathWithNameSmall();
    }

    public function getPathWithNameSmall(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameSmall
            ? $this->buildPath() . $this->filenameSmall
            : $this->getPathWithNameMedium();
    }

    public function getPathWithNameMedium(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameMedium
            ? $this->buildPath() . $this->filenameMedium
            : $this->getPathWithNameOriginal();
    }

    public function getPathWithNameLarge(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameLarge
            ? $this->buildPath() . $this->filenameLarge
            : $this->getPathWithNameMedium();
    }

    public function getPathWithNameXLarge(): string
    {
        if ($this->type !== MediaType::Image) {
            return $this->getPathWithNameOriginal();
        }

        return $this->filenameXLarge
            ? $this->buildPath() . $this->filenameXLarge
            : $this->getPathWithNameLarge();
    }

    public function asRawFile(string $extension): RawFile
    {
        return new RawFile(
            originalName: $this->filenameOriginal,
            name: $this->filenameOriginal,
            basePath: rtrim(Core::getConfig()->mediaBaseDir, ' /'),
            extraPath: $this->path ?? '',
            extension: $extension,
            mediaType: MediaType::Image,
        );
    }

    public function asHtml(
        string $className = 'image-item',
        bool $lazyLoading = true,
        ?string $title = null,
    ): string {
        $srcset = [
            $this->getPathWithNameXSmall() . ' ' . ImageSize::XSmall->value . 'w',
            $this->getPathWithNameSmall() . ' ' . ImageSize::Small->value . 'w',
            $this->getPathWithNameMedium() . ' ' . ImageSize::Medium->value . 'w',
            $this->getPathWithNameLarge() . ' ' . ImageSize::Large->value . 'w',
            $this->getPathWithNameXLarge() . ' ' . ImageSize::XLarge->value . 'w',
        ];

        $sizes = [
            '(min-width: ' . round(ImageSize::XSmall->value / 2) . 'px) ' . ImageSize::XSmall->value . 'px',
            '(min-width: ' . round(ImageSize::Small->value / 2) . 'px) ' . ImageSize::Small->value . 'px',
            '(min-width: ' . round(ImageSize::Medium->value / 2) . 'px) ' . ImageSize::Medium->value . 'px',
            '(min-width: ' . round(ImageSize::Large->value / 2) . 'px) ' . ImageSize::Large->value . 'px',
            '(min-width: ' . round(ImageSize::XLarge->value / 2) . 'px) ' . ImageSize::XLarge->value . 'px',
        ];

        $output = [
            'class="' . $className . '"',
            'data-media-id="' . $this->id . '"',
            'data-path-medium="' . $this->getPathWithNameMedium() . '"',
            'src="' . $this->getPathWithNameXSmall() . '"',
            'width="' . ImageSize::XSmall->value . '"',
            'srcset="' . implode(', ', $srcset) . '"',
            'sizes="' . implode(', ', $sizes) . '"',
            'alt="' . $this->buildAltText() . '"',
        ];

        if ($title) {
            $output[] = 'title="' . $title . '"';
        }

        if ($lazyLoading) {
            $output[] = 'loading="lazy"';
        }

        return '<img ' . implode(' ', $output) . '>';
    }

    private function buildDirPath(): string
    {
        $partialPath = $this->path
            ? (trim($this->path, '/ ') . '/')
            : '';
        return Core::getConfig()->mediaBaseDir . '/' . $partialPath;
    }

    private function buildPath(): string
    {
        $partialPath = $this->path
            ? (trim($this->path, '/ ') . '/')
            : '';
        return Core::getConfig()->mediaBaseUrl . '/' . $partialPath;
    }
}
