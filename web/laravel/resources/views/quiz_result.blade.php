<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ結果</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quiz_start.css') }}">
</head>
<body>

@include('header')

<main class="quizStartContainer">
    <div class="quizStartCard">

        <div class="quizStartHeader">
            <span class="quizStartBadge">Result</span>
            <h1 class="quizStartTitle">{{ $quiz->title }}</h1>
            <p class="quizStartDescription">結果を表示しています</p>
        </div>

        <div class="quizStartInfo">
            <div class="quizStartInfoItem">
                <div class="quizStartInfoLabel">スコア</div>
                <div class="quizStartInfoValue">{{ $score }} / {{ $total }}</div>
            </div>

            <div class="quizStartInfoItem">
                <div class="quizStartInfoLabel">正答率</div>
                <div class="quizStartInfoValue">
                    {{ $total > 0 ? round(($score / $total) * 100) : 0 }}%
                </div>
            </div>
        </div>

        <div class="quizStartActions">
            <a href="{{ route('quiz.start', $quiz->id) }}" class="quizStartBackButton">開始画面へ戻る</a>
            <a href="{{ route('quiz.play', ['id' => $quiz->id, 'step' => 0]) }}" class="quizStartButton">もう一度挑戦</a>
        </div>
    </div>
</main>

</body>
</html>
