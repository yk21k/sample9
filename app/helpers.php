<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('mediaUrl')) {
    function mediaUrl($path, $isBefore = false)
    {
        if (!$path) return null;

        // 旧データ（ローカル）
        if ($isBefore) {
            return asset('storage/versions/' . $path);
        }

        // 🔥 S3
        return \Storage::disk('s3')->url($path);
    }
}