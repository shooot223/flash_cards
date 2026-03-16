@extends('errors.layout')

@section('title', 'ページが見つかりません')

@section('content')
<main class="error-container">
    <h1 class="error-code">404</h1>
    <h2 class="error-title">ページが見つかりません</h2>
    <p class="error-message">
        お探しのページは削除されたか、URLが変更された可能性があります。<br>
        正しいURLを入力しているかご確認ください。
    </p>
    <a href="/" class="btn-home">トップページへ戻る</a>
</main>
@endsection
