@component('mail::message')
# 受取用ワンタイムパスワードのご案内

以下の OTP をご利用ください。

## OTPコード：**{{ $otpCode }}**

有効期限：**{{ $expiresAt }}**

@component('mail::button', ['url' => config('app.url')])
アプリを開く
@endcomponent

このコードは第三者に共有しないでください。

@endcomponent
