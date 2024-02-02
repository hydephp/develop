<x-hyde-ui::layouts.focus>
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
</x-hyde-ui::layouts.focus>
