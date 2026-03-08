<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ確認</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quiz.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>

@php
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $tags = $data['tags'] ?? [];
    $questions = $data['questions'] ?? [];
@endphp

<main class="quizPage">
    <div class="quizForm">

        <section class="pageHeaderCard">
            <div>
                <div class="pageBadge">Confirm Quiz</div>
                <h1 class="pageTitle">入力内容の確認</h1>
                <p class="pageDescription">
                    内容を確認して、問題なければ登録してください。
                </p>
            </div>
        </section>

        <section class="sectionCard">
            <div class="sectionHeader">
                <h2 class="sectionTitle">基本情報</h2>
            </div>

            <div class="fieldGroup">
                <label class="formLabel">クイズタイトル</label>
                <div class="confirmBox">{{ $title }}</div>
            </div>

            <div class="fieldGroup">
                <label class="formLabel">説明文</label>
                <div class="confirmBox confirmBoxPre">{{ $description }}</div>
            </div>
        </section>

        <section class="sectionCard">
            <div class="sectionHeader">
                <h2 class="sectionTitle">タグ</h2>
            </div>

            <div class="confirmTagList">
                @php
                    $filteredTags = collect($tags)->filter(fn($tag) => $tag !== null && $tag !== '');
                @endphp

                @forelse ($filteredTags as $tag)
                    <span class="confirmTag">{{ $tag }}</span>
                @empty
                    <div class="emptyText">タグはありません</div>
                @endforelse
            </div>
        </section>

        <section class="sectionCard">
            <div class="sectionHeader">
                <h2 class="sectionTitle">問題</h2>
            </div>

            <div class="questionList">
                @forelse ($questions as $i => $question)
                    @if (!empty($question['question']))
                        <section class="questionCard">
                            <div class="questionCardHeader">
                                <div>
                                    <div class="questionNumber">問題 {{ $i + 1 }}</div>
                                    <div class="questionSubText">登録前の確認内容です</div>
                                </div>
                            </div>

                            <div class="fieldGroup">
                                <label class="formLabel">問題文</label>
                                <div class="confirmBox confirmBoxPre">
                                    {{ $question['question'] ?? '' }}
                                </div>
                            </div>

                            <div class="fieldGroup">
                                <label class="formLabel">選択肢</label>

                                <div class="confirmChoiceList">
                                    @foreach (($question['choices'] ?? []) as $c => $choiceText)
                                        <div class="confirmChoiceRow">
                                            <div class="confirmChoiceLeft">
                                                <span class="confirmChoiceIndex">選択肢{{ $c + 1 }}</span>

                                                @if (isset($question['correct']) && (string)$question['correct'] === (string)$c)
                                                    <span class="confirmCorrectBadge">正解</span>
                                                @endif
                                            </div>

                                            <div class="confirmChoiceText">
                                                {{ $choiceText }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endif
                @empty
                    <div class="emptyText">問題はありません</div>
                @endforelse
            </div>
        </section>

        <div class="formActions">

            <form method="POST" action="{{ route('quiz.create') }}">
                @csrf

                <input type="hidden" name="title" value="{{ $title }}">
                <input type="hidden" name="description" value="{{ $description }}">

                @foreach ($tags as $ti => $tag)
                    <input type="hidden" name="tags[{{ $ti }}]" value="{{ $tag }}">
                @endforeach

                @foreach ($questions as $i => $question)
                    <input type="hidden" name="questions[{{ $i }}][question]" value="{{ $question['question'] ?? '' }}">
                    <input type="hidden" name="questions[{{ $i }}][correct]" value="{{ $question['correct'] ?? '' }}">

                    @foreach (($question['choices'] ?? []) as $c => $choiceText)
                        <input type="hidden" name="questions[{{ $i }}][choices][{{ $c }}]" value="{{ $choiceText }}">
                    @endforeach
                @endforeach

                <button type="submit" class="secondaryButton">修正する</button>
            </form>

            <form method="POST" action="{{ route('quiz.store') }}">
                @csrf

                <input type="hidden" name="title" value="{{ $title }}">
                <input type="hidden" name="description" value="{{ $description }}">

                @foreach ($tags as $ti => $tag)
                    <input type="hidden" name="tags[{{ $ti }}]" value="{{ $tag }}">
                @endforeach

                @foreach ($questions as $i => $question)
                    <input type="hidden" name="questions[{{ $i }}][question]" value="{{ $question['question'] ?? '' }}">
                    <input type="hidden" name="questions[{{ $i }}][correct]" value="{{ $question['correct'] ?? '' }}">

                    @foreach (($question['choices'] ?? []) as $c => $choiceText)
                        <input type="hidden" name="questions[{{ $i }}][choices][{{ $c }}]" value="{{ $choiceText }}">
                    @endforeach
                @endforeach

                <button type="submit" class="primaryButton">登録する</button>
            </form>

        </div>
    </div>
</main>
</body>
</html>
