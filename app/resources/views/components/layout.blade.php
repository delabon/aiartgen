<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Karla:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dribbble-500">
    <div class="mx-auto max-w-7xl">
        <nav class="my-6">
            <a href="/">
                <span class="font-bold text-xl">AI.Art</span>
            </a>
        </nav>

        {{ $slot }}

        <footer class="flex justify-center items-center my-8">
            Made with <img class="mx-1" width="16" src="{{ asset('img/heart.svg') }}" alt="Love"> by <a href="https://delabon.com" class="ml-1 hover:underline">Sabri Taieb</a>
        </footer>
    </div>
</body>
</html>
