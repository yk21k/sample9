<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\DeleteShop;

use App\Mail\ShopDeleteActivationRequest;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;
use Auth;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('users.delete_shop');
    }

    public function termination(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:5000',
        ]);

        if($validator->passes()){
            $data = $request->all();

            // dd($data);
            $termination = new DeleteShop;
            $termination->user_id = Auth::user()->id;
            $termination->reason = $data['reason'];
            $termination->status = 0;

            // dd($termination);

            $termination->save();            

        }
        $terminations = $termination;

        $admins = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        Mail::to($admins)->send(new ShopDeleteActivationRequest($terminations));


        return redirect()->route('users.delete_shop')->withMessage('We have received your Request.');


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     $user = User::find($id);
    //     $user->forceDelete();
    //     return redirect()->route('home')->withMessage('The withdrawal process has been completed, thank you');
        
    // }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // ← 論理削除
        return redirect()->route('home')
            ->withMessage('The withdrawal process has been completed, thank you');
    }


    public function delete_confirm()
    {
        return view('users.delete_confirm');
    }
}
