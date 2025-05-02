<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Http\Requests\StoreInquiriesRequest;
use App\Http\Requests\UpdateInquiriesRequest;
use App\Models\Inquiries;
use App\Models\Product;
use App\Models\User;
use App\Models\Shop;

use App\Mail\InquiryActivationRequest;
use Illuminate\Support\Facades\Mail;


use Illuminate\Support\Facades\Validator;
use Auth;


class InquiriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(User $user)
    {
        // dd($id);

        return view('account.inquiry', ['id'=>$user->id]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInquiriesRequest $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'inq_subject' => 'required|string|max:30',
            'inquiry_details' => 'required|string|max:150',
        ]);

        if($validator->passes()){
            $data = $request->all();
            // dd($data);

            $inquiryAnswer = new Inquiries;
            $inquiryAnswer->user_id = Auth::user()->id;
            $inquiryAnswer->inq_subject = $data['inq_subject'];
            $inquiryAnswer->inquiry_details = $data['inquiry_details'];
            $inquiryAnswer->status = 0;

            // dd($validator);
            // dd($inquiryAnswer);


            $inquiryAnswer->save();  

            // send mail
            $inquiryAnswers = $inquiryAnswer;

            

            // $adminId = Shop::where(['id'=>$inquiryAnswers->shop_id])->first();

            // dd($adminId->user_id);


            $admins = User::where('id', '1')->first();

            // dd($admins->email);

            // dd($inquiryAnswers);
            Mail::to($admins->email)->send(new InquiryActivationRequest($inquiryAnswers));

        }
        return back()->withMessage('We have received your inquiry.');

    }

    public function answers()
    {
        // カスタマーとアドミン dd($id);InquiriesController
        $inquiries = Inquiries::where('user_id', Auth::user()->id)->latest()->get();
        // dd($inquiries);
        return view('inquiries.answers', compact('inquiries'));

    }

    /**
     * Display the specified resource.
     */
    public function show(Inquiries $inquiries)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inquiries $inquiries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInquiriesRequest $request, Inquiries $inquiries)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inquiries $inquiries)
    {
        //
    }
}
