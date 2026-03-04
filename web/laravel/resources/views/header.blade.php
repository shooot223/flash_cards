
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
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:none;border:none;padding:0;color:blue;cursor:pointer;">
                ログアウト
            </button>
        </form>
    @else
        <a class="header__link" href="{{route('login')}}">ログイン</a>/
        <a class="header__link" href="{{route('register')}}"> 新規登録</a>
    @endauth
    <a class="header__link" href="{{route('mypage')}}">
        <div class="header__avatar">マイページ</div>
    </a>
</div>
