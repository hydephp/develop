@extends('partials.layout')
@section('title', 'Manage Blog Post')
@section('content')
    <style>
		form {
			margin-top: 1rem;
		}
		form > header {
			padding-bottom: 0.5rem;
		}
		form > footer {
			padding-top: 0.5rem;
			display: flex;
			justify-content: space-between;
		}
		#markdown {
            font-family: monospace;
        }
		
    </style>
    <header>
        <h1>
            Create new blog post
        </h1>
        <p class="prose mx-auto">
			Here you can create a new blog post!
		</p>
    </header>
    <section>
        <form action="" method="POST" class="mx-auto">
			<header>
				<label for="markdown">Blog Post Markdown:</label>
			</header>
            <textarea name="markdown" id="markdown" cols="70" rows="30">{{ request()->old('markdown') }}</textarea>
			<footer>
				<div>
					<input type="submit">
				</div>
			</footer>
		</form>
    </section>
@endsection
