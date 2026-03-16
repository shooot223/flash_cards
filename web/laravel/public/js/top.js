const searchForm = document.getElementById('searchForm');
const keywordInput = document.getElementById('keywordInput');
const tagButtons = document.querySelectorAll('.tagItem');
const quizListArea = document.getElementById('quizListArea');

let selectedCategory = '';
let currentPage = 1;
let isLoading = false;
let observer = null;

async function fetchQuizzes(isAppend = false) {
    if (isLoading) return;
    isLoading = true;

    const keyword = keywordInput.value;

    const params = new URLSearchParams({
        keyword: keyword,
        category: selectedCategory,
        page: currentPage
    });

    const url = searchForm.action;

    try {
        const response = await fetch(`${url}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const html = await response.text();
        if (quizListArea) {
            const oldTrigger = quizListArea.querySelector('.infinite-scroll-trigger');
            if (oldTrigger && isAppend) {
                oldTrigger.remove();
            }

            if (isAppend) {
                quizListArea.insertAdjacentHTML('beforeend', html);
            } else {
                quizListArea.innerHTML = html;
            }

            setupInfiniteScroll();
        }
    } finally {
        isLoading = false;
    }
}

function setupInfiniteScroll() {
    if (observer) {
        observer.disconnect();
    }

    const trigger = document.querySelector('.infinite-scroll-trigger');
    if (!trigger) return;

    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading) {
            currentPage = trigger.dataset.nextPage;
            fetchQuizzes(true);
        }
    }, {
        rootMargin: '100px',
        threshold: 0.1
    });

    observer.observe(trigger);
}

if (searchForm) {
    searchForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        currentPage = 1;
        await fetchQuizzes(false);
    });
}

if (tagButtons) {
    tagButtons.forEach(button => {
        button.addEventListener('click', async function () {
            selectedCategory = this.dataset.category;

            tagButtons.forEach(btn => btn.classList.remove('is-active'));
            this.classList.add('is-active');

            currentPage = 1;
            await fetchQuizzes(false);
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    setupInfiniteScroll();

    const tagBar = document.getElementById('tagBar');
    const tagExpandBtn = document.getElementById('tagExpandBtn');

    if (tagBar && tagExpandBtn) {
        // Check if content exceeds 3 lines
        if (tagBar.scrollHeight > tagBar.clientHeight) {
            tagExpandBtn.style.display = 'block';
        }

        tagExpandBtn.addEventListener('click', function () {
            tagBar.classList.toggle('is-expanded');
            if (tagBar.classList.contains('is-expanded')) {
                tagExpandBtn.textContent = '－ 閉じる';
            } else {
                tagExpandBtn.textContent = '＋ 詳細表示';
            }
        });
    }
});
