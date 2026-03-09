<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>問題挑戦</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quiz_play.css') }}">
</head>
<body>

@include('header')

<main class="quizPlayContainer">
    <div class="quizHeader">
        <div>
            <h1 class="quizTitle">{{ $quiz->title }}</h1>
            <p class="quizStep">問題 {{ $step + 1 }} / {{ $total }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('quiz.answer', $quiz->id) }}">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">

        <div class="questionCard">
            <div class="questionNumber">問題 {{ $step + 1 }}</div>

            <div class="questionText">
                {{ $question->question_text }}
            </div>

            <div class="choices">
                @foreach($question->choices as $choice)
                    <label class="choiceItem">
                        <input
                            type="radio"
                            name="choice_id"
                            value="{{ $choice->id }}"
                            class="choiceRadio"
                            {{ old('choice_id') == $choice->id ? 'checked' : '' }}
                        >
                        <span>{{ $choice->choice_text }}</span>
                    </label>
                @endforeach
            </div>

            @error('choice_id')
            <div class="errorText">{{ $message }}</div>
            @enderror

            <div class="confidenceBlock">
                <div class="confidenceLabel">この回答への自信度</div>

                <div class="confidenceOptions">
                    <label class="confidenceItem">
                        <input type="radio" name="confidence" value="high" {{ old('confidence') === 'high' ? 'checked' : '' }}>
                        <span>高い</span>
                    </label>

                    <label class="confidenceItem">
                        <input type="radio" name="confidence" value="medium" {{ old('confidence') === 'medium' ? 'checked' : '' }}>
                        <span>普通</span>
                    </label>

                    <label class="confidenceItem">
                        <input type="radio" name="confidence" value="low" {{ old('confidence') === 'low' ? 'checked' : '' }}>
                        <span>低い</span>
                    </label>
                </div>

                @error('confidence')
                <div class="errorText">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="quizActions">
            <a href="{{ route('quiz.start', $quiz->id) }}" class="backButton">開始画面へ戻る</a>
            <button type="submit" class="submitButton">回答する</button>
        </div>
    </form>
</main>

</body>
</html>
