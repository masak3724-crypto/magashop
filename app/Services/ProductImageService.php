<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;
use SplQueue;

class ProductImageService
{
    private const LIGHT_MIN = 190;

    private const WHITE_MIN = 235;

    public function directory(): string
    {
        return public_path('images/products');
    }

    public function canProcessImages(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatefromjpeg');
    }

    public function needsBackgroundRemoval(string $path): bool
    {
        if (! is_file($path) || ! $this->canProcessImages()) {
            return false;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg'], true)) {
            return true;
        }

        if (! in_array($ext, ['png', 'webp', 'gif'], true)) {
            return false;
        }

        return $this->hasOpaqueLightBackground($path);
    }

    public function store(Product $product, UploadedFile $file): string
    {
        $this->deleteFile($product);

        $dir = $this->directory();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $sourcePath = $file->getRealPath();

        if ($this->needsBackgroundRemoval($sourcePath)) {
            $filename = $product->slug.'-'.Str::random(6).'.png';
            $targetPath = $dir.DIRECTORY_SEPARATOR.$filename;
            $this->saveWithTransparentBackground($sourcePath, $targetPath);
        } else {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = $product->slug.'-'.Str::random(6).'.'.$extension;
            $file->move($dir, $filename);
        }

        $product->update(['image' => $filename]);

        return $filename;
    }

    public function delete(Product $product): void
    {
        $this->deleteFile($product);
        $product->update(['image' => null]);
    }

    public function deleteFile(Product $product): void
    {
        if (! $product->image) {
            return;
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.$product->image;
        if (is_file($path)) {
            unlink($path);
        }

        $storagePath = public_path('storage/'.$product->image);
        if (is_file($storagePath)) {
            unlink($storagePath);
        }
    }

    public function processFile(string $sourcePath, string $targetPath): bool
    {
        if (! $this->needsBackgroundRemoval($sourcePath)) {
            return false;
        }

        $this->saveWithTransparentBackground($sourcePath, $targetPath);

        return true;
    }

    private function hasOpaqueLightBackground(string $path): bool
    {
        $image = $this->loadImage($path);
        if ($image === false) {
            return false;
        }

        $w = imagesx($image);
        $h = imagesy($image);
        $transparent = 0;
        $light = 0;
        $samples = 0;

        $points = [
            [0, 0], [$w - 1, 0], [0, $h - 1], [$w - 1, $h - 1],
            [(int) ($w / 2), 0], [(int) ($w / 2), $h - 1],
        ];

        foreach ($points as [$x, $y]) {
            for ($dy = -8; $dy <= 8; $dy++) {
                for ($dx = -8; $dx <= 8; $dx++) {
                    $px = $x + $dx;
                    $py = $y + $dy;
                    if ($px < 0 || $py < 0 || $px >= $w || $py >= $h) {
                        continue;
                    }
                    $samples++;
                    [$r, $g, $b, $a] = $this->readPixel($image, $px, $py);
                    if ($a >= 100) {
                        $transparent++;
                    } elseif ($this->isBackgroundColor($r, $g, $b, $a)) {
                        $light++;
                    }
                }
            }
        }

        imagedestroy($image);

        if ($samples === 0) {
            return false;
        }

        if ($transparent / $samples > 0.35) {
            return false;
        }

        return $light / $samples > 0.4;
    }

    private function saveWithTransparentBackground(string $sourcePath, string $targetPath): void
    {
        $image = $this->loadImage($sourcePath);
        if ($image === false) {
            throw new RuntimeException('Неподдерживаемый формат изображения.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $background = array_fill(0, $width * $height, false);
        $queue = new SplQueue;

        for ($x = 0; $x < $width; $x++) {
            $this->seedBackground($image, $x, 0, $background, $queue, $width, $height);
            $this->seedBackground($image, $x, $height - 1, $background, $queue, $width, $height);
        }
        for ($y = 0; $y < $height; $y++) {
            $this->seedBackground($image, 0, $y, $background, $queue, $width, $height);
            $this->seedBackground($image, $width - 1, $y, $background, $queue, $width, $height);
        }

        while (! $queue->isEmpty()) {
            [$x, $y] = $queue->dequeue();

            if ($x < 0 || $y < 0 || $x >= $width || $y >= $height) {
                continue;
            }

            $idx = $y * $width + $x;

            if ($background[$idx]) {
                continue;
            }

            [$r, $g, $b, $a] = $this->readPixel($image, $x, $y);
            if (! $this->isBackgroundColor($r, $g, $b, $a)) {
                continue;
            }

            $background[$idx] = true;

            if ($x + 1 < $width) {
                $queue->enqueue([$x + 1, $y]);
            }
            if ($x > 0) {
                $queue->enqueue([$x - 1, $y]);
            }
            if ($y + 1 < $height) {
                $queue->enqueue([$x, $y + 1]);
            }
            if ($y > 0) {
                $queue->enqueue([$x, $y - 1]);
            }
        }

        $output = imagecreatetruecolor($width, $height);
        imagealphablending($output, false);
        imagesavealpha($output, true);
        $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
        imagefill($output, 0, 0, $transparent);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $idx = $y * $width + $x;
                if ($background[$idx]) {
                    continue;
                }

                [$r, $g, $b] = $this->readPixel($image, $x, $y);
                $color = imagecolorallocatealpha($output, $r, $g, $b, 0);
                imagesetpixel($output, $x, $y, $color);
            }
        }

        imagepng($output, $targetPath);
        imagedestroy($image);
        imagedestroy($output);
    }

    private function seedBackground(
        \GdImage $image,
        int $x,
        int $y,
        array &$background,
        SplQueue $queue,
        int $width,
        int $height,
    ): void {
        if ($x < 0 || $y < 0 || $x >= $width || $y >= $height) {
            return;
        }

        $idx = $y * $width + $x;
        if ($background[$idx]) {
            return;
        }

        [$r, $g, $b, $a] = $this->readPixel($image, $x, $y);
        if ($this->isBackgroundColor($r, $g, $b, $a)) {
            $queue->enqueue([$x, $y]);
        }
    }

    private function isBackgroundColor(int $r, int $g, int $b, int $alpha): bool
    {
        if ($alpha >= 100) {
            return true;
        }

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);

        if ($min >= self::WHITE_MIN) {
            return true;
        }

        if ($min >= self::LIGHT_MIN && ($max - $min) <= 30) {
            return true;
        }

        return false;
    }

    /** @return array{0: int, 1: int, 2: int, 3: int} */
    private function readPixel(\GdImage $image, int $x, int $y): array
    {
        $rgba = imagecolorat($image, $x, $y);

        return [
            ($rgba >> 16) & 0xFF,
            ($rgba >> 8) & 0xFF,
            $rgba & 0xFF,
            ($rgba >> 24) & 0x7F,
        ];
    }

    private function loadImage(string $path): \GdImage|false
    {
        $info = @getimagesize($path);
        if ($info === false) {
            return false;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            IMAGETYPE_GIF => imagecreatefromgif($path),
            default => false,
        };

        if ($image === false) {
            return false;
        }

        if (in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        if (! imageistruecolor($image) && function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        return $image;
    }
}
