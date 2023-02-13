<x-hyde::ui.layouts.focus>
    <x-hyde::ui.components.heading>
        Heading: Hello World!
    </x-hyde::ui.components.heading>

    <div class="mt-8">
        <x-hyde::ui.components.button-primary>
            Primary Button
        </x-hyde::ui.components.button-primary>

        <x-hyde::ui.components.button-secondary>
            Secondary Button
        </x-hyde::ui.components.button-secondary>
    </div>

    <div class="mt-3">
        <x-hyde::ui.components.input value="Input" />
    </div>

    <div>
        <x-hyde::ui.components.card>
            A card with some content.
        </x-hyde::ui.components.card>

        <x-hyde::ui.components.card>
            <x-slot name="title">
                Card Title
            </x-slot>

            A card with some content.
        </x-hyde::ui.components.card>


        <x-hyde::ui.components.card>
            <x-slot name="title">
                Card Title & Footer
            </x-slot>

            A card with some content and a footer.

            <x-slot name="footer" class="text-center">
                <x-hyde::ui.components.button-primary>
                    Primary Button
                </x-hyde::ui.components.button-primary>
            </x-slot>
        </x-hyde::ui.components.card>
    </div>

    <div class="mt-8">
        <x-hyde::ui.components.prose>
            <h2>Prose Heading</h2>
            <p>Prose paragraph</p>
        </x-hyde::ui.components.prose>
    </div>

    <div class="mt-8">
        <x-hyde::ui.components.prose>
            <x-hyde::ui.components.markdown>
### Markdown

Remember to deintent the Markdown content otherwise it will be rendered as a code block.

You may also want to wrap this in a `class="prose"` element or the Markdown will not be styled.
            </x-hyde::ui.components.markdown>
        </x-hyde::ui.components.prose>
    </div>
</x-hyde::ui.layouts.focus>
