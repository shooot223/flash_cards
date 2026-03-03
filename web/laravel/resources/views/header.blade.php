<div class="header__left">
    <!-- ここは空でもOK。将来メニューとか置ける -->
</div>

<div class="header__center">
    <a href="/" class="logo">
        <img src="{{ asset('img/logo.png') }}" alt="Cramist Logo" class="logo__image">
    </a>
</div>

<div class="header__right">
    @auth
        <a class="header__link" href="{{asset('logout')}}">ログアウト</a>
    @else
        <a class="header__link" href="{{asset('login')}}">ログイン / 新規登録</a>
    @endauth
    <a class="header__link" href="{{asset('mypage')}}">
        <div class="header__avatar">My<br>Page</div>
    </a>
</div>
