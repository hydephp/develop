@extends('hyde::layouts.app')
@section('content')
@php
$title = "Blade" 
@endphp
<main class="mx-auto max-w-7xl py-16 px-8 prose dark:prose-invert">
	<h1 class="text-center text-3xl font-bold">Blade Pages offer the full power of Laravel</h1>

	<h4 class="text-center text-2xl">You can even use arbitrary PHP.</h4>

	<figure class="w-fit mx-auto mt-12 mb-4 rounded-lg overflow-hidden"><div style="padding: 1rem; color: rgb(191, 199, 213); background-color: rgb(41, 45, 62); font-family: &quot;Fira Code Regular&quot;, Consolas, &quot;Courier New&quot;, monospace; font-size: 14px; line-height: 20px; white-space: pre; overflow-x: auto; max-width: 90vw;"><div><span style="color: #89ddff;">&lt;</span><span style="color: #ff5572;">div</span><span style="color: #89ddff;"> </span><span style="color: #ffcb6b;">class</span><span style="color: #89ddff;">=</span><span style="color: #d9f5dd;">"</span><span style="color: #c3e88d;">text-center</span><span style="color: #d9f5dd;">"</span><span style="color: #89ddff;">&gt;</span></div><div>&nbsp; &nbsp; <span style="color: #d3423e;">&commat;php</span> </div><div>&nbsp; &nbsp; &nbsp; &nbsp; <span style="color: #89ddff;">echo</span> <span style="color: #d9f5dd;">"</span><span style="color: #c3e88d;">Hello World! </span><span style="color: #d9f5dd;">"</span>;</div><br><div>&nbsp; &nbsp; &nbsp; &nbsp; <span style="color: #89ddff;">echo</span> <span style="color: #d9f5dd;">"</span><span style="color: #c3e88d;">This PHP was executed at </span><span style="color: #d9f5dd;">"</span> <span style="color: #89ddff;">.</span> <span style="color: #89ddff;">date</span>(<span style="color: #d9f5dd;">"</span><span style="color: #c3e88d;">Y-m-d H:i:s</span><span style="color: #d9f5dd;">"</span>);</div><div>&nbsp; &nbsp; <span style="color: #d3423e;">&commat;endphp</span> </div><div><span style="color: #89ddff;">&lt;/</span><span style="color: #ff5572;">div</span><span style="color: #89ddff;">&gt;</span></div></div></figure>
	<div class="text-center">
		@php 
			echo "Hello World! ";

			echo "This PHP was executed at " . date("Y-m-d H:i:s");
		@endphp 
	</div>
</main>

@endsection
