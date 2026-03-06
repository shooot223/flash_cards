<?php

namespace App\Http\Controllers;

use App\Models\QuestionTitle;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;

class TopController extends Controller
{
    public function top(Request $request)
    {
        $query = QuestionTitle::query()->with('categories');

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%')
                ->orWhere('description', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('category')) {
            $categoryId = $request->category;

            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('question_categories.id', $categoryId);
            });
        }

        $quizzes = $query->latest()->paginate(10)->withQueryString();
        $categories = QuestionCategory::orderBy('category_name')->get();

        return view('top', compact('quizzes', 'categories'));
    }
}
