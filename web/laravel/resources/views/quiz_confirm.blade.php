@extends('layouts.app')
@push('css')
    <link rel="stylesheet" href="{{ asset('css/quiz.css') }}"/>
@endpush

@section('title', 'Cramist | 問題登録の確認')
@section('content')
    @php
        $title = $formData['title'] ?? '';
        $description = $formData['description'] ?? '';
        $tags = $formData['tags'] ?? [];
        $questions = $formData['questions'] ?? [];
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
                    <label class="formLabel">問題タイトル</label>
                    <div class="confirmBox">{{ $title }}</div>
                </div>

                <div class="fieldGroup">
                    <label class="formLabel">説明文</label>
                    <div class="confirmBox confirmBoxPre">{{ $description }}</div>
                </div>

                <div class="fieldGroup">
                    <label class="formLabel">問題画像</label>
                    @if (!empty($tempQuizImage))
                        <div class="confirmImageWrap">
                            <img src="{{ Storage::url($tempQuizImage) }}" alt="問題画像" class="confirmImage">
                        </div>
                    @elseif (!empty($currentImagePath))
                        <div class="confirmImageWrap">
                            <img src="{{ Storage::url($currentImagePath) }}" alt="現在の問題画像" class="confirmImage">
                        </div>
                    @else
                        <div class="confirmImageWrap">
                            <img src="{{ asset('img/default_quiz.png') }}" alt="デフォルト画像" class="confirmImage">
                        </div>
                    @endif
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

            <form method="POST" action="{{ $isEdit ? route('quiz.update', $quizId) : route('quiz.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <input type="hidden" name="title" value="{{ $formData['title'] ?? '' }}">
                <input type="hidden" name="description" value="{{ $formData['description'] ?? '' }}">
                <input type="hidden" name="temp_quiz_image" value="{{ $tempQuizImage ?? '' }}">
                <input type="hidden" name="current_image_path" value="{{ $currentImagePath ?? '' }}">

                @if(!empty($formData['tags']))
                    @foreach($formData['tags'] as $tag)
                        <input type="hidden" name="tags[]" value="{{ $tag }}">
                    @endforeach
                @endif

                @if(!empty($formData['questions']))
                    @foreach($formData['questions'] as $i => $question)
                        <input type="hidden" name="questions[{{ $i }}][question]"
                               value="{{ $question['question'] ?? '' }}">
                        <input type="hidden" name="questions[{{ $i }}][correct]"
                               value="{{ $question['correct'] ?? '' }}">

                        @foreach(($question['choices'] ?? []) as $choice)
                            <input type="hidden" name="questions[{{ $i }}][choices][]" value="{{ $choice }}">
                        @endforeach
                    @endforeach
                @endif

                <div class="formActions">
                    <button type="button" class="secondaryButton" onclick="history.back()">戻る</button>
                    <button type="submit" class="primaryButton">
                        {{ $isEdit ? '更新する' : '登録する' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
@endsection
