const searchForm = document.getElementById('searchForm');
const keywordInput = document.getElementById('keywordInput');
const tagButtons = document.querySelectorAll('.tagItem');
const quizListArea = document.getElementById('quizListArea');

let selectedCategory = '';

async function fetchQuizzes() {
    const keyword = keywordInput.value;

    const params = new URLSearchParams({
        keyword: keyword,
        category: selectedCategory
    });

    // Use form action attribute for the API endpoint
    const url = searchForm.action;

    const response = await fetch(`${url}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    const html = await response.text();
    if (quizListArea) {
        quizListArea.innerHTML = html;
    }
}

if (searchForm) {
    searchForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        await fetchQuizzes();
    });
}

if (tagButtons) {
    tagButtons.forEach(button => {
        button.addEventListener('click', async function () {
            selectedCategory = this.dataset.category;

            tagButtons.forEach(btn => btn.classList.remove('is-active'));
            this.classList.add('is-active');

            await fetchQuizzes();
        });
    });
}
