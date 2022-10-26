@php
use Hyde\Framework\Services\DiscoveryService;
@endphp

<x-hyde-admin::header>
	<!-- Card stats -->
	<div class="flex flex-wrap">
		<div class="w-full lg:w-6/12 xl:w-3/12 px-4">
			<div class="relative flex flex-col min-w-0 break-words bg-white rounded mb-6 xl:mb-0 shadow-lg">
				<div class="flex-auto p-4">
					<div class="flex flex-wrap">
						<div class="relative w-full pr-4 max-w-full flex-grow flex-1">
							<h5 class="text-slate-400 uppercase font-bold text-xs"> Blade Pages </h5>
							<span class="font-semibold text-xl text-slate-700"> <b>{{ count(DiscoveryService::getBladePageFiles()) }}</b> pages </span>
						</div>
						<div class="relative w-auto pl-4 flex-initial">
							<div
									class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-red-500">
								<i class="far fa-chart-bar"></i>
							</div>
						</div>
					</div>
					{{-- <p class="text-sm text-slate-400 mt-4">
								<span class="text-emerald-500 mr-2">
									<i class="fas fa-arrow-up"></i> 3.48% </span>
						<span class="whitespace-nowrap"> Since last month </span>
					</p> --}}
				</div>
			</div>
		</div>
		<div class="w-full lg:w-6/12 xl:w-3/12 px-4">
			<div class="relative flex flex-col min-w-0 break-words bg-white rounded mb-6 xl:mb-0 shadow-lg">
				<div class="flex-auto p-4">
					<div class="flex flex-wrap">
						<div class="relative w-full pr-4 max-w-full flex-grow flex-1">
							<h5 class="text-slate-400 uppercase font-bold text-xs"> Markdown Pages </h5>
							<span class="font-semibold text-xl text-slate-700"> <b>{{ count(DiscoveryService::getMarkdownPageFiles()) }}</b> pages </span>
						</div>
						<div class="relative w-auto pl-4 flex-initial">
							<div
									class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-orange-500">
								<i class="fas fa-chart-pie"></i>
							</div>
						</div>
					</div>
					{{-- <p class="text-sm text-slate-400 mt-4">
								<span class="text-red-500 mr-2">
									<i class="fas fa-arrow-down"></i> 3.48% </span>
						<span class="whitespace-nowrap"> Since last week </span>
					</p> --}}
				</div>
			</div>
		</div>
		<div class="w-full lg:w-6/12 xl:w-3/12 px-4">
			<div class="relative flex flex-col min-w-0 break-words bg-white rounded mb-6 xl:mb-0 shadow-lg">
				<div class="flex-auto p-4">
					<div class="flex flex-wrap">
						<div class="relative w-full pr-4 max-w-full flex-grow flex-1">
							<h5 class="text-slate-400 uppercase font-bold text-xs"> Documentation Pages </h5>
							<span class="font-semibold text-xl text-slate-700"> <b>{{ count(DiscoveryService::getDocumentationPageFiles()) }}</b> pages </span>
						</div>
						<div class="relative w-auto pl-4 flex-initial">
							<div
									class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-pink-500">
								<i class="fas fa-users"></i>
							</div>
						</div>
					</div>
					{{-- <p class="text-sm text-slate-400 mt-4">
								<span class="text-orange-500 mr-2">
									<i class="fas fa-arrow-down"></i> 1.10% </span>
						<span class="whitespace-nowrap"> Since yesterday </span>
					</p> --}}
				</div>
			</div>
		</div>
		<div class="w-full lg:w-6/12 xl:w-3/12 px-4">
			<div class="relative flex flex-col min-w-0 break-words bg-white rounded mb-6 xl:mb-0 shadow-lg">
				<div class="flex-auto p-4">
					<div class="flex flex-wrap">
						<div class="relative w-full pr-4 max-w-full flex-grow flex-1">
							<h5 class="text-slate-400 uppercase font-bold text-xs"> Markdown Posts </h5>
							<span class="font-semibold text-xl text-slate-700"> <b>{{ count(DiscoveryService::getMarkdownPostFiles()) }}</b> posts </span>
						</div>
						<div class="relative w-auto pl-4 flex-initial">
							<div
									class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-indigo-500">
								<i class="fas fa-percent"></i>
							</div>
						</div>
					</div>
					{{-- <p class="text-sm text-slate-400 mt-4">
								<span class="text-emerald-500 mr-2">
									<i class="fas fa-arrow-up"></i> 12% </span>
						<span class="whitespace-nowrap"> Since last month </span>
					</p> --}}
				</div>
			</div>
		</div>
	</div>
</x-hyde-admin::header>

<!-- Content -->
<div class="px-4 md:px-10 mx-auto w-full -m-24">
	<div class="flex flex-wrap">
		<div class="w-full xl:w-8/12 mb-12 xl:mb-0 px-4">
			<div class="relative flex flex-col min-w-0 break-words w-full mb-6 ">
				<section class="block w-full overflow-x-auto prose max-w-full shadow-lg  rounded bg-white">
					<header>
						<div class="rounded-t px-0 py-3 border-0">
							<div class="flex flex-wrap items-center">
								<div class="relative w-full px-0 max-w-full flex-grow flex-1">
									<h3 class="font-semibold text-lg text-slate-700 my-1 px-4">Blade Pages</h3>
								</div>
							</div>
						</div>
					</header>
					<table class="table-auto dashboard-table mt-0">
						<thead class="bg-slate-100">
						<tr>
							<th class="px-3 pt-2">Title</th>
							<th class="px-3 pt-2">Source File</th>
						</tr>
						</thead>
						<tbody>
						@foreach (\Hyde\Framework\Models\Pages\BladePage::all() as $page)
							<tr>
								<td class="px-3">
									<a href="{{ $page->getRoute() }}">
										{{ Hyde::makeTitle($page->view) }}
									</a>
								</td>
								<td class="px-3">
									{{ $page->getSourcePath() }}
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</section>

				<section class="block w-full overflow-x-auto prose max-w-full shadow-lg  rounded bg-white mt-8">
					<header>
						<div class="rounded-t px-0 py-3 border-0">
							<div class="flex flex-wrap items-center">
								<div class="relative w-full px-0 max-w-full flex-grow flex-1">
									<h3 class="font-semibold text-lg text-slate-700 my-1 px-4">Markdown Pages</h3>
								</div>
							</div>
						</div>
					</header>
					<table class="table-auto dashboard-table mt-0">
						<thead class="bg-slate-100">
						<tr>
							<th class="px-3 pt-2">Title</th>
							<th class="px-3 pt-2">Source File</th>
						</tr>
						</thead>
						<tbody>
						@foreach (\Hyde\Framework\Models\Pages\MarkdownPage::all() as $page)
							<tr>
								<td class="px-3">
									<a href="{{ $page->getRoute() }}">
										{{ Hyde::makeTitle($page->view) }}
									</a>
								</td>
								<td class="px-3">
									{{ $page->getSourcePath() }}
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</section>
			</div>
		</div>
		<div class="w-full xl:w-4/12 px-4">
			<div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded">
				<div class="rounded-t mb-0 px-4 py-3 bg-white">
					<div class="flex flex-wrap items-center">
						<div class="relative w-full max-w-full flex-grow flex-1">
							<h6 class="uppercase text-slate-400 mb-1 text-xs font-semibold"> Welcome to Hyde!
							</h6>
							<h2 class="text-slate-700 text-xl font-semibold"> Project Details							</h2>
						</div>
					</div>
				</div>
				<div class="p-4 flex-auto">
					<!-- Chart -->
					<div class="relative prose">
						<table class="-mt-3">
							<tbody>
								<tr>
									<th class="w-fit whitespace-nowrap pr-3" scope="row">Project Name:</th>
									<td class="w-full">{{ config('hyde.name', Hyde::makeTitle(basename(Hyde::path()))) }}</td>
								</tr>
						
								<tr>
									<th class="w-fit whitespace-nowrap pr-3" scope="row">Project Path:</th>
									<td class="w-full">{{ Hyde::path() }}</td>
								</tr>
						
								<tr>
									<th class="w-fit whitespace-nowrap pr-3" scope="row">Framework Version:</th>
									<td class="w-full">{{ Hyde::version() }}</td>
								</tr>
						
								<tr>
									<th class="w-fit whitespace-nowrap pr-3" scope="row">PHP Version:</th>
									<td class="w-full">{{ PHP_VERSION }} <small>({{ PHP_SAPI }})</small></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
