@props([
    'outcomes' => [],
])

@php
    $defaultOutcomes = [
        [
            'icon' => 'arrow-up',
            'title' => 'Improved team synchronisation and faster decision making',
        ],
        [
            'icon' => 'users',
            'title' => 'Higher collective commitment and execution speed',
        ],
        [
            'icon' => 'settings',
            'title' => 'Better adaptability to change and reduced resistance',
        ],
        [
            'icon' => 'target',
            'title' => 'Stronger business outcomes and readiness for future changes',
        ],
    ];

    $items = !empty($outcomes) ? $outcomes : $defaultOutcomes;

    $badgeStyles = [
        0 => [
            'bg' => '#22c55e',
            'icon' => 'arrow-up',
        ],
        1 => [
            'bg' => '#3b82f6',
            'icon' => 'users',
        ],
        2 => [
            'bg' => '#a855f7',
            'icon' => 'settings',
        ],
        3 => [
            'bg' => '#f97316',
            'icon' => 'target',
        ],
    ];
@endphp

<div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-6 flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center shrink-0" style="background-color: #f4f2fd !important;">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#6f01d2]" viewBox="0 0 24 24" fill="currentColor">
                <rect x="3.5" y="13.5" width="3.5" height="7.5" rx="1.75" />
                <rect x="10.25" y="8" width="3.5" height="13" rx="1.75" />
                <rect x="17" y="2.5" width="3.5" height="18.5" rx="1.75" />
            </svg>
        </div>
        <h3 class="text-base sm:text-lg font-black text-[#0a0f37] tracking-tight">Expected Outcomes</h3>
    </div>

    <!-- Items List -->
    <div class="space-y-4 sm:space-y-5 flex-1 flex flex-col justify-around py-1">
        @foreach($items as $index => $item)
            @php
                $style = $badgeStyles[$index % 4];
                $iconType = $item['icon'] ?? $style['icon'];
                $bgColor = $style['bg'];
            @endphp
            <div class="flex items-center gap-3.5 sm:gap-4">
                <!-- Circular Icon Badge -->
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center shrink-0 shadow-2xs" style="background-color: {{ $bgColor }} !important;">
                    @if($iconType === 'arrow-up')
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 19V5M5 12l7-7 7 7"/>
                        </svg>
                    @elseif($iconType === 'users')
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            <path d="M4.5 10.5c1.38 0 2.5-1.12 2.5-2.5S5.88 5.5 4.5 5.5 2 6.62 2 8s1.12 2.5 2.5 2.5zm0 1.5C2.83 12 0 12.83 0 14.5V17h5v-1.5c0-.98.39-1.87 1.03-2.56C5.41 12.35 4.88 12 4.5 12z" opacity="0.9"/>
                            <path d="M19.5 10.5c1.38 0 2.5-1.12 2.5-2.5s-1.12-2.5-2.5-2.5-2.5 1.12-2.5 2.5 1.12 2.5 2.5 2.5zm0 1.5c-.38 0-.91.35-1.53.94.64.69 1.03 1.58 1.03 2.56V17h5v-2.5c0-1.67-2.83-2.5-4.5-2.5z" opacity="0.9"/>
                        </svg>
                    @elseif($iconType === 'settings')
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567l-.216 1.294a7.994 7.994 0 0 0-1.464.606l-1.126-.689a1.875 1.875 0 0 0-2.316.429L3.05 6.69a1.875 1.875 0 0 0 .167 2.35l.93.93a8.03 8.03 0 0 0 0 1.706l-.93.93a1.875 1.875 0 0 0-.167 2.35l1.056 1.233a1.875 1.875 0 0 0 2.316.429l1.126-.689c.45.26.942.466 1.464.606l.216 1.294c.151.904.933 1.567 1.85 1.567h1.604c.917 0 1.699-.663 1.85-1.567l.216-1.294c.522-.14 1.014-.346 1.464-.606l1.126.689a1.875 1.875 0 0 0 2.316-.429l1.056-1.233a1.875 1.875 0 0 0-.167-2.35l-.93-.93a8.03 8.03 0 0 0 0-1.706l.93-.93a1.875 1.875 0 0 0 .167-2.35L20.67 5.46a1.875 1.875 0 0 0-2.316-.429l-1.126.689a7.994 7.994 0 0 0-1.464-.606l-.216-1.294A1.875 1.875 0 0 0 13.702 2.25h-1.624ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <circle cx="12" cy="12" r="5"/>
                            <circle cx="12" cy="12" r="2" fill="currentColor"/>
                            <path d="M19 5l-5 5"/>
                            <path d="M15 5h4v4"/>
                        </svg>
                    @endif
                </div>

                <!-- Text -->
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-[13.5px] font-bold text-[#0a0f37] leading-snug">
                        {{ $item['title'] ?? '' }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>
