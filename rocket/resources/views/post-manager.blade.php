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
        <form action="" method="POST" class="mx-auto">
			<header>
				<label for="markdown">Blog Post Markdown:</label>
			</header>
            <textarea name="markdown" id="markdown" cols="70" rows="30">{{ $markdown }}</textarea>
			<footer>
				<div>
					<button onclick="openFile()" type="button" title="Open the file in your system default editor">Open File</button>
				</div>
				<div>
					<input type="reset" style="margin-right: 4px;">
					<input type="submit">
				</div>
			</footer>
		</form>
    </section>

	<script>
		function openFile() {
			// Make async fetch post request
			fetch('/fileapi/open', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					path: '{{ $localPath }}',
				})
			})
		}
	</script>

	@if($saved)
		<script>
			// Remove query string from URL
			window.history.pushState({}, document.title, window.location.pathname);

			// Send toast notification
			const toast = document.createElement('div');
			toast.classList.add('toast');
			toast.innerHTML = 'Saved!';
			document.body.appendChild(toast);
			toast.classList.add('show');
			setTimeout(() => {
				toast.classList.remove('show');
			}, 3000);
		</script>
	@endif
@endsection
