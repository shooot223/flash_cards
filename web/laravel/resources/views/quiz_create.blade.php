<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($quiz) ? 'クイズ編集' : 'クイズ作成' }}</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quiz.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>

@php
    $isEdit = isset($quiz);

    $baseTags = old('tags');
    if (is_null($baseTags) && $isEdit) {
        $baseTags = $quiz->categories->pluck('category_name')->toArray();
    }
    $baseTags = $baseTags ?? [];
    $tagCount = max(3, count($baseTags));
    $tags = array_pad($baseTags, $tagCount, '');

    $baseQuestions = old('questions');

    if (is_null($baseQuestions) && $isEdit) {
        $baseQuestions = $quiz->questions->map(function ($question) {
            $choices = $question->choices->values()->toArray();

            $correctIndex = collect($choices)->search(function ($choice) {
                return (bool) $choice['is_correct'] === true;
            });

            return [
                'question' => $question->question_text,
                'choices' => collect($choices)->pluck('choice_text')->values()->toArray(),
                'correct' => $correctIndex !== false ? $correctIndex : null,
            ];
        })->toArray();
    }

    $baseQuestions = $baseQuestions ?? [];

    $questionCount = max(1, count($baseQuestions));
    $questions = [];

    for ($i = 0; $i < $questionCount; $i++) {
        $q = $baseQuestions[$i] ?? [];

        $questions[] = [
            'question' => $q['question'] ?? '',
            'choices' => array_pad(($q['choices'] ?? []), 4, ''),
            'correct' => $q['correct'] ?? null,
        ];
    }
@endphp

<main class="quizPage">
    <form method="POST"
          action="{{ $isEdit ? route('quiz.update', $quiz->id) : route('quiz.confirm') }}"
          class="quizForm">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <section class="pageHeaderCard">
            <div>
                <div class="pageBadge">{{ $isEdit ? 'Edit Quiz' : 'Create Quiz' }}</div>
                <h1 class="pageTitle">{{ $isEdit ? 'クイズ編集' : 'クイズ作成' }}</h1>
                <p class="pageDescription">
                    問題文・選択肢・正解を設定してクイズを作成します。
                </p>
            </div>
        </section>

        <section class="sectionCard">
            <div class="sectionHeader">
                <h2 class="sectionTitle">基本情報</h2>
            </div>

            <div class="fieldGroup">
                <label class="formLabel" for="title">クイズタイトル</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="formInput"
                    value="{{ old('title', $quiz->title ?? '') }}"
                    placeholder="例：英単語 基礎 / ネットワーク基礎"
                >
                @error('title')
                <div class="fieldError">{{ $message }}</div>
                @enderror
            </div>

            <div class="fieldGroup">
                <label class="formLabel" for="description">説明文</label>
                <textarea
                    id="description"
                    name="description"
                    class="formTextarea"
                    placeholder="このクイズの概要や対象分野を入力してください"
                >{{ old('description', $quiz->description ?? '') }}</textarea>
                @error('description')
                <div class="fieldError">{{ $message }}</div>
                @enderror
            </div>
        </section>

        <section class="sectionCard">
            <div class="sectionHeader">
                <h2 class="sectionTitle">タグ</h2>
                <button type="button" id="addTag" class="circleButton" aria-label="タグを追加">＋</button>
            </div>

            <div id="tagsContainer" class="tagList">
                @for ($i = 0; $i < $tagCount; $i++)
                    <div class="tagRow">
                        <input
                            type="text"
                            id="tag_{{ $i }}"
                            name="tags[]"
                            class="formInput"
                            placeholder="タグ{{ $i + 1 }}"
                            value="{{ $tags[$i] }}"
                        >
                        <button type="button" class="removeButton removeTag" aria-label="タグを削除">－</button>
                    </div>
                @endfor
            </div>

            @error('tags')
            <div class="fieldError">{{ $message }}</div>
            @enderror
        </section>

        <section class="sectionCard">
            <div class="sectionHeader">
                <h2 class="sectionTitle">問題</h2>
            </div>

            <div id="questionsContainer" class="questionList">
                @for ($i = 0; $i < $questionCount; $i++)
                    <section class="questionCard" data-index="{{ $i }}">
                        <div class="questionCardHeader">
                            <div>
                                <div class="questionNumber">問題 {{ $i + 1 }}</div>
                                <div class="questionSubText">4択の選択肢を入力し、正解を1つ選んでください</div>
                            </div>
                            <button type="button" class="removeButton removeQuestion" aria-label="問題を削除">－</button>
                        </div>

                        <div class="fieldGroup">
                            <label class="formLabel" for="q_{{ $i }}">問題文</label>
                            <textarea
                                id="q_{{ $i }}"
                                name="questions[{{ $i }}][question]"
                                class="formTextarea"
                                placeholder="問題文を入力してください"
                            >{{ $questions[$i]['question'] }}</textarea>

                            @error("questions.$i.question")
                            <div class="fieldError">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="fieldGroup">
                            <label class="formLabel">選択肢</label>
                            <div class="correctNote">正解にする選択肢を1つ選んでください</div>

                            <div class="choiceEditorList">
                                @for ($c = 0; $c < 4; $c++)
                                    <label class="choiceEditorCard">
                                        <input
                                            type="radio"
                                            name="questions[{{ $i }}][correct]"
                                            value="{{ $c }}"
                                            class="choiceEditorRadio"
                                            {{ isset($questions[$i]['correct']) && (string)$questions[$i]['correct'] === (string)$c ? 'checked' : '' }}
                                        >

                                        <div class="choiceEditorBody">
                                            <div class="choiceEditorHead">
                                                <span class="choiceIndex">選択肢{{ $c + 1 }}</span>
                                                <span class="correctBadge">正解</span>
                                            </div>

                                            <input
                                                type="text"
                                                name="questions[{{ $i }}][choices][]"
                                                class="formInput choiceEditorInput"
                                                placeholder="選択肢{{ $c + 1 }}"
                                                value="{{ $questions[$i]['choices'][$c] ?? '' }}"
                                            >
                                        </div>
                                    </label>

                                    @error("questions.$i.choices.$c")
                                    <div class="fieldError">{{ $message }}</div>
                                    @enderror
                                @endfor
                            </div>

                            @error("questions.$i.correct")
                            <div class="fieldError">{{ $message }}</div>
                            @enderror
                        </div>
                    </section>
                @endfor
            </div>
            <div class="addQuestionArea">
                <button type="button" id="addQuestion" class="addQuestionButton">
                    ＋ 問題を追加
                </button>
            </div>
        </section>

        <div class="formActions">
            <button type="button" class="secondaryButton" onclick="window.history.back();">戻る</button>
            <button type="submit" class="primaryButton">
                {{ $isEdit ? '更新する' : '確認へ進む' }}
            </button>
        </div>
    </form>
</main>

<script>
    const tagsContainer = document.getElementById('tagsContainer');
    const addTagBtn = document.getElementById('addTag');
    const questionsContainer = document.getElementById('questionsContainer');
    const addQuestionBtn = document.getElementById('addQuestion');

    addTagBtn.addEventListener('click', () => {
        const idx = tagsContainer.querySelectorAll('.tagRow').length;

        const row = document.createElement('div');
        row.className = 'tagRow';

        row.innerHTML = `
            <input
                type="text"
                id="tag_${idx}"
                name="tags[]"
                class="formInput"
                placeholder="タグ${idx + 1}"
            >
            <button type="button" class="removeButton removeTag" aria-label="タグを削除">－</button>
        `;

        tagsContainer.appendChild(row);
    });

    addQuestionBtn.addEventListener('click', () => {
        const idx = questionsContainer.querySelectorAll('.questionCard').length;

        const wrapper = document.createElement('section');
        wrapper.className = 'questionCard';
        wrapper.dataset.index = idx;

        wrapper.innerHTML = `
            <div class="questionCardHeader">
                <div>
                    <div class="questionNumber">問題 ${idx + 1}</div>
                    <div class="questionSubText">4択の選択肢を入力し、正解を1つ選んでください</div>
                </div>
                <button type="button" class="removeButton removeQuestion" aria-label="問題を削除">－</button>
            </div>

            <div class="fieldGroup">
                <label class="formLabel" for="q_${idx}">問題文</label>
                <textarea
                    id="q_${idx}"
                    name="questions[${idx}][question]"
                    class="formTextarea"
                    placeholder="問題文を入力してください"
                ></textarea>
            </div>

            <div class="fieldGroup">
                <label class="formLabel">選択肢</label>
                <div class="correctNote">正解にする選択肢を1つ選んでください</div>

                <div class="choiceEditorList">
                    ${[0,1,2,3].map(c => `
                        <label class="choiceEditorCard">
                            <input
                                type="radio"
                                name="questions[${idx}][correct]"
                                value="${c}"
                                class="choiceEditorRadio"
                            >

                            <div class="choiceEditorBody">
                                <div class="choiceEditorHead">
                                    <span class="choiceIndex">選択肢${c + 1}</span>
                                    <span class="correctBadge">正解</span>
                                </div>

                                <input
                                    type="text"
                                    name="questions[${idx}][choices][]"
                                    class="formInput choiceEditorInput"
                                    placeholder="選択肢${c + 1}"
                                >
                            </div>
                        </label>
                    `).join('')}
                </div>
            </div>
        `;

        questionsContainer.appendChild(wrapper);
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeTag')) {
            e.target.closest('.tagRow')?.remove();
        }

        if (e.target.classList.contains('removeQuestion')) {
            const cards = questionsContainer.querySelectorAll('.questionCard');
            if (cards.length <= 1) return;
            e.target.closest('.questionCard')?.remove();
        }
    });
</script>
</body>
</html>
