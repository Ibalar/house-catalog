<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Строительная компания')</title>
    @yield('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <a href="{{ url('/') }}">
                        <h1>Строительная компания</h1>
                    </a>
                </div>
                <ul class="nav-menu">
                    <li><a href="{{ url('/') }}">Главная</a></li>
                    <li><a href="{{ route('services.index') }}">Услуги</a></li>
                    <li><a href="{{ route('projects.index') }}">Проекты</a></li>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                {!! get_block('footer_content') ?: '<p>© ' . date('Y') . ' Строительная компания. Все права защищены.</p>' !!}
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>