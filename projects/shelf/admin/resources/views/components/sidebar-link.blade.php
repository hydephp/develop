@props([
    'route',
    'label',
    'icon' => 'file',
    'active' => $page->route == $route
])

<a href="?route={{ $route }}" @class([
        'text-xs uppercase py-3 font-bold block text-slate-700 hover:text-slate-500',
        'text-pink-500 hover:text-pink-600' => $active ])>
    <i @class([
        "fas fa-$icon mr-2 text-sm",
        'opacity-75' => $active,
        'text-slate-300' => ! $active
    ])></i>
{{ $label }}</a>
