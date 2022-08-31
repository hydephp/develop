<nav
	class="md:left-0 md:block md:fixed md:top-0 md:bottom-0 md:overflow-y-auto md:flex-row md:flex-nowrap md:overflow-hidden shadow-xl bg-white flex flex-wrap items-center justify-between relative md:w-64 z-10 py-4 px-6">
	<div
		class="md:flex-col md:items-stretch md:min-h-full md:flex-nowrap px-0 flex flex-wrap items-center justify-between w-full mx-auto">
		<button
			class="cursor-pointer text-black opacity-50 md:hidden px-3 py-1 text-xl leading-none bg-transparent rounded border border-solid border-transparent"
			type="button" onclick="toggleNavbar('example-collapse-sidebar')">
			<i class="fas fa-bars"></i>
		</button>
		<a class="md:block text-left md:pb-2 text-slate-600 mr-0 inline-block whitespace-nowrap text-sm uppercase font-bold p-4 px-0"
			href="?route=dashboard"> {{ config('hyde.name') }} Admin </a>
		
		<div class="md:flex md:flex-col md:items-stretch md:opacity-100 md:relative md:shadow-none shadow absolute top-0 left-0 right-0 z-40 overflow-y-auto overflow-x-hidden h-auto items-center flex-1 rounded hidden"
			id="example-collapse-sidebar">
			<div class="md:min-w-full md:hidden block pb-4 mb-4 border-b border-solid border-slate-200">
				<div class="flex flex-wrap">
					<div class="w-6/12">
						<a class="md:block text-left md:pb-2 text-slate-600 mr-0 inline-block whitespace-nowrap text-sm uppercase font-bold p-4 px-0"
							href="?route=dashboard"> {{ config('hyde.name') }} Admin </a>
					</div>
					<div class="w-6/12 flex justify-end">
						<button type="button"
							class="cursor-pointer text-black opacity-50 md:hidden px-3 py-1 text-xl leading-none bg-transparent rounded border border-solid border-transparent"
							onclick="toggleNavbar('example-collapse-sidebar')">
							<i class="fas fa-times"></i>
						</button>
					</div>
				</div>
			</div>
			<form class="mt-6 mb-4 md:hidden">
				<div class="mb-3 pt-0">
					<input type="text" placeholder="Search"
						class="px-3 py-2 h-12 border border-solid border-slate-500 placeholder-slate-300 text-slate-600 bg-white rounded text-base leading-snug shadow-none outline-none focus:outline-none w-full font-normal" />
				</div>
			</form>
			<!-- Divider -->
			<hr class="my-4 md:min-w-full" />
			<!-- Heading -->
			<h6 class="md:min-w-full text-slate-500 text-xs uppercase font-bold block pt-1 pb-4 no-underline">
				Site Content </h6>
			<!-- Navigation -->
			<ul class="md:flex-col md:min-w-full flex flex-col list-none">
				<li class="items-center">
					<x-hyde-admin::sidebar-link route="pages" label="Static Pages" />
				</li>
				<li class="items-center">
					<x-hyde-admin::sidebar-link route="posts" label="Blog Posts" icon="rss" />
				</li>
				<li class="items-center">
					<x-hyde-admin::sidebar-link route="docs" label="Documentation" icon="book" />
				</li>
				<li class="items-center">
					<x-hyde-admin::sidebar-link route="media" label="Media Library" icon="photo-film" />
				</li>
			</ul>
		</div>
	</div>
</nav>