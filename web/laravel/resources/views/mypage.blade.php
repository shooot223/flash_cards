<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>

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
            @forelse ($createdQuizzes as $quiz)
                <article class="quizCard">
                    <div class="quizCard__thumb">
                        <div class="quizCard__thumbText">QUIZ</div>
                    </div>

                    <div class="quizCard__body">
                        <div class="quizCard__top">
                            <h2 class="quizCard__title">{{ $quiz->title }}</h2>
                            <div class="quizCard__chips">
                                <span class="chip">作成済み</span>
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

                        <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('この問題を削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="actionButton actionButton--delete">
                                削除
                            </button>
                        </form>
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

        {{-- 過去に回答した問題 --}}
        <div class="tabContent" id="tab-answered">
            @forelse ($answeredQuizzes as $quiz)
                <article class="quizCard">
                    <div class="quizCard__thumb quizCard__thumb--answered">
                        <div class="quizCard__thumbText">PLAY</div>
                    </div>

                    <div class="quizCard__body">
                        <div class="quizCard__top">
                            <h2 class="quizCard__title">{{ $quiz->title }}</h2>
                            <div class="quizCard__chips">
                                <span class="chip chip--answered">回答済み</span>
                            </div>
                        </div>

                        <p class="quizCard__description">
                            {{ $quiz->description ?? '説明はありません。' }}
                        </p>

                        <div class="quizCard__meta">
                            <span>最終回答日：{{ optional($quiz->pivot->created_at ?? $quiz->updated_at)->format('Y/m/d') }}</span>
                        </div>
                    </div>

                    <div class="quizCard__actions">
                        <a href="{{ route('quiz.play', $quiz->id) }}" class="actionButton actionButton--primary">
                            もう一度解く
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
    });
</script>
</body>
</html>
