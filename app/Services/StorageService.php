<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Upload an image or file to public/uploads directory.
     */
    public function uploadToPublic(UploadedFile $file, string $folder = 'images'): string
    {
        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $targetDir = public_path("uploads/{$folder}");

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return "/uploads/{$folder}/{$filename}";
    }
}
