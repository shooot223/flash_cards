<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ作成完了</title>
    <link rel="stylesheet" href="{{ asset('css/quiz_create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>

<div class="quizForm">
    <div class="formGroup" style="text-align: center; padding: 40px 20px;">
        <div style="font-size: 22px; font-weight: 700; margin-bottom: 16px;">
            クイズを登録しました
        </div>

        <div style="color: var(--muted); margin-bottom: 28px;">
            クイズの作成が正常に完了しました。
        </div>

        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('quiz.create') }}"
               class="submitButton"
               style="text-decoration: none;">
                もう一度作成する
            </a>

            <a href="{{ route('top') }}"
               class="submitButton"
               style="background: #fff; color: var(--text); border: 1px solid var(--border-strong); box-shadow: none; text-decoration: none;">
                ホームへ戻る
            </a>
        </div>
    </div>
</div>
</body>
</html>
