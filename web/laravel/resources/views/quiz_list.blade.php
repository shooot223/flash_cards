<div class="sectionTitle">公開中の問題</div>

@forelse ($quizzes as $quiz)
    <article class="card">
        <div class="thumb">Quiz</div>

        <div class="meta">
            <div class="meta__row meta__row--title">
                {{ $quiz->title }}
            </div>

            <div class="meta__row">
                {{ $quiz->description ?? '説明はありません。' }}
            </div>

            @if ($quiz->categories->isNotEmpty())
                <div class="meta__tags">
                    @foreach ($quiz->categories as $category)
                        <span class="miniTag">{{ $category->category_name }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card__right">
            {{-- 必要なら詳細画面 --}}
            {{-- <a href="{{ route('quiz.show', $quiz->id) }}" class="cardButton">詳細</a> --}}
        </div>
    </article>
@empty
    <div class="emptyBox">該当する問題がありません。</div>
@endforelse
