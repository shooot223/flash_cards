<header class="header">
    <div class="headerInner">

        <div class="headerLeft">
            <a class="headerLink" href="/">問題一覧</a>
        </div>

        <div class="headerCenter">
            <a href="/" class="logo">
                <img src="{{ asset('img/logo.png') }}" class="logoImage" alt="Cramist">
            </a>
        </div>

        <div class="headerRight">
            <div class="headerAuth">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logoutBtn" type="submit">ログアウト</button>
                    </form>
                @else
                    <a class="headerLink" href="{{ route('login') }}">ログイン</a>
                    <a class="headerLink" href="{{ route('register') }}">新規登録</a>
                @endauth
            </div>

            <div class="headerMypage">
                <a href="{{ route('mypage') }}" class="mypageBtn">マイページ</a>
            </div>
        </div>

    </div>
</header>
