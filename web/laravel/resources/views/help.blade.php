@extends('layouts.app')

@section('title', 'Cramist | ヘルプ')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/help.css') }}"/>
@endpush

@section('content')
    <main class="helpPage">
        <section class="helpHero">
            <p class="helpHero__eyebrow">Help</p>
            <h1 class="helpHero__title">使い方ガイド</h1>
            <p class="helpHero__text">
                Cramist の基本的な使い方をまとめています。問題を探す、解く、作る、復習する流れをここで確認できます。
            </p>
        </section>

        <section class="helpSection">
            <h2 class="helpSection__title">1. 問題を探す</h2>
            <div class="helpCardGrid">
                <article class="helpCard">
                    <h3 class="helpCard__title">キーワード検索</h3>
                    <p class="helpCard__text">トップページの検索欄にキーワードを入れると、問題名や説明文から該当する問題を絞り込めます。</p>
                </article>
                <article class="helpCard">
                    <h3 class="helpCard__title">タグで絞り込む</h3>
                    <p class="helpCard__text">タグを選ぶと、そのカテゴリに紐づく問題だけを一覧表示します。複数のテーマを探すときは検索と組み合わせると効率的です。</p>
                </article>
                <article class="helpCard">
                    <h3 class="helpCard__title">一覧を読み込む</h3>
                    <p class="helpCard__text">画面を下に進めると、続きの問題が自動で表示されます。気になる問題を見つけたらそのまま解答画面へ進めます。</p>
                </article>
            </div>
        </section>

        <section class="helpSection">
            <h2 class="helpSection__title">2. 問題を解く</h2>
            <div class="helpTimeline">
                <article class="helpStep">
                    <span class="helpStep__number">01</span>
                    <div>
                        <h3 class="helpStep__title">問題を開始する</h3>
                        <p class="helpStep__text">一覧から問題を選び、スタート画面で内容を確認して学習を始めます。</p>
                    </div>
                </article>
                <article class="helpStep">
                    <span class="helpStep__number">02</span>
                    <div>
                        <h3 class="helpStep__title">回答と解説を確認する</h3>
                        <p class="helpStep__text">各設問に答えると正誤と解説を確認できます。理解が曖昧な箇所をその場で見直せます。</p>
                    </div>
                </article>
                <article class="helpStep">
                    <span class="helpStep__number">03</span>
                    <div>
                        <h3 class="helpStep__title">結果を見る</h3>
                        <p class="helpStep__text">最後に正答数や達成状況を確認できます。ログイン中なら記録も保存されます。</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="helpSection">
            <h2 class="helpSection__title">3. 問題を作成する</h2>
            <div class="helpPanel">
                <p class="helpPanel__text">ログイン後は自分で問題を作成できます。問題文、選択肢、正解、解説、カテゴリを登録して公開設定を選ぶと、一覧に表示できるようになります。</p>
                <p class="helpPanel__text">公開前に確認画面があるため、誤字や正解設定のミスをチェックしてから保存できます。</p>
            </div>
        </section>

        <section class="helpSection">
            <h2 class="helpSection__title">4. 復習する</h2>
            <div class="helpCardGrid helpCardGrid--two">
                <article class="helpCard">
                    <h3 class="helpCard__title">マイページで管理</h3>
                    <p class="helpCard__text">作成した問題や学習した内容はマイページから確認できます。必要に応じて編集や見直しが可能です。</p>
                </article>
                <article class="helpCard">
                    <h3 class="helpCard__title">苦手分野を再確認</h3>
                    <p class="helpCard__text">解説を見直しながら繰り返し解くことで、苦手なカテゴリを重点的に復習できます。</p>
                </article>
            </div>
        </section>
    </main>
@endsection
