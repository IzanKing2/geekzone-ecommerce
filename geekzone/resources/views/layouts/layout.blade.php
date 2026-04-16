<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'GeekZone')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
    @stack('styles')
    <script src="{{ asset('js/auth.js') }}"></script>
</head>
<body>
    @include('layouts.header')

    @yield('content')

    @include('layouts.footer')
</body>
</html>
