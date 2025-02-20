@component('mail::message')
# 仮登録　Temporary registration


サイトへのアカウント仮登録が完了しました。<br>
<br>
以下のURLからログインして、本登録を完了させてください。<br>


@component('mail::button', ['url' => url('register/verify/'.$token)])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent


