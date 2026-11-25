<?php
// Небольшие inline-SVG иконки (stroke = currentColor) вместо эмодзи и стикеров.
if (!function_exists('icon')) {
    function icon(string $name, int $size = 20, string $class = 'ic-svg'): string
    {
        static $paths = [
            'cart'    => '<path d="M3 4h2.2l2.3 10.2a1 1 0 0 0 1 .8h8.3a1 1 0 0 0 1-.75L20.4 8H6"/><circle cx="9.5" cy="19" r="1.4"/><circle cx="17.5" cy="19" r="1.4"/>',
            'search'  => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.7-3.7"/>',
            'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20c1.4-3.6 4.2-5.4 7.5-5.4s6.1 1.8 7.5 5.4"/>',
            'camera'  => '<path d="M3 8.5h4L8.6 6h6.8L17 8.5h4V19H3z"/><circle cx="12" cy="13.2" r="3.4"/>',
            'pencil'  => '<path d="M14.7 5.3l4 4L8.4 19.6l-4.9.9.9-4.9z"/><path d="M13.2 6.8l4 4"/>',
            'lock'    => '<rect x="4.5" y="10" width="15" height="10" rx="2.6"/><path d="M8 10V7.2a4 4 0 0 1 8 0V10"/>',
            'check'   => '<path d="M4.5 12.5l5 5L20 6.5"/>',
            'close'   => '<path d="M6.5 6.5l11 11M17.5 6.5l-11 11"/>',
            'chevron' => '<path d="M6 9.5l6 6 6-6"/>',
            'arrow'   => '<path d="M4 12h14.5"/><path d="M13 6.2l6 5.8-6 5.8"/>',
            'phone'   => '<path d="M6 3.2h3.1l1.9 4.9-2.3 1.4a11.4 11.4 0 0 0 5.8 5.8l1.4-2.3 4.9 1.9v3.1a1.9 1.9 0 0 1-2.1 1.9A15.6 15.6 0 0 1 4.1 5.3 1.9 1.9 0 0 1 6 3.2z"/>',
            'mail'    => '<rect x="3" y="5.2" width="18" height="13.6" rx="2.6"/><path d="M3.8 7l8.2 6 8.2-6"/>',
            'home'    => '<path d="M4 11l8-6.6 8 6.6"/><path d="M6.3 9.6V20h11.4V9.6"/>',
            'truck'   => '<path d="M2.6 6.5h10.6v9.2H2.6z"/><path d="M13.2 9.8h3.6l2.9 3v2.9h-6.5z"/><circle cx="6.6" cy="18" r="1.7"/><circle cx="16.8" cy="18" r="1.7"/>',
            'clock'   => '<circle cx="12" cy="12" r="8.8"/><path d="M12 6.6V12l3.6 2.1"/>',
            'box'     => '<path d="M12 3.2l8.2 4.6v8.4L12 20.8 3.8 16.2V7.8z"/><path d="M4 7.9l8 4.5 8-4.5M12 12.4v8.4"/>',
            'tag'     => '<path d="M11.2 3.4H20v8.8l-8.8 8.8L2.4 12.6z"/><circle cx="16.2" cy="7.8" r="1.5"/>',
            'pin'     => '<path d="M12 21s6.6-6.3 6.6-11A6.6 6.6 0 0 0 5.4 10c0 4.7 6.6 11 6.6 11z"/><circle cx="12" cy="10" r="2.4"/>',
            'chat'    => '<path d="M21 11.5a8.4 8.4 0 1 1-3-6.4"/><path d="M21 4l-8.5 8.5"/><path d="M21 4l-4.5 12-4-7.5L5 6.5z"/>',
            'people'  => '<circle cx="9" cy="7.5" r="3.5"/><path d="M3 20c1-2.5 3.2-4 6-4s5 1.5 6 4"/><circle cx="17.5" cy="8.5" r="2.5"/><path d="M21 20c-.7-2-2.7-3.4-5-3.7"/>',
            'star'    => '<path d="M12 2.5l3 6.1 6.7 1-4.9 4.7 1.2 6.7L12 17.8l-6 3.2 1.2-6.7L2.3 9.6l6.7-1z"/>',
            'send'    => '<path d="M21.5 2.5L2.5 11.5l7.5 2.5 2.5 7.5L21.5 2.5z"/><path d="M10 14l4-4"/>',
        ];
        $body = $paths[$name] ?? '';
        return '<svg class="' . e($class) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" '
            . 'stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . $body . '</svg>';
    }
}
