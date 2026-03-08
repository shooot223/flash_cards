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
             <a href="{{ route('quiz.start', $quiz->id) }}" class="cardButton">開始</a>
        </div>
    </article>
@empty
    <div class="emptyBox">該当する問題がありません。</div>
@endforelse
