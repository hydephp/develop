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
		#openStackEditButton {
			display: flex;
			align-items-center;
		}
		#markdown {
            font-family: monospace;
        }
		.stackedit-iframe-container {
			width: 90%;
			height: 90%;
			margin: auto;
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
            or you can open the document in the fancy in-browser StackEdit editor.
        </p>
    </header>
    <section>
        <form action="" class="mx-auto">
            <header class="flex justify-between">
				<label for="markdown">Blog Post Markdown:</label>
				<button type="button" id="openStackEditButton">
					<img width="16px" height="16px" src="/icons/stackedit.svg" alt="" role="presentation">
					&nbsp;Open StackEdit window
				</button>
			</header>
            <textarea id="markdown" cols="70" rows="30">{{ $post->body }}</textarea>
        </form>
    </section>

    <script defer src="https://unpkg.com/stackedit-js@1.0.7/docs/lib/stackedit.min.js"></script>

    <script>
		
        // when loaded
		window.addEventListener('load', function() {
			const el = document.querySelector('textarea');
			const stackedit = new Stackedit();

			// Listen to StackEdit events and apply the changes to the textarea.
			stackedit.on('fileChange', (file) => {
				el.value = file.content.text;
			});

			// Open the iframe onclick of the button.
			document.getElementById('openStackEditButton').addEventListener('click', function() {
				stackedit.openFile({
				name: 'Filename', // with an optional filename
				content: {
					text: el.value // and the Markdown content.
				}
			});
			});
		});
    </script>
@endsection
