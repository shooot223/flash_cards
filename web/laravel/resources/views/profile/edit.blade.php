<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile_edit.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>

<div class="profilePage">
    <section class="pageHeaderCard">
        <div>
            <div class="pageBadge">Profile</div>
            <h1 class="pageTitle">プロフィール編集</h1>
            <p class="pageDescription">
                名前・メールアドレス・パスワード・プロフィール画像を変更できます。
            </p>
        </div>
        <div class="pageHeaderActions">
            <a href="{{ route('mypage') }}" class="backButton">マイページへ戻る</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert--success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('password_success'))
        <div class="alert alert--success">
            {{ session('password_success') }}
        </div>
    @endif

    @if (session('avatar_success'))
        <div class="alert alert--success">
            {{ session('avatar_success') }}
        </div>
    @endif

    <div class="profileLayout">
        {{-- 左側：プロフィール画像 --}}
        <section class="profileCard">
            <h2 class="sectionHeading">プロフィール画像</h2>

            <div class="avatarArea">
                @if (Auth::user()->avatar)
                    <img
                        src="{{ Storage::url(Auth::user()->avatar) }}"
                        alt="プロフィール画像"
                        class="avatarImage"
                    >
                @else
                    <div class="avatarPlaceholder">
                        {{ mb_substr(Auth::user()->name, 0, 1) }}
                    </div>
                @endif
            </div>

            <div class="profileName">{{ Auth::user()->name }}</div>
            <div class="profileEmail">{{ Auth::user()->email }}</div>

            <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="stackForm">
                @csrf

                <div class="formGroup">
                    <label for="avatar" class="formLabel">画像を選択</label>
                    <input type="file" name="avatar" id="avatar" class="formInputFile" accept="image/*">
                    @error('avatar')
                    <div class="errorText">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="primaryButton fullButton">
                    画像を更新
                </button>
            </form>

            @if (Auth::user()->avatar)
                <form action="{{ route('profile.avatar.destroy') }}" method="POST" class="stackForm">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="dangerButton fullButton" onclick="return confirm('プロフィール画像を削除しますか？');">
                        画像を削除
                    </button>
                </form>
            @endif
        </section>

        {{-- 右側：各種設定 --}}
        <div class="settingsColumn">
            {{-- 基本情報 --}}
            <section class="contentCard">
                <h2 class="sectionHeading">基本情報</h2>

                <form action="{{ route('profile.update') }}" method="POST" class="stackForm">
                    @csrf
                    @method('PATCH')

                    <div class="formGroup">
                        <label for="name" class="formLabel">名前</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="formInput"
                            value="{{ old('name', Auth::user()->name) }}"
                        >
                        @error('name')
                        <div class="errorText">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="email" class="formLabel">メールアドレス</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="formInput"
                            value="{{ old('email', Auth::user()->email) }}"
                        >
                        @error('email')
                        <div class="errorText">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="primaryButton">
                        基本情報を保存
                    </button>
                </form>
            </section>

            {{-- パスワード変更 --}}
            <section class="contentCard">
                <h2 class="sectionHeading">パスワード変更</h2>

                <form action="{{ route('profile.password.update') }}" method="POST" class="stackForm">
                    @csrf
                    @method('PATCH')

                    <div class="formGroup">
                        <label for="current_password" class="formLabel">現在のパスワード</label>
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            class="formInput"
                        >
                        @error('current_password')
                        <div class="errorText">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="password" class="formLabel">新しいパスワード</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="formInput"
                        >
                        @error('password')
                        <div class="errorText">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="formGroup">
                        <label for="password_confirmation" class="formLabel">新しいパスワード（確認）</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="formInput"
                        >
                    </div>

                    <button type="submit" class="primaryButton">
                        パスワードを変更
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
</body>
</html>
