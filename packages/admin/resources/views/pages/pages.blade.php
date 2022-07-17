@php
    use Hyde\Framework\Models\Pages\BladePage;
@endphp

<x-hyde-admin::header>
    <x-hyde-admin::card>
        <article class="prose">
            <h2>Your pages</h2>
            <p class="">Here you'll find an overview of your Blade and Markdown pages.</p>
        </article>
    </x-hyde-admin::card>
</x-hyde-admin::header>

<div class="px-4 md:px-10 mx-auto w-full -m-28">
    <div class="flex flex-wrap">
        <x-hyde-admin::card class="w-1/2">
                <div class="rounded-t mb-3 px-0 py-3 border-0">
                    <div class="flex flex-wrap items-center">
                        <div class="relative w-full px-0 max-w-full flex-grow flex-1">
                            <h3 class="font-semibold text-lg text-slate-700"> Blade Pages </h3>
                        </div>
                        <div class="relative w-full px-0 max-w-full flex-grow flex-1 text-right">
                            <button class="bg-indigo-500 text-white active:bg-indigo-600 text-xs font-bold uppercase px-3 py-1 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150" type="button">
                            Create new
                            </button>
                        </div>
                    </div>
                </div>
                <div class="block w-full overflow-x-auto prose max-w-full">
                    <!-- Projects table -->
                    <table class="table-auto dashboard-table">
                        <thead class="bg-slate-100">
                        <tr>
                            <th class="px-3 pt-2">Title</th>
                            <th class="px-3 pt-2">Source File</th>
                            <th class="px-3 pt-2 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach (BladePage::all() as $page)
                            <tr>
                                <td class="px-3">
                                    <a href="{{ Hyde::pageLink($page->slug . '.html') }}">
                                        {{ Hyde::makeTitle($page->view) }}
                                    </a>
                                </td>
                                <td class="px-3">
                                    {{ (BladePage::$sourceDirectory .'/'. $page->slug . BladePage::$fileExtension) }}
                                </td>
                                <td class="px-3 text-right not-prose">
                                    <a href="{{ Hyde::pageLink($page->slug . '.html') }}" class="text-blue-500 opacity-75 hover:opacity-100 hover:text-blue-700" title="View compiled page">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="" class="text-blue-500 opacity-75 hover:opacity-100 hover:text-blue-700 ml-1" title="Edit source">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <form class="inline ml-1" action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this file? If you do not use version control it may not be recoverable.')">
                                        @method('DELETE')

                                        <button class="text-red-500 opacity-75 hover:opacity-100 hover:text-red-700" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
        </x-hyde-admin::card>
    </div>
</div>