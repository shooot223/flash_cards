<!doctype html>
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

<div class="mypageContainer">
    <div class="createQuiz">
        <a href="{{ route('quiz.create') }}" class="createQuizButton">問題作成</a>
    </div>

    <section class="mypageSection">
        <div class="sectionTitle">マイページ</div>

        <div class="tabArea">
            <button type="button" class="tabButton is-active" data-tab="created">
                作成した問題
            </button>
            <button type="button" class="tabButton" data-tab="answered">
                過去に回答した問題
            </button>
        </div>

        {{-- 作成した問題 --}}
        <div class="tabContent is-active" id="tab-created">
            @foreach ($createdQuizzes as $quiz)
                <article class="card">
                    <div class="thumb">サムネ</div>
                    <div class="meta">
                        <div class="meta__row">{{ $quiz->title }}</div>
                        <div class="meta__row">{{ $quiz->discription }}</div>
                    </div>
                    <div class="card__right">
{{--                        <a href="{{ route('quiz.show', $quiz->id) }}" class="cardButton">詳細</a>--}}
                    </div>
                </article>
            @endforeach
        </div>

        {{-- 過去に回答した問題 --}}
        <div class="tabContent" id="tab-answered">
            <article class="card">
                <div class="thumb">サムネ</div>
                <div class="meta">
                    <div class="meta__row">例：英語｜単語テスト</div>
                    <div class="meta__row">過去に回答した英単語クイズ</div>
                </div>
                <div class="card__right">
                    <a href="#" class="cardButton">もう一度解く</a>
                </div>
            </article>

            <article class="card">
                <div class="thumb">サムネ</div>
                <div class="meta">
                    <div class="meta__row">例：歴史｜戦国時代</div>
                    <div class="meta__row">前回の回答履歴あり</div>
                </div>
                <div class="card__right">
                    <a href="#" class="cardButton">もう一度解く</a>
                </div>
            </article>
{{--            @foreach ($answeredQuizzes as $quiz)--}}
{{--                <article class="card">--}}
{{--                    <div class="thumb">サムネ</div>--}}
{{--                    <div class="meta">--}}
{{--                        <div class="meta__row">{{ $quiz->title }}</div>--}}
{{--                        <div class="meta__row">{{ $quiz->discription }}</div>--}}
{{--                    </div>--}}
{{--                    <div class="card__right">--}}
{{--                        <a href="{{ route('quiz.play', $quiz->id) }}" class="cardButton">もう一度解く</a>--}}
{{--                    </div>--}}
{{--                </article>--}}
{{--            @endforeach--}}
        </div>
    </section>
</div>

<script>
    const tabButtons = document.querySelectorAll('.tabButton');
    const tabContents = document.querySelectorAll('.tabContent');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const target = button.dataset.tab;

            tabButtons.forEach(btn => btn.classList.remove('is-active'));
            tabContents.forEach(content => content.classList.remove('is-active'));

            button.classList.add('is-active');
            document.getElementById(`tab-${target}`).classList.add('is-active');
        });
    });
</script>
</body>
</html>
