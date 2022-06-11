@extends('partials.layout')
@section('title', 'Manage Blog Post')
@section('content')
    <style>
		form {
			margin-top: 2rem;
		}
		form > header {
			padding-bottom: 0.5rem;
		}
		#markdown {
            font-family: monospace;
        }
		
    </style>
    <header>
        <h1>
            Manage Blog Post
        </h1>
        <p class="prose mx-auto">
            Here you can edit the Markdown content of your blog post.
            <br>
            You can use the plaintext Markdown editor below,
			or open it in your system editor.
        </p>
    </header>
    <section>
        <form action="" class="mx-auto">
			<label for="markdown">Blog Post Markdown:</label>
            <textarea id="markdown" cols="70" rows="30">{{ $post->body }}</textarea>
        </form>
    </section>
@endsection
