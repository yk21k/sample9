<?php

namespace App\Services\Video\YouTube;

use App\Models\ShopVideoDraft;
use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\VideoSnippet;
use Google\Service\YouTube\VideoStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVideoDraft;

class YouTubeService
{
    /**
     * YouTube API Clientを取得
     */
    protected function client(): Client
    {
        $client = new Client();

        /*
        |--------------------------------------------------------------------------
        | OAuth Client設定
        |--------------------------------------------------------------------------
        */

        $client->setClientId(
            config('services.google.youtube.client_id')
        );

        $client->setClientSecret(
            config('services.google.youtube.client_secret')
        );

        $client->setAccessType('offline');

        $client->setScopes([
            'https://www.googleapis.com/auth/youtube.upload',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Refresh Token
        |--------------------------------------------------------------------------
        */

        $refreshToken = config(
            'services.google.youtube.refresh_token'
        );

        if (!$refreshToken) {
            throw new \RuntimeException(
                'YouTube Refresh Tokenが設定されていません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | OAuth設定確認
        |--------------------------------------------------------------------------
        */

        Log::debug('YouTube OAuth refresh token check', [
            'refresh_token_length' => strlen($refreshToken),
            'refresh_token_tail' => substr($refreshToken, -10),
        ]);

        Log::debug('YouTube OAuth identity check', [
            'client_id' => $client->getClientId(),

            'client_secret_length' => strlen(
                (string) $client->getClientSecret()
            ),

            'refresh_token_length' => strlen(
                (string) $refreshToken
            ),

            'redirect_uri' => config(
                'services.google.youtube.redirect_uri'
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Refresh Token → Access Token
        |--------------------------------------------------------------------------
        */

        $token = $client->fetchAccessTokenWithRefreshToken(
            $refreshToken
        );

        Log::debug('YouTube refresh token result', [
            'has_access_token' => !empty(
                $token['access_token']
            ),

            'keys' => array_keys($token),

            'error' => $token['error'] ?? null,

            'error_description' =>
                $token['error_description'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | OAuthエラー
        |--------------------------------------------------------------------------
        */

        if (isset($token['error'])) {
            throw new \RuntimeException(
                'YouTube OAuth Token取得失敗: '
                . (
                    $token['error_description']
                    ?? $token['error']
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Access Token確認
        |--------------------------------------------------------------------------
        */

        if (empty($token['access_token'])) {
            throw new \RuntimeException(
                'YouTube Access Tokenを取得できませんでした。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Access Token設定
        |--------------------------------------------------------------------------
        */

        $client->setAccessToken($token);

        return $client;
    }

    /**
     * YouTube API Serviceを取得
     */
    public function youtube(): YouTube
    {
        return new YouTube(
            $this->client()
        );
    }

    /**
     * 認証済みYouTubeチャンネル情報取得
     */
    public function getChannel(): ?YouTube\Channel
    {
        $youtube = $this->youtube();

        $response = $youtube->channels->listChannels(
            'snippet,contentDetails,statistics',
            [
                'mine' => true,
            ]
        );

        Log::debug('YouTube channels.list result', [
            'page_info' => $response->getPageInfo()
                ? [
                    'total_results' =>
                        $response->getPageInfo()->getTotalResults(),

                    'results_per_page' =>
                        $response->getPageInfo()->getResultsPerPage(),
                ]
                : null,

            'item_count' => count(
                $response->getItems()
            ),
        ]);

        $items = $response->getItems();

        return $items[0] ?? null;
    }

    /**
     * ShopVideoDraftをYouTubeへアップロード
     *
     * Resumable Uploadを使用する。
     */
    public function upload(
        ShopVideoDraft|ProductVideoDraft $video
    ): array {

        /*
        |--------------------------------------------------------------------------
        | processed_movie確認
        |--------------------------------------------------------------------------
        */

        if (!$video->processed_movie) {
            throw new \RuntimeException(
                'YouTubeアップロード対象の加工済み動画がありません。'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | S3確認
        |--------------------------------------------------------------------------
        */

        $disk = Storage::disk('s3');

        if (!$disk->exists($video->processed_movie)) {
            throw new \RuntimeException(
                'S3上に加工済み動画が存在しません: '
                . $video->processed_movie
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 一時ディレクトリ
        |--------------------------------------------------------------------------
        */

        $directory = storage_path(
            'app/youtube-upload'
        );

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new \RuntimeException(
                    'YouTubeアップロード用一時ディレクトリを作成できませんでした。'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 一時ファイル
        |--------------------------------------------------------------------------
        */

        $temporaryPath =
            $directory
            . '/'
            . $video->id
            . '-'
            . uniqid('', true)
            . '.mp4';

        try {

            /*
            |--------------------------------------------------------------------------
            | S3 → Local
            |--------------------------------------------------------------------------
            |
            | get()ではなくreadStream()を使用して、
            | S3動画全体をPHPメモリに載せない。
            |
            */

            $sourceStream = $disk->readStream(
                $video->processed_movie
            );

            if (!is_resource($sourceStream)) {
                throw new \RuntimeException(
                    'S3動画の読み込みストリームを取得できませんでした。'
                );
            }

            $destinationStream = fopen(
                $temporaryPath,
                'wb'
            );

            if ($destinationStream === false) {
                fclose($sourceStream);

                throw new \RuntimeException(
                    'YouTubeアップロード用一時ファイルを作成できませんでした。'
                );
            }

            try {

                stream_copy_to_stream(
                    $sourceStream,
                    $destinationStream
                );

            } finally {

                fclose($sourceStream);
                fclose($destinationStream);
            }

            /*
            |--------------------------------------------------------------------------
            | Localファイル確認
            |--------------------------------------------------------------------------
            */

            if (!is_file($temporaryPath)) {
                throw new \RuntimeException(
                    'YouTubeアップロード用一時ファイルが存在しません。'
                );
            }

            $fileSize = filesize($temporaryPath);

            if ($fileSize === false || $fileSize <= 0) {
                throw new \RuntimeException(
                    'YouTubeアップロード用動画ファイルが空です。'
                );
            }

            Log::info('YouTube resumable upload start', [
                'video_type' => $video instanceof ProductVideoDraft
                    ? 'product'
                    : 'shop',
                'video_id' => $video->id,
                'file_size' => $fileSize,
                'file_size_mb' =>
                    round($fileSize / 1024 / 1024, 2),
            ]);

            /*
            |--------------------------------------------------------------------------
            | YouTube Resumable Upload
            |--------------------------------------------------------------------------
            */

            $videoId = $this->uploadVideoResumable(
                videoPath: $temporaryPath,
                title: $video->title,
                description: $video->description,
                privacyStatus: 'private'
            );

            /*
            |--------------------------------------------------------------------------
            | 結果
            |--------------------------------------------------------------------------
            */

            return [
                'video_id' => $videoId,
                'privacy_status' => 'private',
            ];

        } finally {

            /*
            |--------------------------------------------------------------------------
            | 一時ファイル削除
            |--------------------------------------------------------------------------
            */

            if (file_exists($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    /**
     * YouTube Resumable Upload
     */
    protected function uploadVideoResumable(
        string $videoPath,
        string $title,
        ?string $description = null,
        string $privacyStatus = 'private'
    ): string {

        if (!is_file($videoPath)) {
            throw new \RuntimeException(
                'YouTubeアップロード対象動画が存在しません: '
                . $videoPath
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Client
        |--------------------------------------------------------------------------
        */

        $client = $this->client();

        /*
        |--------------------------------------------------------------------------
        | YouTube Service
        |--------------------------------------------------------------------------
        */

        $youtube = new YouTube(
            $client
        );

        /*
        |--------------------------------------------------------------------------
        | Video Metadata
        |--------------------------------------------------------------------------
        */

        $video = new Video();

        /*
        |--------------------------------------------------------------------------
        | Snippet
        |--------------------------------------------------------------------------
        */

        $snippet = new VideoSnippet();

        $snippet->setTitle(
            $title
        );

        $snippet->setDescription(
            $description ?? ''
        );

        /*
        | YouTubeカテゴリ
        |
        | 22 = People & Blogs
        */

        $snippet->setCategoryId(
            '22'
        );

        $video->setSnippet(
            $snippet
        );

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $status = new VideoStatus();

        $status->setPrivacyStatus(
            $privacyStatus
        );

        $video->setStatus(
            $status
        );

        /*
        |--------------------------------------------------------------------------
        | Resumable Upload開始
        |--------------------------------------------------------------------------
        */

        $client->setDefer(
            true
        );

        try {

            $request = $youtube->videos->insert(
                'snippet,status',
                $video
            );

            /*
            |--------------------------------------------------------------------------
            | ファイルサイズ
            |--------------------------------------------------------------------------
            */

            $fileSize = filesize(
                $videoPath
            );

            if ($fileSize === false) {
                throw new \RuntimeException(
                    '動画ファイルサイズを取得できませんでした。'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Media Upload
            |--------------------------------------------------------------------------
            */

            $media = new \Google\Http\MediaFileUpload(
                $client,
                $request,
                'video/mp4',
                null,
                true,
                8 * 1024 * 1024
            );

            $media->setFileSize(
                $fileSize
            );

            /*
            |--------------------------------------------------------------------------
            | ファイル読み込み
            |--------------------------------------------------------------------------
            */

            $handle = fopen(
                $videoPath,
                'rb'
            );

            if ($handle === false) {
                throw new \RuntimeException(
                    '動画ファイルを読み込めませんでした。'
                );
            }

            $statusResponse = null;

            try {

                while (!$statusResponse && !feof($handle)) {

                    $chunk = fread(
                        $handle,
                        8 * 1024 * 1024
                    );

                    if ($chunk === false) {
                        throw new \RuntimeException(
                            '動画ファイルの読み込みに失敗しました。'
                        );
                    }

                    if ($chunk === '') {
                        break;
                    }

                    $statusResponse = $media->nextChunk(
                        $chunk
                    );

                    Log::debug(
                        'YouTube resumable upload chunk',
                        [
                            'video_path' => $videoPath,
                            'uploaded_bytes' =>
                                ftell($handle),
                            'file_size' => $fileSize,
                        ]
                    );
                }

            } finally {

                fclose($handle);
            }

            /*
            |--------------------------------------------------------------------------
            | Upload完了確認
            |--------------------------------------------------------------------------
            */

            if (!$statusResponse) {
                throw new \RuntimeException(
                    'YouTube動画アップロードが完了しませんでした。'
                );
            }

            $uploadedVideoId =
                $statusResponse->getId();

            if (!$uploadedVideoId) {
                throw new \RuntimeException(
                    'YouTube動画IDを取得できませんでした。'
                );
            }

            Log::info(
                'YouTube resumable upload completed',
                [
                    'video_id' => $uploadedVideoId,
                    'title' => $title,
                ]
            );

            return $uploadedVideoId;

        } finally {

            /*
            |--------------------------------------------------------------------------
            | Defer解除
            |--------------------------------------------------------------------------
            */

            $client->setDefer(
                false
            );
        }
    }

    /**
     * Resumable Upload 単体テスト
     *
     * ローカルのテスト動画をYouTubeへ非公開でアップロードする。
     */
    public function uploadTestVideo(
        string $videoPath,
        string $title = 'Resumable Upload Test',
        ?string $description = null
    ): array {

        if (!is_file($videoPath)) {
            throw new \RuntimeException(
                'テスト動画が存在しません: ' . $videoPath
            );
        }

        $fileSize = filesize($videoPath);

        if ($fileSize === false || $fileSize <= 0) {
            throw new \RuntimeException(
                'テスト動画のファイルサイズを取得できません。'
            );
        }

        Log::info('YouTube resumable test upload start', [
            'file' => $videoPath,
            'file_size' => $fileSize,
            'file_size_mb' => round(
                $fileSize / 1024 / 1024,
                2
            ),
        ]);

        $videoId = $this->uploadVideoResumable(
            videoPath: $videoPath,
            title: $title,
            description: $description,
            privacyStatus: 'private'
        );

        Log::info('YouTube resumable test upload completed', [
            'video_id' => $videoId,
        ]);

        return [
            'video_id' => $videoId,
            'privacy_status' => 'private',
        ];
    }
}