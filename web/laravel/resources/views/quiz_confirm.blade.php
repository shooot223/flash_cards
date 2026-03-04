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
<form method="post" action="{{ route('quiz.store') }}" class="quizForm">
    @csrf

</form>
</body>
</html>
