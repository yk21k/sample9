@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('front/css/custom99.css') }}">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">仮会員登録確認</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">メールアドレス</label>

                            <div class="col-md-6">
                                <span class="">{{$email}}</span>
                                <input type="hidden" name="email" value="{{$email}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">電話番号</label>

                            <div class="col-md-6">
                                <span>{{ $phone }}</span>
                                <input type="hidden" name="phone" value="{{ $phone }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>

                            <div class="col-md-6">
                                <span class="">{{$password_mask}}</span>
                                <input type="hidden" name="password" value="{{$password}}">
                            </div>
                        </div>

                        {{-- 同意チェック --}}
                        <div class="mb-4">

                            <div class="form-check mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="agree_usage"
                                    name="agree_usage"
                                    required
                                >
                                <label class="form-check-label" for="agree_usage">
                                    登録したメールアドレスおよび電話番号の利用目的について確認しました
                                </label>
                            </div>

                            <div class="small text-muted ms-4">
                                <ul class="mb-0 ps-3">
                                    <li>メールアドレスは、本人確認・重要なお知らせ・パスワード再設定に利用されます</li>
                                    <li>電話番号は、本人確認および緊急時の連絡のために利用されます</li>
                                    <li>本人の同意なく第三者に提供することはありません</li>
                                </ul>
                            </div>

                        </div>


                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="submit-button">
                                    仮登録
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log("DOMContentLoaded fired"); // ← ページ読込確認

        const form = document.querySelector('form');
        const submitButton = document.getElementById('submit-button');

        form.addEventListener('submit', function (e) {
            console.log("Form submitted"); // ← 送信イベント検知確認

            if (submitButton.disabled) {
                console.log("Button already disabled. Preventing duplicate submit.");
                e.preventDefault(); // 二重送信を防止
                return;
            }

            submitButton.disabled = true;
            console.log("Button disabled after submit.");
        });
    });
</script>



@endsection
