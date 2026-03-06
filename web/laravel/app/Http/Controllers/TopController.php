<?php

namespace App\Http\Controllers;

use App\Models\QuestionTitle;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;

class TopController extends Controller
{
    public function index(Request $request)
    {
        $query = QuestionTitle::with(['categories']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('category')) {
            $categoryId = $request->category;

            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('question_categories.id', $categoryId);
            });
        }

        $quizzes = $query->latest()->get();
        $categories = QuestionCategory::orderBy('category_name')->get();

        if ($request->ajax()) {
            return view('quiz_list', compact('quizzes'))->render();
        }

        return view('top', compact('quizzes', 'categories'));
    }
}
