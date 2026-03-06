<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ確認</title>
    <link rel="stylesheet" href="{{ asset('css/quiz.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>

<div class="quizForm">

    <div class="formGroup">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <strong style="font-size:16px;">入力内容の確認</strong>
            <span style="color:#6b7280; font-size:12px;">この内容で作成してよいか確認してください</span>
        </div>
    </div>

    @php
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $tags = $data['tags'] ?? [];
        $questions = $data['questions'] ?? [];
    @endphp

    <div class="formGroup">
        <label class="formLabel">タイトル</label>
        <div class="formInput" style="background:#f9fafb;">{{ $title }}</div>

        <label class="formLabel">説明</label>
        <div class="formInput" style="background:#f9fafb; white-space:pre-wrap; min-height:110px;">{{ $description }}</div>

        <label class="formLabel">タグ</label>
        @php
            $tagsFiltered = collect($tags)->filter(fn($t) => $t !== null && $t !== '')->values()->all();
        @endphp
        <div class="formInput" style="background:#f9fafb;">
            {{ count($tagsFiltered) ? implode(' / ', $tagsFiltered) : '（なし）' }}
        </div>
    </div>

    <div class="formGroup">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <strong>問題</strong>
        </div>

        <div id="questionsContainer">
            @php $shown = 0; @endphp
            @foreach ($questions as $i => $q)
                @php
                    $qQuestion = $q['question'] ?? null;
                    $qAnswer   = $q['answer'] ?? null;
                    $qChoices  = $q['choices'] ?? [];
                @endphp

                @if ($qQuestion === null || $qQuestion === '')
                    @continue
                @endif

                @php $shown++; @endphp

                <div class="questionBlock">
                    <div>
                        <strong>Q{{ $i + 1 }}</strong>
                    </div>

                    <label class="formLabel">問題文</label>
                    <div class="formInput" style="background:#f9fafb; white-space:pre-wrap;">{{ $qQuestion }}</div>

                    <label class="formLabel">答え</label>
                    <div class="formInput" style="background:#f9fafb;">{{ $qAnswer }}</div>

                    <label class="formLabel">選択肢</label>
                    <div class="formInput" style="background:#f9fafb;">
                        <ol style="margin:0; padding-left:18px;">
                            @foreach ($qChoices as $c)
                                @if ($c === null || $c === '')
                                    @continue
                                @endif
                                <li>{{ $c }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            @endforeach

            @if ($shown === 0)
                <div class="formInput" style="background:#f9fafb; color:#6b7280;">（表示できる問題がありません）</div>
            @endif
        </div>
    </div>

    <div style="display:flex; gap:12px; flex-wrap:wrap; justify-content:flex-end;">
        {{-- 修正する（createへPOSTして old() 復元） --}}
        <form method="post" action="{{ route('quiz.create') }}">
            @csrf
            <input type="hidden" name="title" value="{{ $title }}">
            <input type="hidden" name="description" value="{{ $description }}">

            @foreach ($tags as $ti => $t)
                <input type="hidden" name="tags[{{ $ti }}]" value="{{ $t }}">
            @endforeach

            @foreach ($questions as $i => $q)
                <input type="hidden" name="questions[{{ $i }}][question]" value="{{ $q['question'] ?? '' }}">
                <input type="hidden" name="questions[{{ $i }}][answer]" value="{{ $q['answer'] ?? '' }}">

                @foreach (($q['choices'] ?? []) as $j => $c)
                    <input type="hidden" name="questions[{{ $i }}][choices][{{ $j }}]" value="{{ $c }}">
                @endforeach
            @endforeach

            <button type="submit"
                    class="submitButton"
                    style="background:#fff; color:var(--text); border:1px solid var(--border-strong); box-shadow:none;">
                修正する
            </button>
        </form>

        {{-- 作成する（storeへPOST） --}}
        <form method="post" action="{{ route('quiz.store') }}">
            @csrf
            <input type="hidden" name="title" value="{{ $title }}">
            <input type="hidden" name="description" value="{{ $description }}">

            @foreach ($tags as $ti => $t)
                <input type="hidden" name="tags[{{ $ti }}]" value="{{ $t }}">
            @endforeach

            @foreach ($questions as $i => $q)
                <input type="hidden" name="questions[{{ $i }}][question]" value="{{ $q['question'] ?? '' }}">
                <input type="hidden" name="questions[{{ $i }}][answer]" value="{{ $q['answer'] ?? '' }}">

                @foreach (($q['choices'] ?? []) as $j => $c)
                    <input type="hidden" name="questions[{{ $i }}][choices][{{ $j }}]" value="{{ $c }}">
                @endforeach
            @endforeach

            <button type="submit" class="submitButton">この内容で作成</button>
        </form>
    </div>

</div>
</body>
</html>
