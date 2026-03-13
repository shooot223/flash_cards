@extends('layouts.app')

@section('title', 'マイページ')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endpush

@section('content')
<div class="mypagePage">
    <section class="mypageHero">
        <div class="mypageHero__left">
            <div class="mypageHero__badge">My Page</div>
            <h1 class="mypageHero__title">マイページ</h1>
            <p class="mypageHero__text">
                作成した問題や過去の回答履歴を確認できます。
            </p>
        </div>

        <div class="mypageHero__right">
            <div class="profileCard">
                <div class="profileCard__icon">
                    {{ mb_substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="profileCard__body">
                    <div class="profileCard__name">{{ Auth::user()->name }}</div>
                    <div class="profileCard__email">{{ Auth::user()->email }}</div>
                </div>
                <div class="profileCard__actions">
                    <a href="{{ route('profile.edit') }}" class="profileEditButton">
                        ユーザー情報編集
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="quickActions">
        <a href="{{ route('quiz.create') }}" class="quickAction quickAction--primary">
            <span class="quickAction__icon">＋</span>
            <span class="quickAction__text">新しく問題を作成</span>
        </a>
    </section>

    <section class="mypageStats">
        <div class="statCard">
            <div class="statCard__label">作成した問題</div>
            <div class="statCard__value">{{ $createdQuizzes->count() }}</div>
        </div>
        <div class="statCard">
            <div class="statCard__label">回答した問題</div>
            <div class="statCard__value">{{ $answeredQuizzes->count() }}</div>
        </div>
    </section>

    <section class="mypageMainCard">
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
            <div class="bulkActionBar">
                <label class="bulkActionBar__check">
                    <input type="checkbox" id="checkAllCreated" class="bulkActionBar__checkbox">
                    <span>すべて選択</span>
                </label>

                <div class="bulkActionBar__right">
            <span class="bulkActionBar__count">
                選択中: <span id="selectedCount">0</span>件
            </span>
                    <button type="button" id="exportSelectedButton" class="bulkActionBar__button">
                        選択した問題をCSV出力
                    </button>
                </div>
            </div>

            @if ($errors->has('quiz_ids'))
                <p class="formError">{{ $errors->first('quiz_ids') }}</p>
            @endif

            @forelse ($createdQuizzes as $quiz)
                <article class="quizCard quizCard--selectable">
                    <div class="quizCard__select">
                        <label class="quizSelectCheckbox">
                            <input
                                type="checkbox"
                                value="{{ $quiz->id }}"
                                class="quizCheckbox"
                            >
                            <span class="quizSelectCheckbox__box"></span>
                        </label>
                    </div>

                    <div class="quizCard__thumb">
                        <img src="{{ $quiz->image_path ? asset('storage/' . $quiz->image_path) : asset('img/default_quiz.png') }}"
                             alt="quiz thumbnail"
                             class="quizCard__thumbImage">
                    </div>

                    <div class="quizCard__body">
                        <div class="quizCard__top">
                            <h2 class="quizCard__title">{{ $quiz->title }}</h2>
                            <div class="quizCard__chips">
                                @if ($quiz->is_public)
                                    <span class="chip chip--public">公開中</span>
                                @else
                                    <span class="chip chip--private">非公開</span>
                                @endif
                            </div>
                        </div>

                        <p class="quizCard__description">
                            {{ $quiz->description ?? '説明はありません。' }}
                        </p>

                        <div class="quizCard__meta">
                            <span>作成日：{{ optional($quiz->created_at)->format('Y/m/d') }}</span>
                        </div>
                    </div>

                    <div class="quizCard__actions">
                        <a href="{{ route('quiz.edit', $quiz->id) }}" class="actionButton actionButton--edit">
                            編集
                        </a>

                        @if ($quiz->is_public)
                            <form action="{{ route('quiz.private', $quiz->id) }}" method="POST" onsubmit="return confirm('この問題を非公開にしますか？');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="actionButton actionButton--warning">
                                    非公開にする
                                </button>
                            </form>
                        @else
                            <form action="{{ route('quiz.public', $quiz->id) }}" method="POST" onsubmit="return confirm('この問題を再公開しますか？');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="actionButton actionButton--primary">
                                    再公開する
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="emptyState">
                    <div class="emptyState__icon">✍️</div>
                    <div class="emptyState__title">まだ問題を作成していません</div>
                    <div class="emptyState__text">
                        最初の問題を作成して、学習を始めましょう。
                    </div>
                    <a href="{{ route('quiz.create') }}" class="emptyState__button">問題を作成する</a>
                </div>
            @endforelse
        </div>

        <form action="{{ route('quiz.export.csv') }}" method="POST" id="exportCsvForm" style="display: none;">
            @csrf
        </form>

        {{-- 過去に回答した問題 --}}
        <div class="tabContent" id="tab-answered">
            @forelse ($answeredQuizzes as $quiz)
                <article class="quizCard">
                    <div class="quizCard__thumb">
                        <img src="{{ $quiz->image_path ? asset('storage/' . $quiz->image_path) : asset('img/default_quiz.png') }}" alt="quiz thumbnail" class="quizCard__thumbImage">
                    </div>

                    <div class="quizCard__body">
                        <div class="quizCard__top">
                            <h2 class="quizCard__title">{{ $quiz->title }}</h2>

                            <div class="quizCard__chips">
                                <span class="chip chip--answered">回答済み</span>

                                @if ($quiz->is_public)
                                    <span class="chip chip--public">公開中</span>
                                @else
                                    <span class="chip chip--private">非公開</span>
                                @endif
                            </div>
                        </div>

                        <p class="quizCard__description">
                            {{ $quiz->description ?? '説明はありません。' }}
                        </p>

                        <div class="quizCard__meta">
                    <span>
                        最終回答日：
                        {{ optional($quiz->pivot->created_at ?? $quiz->updated_at)->format('Y/m/d') }}
                    </span>
                        </div>
                    </div>

                    <div class="quizCard__actions">

                        {{-- もう一度解く --}}
                        <a href="{{ route('quiz.play', $quiz->id) }}"
                           class="actionButton actionButton--primary">
                            もう一度解く
                        </a>

                        {{-- 復習 --}}
                        <a href="{{ route('quiz.result', $quiz->id) }}"
                           class="actionButton actionButton--secondary">
                            復習する
                        </a>

                    </div>
                </article>
            @empty
                <div class="emptyState">
                    <div class="emptyState__icon">📘</div>
                    <div class="emptyState__title">まだ回答した問題はありません</div>
                    <div class="emptyState__text">
                        気になる問題を解いて、履歴を増やしていきましょう。
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('JS')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('.tabButton');
        const tabContents = document.querySelectorAll('.tabContent');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const target = button.dataset.tab;

                tabButtons.forEach(btn => btn.classList.remove('is-active'));
                tabContents.forEach(content => content.classList.remove('is-active'));

                button.classList.add('is-active');

                const targetContent = document.getElementById(`tab-${target}`);
                if (targetContent) {
                    targetContent.classList.add('is-active');
                }
            });
        });

        //CSV出力用フォーム
        const checkAllCreated = document.getElementById('checkAllCreated');
        const quizCheckboxes = document.querySelectorAll('.quizCheckbox');
        const selectedCount = document.getElementById('selectedCount');
        const exportSelectedButton = document.getElementById('exportSelectedButton');
        const exportCsvForm = document.getElementById('exportCsvForm');

        function updateSelectedCount() {
            const checkedCount = document.querySelectorAll('.quizCheckbox:checked').length;
            selectedCount.textContent = checkedCount;

            if (checkAllCreated) {
                checkAllCreated.checked = quizCheckboxes.length > 0 && checkedCount === quizCheckboxes.length;
            }

            quizCheckboxes.forEach((checkbox) => {
                const card = checkbox.closest('.quizCard');
                if (!card) return;

                if (checkbox.checked) {
                    card.classList.add('is-selected');
                } else {
                    card.classList.remove('is-selected');
                }
            });
        }

        if (checkAllCreated) {
            checkAllCreated.addEventListener('change', function () {
                quizCheckboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });
        }

        quizCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        if (exportSelectedButton) {
            exportSelectedButton.addEventListener('click', function () {
                exportCsvForm.querySelectorAll('input[name="quiz_ids[]"]').forEach(el => el.remove());

                const checked = document.querySelectorAll('.quizCheckbox:checked');

                if (checked.length === 0) {
                    alert('CSV出力する問題を選択してください。');
                    return;
                }

                checked.forEach((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'quiz_ids[]';
                    input.value = checkbox.value;
                    exportCsvForm.appendChild(input);
                });

                exportCsvForm.submit();
            });
        }

        updateSelectedCount();
    });
</script>
@endpush
