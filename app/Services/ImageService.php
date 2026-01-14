<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function uploadAndResize(UploadedFile $file, string $path, array $sizes = []): array
    {
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . $this->sanitizeFileName($originalName);
        $fullPath = $path . '/' . $fileName;

        // Store original
        Storage::disk('public')->put($fullPath, file_get_contents($file));

        $uploadedImages = [$fullPath];

        // Generate resized versions
        foreach ($sizes as $name => $size) {
            $resizedImage = $this->resize(
                $file,
                $size['width'],
                $size['height'] ?? null,
                $size['fit'] ?? 'cover'
            );

            $pathInfo = pathinfo($fileName);
            $resizedFileName = $pathInfo['filename'] . "_{$name}." . $pathInfo['extension'];
            $resizedPath = $path . '/' . $resizedFileName;

            Storage::disk('public')->put($resizedPath, $resizedImage);
            $uploadedImages[] = $resizedPath;
        }

        return $uploadedImages;
    }

    public function resize(UploadedFile|string $file, int $width, ?int $height = null, string $fit = 'cover'): string
    {
        $image = is_string($file) && Storage::disk('public')->exists($file)
            ? $this->manager->read(Storage::disk('public')->get($file))
            : $this->manager->read($file);

        if ($fit === 'cover') {
            $image = $image->cover($width, $height ?? $width);
        } elseif ($fit === 'contain') {
            $image = $image->contain($width, $height ?? $width);
        } elseif ($fit === 'resize') {
            $image = $image->resize($width, $height);
        } else {
            $image = $image->scaleDown($width, $height);
        }

        return (string) $image->toJpeg(quality: 85);
    }

    public function optimize(string $path, int $quality = 85): void
    {
        if (!Storage::disk('public')->exists($path)) {
            return;
        }

        $image = $this->manager->read(Storage::disk('public')->get($path));
        Storage::disk('public')->put($path, $image->toJpeg(quality: $quality));
    }

    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            // Delete resized versions
            $pathInfo = pathinfo($path);
            $pattern = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_*.' . $pathInfo['extension'];

            $files = Storage::disk('public')->files($pathInfo['dirname']);
            foreach ($files as $file) {
                if (preg_match('/' . $pathInfo['filename'] . '_\w+\.' . $pathInfo['extension'] . '$/', $file)) {
                    Storage::disk('public')->delete($file);
                }
            }
        }
    }

    public function deleteMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    protected function sanitizeFileName(string $fileName): string
    {
        return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
    }

    public function getAvailableSizes(): array
    {
        return [
            'thumbnail' => ['width' => 400, 'height' => 300, 'fit' => 'cover'],
            'medium' => ['width' => 800, 'height' => 600, 'fit' => 'cover'],
            'large' => ['width' => 1200, 'height' => 800, 'fit' => 'cover'],
        ];
    }
}
