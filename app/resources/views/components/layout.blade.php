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
    <div class="mx-4 max-w-7xl xl:mx-auto">
        <header class="my-6 flex items-center justify-between relative">
            <a href="{{ route('home') }}">
                <span class="font-bold text-xl">AI.Art</span>
            </a>

            <button id="dropdown-toggle">
                <img src="{{ asset('img/menu.svg') }}" width="18" alt="Open menu" data-type="burger">
                <img src="{{ asset('img/close.svg') }}" width="18" alt="Close menu" data-type="close" class="hidden">
            </button>

            <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 absolute right-0 top-8">
                <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownDefaultButton">
                    <li>
                        <a href="{{ route('home') }}" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700">Home</a>
                    </li>
                    <li>
                        <a href="{{ route('arts.index') }}" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700">Art</a>
                    </li>
                    @auth
                        <li>
                            <a href="{{ route('arts.user.art', ['user' => auth()->user()]) }}" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700">My art</a>
                        </li>
                        <li>
                            <a href="{{ route('arts.create') }}" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700">Generate art</a>
                        </li>
                        <li>
                            <a href="{{ route('settings.edit') }}" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700">Settings</a>
                        </li>
                    @endauth
                    @guest
                        <li>
                            <a href="{{ route('login') }}" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700">Login</a>
                        </li>
                        <li>
                            <a href="{{ route('register.create') }}" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700">Register</a>
                        </li>
                    @endguest
                    @auth
                        <li>
                            <form action="{{ route('logout') }}" method="post" class="w-full block">
                                @csrf
                                @method('delete')
                                <button type="submit" class="block px-4 py-2 hover:bg-gray-100 hover:text-dark-700 w-full text-left">Log out</button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </header>

        @if (session()->has('success'))
            <div class="p-4 mb-4 text-sm border border-slate-950 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{ $slot }}

        <footer class="flex justify-center items-center my-8">
            Made with <img class="mx-1" width="16" src="{{ asset('img/heart.svg') }}" alt="Love"> by <a href="https://delabon.com" class="ml-1 hover:underline">Sabri Taieb</a>
        </footer>
    </div>
</body>
</html>
