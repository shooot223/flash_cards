@extends('layouts.app')
@push('css')
    <link rel="stylesheet" href="{{ asset('css/quiz.css') }}"/>
@endpush

@section('title', 'Cramist | 問題作成')
@section('content')

    @php
        $isEditPage = isset($quiz);
        $isEdit = $isEditPage || !empty($isEditFromConfirm);

        $quizId = $quiz->id ?? ($quizIdFromConfirm ?? null);

        $fallbackTitle = $oldFormData['title'] ?? ($quiz->title ?? '');
        $fallbackDescription = $oldFormData['description'] ?? ($quiz->description ?? '');

        $baseTags = old('tags');

        if (is_null($baseTags)) {
            if (!empty($oldFormData['tags'])) {
                $baseTags = $oldFormData['tags'];
            } elseif ($isEditPage) {
                $baseTags = $quiz->categories->pluck('category_name')->toArray();
            } else {
                $baseTags = [];
            }
        }

        $tagCount = max(3, count($baseTags));
        $tags = array_pad($baseTags, $tagCount, '');

        $baseQuestions = old('questions');

        if (is_null($baseQuestions)) {
            if (!empty($oldFormData['questions'])) {
                $baseQuestions = $oldFormData['questions'];
            } elseif ($isEditPage) {
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
            } else {
                $baseQuestions = [];
            }
        }

        $questionCount = max(1, count($baseQuestions));
        $questions = [];

        for ($i = 0; $i < $questionCount; $i++) {
            $q = $baseQuestions[$i] ?? [];

            $questions[] = [
                'question' => $q['question'] ?? '',
                'choices' => array_pad($q['choices'] ?? [], 4, ''),
                'correct' => $q['correct'] ?? null,
            ];
        }

        $tempImageValue = old('temp_quiz_image', $tempQuizImage ?? '');
        $currentImageValue = old('current_image_path', $quiz->image_path ?? ($currentImagePath ?? ''));

        $previewImage = '';
        if (!empty($tempImageValue)) {
            $previewImage = asset('storage/' . $tempImageValue);
        } elseif (!empty($currentImageValue)) {
            $previewImage = asset('storage/' . $currentImageValue);
        }
    @endphp

    <main class="quizPage">
        <form action="{{ route('quiz.confirm') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="temp_quiz_image" value="{{ $tempImageValue }}">
            <input type="hidden" name="current_image_path" value="{{ $currentImageValue }}">

            @if($isEdit)
                <input type="hidden" name="is_edit" value="1">
                <input type="hidden" name="quiz_id" value="{{ $quizId }}">
            @endif

            <section class="pageHeaderCard">
                <div>
                    <div class="pageBadge">{{ $isEdit ? 'Edit Quiz' : 'Create Quiz' }}</div>
                    <h1 class="pageTitle">{{ $isEdit ? '問題編集' : '問題作成' }}</h1>
                    <p class="pageDescription">
                        問題文・選択肢・正解を設定して問題を作成します。
                    </p>
                </div>
            </section>

            <section class="sectionCard">
                <div class="sectionHeader">
                    <h2 class="sectionTitle">基本情報</h2>
                </div>

                <div class="fieldGroup">
                    <label class="formLabel" for="title">問題タイトル</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="formInput"
                        value="{{ old('title', $fallbackTitle) }}"
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
                        placeholder="この問題の概要や対象分野を入力してください"
                    >{{ old('description', $fallbackDescription) }}</textarea>
                    @error('description')
                    <div class="fieldError">{{ $message }}</div>
                    @enderror
                </div>

                <div class="fieldGroup">
                    <label for="quiz_image" class="formLabel">問題画像</label>

                    <div id="quizImagePreviewArea">
                        @if (!empty($previewImage))
                            <div class="previewImageWrap">
                                <img src="{{ $previewImage }}" alt="問題画像プレビュー" class="previewImage">
                            </div>
                        @endif
                    </div>

                    <input type="file" name="quiz_image" id="quiz_image" class="formInputFile" accept="image/*">

                    @error('quiz_image')
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
                                <button type="button" class="removeButton removeQuestion" aria-label="問題を削除">－
                                </button>
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
                                                {{ isset($questions[$i]['correct']) && (string) $questions[$i]['correct'] === (string) $c ? 'checked' : '' }}
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
                <button type="submit" class="primaryButton">確認へ進む</button>
            </div>
        </form>
    </main>
@endsection

@push('js')
    <script src="{{ asset('js/quiz_create.js') }}"></script>
@endpush
