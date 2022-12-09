# Hyde UI Kit - Documentation

The HydePHP UI Kit is a collection of minimalistic and un-opinionated TailwindCSS components for Laravel Blade,
indented to be used with HydePHP. Note that these components may require CSS classes not present in the bundled app.css
file and that you may need to recompile the CSS file using the included Laravel Mix configuration.

## Warning

The HydePHP UI Kit is still in development and is not yet ready for production use.
All components including their names can and probably will be changed.

## Preface

Please make sure you're familiar with [Laravel Blade](https://laravel.com/docs/blade) before using the HydePHP UI Kit.

>info Tip: Most components allow you to pass any additional HTML attributes to the element!

## Components

### Buttons

#### Primary

```blade
<x-hyde::ui.components.button-primary>
    Primary Button
</x-hyde::ui.components.button-primary>
```

#### Secondary

```blade
<x-hyde::ui.components.button-secondary>
    Secondary Button
</x-hyde::ui.components.button-secondary>
```

### Input

The base component is `<x-hyde::ui.components.input />`, any additional attributes will be passed to the input element as seen below.

```blade
<x-hyde::ui.components.input type="text" name="name" placeholder="Name" value="John Doe" />
```

### Card

An incredibly versatile component that can be used for a wide variety of purposes.

In the most basic form, a card is just a container with a white background and a shadow.
However, it also supports two slots: `title` and `footer`.

```blade
<x-hyde::ui.components.card>
    A card with some content.
</x-hyde::ui.components.card>
```

```blade
<x-hyde::ui.components.card>
    <x-slot name="title">
        Card Title
    </x-slot>
</x-hyde::ui.components.card>
```

```blade
<x-hyde::ui.components.card>
    <x-slot name="footer">
       Some footer content.
    </x-slot>
</x-hyde::ui.components.card>
```

Why not combine the components?

```blade
<x-hyde::ui.components.card>
    <x-slot name="title">
        My Amazing Card
    </x-slot>

    A card with some content and a footer with a button.

    <x-slot name="footer" class="text-center">
        <x-hyde::ui.components.button-primary>
            Primary Button
        </x-hyde::ui.components.button-primary>
    </x-slot>
</x-hyde::ui.components.card>
```

### Typography Components

#### Heading

This component will create a styled `<h1>` level heading centered on the page.

```blade
<x-hyde::ui.components.heading>
    Lorem ipsum dolor sit amet.
</x-hyde::ui.components.heading>
```

#### Prose

This simple component will create an `<article>` element with [TailwindCSS Typography](https://tailwindcss.com/docs/typography-plugin) (prose) styles applied.

```blade
<x-hyde::ui.components.prose>
    <h2>Prose Heading</h2>
    <p>Prose paragraph</p>
</x-hyde::ui.components.prose>
```

#### Markdown

This component will convert any Markdown within it to HTML using the Hyde Markdown compiler.

```blade
<x-hyde::ui.components.markdown>
## Markdown Heading

Hello world!
</x-hyde::ui.components.markdown>
```

>warn Remember to de-indent the Markdown content otherwise it will be rendered as a code block.
>info Tip: You may also want to wrap this in the prose element or the Markdown will not be styled.

### What's Next?

The UI kit is minimal by design. It's up to you to create something amazing.
You can get surprisingly far when you combine the components. Take this newsletter signup card for example!

```blade
<x-hyde::ui.components.card>
    <x-slot name="title">
        Let your creativity flow!
    </x-slot>

    <x-slot name="main" style="padding-top: 0; padding-bottom: 0;">
        <x-hyde::ui.components.prose>
            <x-hyde::ui.components.markdown>
                The UI kit is minimal by design. It's up to **you** to create something _amazing_.

                Maybe create a form to collect newsletter subscriptions?
            </x-hyde::ui.components.markdown>
        </x-hyde::ui.components.prose>
    </x-slot>

    <x-slot name="footer" class="text-center flex">
        <x-hyde::ui.components.input placeholder="Enter email" />

        <x-hyde::ui.components.button-primary>
            Subscribe
        </x-hyde::ui.components.button-primary>
    </x-slot>
</x-hyde::ui.components.card>
```

