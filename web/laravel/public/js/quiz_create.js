const tagsContainer = document.getElementById('tagsContainer');
const addTagBtn = document.getElementById('addTag');
const questionsContainer = document.getElementById('questionsContainer');
const addQuestionBtn = document.getElementById('addQuestion');

if (addTagBtn) {
    addTagBtn.addEventListener('click', () => {
        const idx = tagsContainer.querySelectorAll('.tagRow').length;

        const row = document.createElement('div');
        row.className = 'tagRow';

        row.innerHTML = `
        <div>
            <input
                type="text"
                id="tag_${idx}"
                name="tags[]"
                class="formInput"
                placeholder="タグ${idx + 1}"
            >
        </div>
        <button type="button" class="removeButton removeTag" aria-label="タグを削除">－</button>
    `;

        tagsContainer.appendChild(row);
    });
}

if (addQuestionBtn) {
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
            <label class="formLabel" for="expl_${idx}">解説（任意）</label>
            <textarea
                id="expl_${idx}"
                name="questions[${idx}][explanation]"
                class="formTextarea"
                placeholder="回答後に表示される解説を入力してください（任意）"
            ></textarea>
        </div>

        <div class="fieldGroup">
            <label class="formLabel">選択肢</label>
            <div class="correctNote">正解にする選択肢を1つ選んでください</div>

            <div class="choiceEditorList">
                ${[0, 1, 2, 3].map(c => `
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
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('removeTag')) {
        e.target.closest('.tagRow')?.remove();
    }

    if (e.target.classList.contains('removeQuestion')) {
        const cards = questionsContainer.querySelectorAll('.questionCard');
        if (cards.length <= 1) return;
        e.target.closest('.questionCard')?.remove();
    }
});

// 画像の即時プレビュー
const quizImageInput = document.getElementById('quiz_image');

if (quizImageInput) {
    quizImageInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (!file) return;
        if (!file.type.startsWith('image/')) return;

        let previewWrap = document.querySelector('.previewImageWrap');
        let previewImage = document.querySelector('.previewImage');

        if (!previewWrap) {
            previewWrap = document.createElement('div');
            previewWrap.className = 'previewImageWrap';

            previewImage = document.createElement('img');
            previewImage.className = 'previewImage';
            previewImage.alt = '問題画像プレビュー';

            previewWrap.appendChild(previewImage);

            quizImageInput.insertAdjacentElement('beforebegin', previewWrap);
        } else if (!previewImage) {
            previewImage = document.createElement('img');
            previewImage.className = 'previewImage';
            previewImage.alt = '問題画像プレビュー';
            previewWrap.appendChild(previewImage);
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImage.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}
