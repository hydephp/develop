@extends('hyde::layouts.app')
@section('content')
@php($title = "Dashboard")

<main class="mx-auto max-w-7xl py-16 px-8">
	<header class="text-center prose dark:prose-invert mx-auto">
		<h1 class="text-3xl font-bold">Project Dashboard</h1>
		<p>
			<strong>
				Here you can get a quick overview of your project.
			</strong>
		</p>
		<p>
			While this is useful when developing locally,
			you may not want to use it when compiling
			for production.
		</p>
	</header>

    <section class="prose dark:prose-invert mx-auto mt-8">
        <header>
            <h2>Content Overview</h2>
        </header>

        <section class="prose dark:prose-invert mx-auto mt-8">
            <h3>Blade Pages</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Source File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\Hyde\Framework\Models\BladePage::all() as $page)
                    <tr>
                        <td>
                            <a href="{{ Hyde::pageLink($page->slug . '.html') }}">
                                {{ Hyde::titleFromSlug($page->view) }}
                            </a>
                        </td>
                        <td>
                            {{ \Hyde\Framework\Models\BladePage::$sourceDirectory .'/'. $page->slug . \Hyde\Framework\Models\BladePage::$fileExtension }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    
        <section class="prose dark:prose-invert mx-auto mt-8">
            <h3>Markdown Pages</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Source File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\Hyde\Framework\Models\MarkdownPage::all() as $page)
                    <tr>
                        <td>
                            <a href="{{ Hyde::pageLink($page->slug . '.html') }}">
                                {{ $page->title }}
                            </a>
                        </td>
                        <td>
                            {{ \Hyde\Framework\Models\MarkdownPage::$sourceDirectory .'/'. $page->slug . \Hyde\Framework\Models\MarkdownPage::$fileExtension }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    
        <section class="prose dark:prose-invert mx-auto mt-8">
            <h3>Documentation Pages</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Source File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\Hyde\Framework\Models\DocumentationPage::all() as $page)
                    <tr>
                        <td>
                            <a href="{{ Hyde::docsDirectory() .'/'. Hyde::pageLink($page->slug . '.html') }}">
                                {{ $page->title }}
                            </a>
                        </td>
                        <td>
                            {{ \Hyde\Framework\Models\DocumentationPage::$sourceDirectory .'/'. $page->slug . \Hyde\Framework\Models\DocumentationPage::$fileExtension }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    
        <section class="prose dark:prose-invert mx-auto mt-8">
            <h3>Blog Posts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Source File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\Hyde\Framework\Models\MarkdownPost::all() as $post)
                    <tr>
                        <td>
                            <a href="posts/{{ Hyde::pageLink($post->slug . '.html') }}">
                                {{ $post->title }}
                            </a>
                        </td>
                        <td>
                            {{ \Hyde\Framework\Models\MarkdownPost::$sourceDirectory .'/'. $post->slug . \Hyde\Framework\Models\MarkdownPost::$fileExtension }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </section>
</main>
@endsection
