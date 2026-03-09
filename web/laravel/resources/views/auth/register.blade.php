<x-guest-layout>
    <section class="authCard">
        <div class="authHeader">
            <div class="authBadge">Register</div>
            <h1 class="authTitle">新規登録</h1>
            <p class="authDescription">
                アカウントを作成して、問題作成や学習履歴の管理を始めましょう。
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="authForm">
            @csrf

            <div class="formGroup">
                <label for="name" class="formLabel">ユーザー名</label>
                <input
                    id="name"
                    class="formInput"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="ユーザー名を入力"
                >
                @error('name')
                <div class="errorText">{{ $message }}</div>
                @enderror
            </div>

            <div class="formGroup">
                <label for="email" class="formLabel">メールアドレス</label>
                <input
                    id="email"
                    class="formInput"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    placeholder="example@example.com"
                >
                @error('email')
                <div class="errorText">{{ $message }}</div>
                @enderror
            </div>

            <div class="formGroup">
                <label for="password" class="formLabel">パスワード</label>
                <input
                    id="password"
                    class="formInput"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="パスワードを入力"
                >
                @error('password')
                <div class="errorText">{{ $message }}</div>
                @enderror
            </div>

            <div class="formGroup">
                <label for="password_confirmation" class="formLabel">パスワード（確認）</label>
                <input
                    id="password_confirmation"
                    class="formInput"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="もう一度パスワードを入力"
                >
            </div>

            <button type="submit" class="primaryButton fullButton">
                新規登録
            </button>

            <div class="authFooter">
                <span>すでにアカウントをお持ちの方はこちら</span>
                <a href="{{ route('login') }}" class="registerLink">
                    ログイン
                </a>
            </div>
        </form>
    </section>
</x-guest-layout>
