@props([
    'route',
    'label',
    'icon' => 'file',
])

<a href="?route={{ $route }}" @class([
        'text-xs uppercase py-3 font-bold block text-slate-700 hover:text-slate-500',
        'text-pink-500 hover:text-pink-600' => $page->route == $route ])>
    <i class="fas fa-{{ $icon }} mr-2 text-sm opacity-75"></i>{{ $label }}</a>
