<?php
declare(strict_types=1);

function social_networks(): array
{
    return ['social_instagram'=>'Instagram','social_facebook'=>'Facebook','social_youtube'=>'YouTube',
        'social_tiktok'=>'TikTok','social_x'=>'X','social_threads'=>'Threads',
        'social_linkedin'=>'LinkedIn','social_telegram'=>'Telegram'];
}

function social_icon(string $key): string
{
    $paths = [
        'social_instagram'=>'<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'social_facebook'=>'<path d="M14 21v-8h3l1-4h-4V7c0-1 .5-2 2-2h2V2h-3c-4 0-5 2-5 5v2H7v4h3v8"/>',
        'social_youtube'=>'<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3V9Z"/>',
        'social_tiktok'=>'<path d="M14 3v13a4 4 0 1 1-4-4M14 3c0 4 3 6 7 6V6c-2 0-4-1-4-3h-3Z"/>',
        'social_x'=>'<path d="M4 3h4l12 18h-4L4 3ZM20 3l-7 8M4 21l7-8"/>',
        'social_threads'=>'<path d="M20 7c-1-3-4-5-8-5C5 2 3 7 3 12s2 10 9 10c5 0 9-3 9-7s-3-6-7-6c-3 0-5 1-5 4 0 4 7 4 7-1 0-4-1-6-4-6-2 0-3 1-4 2"/>',
        'social_linkedin'=>'<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 10v7M7 7h.01M11 17v-7M11 13c0-4 6-4 6 0v4"/>',
        'social_telegram'=>'<path d="m3 10 18-7-4 18-6-5-4 3v-6L18 6 9 14l-6-4Z"/>',
    ];
    if (!isset($paths[$key])) { return ''; }
    return '<svg class="social-icon panel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.$paths[$key].'</svg>';
}
