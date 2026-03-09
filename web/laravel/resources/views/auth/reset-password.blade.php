<x-guest-layout>
    <section class="authCard">
        <div class="authHeader">
            <div class="authBadge">Reset Password</div>
            <h1 class="authTitle">新しいパスワード設定</h1>
            <p class="authDescription">
                新しいパスワードを入力して、再設定を完了してください。
            </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="authForm">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="formGroup">
                <label for="email" class="formLabel">メールアドレス</label>
                <input
                    id="email"
                    class="formInput"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="example@example.com"
                >
                @error('email')
                <div class="errorText">{{ $message }}</div>
                @enderror
            </div>

            <div class="formGroup">
                <label for="password" class="formLabel">新しいパスワード</label>
                <input
                    id="password"
                    class="formInput"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="新しいパスワードを入力"
                >
                @error('password')
                <div class="errorText">{{ $message }}</div>
                @enderror
            </div>

            <div class="formGroup">
                <label for="password_confirmation" class="formLabel">新しいパスワード（確認）</label>
                <input
                    id="password_confirmation"
                    class="formInput"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="もう一度入力"
                >
            </div>

            <button type="submit" class="primaryButton fullButton">
                パスワードを再設定
            </button>

            <div class="authFooter">
                <a href="{{ route('login') }}" class="registerLink">
                    ログイン画面へ戻る
                </a>
            </div>
        </form>
    </section>
</x-guest-layout>
