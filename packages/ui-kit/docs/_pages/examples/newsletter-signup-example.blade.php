<!DOCTYPE html>
<html lang="{{ config('site.language', 'en') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title() }}</title>
    @include('hyde::layouts.styles')
</head>
<body id="app" class="flex flex-col min-h-screen overflow-x-hidden dark:bg-gray-900 dark:text-white">
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="flex">
            <div class="light">
                <x-hyde-ui::card class="mx-auto">
                    <x-slot name="title">
                        Let your creativity flow!
                    </x-slot>
                 
                    <x-slot name="main" style="padding-top: 0; padding-bottom: 0;">
                        <x-hyde-ui::prose>
                            <x-hyde-ui::markdown>
                                The UI kit is minimal by design. It's up to **you** to create something _amazing_.
                 
                                Maybe create a form to collect newsletter subscriptions?
                            </x-hyde-ui::markdown>
                        </x-hyde-ui::prose>
                    </x-slot>
                 
                    <x-slot name="footer" class="flex text-center justify-center">
                        <x-hyde-ui::input placeholder="Enter email" />
                 
                        <x-hyde-ui::button-primary>
                            Subscribe
                        </x-hyde-ui::button-primary>
                    </x-slot>
                </x-hyde-ui::card>
            </div>
            <div class="dark">
                <x-hyde-ui::card class="mx-auto">
                    <x-slot name="title">
                        Let your creativity flow!
                    </x-slot>
                 
                    <x-slot name="main" style="padding-top: 0; padding-bottom: 0;">
                        <x-hyde-ui::prose>
                            <x-hyde-ui::markdown>
                                The UI kit is minimal by design. It's up to **you** to create something _amazing_.
                 
                                Maybe create a form to collect newsletter subscriptions?
                            </x-hyde-ui::markdown>
                        </x-hyde-ui::prose>
                    </x-slot>
                 
                    <x-slot name="footer" class="flex text-center justify-center">
                        <x-hyde-ui::input placeholder="Enter email" />
                 
                        <x-hyde-ui::button-primary>
                            Subscribe
                        </x-hyde-ui::button-primary>
                    </x-slot>
                </x-hyde-ui::card>
            </div>
        </div>
    </main>
</body>
</html>
