<?php

namespace App\Support;

class TradeImageWatermark
{
    public static function apply(string $imagePath): bool
    {
        $imagePath = self::resolvePath($imagePath);
        $watermarkPath = public_path('sntc_watermark.png');

        if ($imagePath === '' || ! is_file($imagePath) || ! is_readable($imagePath)) {
            return false;
        }

        if (! is_file($watermarkPath) || ! function_exists('imagecreatefrompng')) {
            return false;
        }

        $source = self::createImage($imagePath);
        $watermark = @imagecreatefrompng($watermarkPath);
        if ($source === null || $watermark === false) {
            if (self::isGdImage($source)) {
                imagedestroy($source);
            }

            return false;
        }

        imagealphablending($source, true);
        imagesavealpha($watermark, true);

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $wmW = imagesx($watermark);
        $wmH = imagesy($watermark);

        if ($srcW < 1 || $srcH < 1 || $wmW < 1 || $wmH < 1) {
            imagedestroy($watermark);
            imagedestroy($source);

            return false;
        }

        // About 6 marks: 2x3 on landscape, 3x2 on portrait.
        $cols = $srcW >= $srcH ? 3 : 2;
        $rows = $srcW >= $srcH ? 2 : 3;

        $targetW = (int) max(40, min(180, round($srcW * 0.18)));
        $targetH = (int) max(1, round($wmH * ($targetW / $wmW)));
        if ($targetH > (int) ($srcH * 0.18)) {
            $targetH = (int) max(28, min(140, round($srcH * 0.18)));
            $targetW = (int) max(1, round($wmW * ($targetH / $wmH)));
        }

        $scaled = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        $transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
        imagefilledrectangle($scaled, 0, 0, $targetW, $targetH, $transparent);
        imagecopyresampled($scaled, $watermark, 0, 0, 0, 0, $targetW, $targetH, $wmW, $wmH);

        $padX = (int) max(16, round($srcW * 0.08));
        $padY = (int) max(16, round($srcH * 0.08));
        $usableW = max(1, $srcW - (2 * $padX) - $targetW);
        $usableH = max(1, $srcH - (2 * $padY) - $targetH);

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $x = $padX + (int) round($usableW * ($cols === 1 ? 0.5 : $col / ($cols - 1)));
                $y = $padY + (int) round($usableH * ($rows === 1 ? 0.5 : $row / ($rows - 1)));
                imagecopy($source, $scaled, $x, $y, 0, 0, $targetW, $targetH);
            }
        }

        $saved = self::saveImage($source, $imagePath);

        imagedestroy($scaled);
        imagedestroy($watermark);
        imagedestroy($source);

        return $saved;
    }

    private static function resolvePath(string $imagePath): string
    {
        if ($imagePath === '') {
            return '';
        }

        if (is_file($imagePath)) {
            return $imagePath;
        }

        $public = public_path(ltrim($imagePath, '/'));

        return is_file($public) ? $public : $imagePath;
    }

    /**
     * @return \GdImage|resource|null
     */
    private static function createImage(string $imagePath)
    {
        $info = @getimagesize($imagePath);
        $mime = is_array($info) ? ($info['mime'] ?? '') : '';

        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($imagePath) ?: null,
            'image/png' => @imagecreatefrompng($imagePath) ?: null,
            'image/gif' => function_exists('imagecreatefromgif') ? (@imagecreatefromgif($imagePath) ?: null) : null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($imagePath) ?: null) : null,
            default => null,
        };
    }

    /**
     * @param  \GdImage|resource  $image
     */
    private static function saveImage($image, string $imagePath): bool
    {
        $info = @getimagesize($imagePath);
        $mime = is_array($info) ? ($info['mime'] ?? '') : '';

        return match ($mime) {
            'image/jpeg' => imagejpeg($image, $imagePath, 88),
            'image/png' => imagepng($image, $imagePath, 6),
            'image/gif' => function_exists('imagegif') && imagegif($image, $imagePath),
            'image/webp' => function_exists('imagewebp') && imagewebp($image, $imagePath, 88),
            default => false,
        };
    }

    private static function isGdImage($image): bool
    {
        return is_resource($image) || (is_object($image) && $image instanceof \GdImage);
    }
}
