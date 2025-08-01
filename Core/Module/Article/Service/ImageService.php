<?php

namespace Amora\Core\Module\Article\Service;

use Amora\Core\Module\Article\Model\ImageExif;
use Amora\Core\Module\Article\Entity\RawFile;
use Amora\Core\Module\Article\Model\Media;
use Amora\Core\Module\Article\Value\ImageSize;
use Amora\Core\Module\Article\Value\MediaStatus;
use Amora\Core\Module\User\Model\User;
use DateTimeImmutable;
use GdImage;
use Amora\Core\Util\Logger;
use Throwable;

readonly class ImageService
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function resizeRawImage(
        RawFile $rawFile,
        ?User $user,
        ?string $captionHtml = null,
        bool $keepSameImageFormat = false,
    ): Media {
        $phpNativeImageType = $keepSameImageFormat
            ? exif_imagetype($rawFile->getPathWithName())
            : IMAGETYPE_WEBP;

        $imageXSmall = $this->resizeImage($rawFile, ImageSize::XSmall, $phpNativeImageType);
        $imageSmall = $this->resizeImage($rawFile, ImageSize::Small, $phpNativeImageType);
        $imageMedium = $this->resizeImage($rawFile, ImageSize::Medium, $phpNativeImageType);
        $imageLarge = $this->resizeImage($rawFile, ImageSize::Large, $phpNativeImageType);
        $imageXLarge = $this->resizeImage($rawFile, ImageSize::XLarge, $phpNativeImageType);

        list($widthOriginal, $heightOriginal) = @getimagesize($rawFile->getPathWithName());
        $now = new DateTimeImmutable();

        return new Media(
            id: null,
            type: $rawFile->mediaType,
            status: MediaStatus::Active,
            user: $user,
            widthOriginal: $widthOriginal,
            heightOriginal: $heightOriginal,
            path: $rawFile->extraPath,
            filename: $rawFile->getName(),
            filenameSource: $rawFile->originalName,
            filenameXLarge: $imageXLarge,
            filenameLarge: $imageLarge,
            filenameMedium: $imageMedium,
            filenameSmall: $imageSmall,
            filenameXSmall: $imageXSmall,
            captionHtml: $captionHtml,
            createdAt: $now,
            updatedAt: $now,
            uploadedToS3At: null,
            deletedLocallyAt: null,
            exif: $this->getExifData($rawFile->getPathWithName()),
        );
    }

    public function getExifData(string $filePathWithName): ?ImageExif
    {
        if (!file_exists($filePathWithName)) {
            return null;
        }

        $phpNativeImageType = exif_imagetype($filePathWithName);
        if ($phpNativeImageType !== IMAGETYPE_JPEG && $phpNativeImageType !== IMAGETYPE_WEBP) {
            return null;
        }

        try {
            $exif = @exif_read_data($filePathWithName, 'FILE, IFD0, EXIF, ANY_TAG');
            $date = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null;
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error reading EXIF data. File: ' . $filePathWithName
                . ' - Error: ' . $t->getMessage()
            );
            return null;
        }

        return new ImageExif(
            width: isset($exif['COMPUTED']['Width']) ? (int)$exif['COMPUTED']['Width'] : null,
            height: isset($exif['COMPUTED']['Height']) ? (int)$exif['COMPUTED']['Height'] : null,
            sizeBytes: isset($exif['FileSize']) ? (int)$exif['FileSize'] : null,
            cameraModel: isset($exif['Model']) ? substr($exif['Model'], 0, 50) : null,
            takenAt: $date ? DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $date) : null,
            exposureTime: isset($exif['ExposureTime']) ? substr($exif['ExposureTime'], 0, 10) : null,
            iso: isset($exif['ISOSpeedRatings'])
                ? (is_array($exif['ISOSpeedRatings']) ? substr($exif['ISOSpeedRatings'][0], 0, 10) : substr($exif['ISOSpeedRatings'], 0, 10))
                : null,
        );
    }

    private function resizeImage(
        RawFile $image,
        ImageSize $newSize,
        int $phpNativeImageType,
    ): ?string {
        list($originalWidth, $originalHeight) = @getimagesize($image->getPathWithName());

        if (!$originalWidth || !$originalHeight) {
            $this->logger->logError(
                'Error getting width and/or height of image' .
                ' - Original full path: ' . $image->getPathWithName()
            );

            return null;
        }

        $smallerSize = $newSize->getSmaller();

        // The new size and the next smaller size are greater than the original width
        if ($newSize->value > $originalWidth &&
            $smallerSize &&
            $smallerSize->value > $originalWidth
        ) {
            return null;
        }

        // The original width stands between the new size and the next smaller size
        if ($newSize->value >= $originalWidth &&
            $smallerSize &&
            $smallerSize->value <= $originalWidth
        ) {
            $ratio = 1;
            $newWidth = $originalWidth;
        } else {
            $ratio = $newSize->value / $originalWidth;
            $newWidth = $newSize->value;
        }

        $newHeight = (int)round($originalHeight * $ratio);

        $sourceImage = $this->getImageFromSourcePath($image);
        if (!$sourceImage) {
            $this->logger->logError(
                'Error resizing image. Source image could not be generated for ' . $image->getPathWithName()
            );

            return null;
        }

        $extension = $this->getExtension($phpNativeImageType);
        $newFilename = $newSize->getFilenameIdentifier() . $image->baseNameWithoutExtension . '.' .  $extension;

        $outputFullPath = $image->getPath() . '/'  . $newFilename;

        $this->detectImageTypeAndResize(
            $sourceImage,
            $phpNativeImageType,
            $image->getPathWithName(),
            $outputFullPath,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight,
        );

        if (!file_exists($outputFullPath)) {
            $this->logger->logError('Error resizing image: ' . $image->getPathWithName());
            return null;
        }

        $resP = chmod($outputFullPath, MediaService::FILE_PERMISSIONS);
        if (!$resP) {
            $this->logger->logError('Error updating permissions: ' . $outputFullPath);
        }

        return $newFilename;
    }

    private function getImageFromSourcePath(RawFile $sourceImage): GdImage|null|false
    {
        $imageType = exif_imagetype($sourceImage->getPathWithName());

        if ($imageType === IMAGETYPE_JPEG) {
            $output = @imagecreatefromjpeg($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating JPG: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        if ($imageType === IMAGETYPE_WEBP) {
            $output = @imagecreatefromwebp($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating Webp image: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        if ($imageType === IMAGETYPE_PNG) {
            $output = @imagecreatefrompng($sourceImage->getPathWithName());

            if (!$output) {
                $this->logger->logError('Error creating PNG image: ' . $sourceImage->getPathWithName());
            }

            return $output;
        }

        $this->logger->logWarning(
            'Image type not supported: ' . $imageType . ' - Image path: ' . $sourceImage->getPathWithName()
        );

        return null;
    }

    private function detectImageTypeAndResize(
        GdImage $sourceImage,
        int $phpNativeImageType,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        if ($phpNativeImageType === IMAGETYPE_JPEG) {
            $this->resizeJpgImage(
                $sourceImage,
                $sourceFullPath,
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            return;
        }

        if ($phpNativeImageType === IMAGETYPE_WEBP) {
            $this->resizeWebpImage(
                $sourceImage,
                $sourceFullPath,
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            return;
        }

        if ($phpNativeImageType === IMAGETYPE_PNG) {
            $this->resizePngImage(
                $sourceImage,
                $sourceFullPath,
                $outputFullPath,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            return;
        }

        $this->logger->logWarning(
            'Resizing not supported for image type: ' . $phpNativeImageType . ' - Image path: ' . $sourceFullPath
        );
    }

    private function resizeJpgImage(
        GdImage $sourceImage,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        try {
            $outputImage = imagecreatetruecolor($newWidth, $newHeight);

            imagecopyresampled(
                $outputImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            $outputImage = $this->checkExifAndRotateIfNecessary($outputImage, $sourceFullPath);

            $res = imagejpeg($outputImage, $outputFullPath, 85);

            if (!$res) {
                $this->logger->logError('Error resizing JPG image: ' . $outputFullPath);
            }

            imagedestroy($outputImage);
            imagedestroy($sourceImage);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error resizing JPG image: ' . $sourceFullPath
                . ' - Error: ' . $t->getMessage(),
            );
        }
    }

    private function resizeWebpImage(
        GdImage $sourceImage,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        try {
            $outputImage = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($outputImage, false);
            imagesavealpha($outputImage, true);
            $transparent = imagecolorallocatealpha($outputImage, 255, 255, 255, 127);
            imagefilledrectangle($outputImage, 0, 0, $newWidth, $newHeight, $transparent);

            imagecopyresampled(
                $outputImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight
            );

            $outputImage = $this->checkExifAndRotateIfNecessary($outputImage, $sourceFullPath);

            $res = imagewebp($outputImage, $outputFullPath, 85);

            if (!$res) {
                $this->logger->logError('Error resizing Webp image: ' . $outputFullPath);
            }

            imagedestroy($outputImage);
            imagedestroy($sourceImage);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error resizing Webp image: ' . $sourceFullPath
                . ' - Error: ' . $t->getMessage(),
            );
        }
    }

    private function resizePngImage(
        GdImage $sourceImage,
        string $sourceFullPath,
        string $outputFullPath,
        int $newWidth,
        int $newHeight,
        int $originalWidth,
        int $originalHeight,
    ): void {
        try {
            $outputImage = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($outputImage, false);
            imagesavealpha($outputImage, true);
            $transparent = imagecolorallocatealpha($outputImage, 255, 255, 255, 127);
            imagefilledrectangle($outputImage, 0, 0, $newWidth, $newHeight, $transparent);

            imagecopyresampled(
                $outputImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $originalWidth,
                $originalHeight,
            );

            $res = imagepng($outputImage, $outputFullPath);

            if (!$res) {
                $this->logger->logError('Error resizing PNG image: ' . $outputFullPath);
            }

            imagedestroy($outputImage);
            imagedestroy($sourceImage);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error resizing PNG image: ' . $sourceFullPath
                . ' - Error: ' . $t->getMessage(),
            );
        }
    }

    private function checkExifAndRotateIfNecessary(GdImage $image, string $imagePath): GdImage
    {
        try {
            $exif = @exif_read_data($imagePath, 0, true);
        } catch (Throwable $t) {
            $this->logger->logError(
                'Error reading EXIF data. Image path: ' . $imagePath
                . ' - Error: ' . $t->getMessage()
            );
            return $image;
        }

        if (!$exif) {
            return $image;
        }

        $orientation = $exif['Orientation'] ?? ($exif['IFD0']['Orientation'] ?? null);

        if (!isset($orientation)) {
            return $image;
        }

        return match ($orientation) {
            8 => imagerotate($image, 90, 0),
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            default => $image,
        };
    }

    public function getExtension(int $phpNativeImageType): string
    {
        return match($phpNativeImageType) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_WEBP => 'webp',
            IMAGETYPE_PNG => 'png',
            default => image_type_to_extension($phpNativeImageType),
        };
    }
}
