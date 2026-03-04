<!docmenttype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>クイズ作成</title>
    <link rel="stylesheet" href="{{ asset('css/quiz_create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>
<body>
<header class="header">
    @include('header')
</header>
<form method="post" action="{{ route('quiz.confirm') }}" class="quizForm">
    @csrf
    <div class="formGroup">
        <label for="title" class="formLabel">クイズタイトル</label>
        <input type="text" id="title" name="title" class="formInput" required>
    </div>
    <div class="formGroup">
        <label for="description" class="formLabel">説明文</label>
        <textarea id="description" name="description" class="formTextarea" required></textarea>
    </div>
    <div class="formGroup">
        <label for="tags" class="formLabel">タグ（カンマ区切り）</label>
        <input type="text" id="tags" name="tags" class="formInput">
    </div>
    <button type="submit" class="submitButton">クイズを保存</button>

</form>
</body>
</html>
