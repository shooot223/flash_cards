<header class="header">
    <div class="headerInner">

        <div class="headerLeft">
            <a class="headerNavLink" href="/">問題一覧</a>
        </div>

        <div class="headerCenter">
            <a href="/" class="logo">
                <img src="{{ asset('img/logo.png') }}" class="logoImage" alt="Cramist">
            </a>
        </div>

        <div class="headerRight">
            @auth
                <div class="headerMypage">
                    <a href="{{ route('mypage') }}" class="mypageBtn">マイページ</a>
                </div>

                <div class="headerUserMenu">
                    <button type="button" class="headerUserButton" id="userMenuButton">
                        <span class="headerUserIcon">
                            @if (Auth::user()->avatar)
                                <img
                                    src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                    alt="プロフィール画像"
                                    class="headerUserAvatar"
                                >
                            @else
                                {{ mb_substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </span>
                        <span class="headerUserName">{{ Auth::user()->name }}</span>
                        <span class="headerUserCaret">▼</span>
                    </button>

                    <div class="headerDropdown" id="userDropdown">
                        <a href="{{ route('mypage') }}" class="headerDropdownLink">マイページ</a>
                        <a href="{{ route('profile.edit') }}" class="headerDropdownLink">プロフィール編集</a>

                        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('ログアウトしますか？');">
                            @csrf
                            <button class="headerDropdownLogout" type="submit">ログアウト</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="headerAuth">
                    <a class="headerLoginButton" href="{{ route('login') }}">ログイン</a>
                    <a class="headerRegisterButton" href="{{ route('register') }}">新規登録</a>
                </div>
            @endauth
        </div>

    </div>
</header>

<script src="{{ asset('js/header.js') }}"></script>
