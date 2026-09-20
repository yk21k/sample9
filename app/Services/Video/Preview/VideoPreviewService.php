<?php

namespace App\Services\Video\Preview;

use App\Models\ShopVideoDraft;
use App\Services\Video\Storage\VideoStorageService;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class VideoPreviewService
{
    public function __construct(
        private VideoStorageService $storage
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
        ShopVideoDraft $video
    ): array {
        if (!$video->original_movie) {
            throw new RuntimeException(
                'Original movie does not exist.'
            );
        }

        if (!$this->storage->exists(
            $video->original_movie
        )) {
            throw new RuntimeException(
                'Original movie does not exist on S3.'
            );
        }

        $workDirectory = storage_path(
            'app/video-preview/' . Str::uuid()
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

            $this->downloadOriginalMovie(
                $video->original_movie,
                $originalPath
            );

            logger()->debug('download completed');

            $this->generatePreviewMovie(
                $originalPath,
                $previewLocalPath
            );

            logger()->debug('preview completed');

            $this->generateThumbnail(
                $originalPath,
                $thumbnailLocalPath
            );

            logger()->debug('thumbnail completed');


            $previewPath =
                $this->storage->generatePath(
                    'shop',
                    'preview'
                );

            $thumbnailPath =
                $this->storage->generatePath(
                    'shop',
                    'thumbnail',
                    'jpg'
                );

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
        $stream = $this->storage->readStream($sourcePath);

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

            logger()->error('Preview Error', [
                'input' => $input,
                'output' => $output,
                'error' => $process->getErrorOutput(),
            ]);

            throw $e;
        }
    }

    private function generateThumbnail(
        string $input,
        string $output
    ): void {

        logger()->debug(config('video'));
        
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

            logger()->error('Thumbnail Error');
            logger()->error($process->getErrorOutput());

            throw $e;

        }
    }

    private function deleteWorkDirectory(
        string $directory
    ): void {

        if (!is_dir($directory)) {

            return;

        }

        foreach (glob($directory . '/*') as $file) {

            if (is_file($file)) {

                unlink($file);

            }

        }

        rmdir($directory);
    }


}