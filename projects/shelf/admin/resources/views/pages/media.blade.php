@php
use Hyde\Framework\Services\DiscoveryService;
@endphp

<x-hyde-admin::header>
	<x-hyde-admin::card>
		<article class="prose">
			<h2>Your Media Library</h2>
			<p class="">Here you'll find an overview of your site's media assets.</p>
		</article>
	</x-hyde-admin::card>
</x-hyde-admin::header>

<div class="px-4 md:px-10 mx-auto w-full -m-28">
	<div class="flex -mx-3">
		<x-hyde-admin::card class="w-2/3 mx-3">
			<div class="rounded-t mb-3 px-0 py-3 border-0">
				<div class="flex flex-wrap items-center">
					<div class="relative w-full px-0 max-w-full flex-grow flex-1">
						<h3 class="font-semibold text-lg text-slate-700"> Media Files </h3>
					</div>
					<div class="relative w-full px-0 max-w-full flex-grow flex-1 text-right">
						{{-- <button
							class="bg-indigo-500 text-white active:bg-indigo-600 text-xs font-bold uppercase px-3 py-1 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150"
							type="button">
							Upload
						</button> --}}
					</div>
				</div>
			</div>
			<div class="block w-full overflow-x-auto prose max-w-full">
				<!-- Projects table -->
				<table class="table-auto dashboard-table">
					<thead class="bg-slate-100">
						<tr>
							<th class="px-3 pt-2">Filename</th>
							<th class="px-3 pt-2">Type</th>
							<th class="px-3 pt-2">Size</th>
							<th class="px-3 pt-2">Last modified</th>
						</tr>
					</thead>
					<tbody>
						@foreach (DiscoveryService::getMediaAssetFiles() as $file)
						<tr>
							<td class="px-3">
								<a href="media/{{ basename($file) }}">
									{{ basename($file) }}
								</a>
							</td>
							<td class="px-3">
								@php
								$ext = pathinfo($file, PATHINFO_EXTENSION);
								if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'])) {
								echo 'Image';
								} elseif (in_array($ext, ['css', 'scss', 'sass'])) {
								echo 'Stylesheet';
								} elseif (in_array($ext, ['js', 'ts', 'php'])) {
								echo 'Script';
								} elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) {
								echo 'Audio';
								} elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
								echo 'Video';
								} else {
								echo 'File';
								}
								@endphp
							</td>
							<td class="px-3">
								@php
								$size = filesize($file);
								if ($size < 1024) { echo $size . ' bytes' ; } elseif ($size < 1048576) { echo
									round($size / 1024, 2) . ' KB' ; } else { echo round($size / 1048576, 2) . ' MB' ; }
									@endphp </td>
							<td class="px-3">
								{{ \Carbon\Carbon::parse(filemtime($file))->diffForHumans() }}
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</x-hyde-admin::card>

		<x-hyde-admin::card class="w-1/3 mx-3">
			<div class="rounded-t mb-3 px-0 py-3 border-0">
				<div class="flex flex-wrap items-center">
					<div class="relative w-full px-0 max-w-full flex-grow flex-1">
						<h3 class="font-semibold text-lg text-slate-700"> Image Gallery </h3>
					</div>
					<div class="relative w-full px-0 max-w-full flex-grow flex-1 text-right">
						{{-- <button
							class="bg-indigo-500 text-white active:bg-indigo-600 text-xs font-bold uppercase px-3 py-1 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150"
							type="button">
							Upload
						</button> --}}
					</div>
				</div>
			</div>
			<div class="block w-full overflow-x-auto prose max-w-full">
				<div class="flex flex-row flex-wrap">
					@foreach (DiscoveryService::getMediaAssetFiles() as $file)
					@if(in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg']))
					<a href="media/{{ basename($file) }}">
						<img width="64px" height="auto" class="block" src="media/{{ basename($file) }}"
							alt="{{ basename($file) }}" title="{{ basename($file) }}">
					</a>
					@endif
					@endforeach
				</div>
			</div>
		</x-hyde-admin::card>
	</div>
</div>