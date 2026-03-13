<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <!-- favicon -->
    <link rel="icon" href="{{ asset('/img/favicon/favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/img/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/img/favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('/img/favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('/img/favicon/site.webmanifest') }}">

    <!-- 共通CSS -->
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">

    <title>@yield('title')</title>

    <!-- ページ専用CSS -->
    @stack('css')
</head>
<body>

    @include('header')

    @yield('content')

    @stack('js')
</body>
</html>
