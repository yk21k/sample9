<?php

namespace App\Http\Controllers\Auth;

use App\Mail\EmailVerification;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;

use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            // 'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],//update 
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // return User::create([
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($data['password']),
        // ]);
        // dd($data);
            $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verify_token' => base64_encode($data['email']),
        ]);

        $email = new EmailVerification($user);
        Mail::to($user->email)->send($email);

        return $user;
    }

    public function pre_check(Request $request)
    {
        $email = $request->input('email');

        // 同じメールで仮登録済みかどうかチェック
        if (User::where('email', $email)->exists()) {
            return redirect()->back()->withErrors(['email' => 'このメールアドレスはすでに登録されています。']);
        }

        $this->validator($request->all())->validate();
        //flash data
        $request->flashOnly( 'email');

        $bridge_request = $request->all();
        // password マスキング
        $bridge_request['password_mask'] = '******';

        return view('auth.register_check')->with($bridge_request);
    }


    public function register(Request $request)
    {
        $data = $request->all();

        // 既存ユーザーを探す
        $user = \App\Models\User::where('email', $data['email'])->first();

        if (!$user) {
            // いなければcreate()を使って新規作成
            $user = $this->create($data);
        } else {
            // 既存ならupdateしたい場合はここで更新処理を書く
        }

        event(new Registered($user));

        return view('auth.registered');
    }


    // public function register(Request $request)
    // {
    //     event(new Registered($user = $this->create( $request->all() )));

    //     return view('auth.registered');
    // }

    public function showForm($email_token)
    {
        // 使用可能なトークンか
        if ( !User::where('email_verify_token',$email_token)->exists() )
        {
            return view('auth.main.register')->with('message', '無効なトークンです。');
        } else {
            $user = User::where('email_verify_token', $email_token)->first();
            // 本登録済みユーザーか
            if ($user->status == config('const.USER_STATUS.REGISTER')) //REGISTER=1
            {
                logger("status". $user->status );
                return view('auth.main.register')->with('message', 'すでに本登録されています。ログインして利用してください。');
            }
            // ユーザーステータス更新
            $user->status = config('const.USER_STATUS.MAIL_AUTHED');
            $user->email_verified_at = Carbon::now();
            if($user->save()) {
                return view('auth.main.register', compact('email_token'));
            } else{
                return view('auth.main.register')->with('message', 'メール認証に失敗しました。再度、メールからリンクをクリックしてください。');
            }
        }
    }

    public function mainCheck(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'birth_year' => 'required|numeric',
            'birth_month' => 'required|numeric',
            'birth_day' => 'required|numeric',
        ]);
        //データ保持用
        $email_token = $request->email_token;

        $user = new User();
        $user->name = $request->name;
        $user->email_token = $request->email_token;
        $user->birth_year = $request->birth_year;
        $user->birth_month = $request->birth_month;
        $user->birth_day = $request->birth_day;

        return view('auth.main.register_check', compact('user','email_token'));
    }

    public function mainRegister(Request $request)
    {
        // dd($request->all());
        $user = User::where('email_verify_token',$request->email_token)->first();
        // dd($user);
        $user->status = config('const.USER_STATUS.REGISTER');
        $user->name = $request->name;
        $user->birth_year = $request->birth_year;
        $user->birth_month = $request->birth_month;
        $user->birth_day = $request->birth_day;
        $user->save();

        return view('auth.main.registered');
      }



}
