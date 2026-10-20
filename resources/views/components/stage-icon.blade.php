@props(['name'])
@php
    $paths = [
        'code' => 'm8 7-5 5 5 5m8-10 5 5-5 5M14 4l-4 16',
        'team' => 'M12 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM3 21v-3a6 6 0 0 1 12 0v3m1-17a3 3 0 0 1 0 6m2 5a5 5 0 0 1 3 4v2',
        'sparkle' => 'M12 3l2 6 6 3-6 2-2 7-2-7-6-2 6-3 2-6Z',
        'info' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0-10v5m0-8h.01',
        'calendar' => 'M5 5h14v15H5zM5 10h14M9 3v4m6-4v4',
        'chat' => 'M5 5h14v10h-8l-4 4v-4H5z',
    ];
@endphp
<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? '' }}"/></svg>
