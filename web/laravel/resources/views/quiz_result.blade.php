@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/quiz_result.css') }}"/>
@endpush

@section('title', 'Cramist | クイズの結果')

@section('content')
    <main class="result-page">
        <section class="result-summary">
            <p class="result-summary__label">RESULT</p>
            <h1 class="result-summary__title">{{ $quiz->title }} の結果</h1>

            @php
                $rate = $total > 0 ? round(($score / $total) * 100) : 0;
            @endphp

            <div class="score-card">
                <div class="score-card__main">
                    <span class="score-card__score">{{ $score }}</span>
                    <span class="score-card__slash">/</span>
                    <span class="score-card__total">{{ $total }}</span>
                </div>
                <p class="score-card__text">総合スコア</p>

                <div class="score-meta">
                    <div class="score-meta__item">
                        <span class="score-meta__label">正解数</span>
                        <span class="score-meta__value">{{ $score }}問</span>
                    </div>
                    <div class="score-meta__item">
                        <span class="score-meta__label">不正解数</span>
                        <span class="score-meta__value">{{ $total - $score }}問</span>
                    </div>
                    <div class="score-meta__item">
                        <span class="score-meta__label">正答率</span>
                        <span class="score-meta__value">{{ $rate }}%</span>
                    </div>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="result-notice result-notice--success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="result-notice result-notice--error">
                {{ session('error') }}
            </div>
        @endif

        @guest
            <section class="result-save-box">
                <div class="result-save-box__content">
                    <p class="result-save-box__label">SAVE RESULT</p>
                    <h2 class="result-save-box__title">この結果を保存して、あとで復習しませんか？</h2>
                    <p class="result-save-box__text">
                        ログインまたは新規登録をすると、今回の結果を保存できます。
                        保存した結果は、あとから復習画面で見返せます。
                    </p>
                </div>

                <div class="result-save-box__actions">
                    <form method="POST" action="{{ route('quiz.result.prepare-save', $quiz->id) }}">
                        @csrf
                        <button type="submit" class="result-button result-button--primary">
                            ログインして保存
                        </button>
                    </form>

                    <form method="POST" action="{{ route('quiz.result.prepare-save', $quiz->id) }}">
                        @csrf
                        <input type="hidden" name="mode" value="register">
                        <button type="submit" class="result-button result-button--secondary">
                            新規登録して保存
                        </button>
                    </form>
                </div>
            </section>
        @endguest

        <section class="result-list">
            <div class="result-list__head">
                <h2 class="result-list__title">各問題の結果</h2>
                <p class="result-list__caption">問題文・回答・正解・自信度を確認できます</p>
            </div>

            @forelse($resultDetails as $index => $detail)
                @php
                    $confidenceMap = [
                        'high' => ['symbol' => '〇', 'class' => 'confidence-high'],
                        'medium' => ['symbol' => '△', 'class' => 'confidence-medium'],
                        'low' => ['symbol' => '×', 'class' => 'confidence-low'],
                    ];

                    $confidence = $confidenceMap[$detail['confidence']] ?? ['symbol' => '-', 'class' => ''];
                @endphp

                <article class="result-card">
                    <div class="result-card__top">
                        <div class="result-card__number">第{{ $index + 1 }}問</div>

                        @if($detail['is_correct'])
                            <span class="result-badge result-badge--correct">正解</span>
                        @else
                            <span class="result-badge result-badge--wrong">不正解</span>
                        @endif
                    </div>

                    <div class="result-card__body">
                        <div class="result-row">
                            <p class="result-row__label">問題文</p>
                            <p class="result-row__value result-row__value--question">
                                {{ $detail['question_text'] }}
                            </p>
                        </div>

                        <div class="result-grid">
                            <div class="result-row">
                                <p class="result-row__label">あなたの回答</p>
                                <p class="result-row__value">{{ $detail['selected_answer'] }}</p>
                            </div>

                            <div class="result-row">
                                <p class="result-row__label">正解</p>
                                <p class="result-row__value result-row__value--correct">
                                    {{ $detail['correct_answer'] }}
                                </p>
                            </div>

                            <div class="result-row">
                                <p class="result-row__label">自信度</p>
                                <p class="result-row__value result-row__value--confidence {{ $confidence['class'] }}">
                                    {{ $confidence['symbol'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <p>結果データがありません。</p>
                </div>
            @endforelse
        </section>

        <div class="result-actions">
            <a href="{{ route('quiz.start', $quiz->id) }}" class="result-button result-button--primary">
                もう一度挑戦する
            </a>
            <a href="/" class="result-button result-button--secondary">
                トップへ戻る
            </a>
        </div>
    </main>
@endsection
