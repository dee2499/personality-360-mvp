@props([
    'insights' => [],
    'plan' => null,
])

@php
    $planData = $plan ?? ($insights['plan_30_60_90'] ?? []);

    $phase30 = $planData['phase_30'] ?? [
        'title' => 'First 30 Days',
        'subtitle' => 'Align & Engage',
        'items' => [
            'Conduct a team alignment workshop (See, Agree, Act).',
            'Clarify key change priorities and expected outcomes.',
            'Identify major misalignments and address them openly.',
            'Establish regular team check-ins.',
        ],
    ];

    $phase60 = $planData['phase_60'] ?? [
        'title' => 'Next 60 Days',
        'subtitle' => 'Build & Act Together',
        'items' => [
            'Run focused capability building sessions.',
            'Facilitate cross-functional collaboration and joint problem solving.',
            'Define and execute team commitments.',
            'Track progress and remove roadblocks.',
        ],
    ];

    $phase90 = $planData['phase_90'] ?? [
        'title' => 'Next 90 Days',
        'subtitle' => 'Scale & Institutionalise',
        'items' => [
            'Review progress and measure improvements in CQ and CQ Sync.',
            'Embed successful practices into regular ways of working.',
            'Strengthen accountability and collective ownership.',
            'Plan next phase to reach and sustain the Opportunity Zone.',
        ],
    ];
@endphp

<div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background-color: #f4f2fd !important;">
            <svg class="w-5 h-5 text-[#6f01d2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="3"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
                <circle cx="8" cy="14" r="1" fill="currentColor"/>
                <circle cx="12" cy="14" r="1" fill="currentColor"/>
                <circle cx="16" cy="14" r="1" fill="currentColor"/>
                <circle cx="8" cy="18" r="1" fill="currentColor"/>
                <circle cx="12" cy="18" r="1" fill="currentColor"/>
                <circle cx="16" cy="18" r="1" fill="currentColor"/>
            </svg>
        </div>
        <h3 class="text-base sm:text-lg font-black text-[#0a0f37] tracking-tight">30 – 60 – 90 Day Plan</h3>
    </div>

    <!-- 3-Phase Process Columns -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Phase 1: First 30 Days -->
        <div class="flex flex-col space-y-4">
            <div class="plan-chevron-arrow-end py-3 px-4 md:pl-4 md:pr-8 rounded-xl text-center shadow-2xs" style="background-color: #fef3c7 !important;">
                <div class="text-xs font-bold text-[#92400e] leading-tight">{{ $phase30['title'] ?? 'First 30 Days' }}</div>
                <div class="text-sm sm:text-base font-black text-[#78350f] leading-tight mt-0.5">{{ $phase30['subtitle'] ?? 'Align & Engage' }}</div>
            </div>
            <ul class="space-y-3 text-xs sm:text-[13px] text-slate-700 leading-relaxed px-1">
                @foreach($phase30['items'] ?? [] as $item)
                    <li class="flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#d97706] shrink-0 mt-2"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Phase 2: Next 60 Days -->
        <div class="flex flex-col space-y-4">
            <div class="plan-chevron-arrow-both py-3 px-4 md:px-7 rounded-xl text-center shadow-2xs" style="background-color: #dbeafe !important;">
                <div class="text-xs font-bold text-[#1e40af] leading-tight">{{ $phase60['title'] ?? 'Next 60 Days' }}</div>
                <div class="text-sm sm:text-base font-black text-[#1e3a8a] leading-tight mt-0.5">{{ $phase60['subtitle'] ?? 'Build & Act Together' }}</div>
            </div>
            <ul class="space-y-3 text-xs sm:text-[13px] text-slate-700 leading-relaxed px-1">
                @foreach($phase60['items'] ?? [] as $item)
                    <li class="flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563eb] shrink-0 mt-2"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Phase 3: Next 90 Days -->
        <div class="flex flex-col space-y-4">
            <div class="plan-chevron-arrow-start py-3 px-4 md:pl-8 md:pr-4 rounded-xl text-center shadow-2xs" style="background-color: #d1fae5 !important;">
                <div class="text-xs font-bold text-[#065f46] leading-tight">{{ $phase90['title'] ?? 'Next 90 Days' }}</div>
                <div class="text-sm sm:text-base font-black text-[#064e3b] leading-tight mt-0.5">{{ $phase90['subtitle'] ?? 'Scale & Institutionalise' }}</div>
            </div>
            <ul class="space-y-3 text-xs sm:text-[13px] text-slate-700 leading-relaxed px-1">
                @foreach($phase90['items'] ?? [] as $item)
                    <li class="flex items-start gap-2.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#059669] shrink-0 mt-2"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<style>
    @media (min-width: 768px) {
        .plan-chevron-arrow-end {
            clip-path: polygon(0% 0%, calc(100% - 16px) 0%, 100% 50%, calc(100% - 16px) 100%, 0% 100%);
            border-top-right-radius: 0px !important;
            border-bottom-right-radius: 0px !important;
        }
        .plan-chevron-arrow-both {
            clip-path: polygon(0% 0%, calc(100% - 16px) 0%, 100% 50%, calc(100% - 16px) 100%, 0% 100%, 16px 50%);
            border-radius: 0px !important;
        }
        .plan-chevron-arrow-start {
            clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 16px 50%);
            border-top-left-radius: 0px !important;
            border-bottom-left-radius: 0px !important;
        }
    }
</style>
