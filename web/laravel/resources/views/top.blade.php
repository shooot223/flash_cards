@extends('layouts.app')

@section('title', 'Cramist | 問題一覧')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/top.css') }}"/>
@endpush

@section('content')
    <main class="container">
        <section class="hero">
            <h1 class="hero__title">問題一覧</h1>
            <p class="hero__text">気になるテーマの問題を探して、繰り返し学習しましょう。</p>
        </section>

        <section class="searchArea">
            <form id="searchForm" class="searchForm" action="{{ route('top') }}">
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
@endsection


@push('js')
    <script src="{{ asset('js/top.js') }}"></script>
@endpush

