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



        {{-- ===== タブナビゲーション ===== --}}
        @php
            /* タブ別の件数を事前集計 */
            $countAll      = count($resultDetails);
            $countRequired = 0; // 確認必須：〇 or × で不正解
            $countAmbig    = 0; // 曖昧：△
            $countOk       = 0; // 問題なし：正解

            foreach ($resultDetails as $d) {
                if (!$d['is_correct'] || $d['confidence'] === 'low') {
                    /* 不正解 or 自信度× => 確認必須 */
                    $countRequired++;
                } elseif ($d['confidence'] === 'medium') {
                    /* それ以外で、自信度△ => 曖昧 */
                    $countAmbig++;
                } else {
                    /* それ以外(正解 かつ 自信度〇など) => 問題なし */
                    $countOk++;
                }
            }
        @endphp

        <section class="result-list">
            <div class="result-list__head">
                <h2 class="result-list__title">各問題の結果</h2>
                <p class="result-list__caption">問題文・回答・正解・自信度を確認できます</p>
            </div>

            <div class="result-tabs">
                <button class="result-tab result-tab--active" data-tab="all">
                    すべて
                    <span class="result-tab__badge">{{ $countAll }}</span>
                </button>
                <button class="result-tab" data-tab="required">
                    確認必須
                    <span class="result-tab__badge result-tab__badge--required">{{ $countRequired }}</span>
                </button>
                <button class="result-tab" data-tab="ambig">
                    曖昧
                    <span class="result-tab__badge result-tab__badge--ambig">{{ $countAmbig }}</span>
                </button>
                <button class="result-tab" data-tab="ok">
                    問題なし
                    <span class="result-tab__badge result-tab__badge--ok">{{ $countOk }}</span>
                </button>
            </div>

            <div id="result-cards-wrapper">
            @forelse($resultDetails as $index => $detail)
                @php
                    $confidenceMap = [
                        'high' => ['symbol' => '〇', 'class' => 'confidence-high'],
                        'medium' => ['symbol' => '△', 'class' => 'confidence-medium'],
                        'low' => ['symbol' => '×', 'class' => 'confidence-low'],
                    ];

                    $confidence = $confidenceMap[$detail['confidence']] ?? ['symbol' => '-', 'class' => ''];

                    /* タブ分類を data 属性で付与 */
                    if (!$detail['is_correct'] || $detail['confidence'] === 'low') {
                        $tabGroup = 'required';
                    } elseif ($detail['confidence'] === 'medium') {
                        $tabGroup = 'ambig';
                    } else {
                        $tabGroup = 'ok';
                    }
                @endphp

                <article class="result-card" data-tab-group="{{ $tabGroup }}">
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
            </div>

            {{-- タブフィルタが適用されたときに "該当なし" を表示するための空ステート --}}
            <div id="tab-empty-state" class="empty-state" style="display:none;">
                <p>該当する問題はありません。</p>
            </div>
        </section>

        @push('js')
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            /* ----- タブ切り替えロジック ----- */
            const tabs    = document.querySelectorAll('.result-tab');
            const cards   = document.querySelectorAll('#result-cards-wrapper .result-card');
            const emptyEl = document.getElementById('tab-empty-state');

            function switchTab(targetTab) {
                /* タブボタンのアクティブ状態を更新 */
                tabs.forEach(btn => {
                    btn.classList.toggle('result-tab--active', btn.dataset.tab === targetTab);
                });

                /* カード表示・非表示を切り替え */
                let visible = 0;
                cards.forEach(card => {
                    const match = targetTab === 'all' || card.dataset.tabGroup === targetTab;
                    card.style.display = match ? '' : 'none';
                    if (match) visible++;
                });

                /* 該当なしの場合は空ステートを表示 */
                emptyEl.style.display = visible === 0 ? '' : 'none';
            }

            tabs.forEach(btn => {
                btn.addEventListener('click', () => switchTab(btn.dataset.tab));
            });
        });
        </script>
        @endpush

        <div class="result-actions">
            <a href="{{ route('quiz.play', $quiz->id) }}" class="result-button result-button--primary">
                もう一度挑戦する
            </a>
            <a href="{{ route('mypage') }}" class="result-button result-button--secondary">
                マイページへ戻る
            </a>
        </div>
    </main>
@endsection
