<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($quiz) ? 'クイズ編集' : 'クイズ作成' }}</title>
    <link rel="stylesheet" href="{{ asset('css/quiz.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
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
    $tagCount = max(5, count($baseTags));
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

    $questionCount = max(5, count($baseQuestions));
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

<form method="POST"
      action="{{ $isEdit ? route('quiz.update', $quiz->id) : route('quiz.confirm') }}"
      class="quizForm">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="formGroup">
        <label class="formLabel" for="title">クイズタイトル</label>
        <input type="text" id="title" name="title" class="formInput" value="{{ old('title', $quiz->title ?? '') }}">
        @error('title')
        <div style="color:#b91c1c; margin-top:6px;">{{ $message }}</div>
        @enderror
    </div>

    <div class="formGroup">
        <label class="formLabel" for="description">説明文</label>
        <textarea id="description" name="description"
                  class="formTextarea">{{ old('description', $quiz->description ?? '') }}</textarea>
        @error('description')
        <div style="color:#b91c1c; margin-top:6px;">{{ $message }}</div>
        @enderror
    </div>

    <div class="formGroup">
        <div style="display:flex; align-items:center; gap:8px;">
            <span class="formLabel">タグ</span>
            <button type="button" id="addTag" class="submitButton" style="padding:6px 10px;">＋</button>
        </div>

        <div id="tagsContainer">
            @for ($i = 0; $i < $tagCount; $i++)
                <div class="tagRow" style="display:flex; gap:8px; margin-top:6px;">
                    <input type="text" id="tag_{{ $i }}" name="tags[]" class="formInput" placeholder="タグ{{ $i + 1 }}"
                           value="{{ $tags[$i] }}">
                    <button type="button" class="removeTag submitButton" style="padding:6px 10px;">－</button>
                </div>
            @endfor
        </div>

        @error('tags')
        <div style="color:#b91c1c; margin-top:6px;">{{ $message }}</div>
        @enderror
    </div>

    <div class="formGroup">
        <div style="display:flex; align-items:center; gap:8px;">
            <span class="formLabel">問題</span>
            <button type="button" id="addQuestion" class="submitButton" style="padding:6px 10px;">＋</button>
        </div>

        <div id="questionsContainer">
            @for ($i = 0; $i < $questionCount; $i++)
                <div class="questionBlock" data-index="{{ $i }}"
                     style="margin-top:16px; padding:16px; border:1px solid #ddd; border-radius:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                        <strong>問題{{ $i + 1 }}</strong>
                        <button type="button" class="removeQuestion submitButton" style="padding:6px 10px;">－</button>
                    </div>

                    <label class="formLabel" for="q_{{ $i }}">問題文</label>
                    <textarea id="q_{{ $i }}" name="questions[{{ $i }}][question]"
                              class="formInput">{{ $questions[$i]['question'] }}</textarea>
                    @error("questions.$i.question")
                    <div style="color:#b91c1c; margin-top:6px;">{{ $message }}</div>
                    @enderror

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
            @endfor
        </div>
    </div>

    <button type="button" class="submitButton" style="margin-right:8px;" onclick="window.history.back();">戻る</button>
    <button type="submit" class="submitButton">
        {{ $isEdit ? '更新する' : '確認へ' }}
    </button>
</form>

<script>
    const tagsContainer = document.getElementById('tagsContainer');
    const addTagBtn = document.getElementById('addTag');

    addTagBtn.addEventListener('click', () => {
        const idx = tagsContainer.querySelectorAll('.tagRow').length;

        const row = document.createElement('div');
        row.className = 'tagRow';
        row.style.display = 'flex';
        row.style.gap = '8px';
        row.style.marginTop = '6px';

        row.innerHTML = `
            <input type="text"
                   id="tag_${idx}"
                   name="tags[]"
                   class="formInput"
                   placeholder="タグ${idx + 1}">
            <button type="button" class="removeTag submitButton" style="padding:6px 10px;">－</button>
        `;

        tagsContainer.appendChild(row);
    });

    const questionsContainer = document.getElementById('questionsContainer');
    const addQuestionBtn = document.getElementById('addQuestion');

    addQuestionBtn.addEventListener('click', () => {
        const idx = questionsContainer.querySelectorAll('.questionBlock').length;

        const wrapper = document.createElement('div');
        wrapper.className = 'questionBlock';
        wrapper.dataset.index = idx;
        wrapper.style.marginTop = '16px';
        wrapper.style.padding = '16px';
        wrapper.style.border = '1px solid #ddd';
        wrapper.style.borderRadius = '8px';

        wrapper.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
            <strong>問題${idx + 1}</strong>
            <button type="button" class="removeQuestion submitButton" style="padding:6px 10px;">－</button>
        </div>

        <label class="formLabel" for="q_${idx}">問題文</label>
        <textarea id="q_${idx}" name="questions[${idx}][question]" class="formInput"></textarea>

        <label class="formLabel">選択肢</label>

        ${[0,1,2,3].map(c => `
            <div style="display:flex; gap:10px; margin-bottom:8px; align-items:center;">
                <input type="radio" name="questions[${idx}][correct]" value="${c}">
                <input type="text"
                       name="questions[${idx}][choices][]"
                       class="formInput"
                       placeholder="選択肢${c + 1}">
            </div>
        `).join('')}
    `;

        questionsContainer.appendChild(wrapper);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeTag')) {
            e.target.closest('.tagRow')?.remove();
        }
        if (e.target.classList.contains('removeQuestion')) {
            e.target.closest('.questionBlock')?.remove();
        }
    });
</script>
</body>
</html>
