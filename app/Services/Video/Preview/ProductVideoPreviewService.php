<?php

namespace App\Services\Video\Preview;

use App\Models\ProductVideoDraft;
use App\Services\Video\Storage\ProductVideoStorageService;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ProductVideoPreviewService
{
    public function __construct(
        private ProductVideoStorageService $storage
    ) {
    }

    /**
     * Preview動画とサムネイルを生成する
     *
     * @return array{
     *     preview_movie: string,
     *     thumbnail: string
     * }
     */
    public function generate(
        ProductVideoDraft $video
    ): array {
        if (!$video->processed_movie) {
            throw new RuntimeException(
                'Processed movie does not exist.'
            );
        }

        if (!$this->storage->exists(
            $video->processed_movie
        )) {
            throw new RuntimeException(
                'Processed movie does not exist on S3.'
            );
        }

        $workDirectory = storage_path(
            'app/product-video-preview/' . Str::uuid()
        );

        if (!is_dir($workDirectory)) {
            mkdir(
                $workDirectory,
                0755,
                true
            );
        }

        try {

            $originalPath =
                $workDirectory . '/original.mp4';

            $previewLocalPath =
                $workDirectory . '/preview.mp4';

            $thumbnailLocalPath =
                $workDirectory . '/thumbnail.jpg';

            /*
            |--------------------------------------------------------------------------
            | S3 → ローカル
            |--------------------------------------------------------------------------
            */

            $this->downloadOriginalMovie(
                $video->processed_movie,
                $originalPath
            );

            logger()->debug(
                'Product video preview: download completed'
            );

            /*
            |--------------------------------------------------------------------------
            | Preview動画生成
            |--------------------------------------------------------------------------
            */

            $this->generatePreviewMovie(
                $originalPath,
                $previewLocalPath
            );

            logger()->debug(
                'Product video preview: preview completed'
            );

            /*
            |--------------------------------------------------------------------------
            | サムネイル生成
            |--------------------------------------------------------------------------
            */

            $this->generateThumbnail(
                $originalPath,
                $thumbnailLocalPath
            );

            logger()->debug(
                'Product video preview: thumbnail completed'
            );

            /*
            |--------------------------------------------------------------------------
            | Product用S3パス
            |--------------------------------------------------------------------------
            */

            $previewPath =
                $this->storage->generatePreviewPath();

            $thumbnailPath =
                $this->storage->generateThumbnailPath();

            /*
            |--------------------------------------------------------------------------
            | ローカル → S3
            |--------------------------------------------------------------------------
            */

            $this->storage->uploadFromLocal(
                $previewLocalPath,
                $previewPath
            );

            $this->storage->uploadFromLocal(
                $thumbnailLocalPath,
                $thumbnailPath
            );

            return [

                'preview_movie'
                    => $previewPath,

                'thumbnail'
                    => $thumbnailPath,

            ];

        } finally {

            $this->deleteWorkDirectory(
                $workDirectory
            );
        }
    }

    /**
     * S3から元動画をダウンロード
     */
    private function downloadOriginalMovie(
        string $sourcePath,
        string $destinationPath
    ): void {
        $stream =
            $this->storage->readStream(
                $sourcePath
            );

        if ($stream === false) {
            throw new RuntimeException(
                'Failed to read original movie.'
            );
        }

        $destination = fopen(
            $destinationPath,
            'w'
        );

        if ($destination === false) {

            fclose($stream);

            throw new RuntimeException(
                'Failed to create local movie file.'
            );
        }

        stream_copy_to_stream(
            $stream,
            $destination
        );

        fclose($stream);
        fclose($destination);
    }

    /**
     * Preview動画生成
     */
    private function generatePreviewMovie(
        string $input,
        string $output
    ): void {
        $process = new Process([

            config('video.ffmpeg_path'),

            '-y',

            '-i',
            $input,

            '-t',
            config('video.preview_duration'),

            '-vf',
            'scale=-2:720',

            '-c:v',
            'libx264',

            '-preset',
            'veryfast',

            '-crf',
            '28',

            '-c:a',
            'aac',

            '-b:a',
            '128k',

            $output,

        ]);

        try {

            $process->mustRun();

        } catch (\Throwable $e) {

            logger()->error(
                'Product Video Preview Error',
                [
                    'input' => $input,
                    'output' => $output,
                    'error'
                        => $process->getErrorOutput(),
                ]
            );

            throw $e;
        }
    }

    /**
     * サムネイル生成
     */
    private function generateThumbnail(
        string $input,
        string $output
    ): void {

        $process = new Process([

            config('video.ffmpeg_path'),

            '-y',

            '-ss',
            config('video.thumbnail_second'),

            '-i',
            $input,

            '-frames:v',
            '1',

            $output,

        ]);

        try {

            $process->mustRun();

        } catch (\Throwable $e) {

            logger()->error(
                'Product Video Thumbnail Error',
                [
                    'error'
                        => $process->getErrorOutput(),
                ]
            );

            throw $e;
        }
    }

    /**
     * 作業ディレクトリ削除
     */
    private function deleteWorkDirectory(
        string $directory
    ): void {

        if (!is_dir($directory)) {
            return;
        }

        foreach (
            glob($directory . '/*')
            as $file
        ) {

            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($directory);
    }
}