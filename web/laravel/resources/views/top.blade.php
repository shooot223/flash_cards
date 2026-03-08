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
        <p class="hero__text">気になるテーマの問題を探して、繰り返し学習しましょう。</p>
    </section>

    <section class="searchArea">
        <form id="searchForm" class="searchForm">
            <input
                type="text"
                name="keyword"
                id="keywordInput"
                class="searchInput"
                placeholder="キーワードで検索（例：HTTP / 会計 / 英単語）"
                value="{{ request('keyword') }}"
            >
            <button type="submit" class="searchButton">検索</button>
        </form>
    </section>

    <section class="tagArea">
        <div class="sectionTitle">タグ</div>
        <div class="tagBar" id="tagBar">
            <button type="button"
                    class="tagItem {{ request('category') ? '' : 'is-active' }}"
                    data-category="">
                すべて
            </button>

            @foreach ($categories as $category)
                <button type="button"
                        class="tagItem {{ request('category') == $category->id ? 'is-active' : '' }}"
                        data-category="{{ $category->id }}">
                    {{ $category->category_name }}
                </button>
            @endforeach
        </div>
    </section>

    <section class="listWrap" id="quizListArea">
        @include('quiz_list', ['quizzes' => $quizzes])
    </section>
</main>

<script>
    const searchForm = document.getElementById('searchForm');
    const keywordInput = document.getElementById('keywordInput');
    const tagButtons = document.querySelectorAll('.tagItem');
    const quizListArea = document.getElementById('quizListArea');

    let selectedCategory = '';

    async function fetchQuizzes() {
        const keyword = keywordInput.value;

        const params = new URLSearchParams({
            keyword: keyword,
            category: selectedCategory
        });

        const response = await fetch(`{{ route('top') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const html = await response.text();
        quizListArea.innerHTML = html;
    }

    searchForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        await fetchQuizzes();
    });

    tagButtons.forEach(button => {
        button.addEventListener('click', async function () {
            selectedCategory = this.dataset.category;

            tagButtons.forEach(btn => btn.classList.remove('is-active'));
            this.classList.add('is-active');

            await fetchQuizzes();
        });
    });
</script>
</body>
</html>
