<?php

namespace App\Services\Video\Storage;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Contracts\Filesystem\Filesystem;
use RuntimeException;

class VideoStorageService
{
    protected string $disk = 's3';

    /**
     * ディスク取得
     */
    protected function disk(): Filesystem
    {
        return Storage::disk(
            $this->disk
        );
    }

    /**
     * ファイル存在確認
     */
    public function exists(
        string $path
    ): bool {
        return $this->disk()->exists(
            $path
        );
    }

    /**
     * ファイル読み込み
     */
    public function readStream(
        string $path
    ) {
        return $this->disk()->readStream(
            $path
        );
    }

    /**
     * S3ファイルをローカル一時ファイルへ保存
     *
     * 動画全体をメモリに読み込まず、
     * ストリームのままローカルディスクへコピーする。
     */
    public function downloadToLocal(
        string $s3Path
    ): string {

        if (!$this->exists($s3Path)) {
            throw new RuntimeException(
                'S3上にファイルが存在しません: '
                . $s3Path
            );
        }

        $directory = storage_path(
            'app/video-temp'
        );

        if (!is_dir($directory)) {

            if (!mkdir(
                $directory,
                0775,
                true
            ) && !is_dir($directory)) {

                throw new RuntimeException(
                    '動画一時ディレクトリを作成できません。'
                );
            }
        }

        $localPath = tempnam(
            $directory,
            'video_'
        );

        if ($localPath === false) {
            throw new RuntimeException(
                '動画一時ファイルを作成できません。'
            );
        }

        $input = null;
        $output = null;

        try {

            $input = $this->disk()->readStream(
                $s3Path
            );

            if ($input === false) {
                throw new RuntimeException(
                    'S3から動画ストリームを取得できません: '
                    . $s3Path
                );
            }

            $output = fopen(
                $localPath,
                'wb'
            );

            if ($output === false) {
                throw new RuntimeException(
                    '動画一時ファイルを開けません: '
                    . $localPath
                );
            }

            $copied = stream_copy_to_stream(
                $input,
                $output
            );

            if ($copied === false) {
                throw new RuntimeException(
                    'S3からローカルへの動画コピーに失敗しました。'
                );
            }

            fflush($output);

            logger()->info(
                'Video downloaded to local temp',
                [
                    's3_path' => $s3Path,
                    'local_path' => $localPath,
                    'bytes' => $copied,
                ]
            );

            return $localPath;

        } catch (\Throwable $e) {

            if (
                $localPath &&
                file_exists($localPath)
            ) {
                @unlink($localPath);
            }

            throw $e;

        } finally {

            if (is_resource($input)) {
                fclose($input);
            }

            if (is_resource($output)) {
                fclose($output);
            }
        }
    }

    /**
     * ローカルファイルをS3へ保存
     *
     * ストリームでアップロードするため、
     * ファイル全体をPHPメモリに読み込まない。
     */
    public function uploadFromLocal(
        string $localPath,
        string $s3Path,
        ?string $contentType = null
    ): void {

        if (!is_file($localPath)) {
            throw new RuntimeException(
                'ローカルファイルが存在しません: '
                . $localPath
            );
        }

        $stream = fopen(
            $localPath,
            'rb'
        );

        if ($stream === false) {
            throw new RuntimeException(
                'ローカルファイルを開けません: '
                . $localPath
            );
        }

        try {

            $options = [];

            if ($contentType !== null) {
                $options['ContentType'] = $contentType;
            }

            $result = $this->disk()->put(
                $s3Path,
                $stream,
                $options
            );

            if (!$result) {
                throw new RuntimeException(
                    'S3へのファイルアップロードに失敗しました: '
                    . $s3Path
                );
            }

            logger()->info(
                'Video file uploaded from local',
                [
                    'local_path' => $localPath,
                    's3_path' => $s3Path,
                    'content_type' => $contentType,
                    'bytes' => filesize($localPath),
                ]
            );

        } finally {

            fclose($stream);
        }
    }

    /**
     * ファイルコピー
     */
    public function copy(
        string $from,
        string $to
    ): bool {
        return $this->disk()->copy(
            $from,
            $to
        );
    }

    /**
     * ファイル削除
     */
    public function delete(
        string $path
    ): bool {
        return $this->disk()->delete(
            $path
        );
    }

    /**
     * 一時URL
     */
    public function temporaryUrl(
        string $path,
        int $minutes = 30
    ): string {
        return $this->disk()->temporaryUrl(
            $path,
            now()->addMinutes(
                $minutes
            )
        );
    }

    /**
     * 保存パス生成
     */
    public function generatePath(
        string $category,
        string $type,
        string $extension = 'mp4'
    ): string {
        return sprintf(
            '%s-videos/%s/%s.%s',
            $category,
            $type,
            Str::uuid(),
            $extension
        );
    }

    /**
     * ファイルサイズ
     */
    public function size(
        string $path
    ): int {
        return $this->disk()->size(
            $path
        );
    }

    /**
     * ローカル一時ファイル削除
     */
    public function deleteTempFile(
        ?string $path
    ): void {

        if (
            !$path ||
            !file_exists($path)
        ) {
            return;
        }

        if (!unlink($path)) {
            logger()->warning(
                'Video temp file delete failed',
                [
                    'path' => $path,
                ]
            );
        }
    }
}