<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopMember;
use App\Models\Shop;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    // // 🔥 ここに追加
    // protected function authenticated(Request $request, $user)
    // {
    //         dd([
    //     'user_id' => $user->id,

    //     'email' => $user->email,

    //     'member' => ShopMember::where(
    //         'user_id',
    //         $user->id
    //     )->first(),
    // ]);
    //     // 🔥 shop_member取得
    //     $member = ShopMember::where('user_id', $user->id)->first();
    //     // dd($member);

    //     // 🔥 manager / staff は強制的に seller
    //     if ($member && in_array($member->role, ['manager','staff'])) {
    //         // return redirect('/seller');
    //         return response()->redirectTo('/seller');
    //     }

    //     // 🔥 owner（Shopテーブルで判定）
    //     $owner = Shop::where('user_id', $user->id)->exists();

    //     if ($owner) {
    //         return redirect('/seller'); // or /admin
    //     }

    //     // 🔥 それ以外（購入者）
    //     return redirect('/');
    // }

    /**
     * ログイン後リダイレクト
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        // ========================================
        // 🔥 shop_member
        // ========================================
        $member = ShopMember::where(
            'user_id',
            $user->id
        )->first();

        // ========================================
        // 🔥 manager / staff
        // ========================================
        if (
            $member &&
            in_array(
                $member->role,
                ['manager', 'staff']
            )
        ) {

            return '/seller';

        }

        // ========================================
        // 🔥 owner
        // ========================================
        $owner = Shop::where(
            'user_id',
            $user->id
        )->exists();

        if ($owner) {

            return '/seller';

        }

        // ========================================
        // 🔥 buyer
        // ========================================
        return '/';
    }


}
