@if($quizzes->currentPage() === 1)
    <div class="sectionTitle">公開中の問題</div>
@endif

@forelse ($quizzes as $quiz)
    <article class="card">
        <div class="thumb">
            <img
                src="{{ $quiz->image_path ? Storage::url($quiz->image_path) : asset('img/default_quiz.png') }}"
                alt="{{ $quiz->title }} のサムネイル"
                class="thumbImage"
            >
        </div>

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
            <a href="{{ route('quiz.start', $quiz->id) }}" class="cardButton">問題に挑戦</a>
        </div>
    </article>
@empty
    @if($quizzes->currentPage() === 1)
        <div class="emptyBox">該当する問題がありません。</div>
    @endif
@endforelse

@if($quizzes->hasMorePages())
    <div class="infinite-scroll-trigger" data-next-page="{{ $quizzes->currentPage() + 1 }}" style="text-align:center; padding: 20px; color: var(--muted); font-size: 14px;">
        読み込み中...
    </div>
@endif
