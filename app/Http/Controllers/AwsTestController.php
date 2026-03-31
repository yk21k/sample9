<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\RekognitionService;

class AwsTestController extends Controller
{
    public function s3Test()
    {
        Storage::disk('s3')->put('test.txt', 'hello from laravel');

        return response()->json([
            'message' => 'S3アップロード成功'
        ]);
    }

    public function uploadTestImage()
    {
        $path = Storage::disk('s3')->put(
            'products/test.jpg',
            file_get_contents(public_path('test.jpg')),
            [
                'ContentType' => 'image/jpeg'
            ]
        );

        return response()->json([
            'message' => '画像アップ成功',
            'path' => $path,
            'url' => Storage::disk('s3')->url('products/test.jpg')
        ]);
    }
    
    public function s3PutTest()
    {
        $result = Storage::disk('s3')->put('test.txt', 'hello');

        return response()->json([
            'success' => $result,
            'message' => $result ? '成功' : '失敗'
        ]);
    }

    public function rekognitionTest()
    {
        $imagePath = 'products/test.jpg';

        $rekognition = new RekognitionService();

        $result = $rekognition->analyze($imagePath);

        return response()->json($result);
    }
}
