<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ開始</title>

    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quiz_start.css') }}">
</head>
<body>

@include('header')

<main class="quizStartContainer">

    <div class="quizStartCard">

        <div class="quizStartHeader">
            <span class="quizStartBadge">Quiz Start</span>

            <h1 class="quizStartTitle">
                {{ $quiz->title }}
            </h1>

            <p class="quizStartDescription">
                {{ $quiz->description ?? $quiz->discription ?? '説明はありません。' }}
            </p>
        </div>

        @if ($quiz->categories->isNotEmpty())
            <div class="quizStartTags">
                @foreach ($quiz->categories as $category)
                    <span class="quizStartTag">
                    {{ $category->category_name }}
                </span>
                @endforeach
            </div>
        @endif


        <div class="quizStartInfo">

            <div class="quizStartInfoItem">
                <div class="quizStartInfoLabel">
                    問題数
                </div>

                <div class="quizStartInfoValue">
                    {{ $quiz->questions->count() }}問
                </div>
            </div>

            <div class="quizStartInfoItem">

                <div class="quizStartInfoLabel">
                    前回の記録
                </div>

                @auth

                    @if($latestScore)
                        <div class="quizStartInfoValue">
                            {{ $latestScore->score_value }}点
                        </div>
                        <div class="quizStartInfoSub">
                            {{ $latestScore->correct_count }} / {{ $latestScore->answered_count }} 正解
                        </div>
                    @else
                        <div class="quizStartInfoSub">
                            まだ回答履歴はありません
                        </div>
                    @endif

                @else

                    <div class="quizStartInfoSub">
                        ログインすると前回の記録を表示できます
                    </div>

                @endauth

            </div>

        </div>


        <div class="quizStartActions">

            <a href="{{ url()->previous() }}" class="quizStartBackButton">
                戻る
            </a>

            <a href="{{ route('quiz.play',$quiz->id) }}" class="quizStartButton">
                開始
            </a>

        </div>

    </div>

</main>

</body>
</html>
