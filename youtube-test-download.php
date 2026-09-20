<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(
    \Illuminate\Contracts\Console\Kernel::class
)->bootstrap();

use Illuminate\Support\Facades\Storage;

$source = 'shop-videos/original/vMV534w2n1qzShBZa4XGHmqkKShWHOHWtb0LB0j2.mov';

$destination = storage_path(
    'app/youtube-test/source.mov'
);

$directory = dirname($destination);

if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
}

$disk = Storage::disk('s3');

if (!$disk->exists($source)) {
    throw new RuntimeException(
        'S3上に元動画がありません: ' . $source
    );
}

$stream = $disk->readStream($source);

if (!is_resource($stream)) {
    throw new RuntimeException(
        'S3動画のストリームを取得できませんでした。'
    );
}

$out = fopen($destination, 'wb');

if ($out === false) {
    fclose($stream);

    throw new RuntimeException(
        'ローカルファイルを作成できませんでした。'
    );
}

try {
    stream_copy_to_stream(
        $stream,
        $out
    );
} finally {
    fclose($stream);
    fclose($out);
}

echo "Downloaded:\n";
echo $destination . "\n";
echo "Size: " . filesize($destination) . " bytes\n";
