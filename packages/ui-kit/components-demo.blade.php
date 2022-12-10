<!DOCTYPE html>
<html lang="{{ config('site.language', 'en') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->htmlTitle() }}</title>
    @include('hyde::layouts.styles')
</head>
<body id="app" class="flex flex-col min-h-screen overflow-x-hidden dark:bg-gray-900 dark:text-white">
<section>
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <x-hyde::ui.components.prose class="text-center mx-auto mb-12">
            <h1 class="mb-4">HydePHP UI Kit Components</h1>
            <p class="lead">
                Below is a demonstration of the UI components in both light and dark mode.
            </p>
        </x-hyde::ui.components.prose>
        <div class="flex">
            <div class="light">
                <div class="dark:bg-gray-900 dark:text-white w-[70ch] p-8 border-gray-900 border-2">
                    @include('ui-examples.components')
                </div>
            </div>
            <div class="dark">
                <div class="dark:bg-gray-900 dark:text-white w-[70ch] p-8 border-gray-900 border-2">
                    @include('ui-examples.components')
                </div>
            </div>
        </div>
    </main>
</section>
</body>
</html>
