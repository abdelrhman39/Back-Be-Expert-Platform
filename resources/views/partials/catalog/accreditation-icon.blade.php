@switch ($name ?? '')
    @case('quality')
        <svg viewBox="0 0 80 80" aria-hidden="true">
            <rect x="4" y="4" width="72" height="72" rx="22" fill="#E7F2EB"/>
            <rect x="18" y="16" width="30" height="40" rx="5" fill="#fff" stroke="#074d31" stroke-width="2"/>
            <path d="M25 27h16M25 34h16M25 41h10" stroke="#9DB8A8" stroke-width="2" stroke-linecap="round"/>
            <circle cx="50" cy="48" r="12" fill="#fff" stroke="#074d31" stroke-width="2.4"/>
            <circle cx="50" cy="48" r="6" fill="#E7F2EB"/>
            <path d="M58.5 56.5 66 64" stroke="#074d31" stroke-width="3" stroke-linecap="round"/>
            <circle cx="58" cy="22" r="8" fill="#074d31"/>
            <path d="M54.8 22.1 57 24.3 61.4 19.6" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('attendance')
        <svg viewBox="0 0 80 80" aria-hidden="true">
            <rect x="4" y="4" width="72" height="72" rx="22" fill="#E7F2EB"/>
            <rect x="16" y="20" width="40" height="40" rx="6" fill="#fff" stroke="#074d31" stroke-width="2"/>
            <path d="M16 32h40" stroke="#074d31" stroke-width="2"/>
            <rect x="16" y="20" width="40" height="12" rx="6" fill="#074d31"/>
            <path d="M28 16v8M44 16v8" stroke="#074d31" stroke-width="2.4" stroke-linecap="round"/>
            <circle cx="28" cy="42" r="3" fill="#074d31"/>
            <circle cx="38" cy="42" r="3" fill="#B7D7C4"/>
            <circle cx="48" cy="42" r="3" fill="#074d31"/>
            <circle cx="28" cy="52" r="3" fill="#B7D7C4"/>
            <circle cx="38" cy="52" r="3" fill="#074d31"/>
            <circle cx="58" cy="54" r="10" fill="#074d31"/>
            <path d="M58 49.5v5l3.2 2" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('exam')
        <svg viewBox="0 0 80 80" aria-hidden="true">
            <rect x="4" y="4" width="72" height="72" rx="22" fill="#E7F2EB"/>
            <rect x="22" y="14" width="32" height="50" rx="6" fill="#fff" stroke="#074d31" stroke-width="2"/>
            <rect x="32" y="11" width="12" height="7" rx="2" fill="#074d31"/>
            <circle cx="32" cy="32" r="3.2" fill="#074d31"/>
            <path d="M30.6 32.1 31.7 33.3 33.6 30.8" stroke="#fff" stroke-width="1.2" stroke-linecap="round"/>
            <path d="M38 32h10" stroke="#9DB8A8" stroke-width="2" stroke-linecap="round"/>
            <circle cx="32" cy="43" r="3.2" fill="#074d31"/>
            <path d="M30.6 43.1 31.7 44.3 33.6 41.8" stroke="#fff" stroke-width="1.2" stroke-linecap="round"/>
            <path d="M38 43h10" stroke="#9DB8A8" stroke-width="2" stroke-linecap="round"/>
            <circle cx="32" cy="54" r="3.2" fill="#E7F2EB" stroke="#074d31" stroke-width="1.4"/>
            <path d="M38 54h10" stroke="#9DB8A8" stroke-width="2" stroke-linecap="round"/>
            <path d="M48 46 62 34" stroke="#161616" stroke-width="2.2" stroke-linecap="round"/>
            <path d="M58 32h8v8" stroke="#161616" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('certificate')
        <svg viewBox="0 0 80 80" aria-hidden="true">
            <rect x="4" y="4" width="72" height="72" rx="22" fill="#E7F2EB"/>
            <rect x="16" y="18" width="48" height="36" rx="5" fill="#fff" stroke="#074d31" stroke-width="2"/>
            <path d="M24 28h22M24 35h16" stroke="#9DB8A8" stroke-width="2" stroke-linecap="round"/>
            <circle cx="50" cy="38" r="8" fill="#E7F2EB" stroke="#074d31" stroke-width="2"/>
            <path d="M50 33.5 51.4 36.4 54.6 36.8 52.2 39 52.8 42.2 50 40.6 47.2 42.2 47.8 39 45.4 36.8 48.6 36.4Z" fill="#074d31"/>
            <path d="M42 54h6l-1.2 10L44 61l-2.8 3L42 54Z" fill="#074d31"/>
            <path d="M48 54h6l-1.2 10L50 61l-2.8 3L48 54Z" fill="#161616"/>
        </svg>
        @break
    @case('partners')
        <svg viewBox="0 0 80 80" aria-hidden="true">
            <rect x="4" y="4" width="72" height="72" rx="22" fill="#E7F2EB"/>
            <path d="M14 58V34l12-8 12 8v24" fill="#fff" stroke="#074d31" stroke-width="2" stroke-linejoin="round"/>
            <path d="M42 58V30l12-8 12 8v28" fill="#fff" stroke="#074d31" stroke-width="2" stroke-linejoin="round"/>
            <path d="M20 42h6M20 49h6M48 40h6M48 47h6" stroke="#B7D7C4" stroke-width="2" stroke-linecap="round"/>
            <circle cx="40" cy="46" r="8" fill="#074d31"/>
            <path d="M40 42.2v7.6M36.2 46h7.6" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        @break
    @case('payment')
        <svg viewBox="0 0 80 80" aria-hidden="true">
            <rect x="4" y="4" width="72" height="72" rx="22" fill="#E7F2EB"/>
            <rect x="14" y="24" width="44" height="30" rx="6" fill="#fff" stroke="#074d31" stroke-width="2"/>
            <path d="M14 34h44" stroke="#074d31" stroke-width="2"/>
            <rect x="20" y="40" width="10" height="7" rx="1.5" fill="#074d31"/>
            <path d="M36 43.5h14" stroke="#B7D7C4" stroke-width="2" stroke-linecap="round"/>
            <circle cx="56" cy="50" r="11" fill="#074d31"/>
            <path d="M51.6 50.2 54.6 53.2 60.6 46.8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
@endswitch
