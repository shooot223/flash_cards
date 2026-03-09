<x-guest-layout>
    <section class="authCard">
        <div class="authHeader">
            <div class="authBadge">Password Reset</div>
            <h1 class="authTitle">パスワード再設定</h1>
            <p class="authDescription">
                登録しているメールアドレスを入力してください。<br>
                パスワード再設定用のリンクをお送りします。
            </p>
        </div>

        @if (session('status'))
            <div class="authStatus">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="authForm">
            @csrf

            <div class="formGroup">
                <label for="email" class="formLabel">メールアドレス</label>
                <input
                    id="email"
                    class="formInput"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="example@example.com"
                >
                @error('email')
                <div class="errorText">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="primaryButton fullButton">
                再設定リンクを送信
            </button>

            <div class="authFooter">
                <a href="{{ route('login') }}" class="registerLink">
                    ログイン画面へ戻る
                </a>
            </div>
        </form>
    </section>
</x-guest-layout>
