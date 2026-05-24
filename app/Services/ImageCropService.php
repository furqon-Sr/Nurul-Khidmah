<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ImageCropService
{
    /**
     * Aturan validasi koordinat crop (suffix: _crop_x, _crop_y, _crop_width, _crop_height).
     *
     * @return array<string, array<int, string>>
     */
    public function cropRules(string $fieldName): array
    {
        return [
            "{$fieldName}_crop_x" => ['required_with:'.$fieldName, 'nullable', 'integer', 'min:0'],
            "{$fieldName}_crop_y" => ['required_with:'.$fieldName, 'nullable', 'integer', 'min:0'],
            "{$fieldName}_crop_width" => ['required_with:'.$fieldName, 'nullable', 'integer', 'min:1'],
            "{$fieldName}_crop_height" => ['required_with:'.$fieldName, 'nullable', 'integer', 'min:1'],
        ];
    }

    public function hasCropData(Request $request, string $fieldName): bool
    {
        return $request->filled("{$fieldName}_crop_width")
            && $request->filled("{$fieldName}_crop_height")
            && (int) $request->input("{$fieldName}_crop_width") > 0
            && (int) $request->input("{$fieldName}_crop_height") > 0;
    }

    /**
     * Crop gambar sesuai koordinat dari Cropper.js (getData(true)) lalu simpan ke disk public.
     */
    public function storeCropped(
        UploadedFile $file,
        Request $request,
        string $fieldName,
        string $directory,
        int $quality = 90
    ): string {
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
}
