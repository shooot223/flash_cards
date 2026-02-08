<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>ダッシュボード</h1>

    <p>ログイン中です。</p>

    <form method="POST" action="/logout">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</body>
</html>
