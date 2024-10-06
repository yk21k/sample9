<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Auth;


class AccountController extends Controller
{
    public function index()
    {
        $profiles = User::where('id', Auth::user()->id)->first();
        
        return view('account.account', compact('profiles'));
    }

    public function updateProf(Request $request, $id)
    {

        if($request->isMethod('post')){

            $data = $request->all();

            $rules = [
                'name' => 'required|between:1, 30|unique:users,name,'. $id,
                'email' => 'required|string|email|max:255|unique:users,email,'. $id,
                // 'email' => 'required|string|email:strict,dns,spoof|max:255',When user register
                'password' => 'required|between:1, 30'
            ];

            $customMessages = [
                'name.required' => 'Name is required',
                'name.unique' => 'This Name is currently unavailable',
                'email.required' => 'Email is required',
                'email.unique' => 'This Email is currently unavailable',
                'password.required' => 'Password is required',
            ];

            $this->validate($request, $rules, $customMessages);
            User::where('id', $id)->update(['name'=>$data['name'], 'email'=>$data['email'], 'password'=>Hash::make($data['password'])]);
        }
        return redirect()->route('account.account', ['id', $id])->withMessage('Update Profile!!');

    }    
}        
