<?php

namespace App\Http\Controllers;

use App\Events\QuizCreated;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Answer;
use App\Models\Choice;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizController extends Controller
{
    public function create(Request $request)
    {
        return view('quiz_create', [
            'oldFormData' => [
                'title' => $request->input('title', ''),
                'description' => $request->input('description', ''),
                'tags' => $request->input('tags', []),
                'questions' => $request->input('questions', []),
            ],
            'tempQuizImage' => $request->input('temp_quiz_image'),
            'currentImagePath' => $request->input('current_image_path'),
            'isEditFromConfirm' => (bool)$request->input('is_edit', false),
            'quizIdFromConfirm' => $request->input('quiz_id'),
        ]);
    }

    // 確認画面
    public function confirm(StoreQuizRequest $request)
    {
        $validated = $request->validated();

        $isEdit = (bool)$request->input('is_edit', false);
        $quizId = $request->input('quiz_id');

        $tempQuizImage = $request->input('temp_quiz_image');
        $currentImagePath = $request->input('current_image_path');

        if ($request->hasFile('quiz_image')) {
            if (!empty($tempQuizImage) && Storage::disk('public')->exists($tempQuizImage)) {
                Storage::disk('public')->delete($tempQuizImage);
            }

            $tempQuizImage = $request->file('quiz_image')->store('tmp/quizzes', 'public');
        }

        return view('quiz_confirm', [
            'formData' => $validated,
            'isEdit' => $isEdit,
            'quizId' => $quizId,
            'tempQuizImage' => $tempQuizImage,
            'currentImagePath' => $currentImagePath,
        ]);
    }

    // 保存処理
    public function store(StoreQuizRequest $request)
    {
        $validated = $request->validated();

        // トランザクション内で作成したクイズを外側で参照するための変数
        $createdQuiz = null;

        DB::transaction(function () use ($validated, &$createdQuiz) {
            $imagePath = null;

            if (!empty($validated['temp_quiz_image']) && Storage::disk('public')->exists($validated['temp_quiz_image'])) {
                $filename = basename($validated['temp_quiz_image']);
                $newPath = 'quizzes/' . $filename;

                Storage::disk('public')->move($validated['temp_quiz_image'], $newPath);
                $imagePath = $newPath;
            }

            $title = Quiz::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'user_id' => auth()->id(),
                'image_path' => $imagePath,
            ]);

            $this->syncTags($title, $validated['tags'] ?? []);
            $this->replaceQuestions($title, $validated['questions']);

            // リレーション済みモデルをイベントに渡すため事前に取得しておく
            $createdQuiz = $title->load(['categories', 'questions']);
        });

        // クイズ作成イベントを発火してログリスナーに通知する
        QuizCreated::dispatch($createdQuiz);

        // キャッシュをクリアして最新状態を反映させる
        $this->clearTopCache();

        return redirect()->route('quiz.complete', ['mode' => 'create']);
    }

    public function complete(Request $request)
    {
        $mode = $request->query('mode', 'create');

        return view('quiz_complete', compact('mode'));
    }

    public function edit($id)
    {
        $quiz = Quiz::with(['questions.choices', 'categories'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('quiz_create', compact('quiz'));
    }

    public function update(UpdateQuizRequest $request, $id)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $id) {
            $title = Quiz::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $imagePath = $validated['current_image_path'] ?? $title->image_path;

            if (!empty($validated['temp_quiz_image']) && Storage::disk('public')->exists($validated['temp_quiz_image'])) {
                if (!empty($title->image_path) && Storage::disk('public')->exists($title->image_path)) {
                    Storage::disk('public')->delete($title->image_path);
                }

                $filename = basename($validated['temp_quiz_image']);
                $newPath = 'quizzes/' . $filename;

                Storage::disk('public')->move($validated['temp_quiz_image'], $newPath);
                $imagePath = $newPath;
            }

            $title->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'image_path' => $imagePath,
            ]);

            QuizCategory::where('quiz_id', $title->id)->delete();
            $this->syncTags($title, $validated['tags'] ?? []);
            $this->replaceQuestions($title, $validated['questions']);
        });

        // キャッシュをクリアして最新状態を反映させる
        $this->clearTopCache();

        return redirect()->route('quiz.complete', ['mode' => 'update']);
    }

    public function private($id)
    {
        $quiz = Quiz::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $quiz->update([
            'is_public' => false,
        ]);

        // キャッシュをクリアして最新状態を反映させる
        $this->clearTopCache();

        return redirect()->route('mypage')->with('success', '問題を非公開にしました。');
    }

    public function public($id)
    {
        $quiz = Quiz::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $quiz->update([
            'is_public' => true,
        ]);

        // キャッシュをクリアして最新状態を反映させる
        $this->clearTopCache();

        return redirect()->route('mypage')->with('success', '問題を再公開しました。');
    }

    private function syncTags(Quiz $title, array $tags): void
    {
        $filteredTags = collect($tags)
            ->filter(fn($tag) => !empty($tag))
            ->unique()
            ->values();

        foreach ($filteredTags as $tagName) {
            $category = QuestionCategory::firstOrCreate([
                'category_name' => $tagName,
            ]);

            QuizCategory::create([
                'quiz_id' => $title->id,
                'category_id' => $category->id,
            ]);
        }
    }

    //クイズの問題の置き換え処理
    private function replaceQuestions(Quiz $title, array $questions): void
    {
        $questionIds = Question::where('quiz_id', $title->id)->pluck('id');

        $choiceIds = Choice::whereIn('question_id', $questionIds)->pluck('id');

        // 既存の回答を削除（choiceに紐づくAnswerを先に消す）
        Answer::whereIn('choice_id', $choiceIds)->delete();

        // Answerが消えたあと、関連するScoreも削除（不整合を防ぐ）
        Score::where('quiz_id', $title->id)->delete();

        // 既存の選択肢を削除
        Choice::whereIn('question_id', $questionIds)->delete();

        // 既存の問題を削除
        Question::where('quiz_id', $title->id)->delete();

        $filteredQuestions = $this->normalizeQuestions($questions);

        foreach ($filteredQuestions as $q) {
            $question = Question::create([
                'quiz_id' => $title->id,
                'question_text' => $q['question'],
                'explanation' => $q['explanation'],
            ]);

            foreach ($q['choices'] as $index => $choiceText) {
                Choice::create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceText,
                    'is_correct' => (string) $index === (string) $q['correct'],
                ]);
            }
        }
    }

    // 未入力の問題を除外し、問題文・選択肢の前後の空白を削除して配列を整形する
    private function normalizeQuestions(array $questions): array
    {
        return collect($questions)
            ->map(function ($q) {
                return [
                    // 問題文の前後の空白を削除
                    'question' => trim((string)($q['question'] ?? '')),

                    // 解説の前後の空白を削除
                    'explanation' => trim((string)($q['explanation'] ?? '')),

                    // 選択肢の前後の空白を削除
                    'choices' => collect($q['choices'] ?? [])
                        ->map(fn($choice) => trim((string)$choice))
                        ->all(),

                    'correct' => $q['correct'] ?? null,
                ];
            })
            // 問題文が空の問題は除外
            ->filter(fn($q) => filled($q['question']))
            ->values()
            ->all();
    }

    //csv出力

    public function export_csv(Request $request): StreamedResponse
    {
        // バリデーション
        $validated = $request->validate([
            'quiz_ids' => ['required', 'array'],
            'quiz_ids.*' => ['integer', 'exists:quizzes,id'],
        ]);

        $quizIds = $validated['quiz_ids'];

        // クイズ取得（問題と選択肢も一緒に取得）
        $quizzes = Quiz::with(['questions.choices'])
            ->whereIn('id', $quizIds)
            ->where('user_id', auth()->id()) // 自分の問題だけ
            ->get();

        //ファイル名
        $fileName = 'quiz_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($quizzes) {

            $handle = fopen('php://output', 'w');

            // Excel文字化け対策
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー
            fputcsv($handle, [
                '問題ID',
                '問題タイトル',
                '説明文',
                '問題文',
                '選択肢１',
                '選択肢２',
                '選択肢３',
                '選択肢４',
                '正解選択肢'
            ]);

            foreach ($quizzes as $quiz) {

                foreach ($quiz->questions as $question) {

                    // choices を配列化
                    $choices = $question->choices->values();

                    $choice1 = $choices[0]->choice_text ?? '';
                    $choice2 = $choices[1]->choice_text ?? '';
                    $choice3 = $choices[2]->choice_text ?? '';
                    $choice4 = $choices[3]->choice_text ?? '';

                    // 正解取得
                    $correct = '';

                    foreach ($choices as $index => $choice) {
                        if ($choice->is_correct) {
                            $correct = $index + 1;
                            break;
                        }
                    }

                    fputcsv($handle, [
                        $quiz->id,
                        $quiz->title,
                        $quiz->description,
                        $question->question_text,
                        $choice1,
                        $choice2,
                        $choice3,
                        $choice4,
                        '選択肢' . $correct
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // 復習画面の表示
    public function review($id)
    {
        $quiz = Quiz::findOrFail($id);
        $userId = auth()->id();

        // 当該クイズに対する、このユーザーの最新のScoreを取得する
        $scoreRecord = Score::where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->first();

        if (!$scoreRecord) {
            return redirect()->route('mypage')
                ->with('error', 'この問題の回答履歴がありません');
        }

        // Scoreに紐づくAnswerを取得 (問題, 選択肢, 自信度 をロード)
        $scoreRecord->load(['answers.question', 'answers.choice', 'answers.confidence']);

        $resultDetails = [];
        foreach ($scoreRecord->answers as $answer) {
            // 問題がない、または選択肢がない場合はスキップするなどの安全策
            if (!$answer->question) {
                continue;
            }

            // この問題の「正解」の選択肢を取得する
            $correctChoice = Choice::where('question_id', $answer->question_id)
                ->where('is_correct', true)
                ->first();

            $resultDetails[] = [
                'question_id'        => $answer->question_id,
                'question_text'      => $answer->question->question_text,
                'question_explanation'=> $answer->question->explanation,
                'selected_choice_id' => $answer->choice_id,
                'selected_answer'    => $answer->choice ? $answer->choice->choice_text : '未回答',
                'correct_choice_id'  => $correctChoice ? $correctChoice->id : null,
                'correct_answer'     => $correctChoice ? $correctChoice->choice_text : '未設定',
                'confidence'         => $answer->confidence ? $answer->confidence->confidence_level : '未回答',
                'is_correct'         => (bool) $answer->is_correct,
            ];
        }

        return view('quiz_review', [
            'quiz'          => $quiz,
            'score'         => $scoreRecord->correct_count,
            'total'         => $scoreRecord->answers->count(),
            'resultDetails' => $resultDetails,
        ]);
    }

    // クイズの物理削除処理
    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $quiz = Quiz::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // 関連テーブルの削除準備
            $questionIds = Question::where('quiz_id', $quiz->id)->pluck('id');
            $choiceIds = Choice::whereIn('question_id', $questionIds)->pluck('id');

            // 回答記録 (Answer) の削除
            Answer::whereIn('choice_id', $choiceIds)->delete();

            // 成績記録 (Score) の削除
            Score::where('quiz_id', $quiz->id)->delete();

            // 選択肢 (Choice) の削除
            Choice::whereIn('question_id', $questionIds)->delete();

            // 問題 (Question) の削除
            Question::where('quiz_id', $quiz->id)->delete();

            // カテゴリ連携 (QuizCategory) の削除
            QuizCategory::where('quiz_id', $quiz->id)->delete();

            // 画像の物理削除
            if (!empty($quiz->image_path) && Storage::disk('public')->exists($quiz->image_path)) {
                Storage::disk('public')->delete($quiz->image_path);
            }

            // クイズ本体 (Quiz) の削除
            $quiz->delete();
        });

        // キャッシュをクリアして最新状態を反映させる
        $this->clearTopCache();

        return redirect()->route('mypage')->with('status', '問題を削除しました。');
    }

    /**
     * トップ画面のクイズ一覧キャッシュを全ページ分、カテゴリ一覧分クリアする
     */
    private function clearTopCache(): void
    {
        // top_quizzes_page_1, top_quizzes_page_2, ... を削除（5ページ分まで対応）
        for ($i = 1; $i <= 50; $i++) {
            Cache::forget("top_quizzes_page_{$i}");
        }
        Cache::forget('top_categories');
    }
}
