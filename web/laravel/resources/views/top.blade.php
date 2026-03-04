<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Cramist | 問題一覧</title>
    <link rel="stylesheet" href="{{ asset('/css/top.css') }}"/>
    <link rel="stylesheet" href="{{ asset('/css/header.css') }}" />
</head>

<body>
<!-- ヘッダー -->
<header class="header">
    @include('header')
</header>

<main class="container">
    <!-- 検索ボックス（簡易表示） -->
    <section class="searchBox">
        キーワードで検索（例：HTTP / 会計 / 英単語）
    </section>

    <!-- タグ（簡易表示） -->
    <section class="tagBar">
        タグ：資格 / IT / ビジネス / 英語 / 研修
    </section>

    <!-- 問題一覧 -->
    <section class="listWrap">
        <div class="sectionTitle">問題一覧</div>

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

    <div class="pager">1  /  10</div>
</main>
</body>
</html>
