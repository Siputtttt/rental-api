<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    protected function saveBase64Image($base64Image, $directory, $max_size_mb = 2)
    {
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        $image_parts = explode(";base64,", $base64Image);
        if (count($image_parts) !== 2) {
            throw new \Exception('Invalid base64 image format');
        }

        $base64Data = $image_parts[1];
        $file_size_in_bytes = (strlen($base64Data) * 3) / 4;
        $fileSizeInMB = $file_size_in_bytes / (1024 * 1024);

        if ($fileSizeInMB > $max_size_mb) {
            throw new \Exception('Image size exceeds maximum allowed size of ' . $max_size_mb . ' MB');
        }

        $image_base64 = base64_decode($base64Data);

        $mimeType = explode(':', $image_parts[0])[1];
        $extension = explode('/', $mimeType)[1];

        $filename = time() . '_' . Str::random(10) . '.' . $extension;
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $image_base64);

        return $path;
    }
}
