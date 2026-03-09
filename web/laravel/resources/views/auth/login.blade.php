<x-guest-layout>
    <section class="authCard">
        <div class="authHeader">
            <div class="authBadge">Login</div>
            <h1 class="authTitle">ログイン</h1>
            <p class="authDescription">
                アカウントにログインして、作成した問題や学習履歴を確認しましょう。
            </p>
        </div>

        <x-auth-session-status class="authStatus" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="authForm">
            @csrf

            <div class="formGroup">
                <x-input-label for="email" :value="'メールアドレス'" class="formLabel" />
                <x-text-input
                    id="email"
                    class="formInput"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="example@example.com"
                />
                <x-input-error :messages="$errors->get('email')" class="errorText" />
            </div>

            <div class="formGroup">
                <x-input-label for="password" :value="'パスワード'" class="formLabel" />
                <x-text-input
                    id="password"
                    class="formInput"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="パスワードを入力"
                />
                <x-input-error :messages="$errors->get('password')" class="errorText" />
            </div>

            <div class="authOptions">
                <label for="remember_me" class="rememberLabel">
                    <input id="remember_me" type="checkbox" class="rememberCheckbox" name="remember">
                    <span>ログイン状態を保持する</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="forgotLink" href="{{ route('password.request') }}">
                        パスワードをお忘れですか？
                    </a>
                @endif
            </div>

            <button type="submit" class="primaryButton fullButton">
                ログイン
            </button>

            @if (Route::has('register'))
                <div class="authFooter">
                    <span>アカウントをお持ちでない方はこちら</span>
                    <a href="{{ route('register') }}" class="registerLink">
                        新規登録
                    </a>
                </div>
            @endif
        </form>
    </section>
</x-guest-layout>
