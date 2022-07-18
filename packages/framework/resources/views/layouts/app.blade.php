<!DOCTYPE html>
<html lang="{{ config('site.language', 'en') }}">
<head>
    @include('hyde::layouts.head')
</head>
<body id="app" class="flex flex-col min-h-screen overflow-x-hidden dark:bg-gray-900 dark:text-white">
    <a href="#content" id="skip-to-content">Skip to content</a>
    @include('hyde::layouts.navigation') 

    <section>
        @yield('content') 
    </section>

    @includeWhen(config('hyde.footer.enabled', true), 'hyde::layouts.footer') 

    @include('hyde::layouts.scripts') 
</body>
</html>
