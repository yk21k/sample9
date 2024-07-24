<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;


class AccountController extends Controller
{
    public function index()
    {
        $profiles = User::where('id', Auth::user()->id)->first();
        
        return view('account.account', compact('profiles'));
    }

    public function updateProf(Request $request)
    {
        $data = $request->all();
        // dd($data);
        // dd($profiles);
        if($request){
            // dd($data['name'], $data['email']);
            User::where('id', Auth::user()->id)->update(['name'=>$data['name'], 'email'=>$data['email']]);

        }
        return redirect()->route('account.account')->withMessage('Update Profile!!');

    }
}
