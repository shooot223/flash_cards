<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TopController extends Controller
{
    // キャッシュのTTL（秒） ＝ 5分
    private const CACHE_TTL = 300;

    //クイズ一覧画面表示（トップ画面）
    public function index(Request $request)
    {
        $keyword    = $request->input('keyword');
        $categoryId = $request->input('category');

        // 検索・タグ絞り込みがある場合はキャッシュを使わず直接クエリ
        $hasFilter = filled($keyword) || filled($categoryId);

        $quizzes = null;

        if (!$hasFilter) {
            // フィルターなしの場合はページ番号をキャッシュキーに含める
            $page      = $request->input('page', 1);
            $cacheKey  = "top_quizzes_page_{$page}";

            $quizzes = Cache::remember($cacheKey, self::CACHE_TTL, function () {
                return Quiz::with(['categories', 'questions'])
                    ->where('is_public', true)
                    ->latest()
                    ->paginate(10);
            });
        } else {
            // フィルターあり：毎回DBから取得
            $query = Quiz::with(['categories', 'questions']);

            // キーワード検索
            if (filled($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            }

            // タグ検索
            if (filled($categoryId)) {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('question_categories.id', $categoryId);
                });
            }

            $quizzes = $query->where('is_public', true)->latest()->paginate(10);
        }

        $categories = Cache::remember('top_categories', self::CACHE_TTL, function () {
            return QuestionCategory::orderBy('category_name')->get();
        });

        if ($request->ajax()) {
            return view('quiz_list', compact('quizzes'))->render();
        }

        return view('top', compact('quizzes', 'categories'));
    }
}
