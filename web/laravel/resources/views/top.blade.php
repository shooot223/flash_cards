<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Cramist | 問題一覧</title>
    <link rel="stylesheet" href="{{ asset('/css/top.css') }}"/>
    <link rel="stylesheet" href="{{ asset('/css/header.css') }}" />
</head>

<body>
<header class="header">
    @include('header')
</header>

<main class="container">
    <section class="hero">
        <h1 class="hero__title">問題一覧</h1>
        <p class="hero__text">気になるテーマのクイズを探して、繰り返し学習しましょう。</p>
    </section>

    <section class="searchArea">
        <form method="GET" action="{{ route('top') }}" class="searchForm">
            <input
                type="text"
                name="keyword"
                class="searchInput"
                placeholder="キーワードで検索（例：HTTP / 会計 / 英単語）"
                value="{{ request('keyword') }}"
            >
            <button type="submit" class="searchButton">検索</button>
        </form>
    </section>

    <section class="tagArea">
        <div class="sectionTitle">タグ</div>
        <div class="tagBar">
            @forelse ($categories ?? [] as $category)
                <a
                    href="{{ route('top', ['category' => $category->id]) }}"
                    class="tagItem {{ request('category') == $category->id ? 'is-active' : '' }}"
                >
                    {{ $category->category_name }}
                </a>
            @empty
                <span class="emptyText">タグはまだありません</span>
            @endforelse
        </div>
    </section>

    <section class="listWrap">
        <div class="sectionTitle">公開中のクイズ</div>

        @forelse ($quizzes ?? [] as $quiz)
            <article class="card">
                <div class="thumb">Quiz</div>

                <div class="meta">
                    <div class="meta__row meta__row--title">{{ $quiz->title }}</div>
                    <div class="meta__row">{{ $quiz->description ?? '説明はありません。' }}</div>

                    @if (!empty($quiz->categories) && count($quiz->categories) > 0)
                        <div class="meta__tags">
                            @foreach ($quiz->categories as $category)
                                <span class="miniTag">{{ $category->category_name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

{{--                <div class="card__right">--}}
{{--                    <a href="{{ route('quiz.show', $quiz->id) }}" class="cardButton">詳細</a>--}}
{{--                </div>--}}
            </article>
        @empty
            <div class="emptyBox">該当するクイズがありません。</div>
        @endforelse
    </section>

    @if (isset($quizzes) && method_exists($quizzes, 'links'))
        <div class="pager">
            {{ $quizzes->links() }}
        </div>
    @endif
</main>
</body>
</html>
