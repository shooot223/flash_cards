<!documenttype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>
<div class="createQuiz">
    <a href="{{ route('quiz.create') }}" class="createQuiz__link">クイズ作成</a>
</div>
<section>
    <div class="sectionTitle">作成したクイズ</div>

    <article class="card">
        <div class="thumb">サムネ</div>
        <div class="meta">
            <div class="meta__row">例：基本情報｜ネットワーク</div>
            <div class="meta__row">TCP/UDP、ポート、DNSなどの要点をクイズで復習</div>
        </div>
        <div class="card__right"></div>
    </article>

    <article class="card">
        <div class="thumb">サムネ</div>
        <div class="meta">
            <div class="meta__row">例：会計｜仕訳の基礎</div>
            <div class="meta__row">借方/貸方、勘定科目を反復して定着させる</div>
        </div>
        <div class="card__right"></div>
    </article>
</section>
</body>
</html>
