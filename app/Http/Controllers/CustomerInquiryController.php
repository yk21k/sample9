<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Models\CustomerInquiry;
use App\Models\User;
use App\Models\Shop;

use App\Mail\InquiryShopActivationRequest;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;
use Auth;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CustomerInquiryController extends Controller
{
    public function inquiryForm(Request $request, Shop $shopId)
    {
        // dd($shopId->id);
        $id = $shopId->id;
        return view('inquiries.inquiries_create', compact('id'));

    }

    public function inquiryAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inq_subject' => 'required|in:1,2,3,4,5,6',  // 必須、1から6のいずれか
            'inq_reason' => 'required_if:inq_subject,6', // 「6.キャンセルしたい」を選択した場合に必須
            'inquiry_details' => 'required|string|max:1000',  // 問い合わせ内容は必須、文字列で最大1000文字
            'inquiry_file' => 'nullable|file|mimes:jpg,png,pdf|max:2048',  // ファイルは画像(jpg, png)またはPDF、最大2MBまで
        ]);


        // dd($request);

        if($validator->passes()){
            $data = $request->all();
            // dd($data);   

            $inquiryAnswer = new CustomerInquiry;
            $inquiryAnswer->user_id = Auth::user()->id;
            $inquiryAnswer->shop_id = $data['shop_id'];
            $inquiryAnswer->inq_subject = $data['inq_subject'];

            // キーが存在するときだけ代入
            if (isset($data['inq_reason'])) {
                $inquiryAnswer->inq_reason = $data['inq_reason'];
            }
            $inquiryAnswer->inquiry_details = $data['inquiry_details'];

            $image = $request->file('inq_file');

            if ($image) {
                // ファイルの拡張子を取得
                $extension = $image->getClientOriginalExtension();

                // ユニークなファイル名を生成
                $fileName = Str::random(40) . '.' . $extension;
                
                // 追加のユニーク値
                $str_r = md5(uniqid(rand(), true));
                
                // 現在の日付時刻
                $now = now()->format('Y-m-d_H-i-s');  // 例: 2025-04-06_12-34-56

                // 保存先ディレクトリのパス
                $directory = 'customer_inquiries/' . $str_r . '/' . $now;

                // ファイルをpublicディスクに保存
                $image_url = $image->storeAs($directory, $fileName, 'public');

                // 保存されたファイルのURLを取得（publicディスクの場合）
                $imageUrl = Storage::url($image_url);  // 保存されたURLを取得

                // 例: $imageUrlを保存する処理
                $inquiryAnswer->inq_file = $imageUrl;  // ファイルのURLをデータベースに保存
            }

            $inquiryAnswer->status = 0;
            $inquiryAnswer->save();  // DBに保存
           

        }
        // send mail
        $inquiryAnswers = $inquiryAnswer;

        $shop_managers = Shop::where('id', $inquiryAnswer->shop_id)->first();
        // dd($shop_managers->email);
        // dd($inquiryAnswers->inqUser->email);
        $shop_managers_address = $shop_managers->email;

        Mail::to($shop_managers_address)->send(new InquiryShopActivationRequest($inquiryAnswers));

        return back()->withMessage('We have received your inquiry.');

    } 

    public function answers()
    {
        // shop　と　顧客
        $inquiries = CustomerInquiry::where('user_id', Auth::user()->id)->latest()->get();
        // dd($inquiries);
        return view('inquiries.answers', compact('inquiries'));

    }
    
}
