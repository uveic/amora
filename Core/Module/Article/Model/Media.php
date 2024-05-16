<?php

namespace Amora\Core\Module\Article\Model;

use Amora\Core\Core;
use Amora\Core\Module\Article\Value\ImageSize;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\Article\Value\MediaType;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Util\DateUtil;
use DateTimeImmutable;

class Media
{
    public function __construct(
        public ?int $id,
        public readonly MediaType $type,
        public readonly MediaStatus $status,
        public readonly ?User $user,
        public readonly ?int $widthOriginal,
        public readonly ?int $heightOriginal,
        public readonly ?string $path,
        public readonly string $filename,
        public readonly string $filenameSource,
        public readonly ?string $filenameXLarge,
        public readonly ?string $filenameLarge,
        public readonly ?string $filenameMedium,
        public readonly ?string $filenameSmall,
        public readonly ?string $filenameXSmall,
        public readonly ?string $captionHtml,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?DateTimeImmutable $uploadedToS3At,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)$data['media_id'],
            type: MediaType::from($data['media_type_id']),
            status: MediaStatus::from($data['media_status_id']),
            user: isset($data['user_id']) ? User::fromArray($data) : null,
            widthOriginal: isset($data['media_width_original']) ? (int)$data['media_width_original'] : null,
            heightOriginal: isset($data['media_height_original']) ? (int)$data['media_height_original'] : null,
            path: $data['media_path'] ?? null,
            filename: $data['media_filename'],
            filenameSource: $data['media_filename_source'],
            filenameXLarge: $data['media_filename_extra_large'] ?? null,
            filenameLarge: $data['media_filename_large'] ?? null,
            filenameMedium: $data['media_filename_medium'] ?? null,
            filenameSmall: $data['media_filename_small'] ?? null,
            filenameXSmall: $data['media_filename_extra_small'] ?? null,
            captionHtml: $data['media_caption_html'] ?? null,
            createdAt: DateUtil::convertStringToDateTimeImmutable($data['media_created_at']),
            updatedAt: DateUtil::convertStringToDateTimeImmutable($data['media_updated_at']),
            uploadedToS3At: $data['media_uploaded_to_s3_at'] ?
                DateUtil::convertStringToDateTimeImmutable($data['media_uploaded_to_s3_at'])
                : null,
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
            'width_original' => $this->widthOriginal,
            'height_original' => $this->heightOriginal,
            'path' => $this->path,
            'filename' => $this->filename,
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

    public function asPublicArray(): array
    {
        return [
            'id' => $this->id,
            'pathXSmall' => $this->getPathWithNameXSmall(),
            'fullPathXSmall' => $this->getPathWithNameXSmall(),
            'pathSmall' => $this->getPathWithNameSmall(),
            'fullPathSmall' => $this->getPathWithNameSmall(),
            'pathMedium' => $this->getPathWithNameMedium(),
            'fullPathMedium' => $this->getPathWithNameMedium(),
            'pathLarge' => $this->getPathWithNameLarge(),
            'fullPathLarge' => $this->getPathWithNameLarge(),
            'caption' => $this->buildAltText(),
            'captionHtml' => $this->captionHtml,
            'name' => $this->filename,
            'sourceName' => $this->filenameSource,
            'createdAt' => $this->createdAt->format('c'),
            'userId' => $this->user?->id,
            'userName' => $this->user?->getNameOrEmail(),
            'sizes' => $this->buildSizes(),
            'srcset' => $this->buildSrcset(),
            'asHtml' => $this->asHtml(),
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
        return $this->buildDirPath() . $this->filename;
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
        return $this->buildPath() . $this->filename;
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

    public function asHtml(
        ImageSize $size = ImageSize::XSmall,
        string $className = 'media-item',
        bool $lazyLoading = true,
        string $sizes = '',
        ?string $title = null,
        ?string $alt = null,
    ): string {
        if ($this->type === MediaType::Image) {
            return $this->imageAsHtml(
                size: $size,
                className: $className,
                lazyLoading: $lazyLoading,
                sizes: $sizes,
                title: $title,
                alt: $alt,
            );
        }

        return $this->fileAsHtml();
    }

    private function imageAsHtml(
        ImageSize $size = ImageSize::XSmall,
        string $className = 'media-item',
        bool $lazyLoading = true,
        string $sizes = '',
        ?string $title = null,
        ?string $alt = null,
    ): string {
        $src = match ($size) {
            ImageSize::XSmall => $this->getPathWithNameXSmall(),
            ImageSize::Small => $this->getPathWithNameSmall(),
            ImageSize::Medium => $this->getPathWithNameMedium(),
            ImageSize::Large => $this->getPathWithNameLarge(),
            ImageSize::XLarge => $this->getPathWithNameXLarge(),
        };

        $output = [
            'data-media-id="' . $this->id . '"',
            'data-path-medium="' . $this->getPathWithNameMedium() . '"',
            'src="' . $src . '"',
            'alt="' . ($alt ?? $this->buildAltText()) . '"',
            'srcset="' . $this->buildSrcset() . '"',
            'sizes="' . ($sizes ?: $this->buildSizes()) . '"',
            'width="' . $size->value . '"',
        ];

        if ($this->heightOriginal && $this->widthOriginal) {
            $ratio = $size->value / $this->widthOriginal;
            $output[] = 'height="' . (int)round($ratio * $this->heightOriginal) . '"';
        }

        if ($className) {
            $output[] = 'class="' . $className . '"';
        }

        if ($title) {
            $output[] = 'title="' . $title . '"';
        }

        if ($lazyLoading) {
            $output[] = 'loading="lazy"';
        }

        return '<img ' . implode(' ', $output) . '>';
    }

    private function fileAsHtml(): string
    {
        $dateString = DateUtil::formatDateShort(
            date: $this->createdAt,
        );

        $output = [
            '<a href="' . $this->getPathWithNameOriginal() . '" target="_blank" class="media-item" data-media-id="' . $this->id . '">',
            '<div class="media-header">',
            $this->type->getIcon('img-svg-30'),
            '<span class="media-name">' . $this->filenameSource . '</span>',
            '</div>',
            '<span class="media-info">',
            '<span class="media-id">#' . $this->id . '</span>',
        ];

        if ($this->user?->getNameOrEmail()) {
            $output[] = '<span class="media-icon"><img class="img-svg" width="20" height="20" src="/img/svg/user.svg" alt="User" title="User">' . $this->user?->getNameOrEmail() . '</span>';
        }

        $output[] = '<span class="media-icon"><img class="img-svg" width="20" height="20" src="/img/svg/calendar-blank.svg" alt="Date">' . $dateString . '</span>';

        $output[] = '</span>';
        $output[] = '</a>';

        return implode(PHP_EOL, $output);
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
        $config = Core::getConfig();
        return $this->uploadedToS3At && $config->s3Config ?
            ($config->s3Config->originEndpoint . '/')
            : ($config->mediaBaseUrl . '/' . $this->path . '/');
    }

    private function buildSrcset(): string
    {
        $output = [
            $this->getPathWithNameXSmall() . ' ' . ImageSize::XSmall->value . 'w',
        ];

        if ($this->filenameSmall) {
            $output[] = $this->getPathWithNameSmall() . ' ' . ImageSize::Small->value . 'w';
        }

        if ($this->filenameMedium) {
            $output[] = $this->getPathWithNameMedium() . ' ' . ImageSize::Medium->value . 'w';
        }

        if ($this->filenameLarge) {
            $output[] = $this->getPathWithNameLarge() . ' ' . ImageSize::Large->value . 'w';
        }

        if ($this->filenameXLarge) {
            $output[] = $this->getPathWithNameXLarge() . ' ' . ImageSize::XLarge->value . 'w';
        }

        return implode(', ', $output);
    }

    private function buildSizes(): string
    {
        $output = [
            '(min-width: 2960px) 5vw',
            '(min-width: 2660px) calc(2.5vw + 85px)',
            '(min-width: 2500px) calc(5.71vw + 8px)',
            '(min-width: 2340px) 6.43vw',
            '(min-width: 2200px) calc(7.5vw - 14px)',
            '(min-width: 2040px) calc(7.86vw - 9px)',
            '(min-width: 1880px) calc(8.57vw - 11px)',
            '(min-width: 1740px) 9.17vw',
            '(min-width: 1580px) 10vw',
            '(min-width: 1420px) 10.71vw',
            '(min-width: 1260px) calc(12.86vw - 12px)',
            '(min-width: 1120px) 14.17vw',
            '(min-width: 960px) calc(17.14vw - 14px)',
            '(min-width: 800px) calc(20vw - 10px)',
            '(min-width: 340px) calc(33.41vw - 9px)',
            'calc(50vw - 10px)',
        ];

        return implode(', ', $output);
    }
}
