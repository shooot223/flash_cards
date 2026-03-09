<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Cramist' }}</title>

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="authPage">
    <div class="authShell">
        {{ $slot }}
    </div>
</div>
</body>
</html>
