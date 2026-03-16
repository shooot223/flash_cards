<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <!-- favicon -->
    <link rel="icon" href="{{ asset('/img/favicon/favicon.ico') }}" sizes="any">
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/error.css') }}">

    <title>@yield('title') | Cramist</title>
</head>
<body>
    <header class="header">
        <div class="headerInner" style="justify-content: center;">
            <div class="headerCenter" style="margin: 0 auto;">
                <a href="/" class="logo">
                    <img src="{{ asset('img/logo.png') }}" class="logoImage" alt="Cramist" style="height:40px;">
                </a>
            </div>
        </div>
    </header>

    @yield('content')

</body>
</html>
