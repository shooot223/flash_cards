<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">

    <!-- favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- 共通CSS -->
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">

    <title>@yield('title')</title>

    <!-- ページ専用CSS -->
    @stack('css')
<body>

    @include('header')

    @yield('content')

    @stack('js')
</body>
</html>
