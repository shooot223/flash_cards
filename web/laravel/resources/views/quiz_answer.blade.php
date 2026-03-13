@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/quiz_answer.css') }}"/>
@endpush

@section('title', 'Cramist | 問題の回答')

@section('content')
    <main class="quizAnswerContainer">
        <div class="quizAnswerCard">
            <div class="quizAnswerBadge {{ $isCorrect ? 'is-correct' : 'is-wrong' }}">
                {{ $isCorrect ? '正解' : '不正解' }}
            </div>

            <div class="quizAnswerQuestion">
                {{ $question->question_text }}
            </div>

            <div class="quizAnswerBlock">
                <div class="quizAnswerLabel">あなたの回答</div>
                <div class="quizAnswerValue">
                    {{ $selectedChoice->choice_text ?? '未回答' }}
                </div>
            </div>

            <div class="quizAnswerBlock">
                <div class="quizAnswerLabel">正解</div>
                <div class="quizAnswerValue correct">
                    {{ $correctChoice->choice_text ?? '正解データなし' }}
                </div>
            </div>

            <div class="quizAnswerBlock">
                <div class="quizAnswerLabel">自信度</div>
                <div class="quizAnswerValue">
                    @if ($confidence === 'high')
                        高い
                    @elseif ($confidence === 'medium')
                        普通
                    @else
                        低い
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('quiz.next', $quiz->id) }}">
                @csrf
                <input type="hidden" name="step" value="{{ $step }}">

                <div class="quizAnswerActions">
                    <a href="{{ route('quiz.start', $quiz->id) }}" class="quizAnswerBackButton">開始画面へ戻る</a>
                    <button type="submit" class="quizAnswerNextButton">
                        {{ $isLast ? '結果を見る' : '次の問題へ' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
@endsection
