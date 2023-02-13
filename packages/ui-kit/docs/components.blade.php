<x-hyde-ui::heading align="left">
    Heading Component
</x-hyde-ui::heading>

<div class="mt-3">
    <x-hyde-ui::prose>
        <p>The above heading component is normally centered but is aligned here.</p>
    </x-hyde-ui::prose>
</div>

<div class="mt-8">
    <x-hyde-ui::prose>
        <h3>Prose Component</h3>
        <p>Any text-related <abbr>HTML</abbr> here will be styled using the <a href="https://tailwindcss.com/docs/typography-plugin" rel="nofollow noopener" target="_blank">Tailwind Typography</a> plugin.</p>
    </x-hyde-ui::prose>
</div>

<div class="mt-8">
    <x-hyde-ui::prose>
        <x-hyde-ui::markdown>
            ### Markdown Component

            Any **Markdown** within these tags will be *compiled* to HTML.<br>
            _Perfect_ to use with the `Prose` component so that it gets styled!
        </x-hyde-ui::markdown>
    </x-hyde-ui::prose>
</div>

<div class="mt-8">
    <x-hyde-ui::prose class="mb-4">
        <h3>Buttons & Inputs</h3>
        <p>These stylish and simple buttons are perfect for all your buttoning needs!</p>
    </x-hyde-ui::prose>

    <x-hyde-ui::button-primary>
        Primary Button
    </x-hyde-ui::button-primary>

    <x-hyde-ui::button-secondary>
        Secondary Button
    </x-hyde-ui::button-secondary>
</div>

<div class="mt-3">
    <x-hyde-ui::prose class="mb-3">
        <p>The kit even comes with a simple input element too!</p>
    </x-hyde-ui::prose>
    <x-hyde-ui::input placeholder="Write something!" />
</div>

<div class="mt-8 sm:-mx-6">
    <x-hyde-ui::prose class="mb-2 sm:px-6">
        <h3>Cards</h3>
        <p>The versatile card component lets you create perfect cards for your site.<br>
            Of course they come with a variety of options!</p>
    </x-hyde-ui::prose>

    <x-hyde-ui::card>
        A card with some content.
    </x-hyde-ui::card>

    <x-hyde-ui::card>
        <x-slot name="title">
            Card Title
        </x-slot>

        A card with some content.
    </x-hyde-ui::card>

    <x-hyde-ui::card>
        <x-slot name="title">
            Card Title & Footer
        </x-slot>

        A card with some content and a footer.

        <x-slot name="footer">
            Footer content
        </x-slot>
    </x-hyde-ui::card>
</div>
