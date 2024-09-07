<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

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

        if($request->isMethod('post')){

            $data = $request->all();

            $rules = [
                'name' => 'required|between:1, 30|unique:users,name',
                'email' => 'required|string|email|max:255|unique:users,name',
                // 'email' => 'required|string|email:strict,dns,spoof|max:255',When user register
            ];

            $customMessages = [
                'name.required' => 'Name is required',
                'name.unique' => 'This is currently unavailable',
                'email.required' => 'Name is required',
                'email.unique' => 'This is currently unavailable',
            ];

            $this->validate($request, $rules, $customMessages);
            User::where('id', Auth::user()->id)->update(['name'=>$data['name'], 'email'=>$data['email']]);
        }
        return redirect()->route('account.account')->withMessage('Update Profile!!');

    }    
}        
