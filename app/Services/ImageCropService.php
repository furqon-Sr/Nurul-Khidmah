<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ImageCropService
{
    /**
     * Aturan validasi koordinat crop (suffix: _crop_x, _crop_y, _crop_width, _crop_height, _cropped_data).
     *
     * @return array<string, array<int, string>>
     */
    public function cropRules(string $fieldName): array
    {
        return [
            "{$fieldName}_crop_x" => ['nullable', 'integer'],
            "{$fieldName}_crop_y" => ['nullable', 'integer'],
            "{$fieldName}_crop_width" => ['nullable', 'integer'],
            "{$fieldName}_crop_height" => ['nullable', 'integer'],
            "{$fieldName}_cropped_data" => ['nullable', 'string'],
        ];
    }

    public function hasCropData(Request $request, string $fieldName): bool
    {
        if ($request->filled("{$fieldName}_cropped_data")) {
            return true;
        }

        return $request->filled("{$fieldName}_crop_width")
            && $request->filled("{$fieldName}_crop_height")
            && (int) $request->input("{$fieldName}_crop_width") > 0
            && (int) $request->input("{$fieldName}_crop_height") > 0;
    }

    /**
     * Crop gambar sesuai koordinat dari Cropper.js (getData(true)) atau base64 canvas lalu simpan ke disk public.
     */
    public function storeCropped(
        UploadedFile $file,
        Request $request,
        string $fieldName,
        string $directory,
        int $quality = 90
    ): string {
        // High fidelity client-side cropped canvas (preserves user's exact zoom, rotation, free aspect ratio, and padding)
        $croppedData = $request->input("{$fieldName}_cropped_data");
        if (! empty($croppedData) && is_string($croppedData) && str_starts_with($croppedData, 'data:image/')) {
            return $this->storeBase64Cropped($croppedData, $directory, $quality);
        }

        if (! $this->hasCropData($request, $fieldName)) {
            throw new \InvalidArgumentException(
                "Koordinat crop untuk [{$fieldName}] wajib diisi. Pilih gambar, sesuaikan area crop, lalu klik Terapkan Crop."
            );
        }

        $x = (int) $request->input("{$fieldName}_crop_x", 0);
        $y = (int) $request->input("{$fieldName}_crop_y", 0);
        $width = (int) $request->input("{$fieldName}_crop_width");
        $height = (int) $request->input("{$fieldName}_crop_height");

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $filename = Str::uuid().'.'.$extension;
        $path = trim($directory, '/').'/'.$filename;

        
        // Load image using GD based on extension
        $realPath = $file->getRealPath();
        switch ($extension) {
            case 'png':
                $srcImage = imagecreatefrompng($realPath);
                if ($srcImage !== false) {
                    imagealphablending($srcImage, false);
                    imagesavealpha($srcImage, true);
                }
                break;
            case 'webp':
                $srcImage = imagecreatefromwebp($realPath);
                if ($srcImage !== false) {
                    imagealphablending($srcImage, false);
                    imagesavealpha($srcImage, true);
                }
                break;
            default:
                $srcImage = imagecreatefromjpeg($realPath);
        }
        if ($srcImage === false) {
            throw new \RuntimeException('Failed to create image resource from file');
        }

        // Clamp crop bounds to avoid negative coordinates / overflow on GD fallback
        $imgWidth = imagesx($srcImage);
        $imgHeight = imagesy($srcImage);
        $x = max(0, min($imgWidth - 1, $x));
        $y = max(0, min($imgHeight - 1, $y));
        $width = max(1, min($imgWidth - $x, $width));
        $height = max(1, min($imgHeight - $y, $height));

        // Perform crop
        $cropRect = ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height];
        $croppedImage = imagecrop($srcImage, $cropRect);
        imagedestroy($srcImage);
        if ($croppedImage === false) {
            throw new \RuntimeException('Failed to crop image');
        }
        
        // Auto-scale down if dimensions are excessively large to save memory and size
        $croppedWidth = imagesx($croppedImage);
        $croppedHeight = imagesy($croppedImage);
        $maxDimension = 1920; // Standard HD max dimension limit
        
        if ($croppedWidth > $maxDimension || $croppedHeight > $maxDimension) {
            if ($croppedWidth > $croppedHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($croppedHeight / $croppedWidth) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($croppedWidth / $croppedHeight) * $maxDimension);
            }
            $resizedImage = imagescale($croppedImage, $newWidth, $newHeight, IMG_BILINEAR);
            if ($resizedImage !== false) {
                imagedestroy($croppedImage);
                $croppedImage = $resizedImage;
            }
        }
        
        // Preserve alpha transparency for cropped PNG / WebP
        if ($extension === 'png' || $extension === 'webp') {
            imagealphablending($croppedImage, false);
            imagesavealpha($croppedImage, true);
        }

        // Auto-compress iterative loop to force file size below 2MB (2,097,152 bytes)
        $maxBytes = 2 * 1024 * 1024;
        $currentQuality = $quality;
        $encoded = '';

        do {
            ob_start();
            switch ($extension) {
                case 'png':
                    // PNG quality in GD: 0 (no compression) to 9 (maximum compression).
                    // As currentQuality goes down (e.g. from 90 to 50), compression index goes up.
                    $pngQuality = (int) round((100 - $currentQuality) / 11.1111);
                    imagepng($croppedImage, null, $pngQuality);
                    break;
                case 'webp':
                    imagewebp($croppedImage, null, $currentQuality);
                    break;
                default:
                    imagejpeg($croppedImage, null, $currentQuality);
            }
            $encoded = ob_get_clean();

            // Stop if file size is under 2MB or if quality is already low
            if (strlen($encoded) <= $maxBytes || $currentQuality <= 50) {
                break;
            }

            // Lower quality and try again
            $currentQuality -= 10;
        } while (true);

        // If it's STILL over 2MB, scale down the resolution further to force under 2MB
        if (strlen($encoded) > $maxBytes) {
            $scaleFactor = 0.7; // Scale down by 30%
            $currentWidth = imagesx($croppedImage);
            $currentHeight = imagesy($croppedImage);
            $newWidth = (int) round($currentWidth * $scaleFactor);
            $newHeight = (int) round($currentHeight * $scaleFactor);
            
            $resizedImage = imagescale($croppedImage, $newWidth, $newHeight, IMG_BILINEAR);
            if ($resizedImage !== false) {
                imagedestroy($croppedImage);
                $croppedImage = $resizedImage;
                
                if ($extension === 'png' || $extension === 'webp') {
                    imagealphablending($croppedImage, false);
                    imagesavealpha($croppedImage, true);
                }
                
                ob_start();
                switch ($extension) {
                    case 'png':
                        imagepng($croppedImage, null, 6);
                        break;
                    case 'webp':
                        imagewebp($croppedImage, null, 70);
                        break;
                    default:
                        imagejpeg($croppedImage, null, 70);
                }
                $encoded = ob_get_clean();
            }
        }

        imagedestroy($croppedImage);

        // Ensure target directory exists
        $directoryPath = dirname($path);
        if (!Storage::disk('public')->exists($directoryPath)) {
            Storage::disk('public')->makeDirectory($directoryPath);
        }

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Memproses string Base64 gambar hasil crop client-side (Cropper.js canvas)
     * menggunakan ekstensi PHP GD untuk validasi citra, pembatasan resolusi, dan kompresi < 2MB.
     */
    public function storeBase64Cropped(
        string $base64Data,
        string $directory = 'photos',
        int $quality = 90
    ): string {
        if (! preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            throw new \InvalidArgumentException('Format gambar tidak valid.');
        }

        $extension = strtolower($type[1]);
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \InvalidArgumentException('Format gambar harus berupa JPG, PNG, atau WEBP.');
        }
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $rawBytes = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1), true);
        if ($rawBytes === false) {
            throw new \InvalidArgumentException('Gagal membaca data gambar.');
        }

        $image = @imagecreatefromstring($rawBytes);
        if ($image === false) {
            throw new \InvalidArgumentException('Berkas gambar rusak atau tidak valid.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxDimension = 1920;

        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width > $height) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($height / $width) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($width / $height) * $maxDimension);
            }
            $scaled = imagescale($image, $newWidth, $newHeight, IMG_BILINEAR);
            if ($scaled !== false) {
                imagedestroy($image);
                $image = $scaled;
            }
        }

        if ($extension === 'png' || $extension === 'webp') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $maxBytes = 2 * 1024 * 1024;
        $currentQuality = $quality;
        $encoded = '';

        do {
            ob_start();
            switch ($extension) {
                case 'png':
                    $pngLevel = (int) round((100 - $currentQuality) / 11.1111);
                    imagepng($image, null, $pngLevel);
                    break;
                case 'webp':
                    imagewebp($image, null, $currentQuality);
                    break;
                default:
                    imagejpeg($image, null, $currentQuality);
            }
            $encoded = (string) ob_get_clean();

            if (strlen($encoded) <= $maxBytes || $currentQuality <= 50) {
                break;
            }
            $currentQuality -= 10;
        } while (true);

        imagedestroy($image);

        $filename = Str::uuid().'.'.$extension;
        $path = trim($directory, '/').'/'.$filename;

        if (! Storage::disk('public')->exists(dirname($path))) {
            Storage::disk('public')->makeDirectory(dirname($path));
        }

        Storage::disk('public')->put($path, $encoded);

        return $path;
    }
}
