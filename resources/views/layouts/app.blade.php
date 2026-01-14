<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $seoData['title'] ?? __('messages.site_default_name') }}</title>

    @if(!empty($seoData['description']))
        <meta name="description" content="{{ $seoData['description'] }}">
    @endif

    @if(!empty($seoData['canonical']))
        <link rel="canonical" href="{{ $seoData['canonical'] }}">
    @endif

    @if(isset($seoData['og_type']))
        {!! \App\Helpers\SeoHelper::ogTags([
            'title' => $seoData['title'] ?? '',
            'description' => $seoData['description'] ?? '',
            'url' => $seoData['canonical'] ?? url()->current(),
            'type' => $seoData['og_type'] ?? 'website',
            'image' => $seoData['og_image'] ?? null,
        ]) !!}
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <a href="{{ url('/') }}">
                        <h1>{{ get_setting('site_name', __('messages.site_default_name')) }}</h1>
                    </a>
                </div>
                <ul class="nav-menu">
                    <li><a href="{{ url('/') }}">{{ __('messages.home') }}</a></li>
                    <li><a href="{{ route('services.index') }}">{{ __('admin.services') }}</a></li>
                    <li><a href="{{ route('projects.index') }}">{{ __('admin.projects') }}</a></li>
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
                {!! get_block('footer_content') ?: '<p>© ' . date('Y') . ' ' . get_setting('site_name', __('messages.site_default_name')) . '. ' . __('messages.all_rights_reserved') . '</p>' !!}
            </div>
        </div>
    </footer>

    @if(request()->route()->getName() === 'home')
        {!! \App\Helpers\SeoHelper::organizationSchema() !!}
        {!! \App\Helpers\SeoHelper::localBusinessSchema() !!}
    @endif

    <script>
        window.translations = @json([
            'success' => __('messages.success'),
            'error' => __('messages.error'),
            'server_error' => __('messages.server_error'),
            'error_try_later' => __('messages.error_try_later'),
        ]);
    </script>

    @stack('scripts')

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
