@extends('hyde::layouts.app')
@section('content')

	<section class="mx-auto items-center text-center py-16 px-4 lg:min-h-screen">
		<div class="p-8  max-w-7xl mx-auto">
			<img src="https://raw.githubusercontent.com/hydephp/examples/master/media/Posts.png" alt="Create Blog Posts from Markdown" title="Create Blog Posts from Markdown">
		</div>
	</section>

	<section class="mx-auto items-center text-center py-16 px-4 lg:min-h-screen bg-slate-100">
		<h1 class="text-2xl md:3-xl lg:text-5xl font-black text-slate-700 px-3 my-3">
			Create Markdown Driven Pages
		</h1>
		<strong class="text-xl md:text-2xl lg:text-3xl text-slate-800 px-3">
			With ease. Front Matter included.
		</strong>

		<div class="p-8  max-w-5xl mx-auto">
			<img class="shadow-2xl mx-auto"
				src="https://raw.githubusercontent.com/hydephp/examples/master/examples/markdown-pages/screenshot.png"
				alt="screenshot.png">
		</div>
		<p>
			<a href="https://github.com/hydephp/examples/blob/master/examples/markdown-pages/installation.md">View source on GitHub</a>
		</p>
	</section>

	<section class="mx-auto items-center text-center py-16 px-4 lg:min-h-screen">
		<h1 class="text-2xl md:3-xl lg:text-5xl font-black text-slate-700 px-3 my-3">
			Beautiful Documentation Pages
		</h1>
		<strong class="text-xl md:text-2xl lg:text-3xl text-slate-800 px-3">
			All without breaking a sweat.
		</strong>

		<div class="p-8 max-w-5xl mx-auto">
			<img class="shadow-2xl mx-auto"
				src="https://raw.githubusercontent.com/hydephp/examples/master/examples/markdown-documentation/screenshot_mbp.png"
				alt="screenshot.png">
		</div>
		<p>
			<a href="https://github.com/hydephp/examples/blob/master/examples/markdown-documentation/installation.md">View source on GitHub</a>
		</p>
	</section>

	<section class="mx-auto items-center text-center py-16 px-4 lg:min-h-screen bg-slate-100">
		<h1 class="text-2xl md:3-xl lg:text-5xl font-black text-slate-700 px-3 my-3">
			Fully Mobile Friendly, of course.
		</h1>
		<strong class="text-xl md:text-2xl lg:text-3xl text-slate-800 px-3">
			Enjoy your site in any size of screen.
		</strong>

		<div class="devices relative w-full flex gap-6 lg:gap-10 snap-x snap-mandatory overflow-x-auto justify-center py-8 lg:mt-4">
			<div class="snap-center shrink-0">
				<div class="shrink-0 w-4 sm:w-48"></div>
			  </div>
			  <div class="snap-center shrink-0 first:pl-8 last:pr-8">
				<img class="shrink-0 w-80 rounded-lg" src="https://raw.githubusercontent.com/hydephp/examples/master/media/devices/post_example_ios_8.png" />
			  </div>
			  <div class="snap-center shrink-0 first:pl-8 last:pr-8">
				<img class="shrink-0 w-80 rounded-lg" src="https://raw.githubusercontent.com/hydephp/examples/master/media/devices/post_feed_ios_8.png" />
			  </div>
			  <div class="snap-center shrink-0 first:pl-8 last:pr-8">
				<img class="shrink-0 w-80 rounded-lg" src="https://raw.githubusercontent.com/hydephp/examples/master/media/devices/docs_example_ios_8.png" />
			  </div>
			<div class="snap-center shrink-0">
				<div class="shrink-0 w-4 sm:w-48"></div>
			  </div>
		  </div>
	</section>

<footer>
	<div class="text-center p-4">
        Images hosted with ❤ by <a href="https://github.com">GitHub</a>
	</div>
</footer>

<style>
	.devices img {
		max-height: 80vh;
	}
</style>

@endsection