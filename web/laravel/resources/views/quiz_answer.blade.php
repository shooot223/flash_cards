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

            @php
                $confidenceMap = [
                    'high' => ['symbol' => '〇', 'class' => 'confidenceHigh'],
                    'medium' => ['symbol' => '△', 'class' => 'confidenceMedium'],
                    'low' => ['symbol' => '×', 'class' => 'confidenceLow'],
                ];

                $confidenceDisplay = (is_string($confidence) && isset($confidenceMap[$confidence])) ? $confidenceMap[$confidence] : ['symbol' => '-', 'class' => ''];
            @endphp

            <div class="quizAnswerBlock confidenceBlock">
                <div class="quizAnswerLabel">自信度</div>
                <div class="quizAnswerValue">
                    <span class="confidenceBadge {{ $confidenceDisplay['class'] }}">
                        {{ $confidenceDisplay['symbol'] }}
                    </span>
                </div>
            </div>

            <div class="quizAnswerBlock explanationBlock" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #e2e8f0;">
                <div class="quizAnswerLabel">解説</div>
                <div class="quizAnswerValue" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6; color: #4a5568; text-align: left; max-height: 15vh; overflow-y: auto;">
                    {{ !empty($question->explanation) ? $question->explanation : '解説なし' }}
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
