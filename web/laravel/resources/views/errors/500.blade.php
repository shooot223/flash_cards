@extends('errors.layout')

@section('title', 'システムエラー')

@section('content')
<main class="error-container">
    <h1 class="error-code">500</h1>
    <h2 class="error-title">システムエラーが発生しました</h2>
    <p class="error-message">
        申し訳ありません。サーバー側で予期せぬエラーが発生しました。<br>
        しばらく時間をおいてから、再度お試しください。
    </p>
    <a href="/" class="btn-home">トップページへ戻る</a>
</main>
@endsection
