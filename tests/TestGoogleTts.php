<?php

require __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

$outputPath =
    __DIR__
    . '/../storage/app/video-temp/test-google-tts.mp3';

$directory = dirname($outputPath);

if (
    !is_dir($directory) &&
    !mkdir($directory, 0775, true) &&
    !is_dir($directory)
) {
    throw new RuntimeException(
        '出力ディレクトリを作成できません。'
    );
}

$text =
    'これはGoogle Cloud Text-to-Speechの動作確認です。';

echo "Google Cloud TTS test START\n";
echo "Text: {$text}\n";
echo "Output: {$outputPath}\n";

$client = new TextToSpeechClient();

try {

    $input =
        (new SynthesisInput())
        ->setText($text);

    $voice =
        (new VoiceSelectionParams())
        ->setLanguageCode('ja-JP')
        ->setName('ja-JP-Neural2-B');

    $audioConfig =
        (new AudioConfig())
        ->setAudioEncoding(
            AudioEncoding::MP3
        )
        ->setSpeakingRate(1.0)
        ->setPitch(0.0);

    $request =
        (new SynthesizeSpeechRequest())
        ->setInput($input)
        ->setVoice($voice)
        ->setAudioConfig($audioConfig);

    echo "Calling Google Cloud TTS...\n";

    $response =
        $client->synthesizeSpeech(
            $request
        );

    $audioContent =
        $response->getAudioContent();

    if (
        !$audioContent ||
        strlen($audioContent) === 0
    ) {
        throw new RuntimeException(
            'Google Cloud TTSから音声データが返されませんでした。'
        );
    }

    file_put_contents(
        $outputPath,
        $audioContent
    );

    echo "SUCCESS\n";
    echo "File size: "
        . filesize($outputPath)
        . " bytes\n";

} finally {

    $client->close();
}