<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Welcome')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap / 共通CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Public専用CSS --}}
    <link rel="stylesheet" href="{{ asset('front/css/public.css') }}">
    <link rel="stylesheet" href="{{ asset('front/css/public2.css') }}">
</head>

<body class="public-bg {{ $publicType ?? '' }} {{ request()->cookie('theme', 'dark') === 'light' ? 'theme-light' : 'theme-dark' }}">

    {{-- ダーク / ライト切替 --}}
    <button
        id="themeToggle"
        class="btn btn-sm btn-outline-secondary"
        style="
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1000;
        "
    >
        🌙 / ☀️
    </button>
    {{-- スキップ・補助導線 --}}
    <div class="text-center mt-5">
        <form method="POST" action="{{ route('entrance.pass') }}">
            @csrf
            <button class="btn btn-link text-muted">
                今回は省略し、商品ページへ進む
            </button>
        </form>
    </div>

    <main>
        @yield('content')
    </main>

    {{-- Theme Toggle JS（body 後が安全） --}}
    <script src="{{ asset('front/js/theme.js') }}"></script>

    <script>
    var botmanWidget = {
        title: 'FAQ（ログイン後利用可）',
        aboutText: '❓ よくある質問はこちら',
        introMessage:
            "👋 出店前によくある質問をまとめています。\n\n" +
            "🔒 チャットでの質問はログイン後に可能です。\n" +
            "まずは内容をご覧ください。",
        bubbleBackground: '#2563eb', // 吹き出し色（重要）
        bubbleAvatarUrl: '',         // 未指定でOK
    };
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            const btn = document.querySelector('#botmanWidgetRoot button');
            if (btn) {
                btn.classList.add('botman-attention');
            }
        }, 1000);
    });
    </script>

    <script>
    (function waitBotman() {
        const root = document.getElementById('botmanWidgetRoot');
        if (!root) {
            setTimeout(waitBotman, 300);
            return;
        }

        root.style.animation = 'botmanPulse 2s infinite';
        root.style.borderRadius = '50%';
    })();
    </script>


    <script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>

</body>
</html>
