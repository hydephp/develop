<button id="searchMenuButton" class="absolute right-4 top-4 mr-4 z-10 opacity-75 hover:opacity-100 hidden md:block" onclick="toggleSearchMenu()" aria-label="Toggle search menu">
	Search 
	<svg class="float-left mr-1 dark:fill-white" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" role="presentation"><path d="M0 0h24v24H0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
</button>
<button id="searchMenuButtonMobile" class="block md:hidden fixed bottom-4 right-4 z-10 rounded-full p-2 opacity-75 hover:opacity-100 fill-black bg-gray-200 dark:fill-gray-200 dark:bg-gray-700" onclick="toggleSearchMenu()" aria-label="Toggle search menu">
	<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" role="presentation"><path d="M0 0h24v24H0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
</button>
@push('scripts')
	
<dialog id="searchMenu" class="prose dark:prose-invert bg-gray-100 dark:bg-gray-800 fixed z-50 p-4 rounded-lg overflow-y-hidden mt-[10vh] min-h-[300px] max-h-[75vh] w-[70ch] max-w-[90vw]">
	<x-hyde::docs.search-input />
	<footer class="mt-auto -mb-2 leading-4 text-center font-mono hidden sm:flex justify-center">
		<small>
			Press <code><kbd title="Forward slash">/</kbd></code> to open search window.
			Use <code><kbd title="Escape key">esc</kbd></code> to close.
		</small>
	</footer>
</dialog>

<script defer src="{{ Asset::cdnLink('HydeSearchWindow.js') }}"></script>
@endpush