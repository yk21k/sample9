@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">本会員登録</div>

                @isset($message)
                    <div class="card-body">
                        {{ $message }}
                    </div>
                @endisset

                @empty($message)
                <div class="card-body">
                    <form method="POST" action="{{ route('register.main.check', ['email_token'=>$email_token]) }}">
                        @csrf

                        {{-- 名前 --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">名前</label>
                            <div class="col-md-6">
                                <input
                                    type="text"
                                    class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                >

                                @error('name')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- 生年月日 --}}
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">生年月日</label>
                            <div class="col-md-6">
                                <div class="row">

                                    {{-- 年 --}}
                                    <div class="col-md-4">
                                        <select id="birth_year" class="form-control" name="birth_year" required>
                                            <option value="">----</option>
                                            @for ($y = now()->year - 18; $y >= now()->year - 80; $y--)
                                                <option value="{{ $y }}" @selected(old('birth_year') == $y)>
                                                    {{ $y }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('birth_year')
                                            <span class="help-block text-danger">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>年

                                    {{-- 月 --}}
                                    <div class="col-md-3">
                                        <select id="birth_month" class="form-control" name="birth_month" required>
                                            <option value="">--</option>
                                            @for ($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" @selected(old('birth_month') == $m)>
                                                    {{ $m }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('birth_month')
                                            <span class="help-block text-danger">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>月

                                    {{-- 日 --}}
                                    <div class="col-md-3">
                                        <select id="birth_day" class="form-control" name="birth_day" required>
                                            <option value="">--</option>
                                        </select>
                                        @error('birth_day')
                                            <span class="help-block text-danger">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>日
                                </div>

                                {{-- 日付全体エラー --}}
                                @error('birth')
                                    <div class="mt-2 text-danger">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- 送信 --}}
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    確認画面へ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endempty
            </div>
        </div>
    </div>
</div>

{{-- 生年月日制御 JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const year  = document.getElementById('birth_year');
    const month = document.getElementById('birth_month');
    const day   = document.getElementById('birth_day');

    function updateDays() {
        const y = parseInt(year.value);
        const m = parseInt(month.value);

        day.innerHTML = '<option value="">--</option>';

        if (!y || !m) return;

        const lastDay = new Date(y, m, 0).getDate();

        for (let d = 1; d <= lastDay; d++) {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;

            if ("{{ old('birth_day') }}" == d) {
                opt.selected = true;
            }

            day.appendChild(opt);
        }
    }

    year.addEventListener('change', updateDays);
    month.addEventListener('change', updateDays);

    updateDays();
});
</script>
@endsection
