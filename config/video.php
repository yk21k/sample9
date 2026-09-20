<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FFmpeg
    |--------------------------------------------------------------------------
    */

    'ffmpeg_path' => env(
        'FFMPEG_PATH',
        '/opt/homebrew/bin/ffmpeg'
    ),

    'ffprobe_path' => env(
        'FFPROBE_PATH',
        '/opt/homebrew/bin/ffprobe'
    ),

    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    'preview_duration' => env(
        'VIDEO_PREVIEW_DURATION',
        15
    ),

    'thumbnail_second' => 0.5,

];