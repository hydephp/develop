<nav
	class="absolute py-8 top-0 left-0 w-full z-10 bg-transparent md:flex-row md:flex-nowrap md:justify-start flex items-center p-4">
	<div class="w-full mx-autp items-center flex justify-between md:flex-nowrap flex-wrap md:px-10 px-4">
		<a class="text-white text-sm uppercase hidden lg:inline-block font-semibold" href="?route=dashboard">Dashboard</a>
		<a class="text-white text-sm uppercase hidden lg:inline-block font-semibold" href="{{ Route::home() }}">Back to site</a>
		{{-- <form class="md:flex hidden flex-row flex-wrap items-center lg:ml-auto mr-3">
			<div class="relative flex w-full flex-wrap items-stretch">
				<span
					class="z-10 h-full leading-snug font-normal text-center text-slate-300 absolute bg-transparent rounded text-base items-center justify-center w-8 pl-3 py-3">
					<i class="fas fa-search"></i>
				</span>
				<input type="text" placeholder="Search here..."
					class="border-0 px-3 py-3 placeholder-slate-300 text-slate-600 relative bg-white rounded text-sm shadow outline-none focus:outline-none focus:ring w-full pl-10" />
			</div>
		</form> --}}
	</div>
</nav>