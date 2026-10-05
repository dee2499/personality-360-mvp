@props([
    'insights' => [],
])

@php
    $stats = $insights['statistics'] ?? [];
    $cohortSize = (int) ($insights['cohort_size'] ?? 0);
    $cqScore = (float) ($insights['team_cq_score'] ?? 0.0);
    $meanCQ = (float) ($stats['mean'] ?? $cqScore);
    $medianCQ = (float) ($stats['median'] ?? $cqScore);
    $benchmarkCQ = (float) ($insights['benchmark_cq'] ?? 8.0);
    $gapCQ = (float) ($insights['gap_cq'] ?? round($meanCQ - $benchmarkCQ, 1));

    $getArchetypeData = function (float $score) {
        if ($score <= 2.0) {
            return [
                'name' => 'Change Resistor',
                'badge_style' => 'background-color: #fee2e2 !important; color: #991b1b !important; border: 1px solid #fca5a5;',
            ];
        } elseif ($score <= 4.0) {
            return [
                'name' => 'Change Follower',
                'badge_style' => 'background-color: #ffedd5 !important; color: #9a3412 !important; border: 1px solid #fdba74;',
            ];
        } elseif ($score <= 6.5) {
            return [
                'name' => 'Change Supporter',
                'badge_style' => 'background-color: #fef08a !important; color: #0a0f37 !important; border: 1px solid #fde047;',
            ];
        } elseif ($score <= 8.0) {
            return [
                'name' => 'Change Driver',
                'badge_style' => 'background-color: #fef3c7 !important; color: #92400e !important; border: 1px solid #fcd34d;',
            ];
        } else {
            return [
                'name' => 'Change Champion',
                'badge_style' => 'background-color: #d1fae5 !important; color: #065f46 !important; border: 1px solid #a7f3d0;',
            ];
        }
    };

    $meanArchetype = $getArchetypeData($meanCQ);
    $medianArchetype = $getArchetypeData($medianCQ);
@endphp

<!-- 5 Top Cards (Matching Theme Template) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5 sm:gap-4 select-none">
    <!-- 1. Team Size Card -->
    <div class="rounded-2xl p-4 shadow-2xs flex items-center gap-3.5 sm:gap-4" 
         style="background-color: #f8faff !important; border: 1px solid #e9edf5 !important;">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shrink-0 shadow-2xs" 
             style="background-color: #f3e8ff !important;">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#7c3aed]" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                <path d="M4.5 10.5c1.38 0 2.5-1.12 2.5-2.5S5.88 5.5 4.5 5.5 2 6.62 2 8s1.12 2.5 2.5 2.5zm0 1.5C2.83 12 0 12.83 0 14.5V17h5v-1.5c0-.98.39-1.87 1.03-2.56C5.41 12.35 4.88 12 4.5 12z" opacity="0.85"/>
                <path d="M19.5 10.5c1.38 0 2.5-1.12 2.5-2.5s-1.12-2.5-2.5-2.5-2.5 1.12-2.5 2.5 1.12 2.5 2.5 2.5zm0 1.5c-.38 0-.91.35-1.53.94.64.69 1.03 1.58 1.03 2.56V17h5v-2.5c0-1.67-2.83-2.5-4.5-2.5z" opacity="0.85"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs sm:text-[13px] font-black text-[#0a0f37] leading-tight">Team Size</div>
            <div class="text-2xl sm:text-[28px] font-black text-[#0a0f37] tracking-tight leading-none mt-1">
                {{ $cohortSize }}
            </div>
            <div class="text-[11px] sm:text-xs font-semibold text-slate-500 leading-snug mt-1">
                Employees assessed
            </div>
        </div>
    </div>

    <!-- 2. Average (Mean) CQ Score Card -->
    <div class="rounded-2xl p-4 shadow-2xs flex items-center gap-3.5 sm:gap-4" 
         style="background-color: #f0f7ff !important; border: 1px solid #d7ebfc !important;">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shrink-0 shadow-2xs" 
             style="background-color: #dbeafe !important;">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#0284c7]" viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="13" width="4" height="8" rx="2" />
                <rect x="10" y="8" width="4" height="13" rx="2" />
                <rect x="17" y="3" width="4" height="18" rx="2" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs sm:text-[13px] font-black text-[#0a0f37] leading-tight">
                Average (Mean) CQ Score
                <span class="sr-only">Team CQ (Group Score)</span>
            </div>
            <div class="mt-1 flex items-baseline gap-1 leading-none">
                <span class="text-2xl sm:text-[28px] font-black text-[#0a0f37] tracking-tight">
                    {{ number_format($meanCQ, 1) }}
                </span>
                <span class="text-xs sm:text-sm font-semibold text-slate-500">/ 10</span>
            </div>
            <div class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-extrabold whitespace-nowrap shadow-2xs" 
                      style="{{ $meanArchetype['badge_style'] }}">
                    {{ $meanArchetype['name'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- 3. Median CQ Score Card -->
    <div class="rounded-2xl p-4 shadow-2xs flex items-center gap-3.5 sm:gap-4" 
         style="background-color: #faf5ff !important; border: 1px solid #ede4fc !important;">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shrink-0 shadow-2xs" 
             style="background-color: #f3e8ff !important;">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#7c3aed]" viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="12" width="4" height="9" rx="2" />
                <rect x="10" y="7" width="4" height="14" rx="2" />
                <rect x="17" y="4" width="4" height="17" rx="2" />
                <polygon points="12,1 14,5 10,5" fill="#7c3aed" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs sm:text-[13px] font-black text-[#0a0f37] leading-tight">Median CQ Score</div>
            <div class="mt-1 flex items-baseline gap-1 leading-none">
                <span class="text-2xl sm:text-[28px] font-black text-[#0a0f37] tracking-tight">
                    {{ number_format($medianCQ, 1) }}
                </span>
                <span class="text-xs sm:text-sm font-semibold text-slate-500">/ 10</span>
            </div>
            <div class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-extrabold whitespace-nowrap shadow-2xs" 
                      style="{{ $medianArchetype['badge_style'] }}">
                    {{ $medianArchetype['name'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- 4. Expected / Benchmark Card -->
    <div class="rounded-2xl p-4 shadow-2xs flex items-center gap-3.5 sm:gap-4" 
         style="background-color: #f0fdf4 !important; border: 1px solid #dcfce7 !important;">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shrink-0 shadow-2xs" 
             style="background-color: #dcfce7 !important;">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#059669]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"/>
                <circle cx="12" cy="12" r="5"/>
                <circle cx="12" cy="12" r="2" fill="currentColor"/>
                <path d="M19 5l-5 5"/>
                <path d="M15 5h4v4"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs sm:text-[13px] font-black text-[#0a0f37] leading-tight">Expected / Benchmark</div>
            <div class="mt-1 flex items-baseline gap-1 leading-none">
                <span class="text-2xl sm:text-[28px] font-black tracking-tight" style="color: #059669 !important;">
                    {{ number_format($benchmarkCQ, 1) }}
                </span>
                <span class="text-xs sm:text-sm font-semibold text-slate-500">/ 10</span>
            </div>
            <div class="mt-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] sm:text-[11px] font-extrabold whitespace-nowrap shadow-2xs" 
                      style="background-color: #d1fae5 !important; color: #065f46 !important; border: 1px solid #a7f3d0;">
                    Change Champion
                </span>
            </div>
        </div>
    </div>

    <!-- 5. Gap to Benchmark Card -->
    <div class="rounded-2xl p-4 shadow-2xs flex items-center gap-3.5 sm:gap-4" 
         style="background-color: #fff1f2 !important; border: 1px solid #ffe4e6 !important;">
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shrink-0 shadow-2xs" 
             style="background-color: #ffe4e6 !important;">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#e11d48]" viewBox="0 0 24 24" fill="currentColor">
                <rect x="3" y="14" width="4" height="7" rx="2" />
                <rect x="10" y="10" width="4" height="11" rx="2" />
                <rect x="17" y="6" width="4" height="15" rx="2" />
                <path d="M4 10 L12 4 L18 8 L22 2" stroke="#e11d48" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                <polyline points="18 2 22 2 22 6" stroke="#e11d48" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs sm:text-[13px] font-black text-[#0a0f37] leading-tight">Gap to Benchmark</div>
            <div class="mt-1 flex items-baseline gap-1 leading-none">
                <span class="text-2xl sm:text-[28px] font-black tracking-tight" 
                      style="color: {{ $gapCQ < 0 ? '#e11d48' : '#059669' }} !important;">
                    {{ $gapCQ > 0 ? '+'.number_format($gapCQ, 1) : number_format($gapCQ, 1) }}
                </span>
            </div>
            <div class="text-[11px] sm:text-xs font-semibold text-slate-500 leading-snug mt-1">
                {{ $gapCQ < 0 ? 'Team needs to strengthen change capability' : 'Team meets or exceeds benchmark capability' }}
            </div>
        </div>
    </div>
</div>
