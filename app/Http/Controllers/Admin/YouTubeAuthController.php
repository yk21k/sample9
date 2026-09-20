<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client;
use App\Services\Video\YouTube\YouTubeService;

class YouTubeAuthController extends Controller
{
    /**
     * YouTube OAuth開始
     */
    public function connect()
    {
        $client = new Client();

        $client->setClientId(
            config('services.google.youtube.client_id')
        );

        $client->setClientSecret(
            config('services.google.youtube.client_secret')
        );

        $client->setRedirectUri(
            config('services.google.youtube.redirect_uri')
        );

        $client->setAccessType('offline');

        $client->setPrompt('consent');

        $client->setScopes([
            'https://www.googleapis.com/auth/youtube',
        ]);

        return redirect()->away(
            $client->createAuthUrl()
        );
    }

    /**
     * YouTube OAuth callback
     */
    public function callback(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Google OAuth エラー
        |--------------------------------------------------------------------------
        */

        if ($request->has('error')) {

            return response()->json([
                'error' => $request->get('error'),
                'error_description'
                    => $request->get('error_description'),
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Authorization Code確認
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $request->filled('code'),
            400,
            'Authorization code がありません。'
        );

        /*
        |--------------------------------------------------------------------------
        | Google Client
        |--------------------------------------------------------------------------
        */

        $client = new Client();

        $client->setClientId(
            config('services.google.youtube.client_id')
        );

        $client->setClientSecret(
            config('services.google.youtube.client_secret')
        );

        $client->setRedirectUri(
            config('services.google.youtube.redirect_uri')
        );

        $client->setAccessType('offline');

        /*
        |--------------------------------------------------------------------------
        | Refresh Tokenを確実に取得するため再同意を要求
        |--------------------------------------------------------------------------
        */

        $client->setPrompt('consent');

        $client->setScopes([
            'https://www.googleapis.com/auth/youtube',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Authorization Code → Token
        |--------------------------------------------------------------------------
        */

        $token = $client->fetchAccessTokenWithAuthCode(
            $request->get('code')
        );

        /*
        |--------------------------------------------------------------------------
        | Google OAuthエラー
        |--------------------------------------------------------------------------
        */

        if (isset($token['error'])) {

            return response()->json([
                'error' => $token['error'],
                'error_description'
                    => $token['error_description'] ?? null,
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh Token取得
        |--------------------------------------------------------------------------
        */

        $refreshToken = $token['refresh_token'] ?? null;

        abort_unless(
            $refreshToken,
            400,
            'Refresh Tokenを取得できませんでした。'
        );

        /*
        |--------------------------------------------------------------------------
        | .envへ保存
        |--------------------------------------------------------------------------
        */

        $envPath = base_path('.env');

        abort_unless(
            file_exists($envPath),
            500,
            '.envファイルが見つかりません。'
        );

        $env = file_get_contents($envPath);

        /*
        |--------------------------------------------------------------------------
        | 既存のGOOGLE_YOUTUBE_REFRESH_TOKENを置換
        |--------------------------------------------------------------------------
        */

        $updated = preg_replace(
            '/^GOOGLE_YOUTUBE_REFRESH_TOKEN=.*$/m',
            'GOOGLE_YOUTUBE_REFRESH_TOKEN=' . $refreshToken,
            $env,
            1,
            $count
        );

        /*
        |--------------------------------------------------------------------------
        | .envに存在しない場合は追加
        |--------------------------------------------------------------------------
        */

        if ($count === 0) {

            $updated = rtrim($env) . PHP_EOL
                . 'GOOGLE_YOUTUBE_REFRESH_TOKEN='
                . $refreshToken
                . PHP_EOL;
        }

        /*
        |--------------------------------------------------------------------------
        | .env保存
        |--------------------------------------------------------------------------
        */

        file_put_contents(
            $envPath,
            $updated,
            LOCK_EX
        );

        /*
        |--------------------------------------------------------------------------
        | 確認用ログ
        |--------------------------------------------------------------------------
        |
        | Refresh Token本体は絶対にログへ出さない
        |
        */

        \Log::info('YouTube OAuth Refresh Token saved', [
            'refresh_token_length' => strlen($refreshToken),
            'refresh_token_tail' => substr($refreshToken, -10),
            'scope' => $token['scope'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 結果
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'YouTube OAuth認証に成功し、Refresh Tokenを.envへ保存しました。',
            'has_access_token' => !empty($token['access_token']),
            'has_refresh_token' => true,
            'refresh_token_length' => strlen($refreshToken),
            'refresh_token_tail' => substr($refreshToken, -10),
            'token_type' => $token['token_type'] ?? null,
            'expires_in' => $token['expires_in'] ?? null,
            'scope' => $token['scope'] ?? null,
        ]);
    }


    /**
     * YouTube接続テスト
     */
    public function test()
    {
        $youtubeService = app(
            \App\Services\Video\YouTube\YouTubeService::class
        );

        $channel = $youtubeService->getChannel();

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'YouTubeチャンネルを取得できませんでした。',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'YouTubeチャンネル取得に成功しました。',
            'channel' => [
                'id' => $channel->getId(),
                'title' => $channel->getSnippet()?->getTitle(),
                'description' => $channel->getSnippet()?->getDescription(),
            ],
        ]);
    }

    /**
     * YouTube Resumable Upload テスト
     */
    public function uploadTest()
    {
        $videoPath = storage_path(
            'app/youtube-test/source-100mb.mp4'
        );

        $result = app(
            \App\Services\Video\YouTube\YouTubeService::class
        )->uploadTestVideo(
            videoPath: $videoPath,
            title: 'Resumable Upload Test 88MB',
            description: 'YouTube Resumable Uploadのテスト動画です。'
        );

        return response()->json([
            'success' => true,
            'message' => 'YouTube Resumable Uploadに成功しました。',
            'result' => $result,
        ]);
    }

    /**
     * S3 → ローカル一時ファイル テスト
     */
    public function downloadVideoTest()
    {
        $path = 'shop-videos/original/8UZwyy5Vb7QVvvn7Wl4won0JPQJDbFxGpAXtiZbQ.mp4';

        $storage = app(
            \App\Services\Video\Storage\VideoStorageService::class
        );

        $tempPath = $storage->downloadToTempFile(
            $path
        );

        return response()->json([
            'success' => true,
            'temp_path' => $tempPath,
            'size' => filesize($tempPath),
        ]);
    }
}