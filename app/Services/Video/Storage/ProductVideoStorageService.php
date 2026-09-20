<?php

namespace App\Services\Video\Storage;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductVideoStorageService
{
    /*
    |--------------------------------------------------------------------------
    | S3 Disk
    |--------------------------------------------------------------------------
    */

    protected string $disk = 's3';


    /*
    |--------------------------------------------------------------------------
    | Base Paths
    |--------------------------------------------------------------------------
    */

    protected string $originalPath = 'product-videos/originals';

    protected string $processedPath = 'product-videos/processed';

    protected string $previewPath = 'product-videos/previews';

    protected string $thumbnailPath = 'product-videos/thumbnails';


    /*
    |--------------------------------------------------------------------------
    | Original Video
    |--------------------------------------------------------------------------
    */

    /**
     * 元動画をS3へ保存
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string S3 path
     */
    public function storeOriginal($file): string
    {
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $filename =
            Str::uuid()
            .'.'
            .$extension;

        $path = $this->originalPath
            .'/'
            .$filename;

        $stored = Storage::disk($this->disk)
            ->putFileAs(
                $this->originalPath,
                $file,
                $filename
            );

        if (!$stored) {

            throw new RuntimeException(
                '商品動画のS3保存に失敗しました。'
            );
        }

        return $stored;
    }


    /*
    |--------------------------------------------------------------------------
    | Processed Video
    |--------------------------------------------------------------------------
    */

    /**
     * 加工済み動画をS3へ保存
     *
     * @param string $localPath
     * @param string|null $filename
     * @return string
     */
    public function storeProcessed(
        string $localPath,
        ?string $filename = null
    ): string {

        if (!is_file($localPath)) {

            throw new RuntimeException(
                '加工済み動画のローカルファイルが存在しません。'
            );
        }

        $filename ??=
            Str::uuid()
            .'.mp4';

        $path =
            $this->processedPath
            .'/'
            .$filename;

        $stream = fopen(
            $localPath,
            'rb'
        );

        if ($stream === false) {

            throw new RuntimeException(
                '加工済み動画を開けません。'
            );
        }

        try {

            $stored = Storage::disk($this->disk)
                ->put(
                    $path,
                    $stream
                );

        } finally {

            fclose($stream);
        }

        if (!$stored) {

            throw new RuntimeException(
                '加工済み動画のS3保存に失敗しました。'
            );
        }

        return $path;
    }


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    /**
     * Preview動画をS3へ保存
     */
    public function storePreview(
        string $localPath,
        ?string $filename = null
    ): string {

        if (!is_file($localPath)) {

            throw new RuntimeException(
                'Preview動画のローカルファイルが存在しません。'
            );
        }

        $filename ??=
            Str::uuid()
            .'.mp4';

        $path =
            $this->previewPath
            .'/'
            .$filename;

        $stream = fopen(
            $localPath,
            'rb'
        );

        if ($stream === false) {

            throw new RuntimeException(
                'Preview動画を開けません。'
            );
        }

        try {

            $stored = Storage::disk($this->disk)
                ->put(
                    $path,
                    $stream
                );

        } finally {

            fclose($stream);
        }

        if (!$stored) {

            throw new RuntimeException(
                'Preview動画のS3保存に失敗しました。'
            );
        }

        return $path;
    }


    /*
    |--------------------------------------------------------------------------
    | Thumbnail
    |--------------------------------------------------------------------------
    */

    /**
     * サムネイルをS3へ保存
     */
    public function storeThumbnail(
        string $localPath,
        ?string $filename = null
    ): string {

        if (!is_file($localPath)) {

            throw new RuntimeException(
                'サムネイルのローカルファイルが存在しません。'
            );
        }

        $filename ??=
            Str::uuid()
            .'.jpg';

        $path =
            $this->thumbnailPath
            .'/'
            .$filename;

        $stream = fopen(
            $localPath,
            'rb'
        );

        if ($stream === false) {

            throw new RuntimeException(
                'サムネイルを開けません。'
            );
        }

        try {

            $stored = Storage::disk($this->disk)
                ->put(
                    $path,
                    $stream
                );

        } finally {

            fclose($stream);
        }

        if (!$stored) {

            throw new RuntimeException(
                'サムネイルのS3保存に失敗しました。'
            );
        }

        return $path;
    }


    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    public function exists(
        ?string $path
    ): bool {

        if (!$path) {
            return false;
        }

        return Storage::disk($this->disk)
            ->exists($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Size
    |--------------------------------------------------------------------------
    */

    /**
     * S3ファイルサイズを取得
     */
    public function size(
        string $path
    ): int {

        if (!$this->exists($path)) {

            throw new RuntimeException(
                "S3上にファイルが存在しません: {$path}"
            );
        }

        return Storage::disk($this->disk)
            ->size($path);
    }


    /*
    |--------------------------------------------------------------------------
    | Read Stream
    |--------------------------------------------------------------------------
    */

    public function readStream(
        string $path
    ) {

        if (!$this->exists($path)) {

            throw new RuntimeException(
                "S3上にファイルが存在しません: {$path}"
            );
        }

        return Storage::disk($this->disk)
            ->readStream($path);
    }


    /*
    |--------------------------------------------------------------------------
    | Temporary URL
    |--------------------------------------------------------------------------
    */

    public function temporaryUrl(
        string $path,
        int $minutes = 30
    ): string {

        if (!$this->exists($path)) {

            throw new RuntimeException(
                "S3上にファイルが存在しません: {$path}"
            );
        }

        return Storage::disk($this->disk)
            ->temporaryUrl(
                $path,
                now()->addMinutes($minutes)
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Download To Local
    |--------------------------------------------------------------------------
    */

    /**
     * S3動画をローカル一時ファイルへダウンロード
     *
     * FFmpeg / ffprobe 用
     */
    public function downloadToLocal(
        string $s3Path
    ): string {

        if (!$this->exists($s3Path)) {

            throw new RuntimeException(
                "S3上にファイルが存在しません: {$s3Path}"
            );
        }

        $extension =
            pathinfo(
                $s3Path,
                PATHINFO_EXTENSION
            );

        $localPath =
            storage_path(
                'app/video-temp/'
                .Str::uuid()
                .($extension
                    ? '.'.$extension
                    : '')
            );

        $directory =
            dirname($localPath);

        if (!is_dir($directory)) {

            mkdir(
                $directory,
                0755,
                true
            );
        }

        $readStream =
            Storage::disk($this->disk)
                ->readStream($s3Path);

        if ($readStream === false) {

            throw new RuntimeException(
                "S3ファイルの読み込みに失敗しました: {$s3Path}"
            );
        }

        $writeStream =
            fopen(
                $localPath,
                'wb'
            );

        if ($writeStream === false) {

            fclose($readStream);

            throw new RuntimeException(
                'ローカル一時ファイルを作成できません。'
            );
        }

        try {

            stream_copy_to_stream(
                $readStream,
                $writeStream
            );

        } finally {

            fclose($readStream);
            fclose($writeStream);
        }

        return $localPath;
    }


    /*
    |--------------------------------------------------------------------------
    | Upload From Local
    |--------------------------------------------------------------------------
    */

    /**
     * ローカルファイルを指定したS3パスへアップロード
     */
    public function uploadFromLocal(
        string $localPath,
        string $s3Path
    ): string {

        if (!is_file($localPath)) {

            throw new RuntimeException(
                "ローカルファイルが存在しません: {$localPath}"
            );
        }

        $stream = fopen(
            $localPath,
            'rb'
        );

        if ($stream === false) {

            throw new RuntimeException(
                "ローカルファイルを開けません: {$localPath}"
            );
        }

        try {

            $stored = Storage::disk($this->disk)
                ->put(
                    $s3Path,
                    $stream
                );

        } finally {

            fclose($stream);
        }

        if (!$stored) {

            throw new RuntimeException(
                "S3へのアップロードに失敗しました: {$s3Path}"
            );
        }

        return $s3Path;
    }


    /*
    |--------------------------------------------------------------------------
    | Copy
    |--------------------------------------------------------------------------
    */

    public function copy(
        string $from,
        string $to
    ): bool {

        if (!$this->exists($from)) {

            throw new RuntimeException(
                "コピー元がS3上に存在しません: {$from}"
            );
        }

        return Storage::disk($this->disk)
            ->copy(
                $from,
                $to
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ?string $path
    ): bool {

        if (!$path) {
            return true;
        }

        if (!$this->exists($path)) {
            return true;
        }

        return Storage::disk($this->disk)
            ->delete($path);
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Temp File
    |--------------------------------------------------------------------------
    */

    public function deleteTempFile(
        ?string $path
    ): void {

        if (!$path) {
            return;
        }

        if (is_file($path)) {

            @unlink($path);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Path
    |--------------------------------------------------------------------------
    */

    public function generateOriginalPath(
        string $extension = 'mp4'
    ): string {

        return $this->originalPath
            .'/'
            .Str::uuid()
            .'.'
            .strtolower($extension);
    }

    public function generateProcessedPath(
        string $extension = 'mp4'
    ): string {

        return $this->processedPath
            .'/'
            .Str::uuid()
            .'.'
            .strtolower($extension);
    }

    public function generatePreviewPath(
        string $extension = 'mp4'
    ): string {

        return $this->previewPath
            .'/'
            .Str::uuid()
            .'.'
            .strtolower($extension);
    }

    public function generateThumbnailPath(): string
    {
        return $this->thumbnailPath
            .'/'
            .Str::uuid()
            .'.jpg';
    }
}