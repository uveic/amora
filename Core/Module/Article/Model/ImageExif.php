<?php

namespace Amora\Core\Module\Article\Model;

use Amora\App\Value\Language;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\StringUtil;
use Amora\Core\Value\CoreIcons;
use DateTimeImmutable;

readonly class ImageExif
{
    public function __construct(
        public ?int $width,
        public ?int $height,
        public ?int $sizeBytes,
        public ?string $cameraModel,
        public ?DateTimeImmutable $takenAt,
        public ?string $exposureTime,
        public ?string $iso,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            width: isset($data['media_exif_width']) ? (int)$data['media_exif_width'] : null,
            height: isset($data['media_exif_height']) ? (int)$data['media_exif_height'] : null,
            sizeBytes: isset($data['media_exif_size_bytes']) ? (int)$data['media_exif_size_bytes'] : null,
            cameraModel: $data['media_exif_camera_model'] ?? null,
            takenAt: isset($data['media_exif_taken_at']) ?
                DateUtil::convertStringToDateTimeImmutable($data['media_exif_taken_at'])
                : null,
            exposureTime: $data['media_exif_exposure_time'] ?? null,
            iso: $data['media_exif_iso'] ?? null,
        );
    }

    public function asArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'size_bytes' => $this->sizeBytes,
            'camera_model' => $this->cameraModel,
            'taken_at' => $this->takenAt?->format(DateUtil::MYSQL_DATETIME_FORMAT),
            'exposure_time' => $this->exposureTime,
            'iso' => $this->iso,
        ];
    }

    public function asPublicArray(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'sizeBytes' => $this->sizeBytes,
            'cameraModel' => $this->cameraModel,
            'date' => $this->takenAt?->format('c'),
            'exposureTime' => $this->exposureTime,
            'ISO' => $this->iso,
        ];
    }

    public function isEmpty(): bool
    {
        return !$this->width &&
            !$this->height &&
            !isset($this->sizeBytes) &&
            !$this->cameraModel &&
            !$this->takenAt &&
            !$this->exposureTime &&
            !$this->iso;
    }

    public function asHtml(
        Language $language,
        Media $media,
        string $indentation = '',
    ): string {
        $createdAtString = DateUtil::formatDate(
            date: $media->createdAt,
            lang: $language,
            includeTime: true,
        );

        $output = [];

        if ($this->takenAt) {
            $takenAtString = DateUtil::formatDate(
                date: $this->takenAt,
                lang: $language,
                includeTime: true,
            );
            $output[] = $indentation . '<div>' . CoreIcons::CALENDAR_BLANK . '<span>' . $takenAtString . '</span></div>';
        }

        if ($this->cameraModel) {
            $output[] = $indentation . '<div>' . CoreIcons::CAMERA . '<span>' . $this->cameraModel . '</span></div>';
        }

        if ($this->exposureTime || $this->iso) {
            $text = $this->exposureTime ?: '';
            if ($text) {
                $text .= ' - ';
            }
            $text .= $this->iso;
            $output[] = $indentation . '<div>' . CoreIcons::APERTURE . '<span>' . $text . '</span></div>';
        }

        if ($this->width) {
            $output[] = $indentation . '<div>' . CoreIcons::FRAME_CORNERS . $this->width . ' x ' . $this->height . '<a target="_blank" href="' . $media->getPathWithNameOriginal() . '">' . CoreIcons::ARROW_SQUARE_OUT . '</a></div>';
        }

        if ($this->sizeBytes) {
            $number = StringUtil::formatNumber(
                language: $language,
                number: $this->sizeBytes / 1000000,
                decimals: 3,
            );

            $output[] = $indentation . '<div>' . CoreIcons::HARD_DRIVES . $number . ' Mb' . '</a></div>';
        }

        $output[] = $indentation . '<div class="image-path">';
        $output[] = $indentation . '  ' . CoreIcons::LINK . '<span class="ellipsis">' .  $media->getPathWithNameLarge() . '</span>';
        $output[] = $indentation . '  <a href="' . $media->getPathWithNameLarge() . '" target="_blank">' . CoreIcons::ARROW_SQUARE_OUT . '</a>';
        $output[] = $indentation . '  <a href="' . $media->getPathWithNameLarge() . '" class="copy-link">' . CoreIcons::COPY_SIMPLE . '</a>';
        $output[] = $indentation . '</div>';

        $output[] = $indentation . '<div>' . CoreIcons::UPLOAD_SIMPLE . $createdAtString . '</div>';

        if ($media->user) {
            $output[] = '<div>' . CoreIcons::USER . $media->user->getNameOrEmail() . '</div>';
        }

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
