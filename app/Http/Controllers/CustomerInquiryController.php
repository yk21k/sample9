<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Models\CustomerInquiry;
use App\Models\User;

use App\Mail\InquiryActivationRequest;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;
use Auth;

class CustomerInquiryController extends Controller
{
    public function inquiryForm(Request $request)
    {

        return view('account.inquiry');

    }

    public function inquiryAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inq_subject' => 'required|string|max:30',
            'inquiry_details' => 'required|string|max:150',
        ]);


        // $validated = $request->validate([
        //     'inq_subject' => 'required|string|max:30',
        //     'inquiry_details' => 'required|string|max:150',
        // ]);-> array de kaeru

        if($validator->passes()){
            $data = $request->all();
            // dd($data);

            $inquiryAnswer = new CustomerInquiry;
            $inquiryAnswer->user_id = Auth::user()->id;
            $inquiryAnswer->inq_subject = $data['inq_subject'];
            $inquiryAnswer->inquiry_details = $data['inquiry_details'];
            $inquiryAnswer->status = 0;

            // dd($validator);


            $inquiryAnswer->save();            

        }
        // send mail
        $inquiryAnswers = $inquiryAnswer;

        $admins = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        Mail::to($admins)->send(new InquiryActivationRequest($inquiryAnswers));

        return redirect()->route('account.inquiry')->withMessage('We have received your inquiry.');

    } 

    public function answers()
    {
        $inquiries = CustomerInquiry::where('user_id', Auth::user()->id)->get();
        // dd($inquiries);
        return view('account.answers', compact('inquiries'));

    }
    
}
