<x-layouts.app>
    @php
        $cqScore = (float) ($insights['team_cq_score'] ?? 0.0);
        $syncScore = (float) ($insights['team_cq_sync_score'] ?? 0.0);
        $benchmarkCQ = (float) ($insights['benchmark_cq'] ?? 8.0);
        $benchmarkSync = (float) ($insights['benchmark_sync'] ?? 8.0);
        $gapCQ = (float) ($insights['gap_cq'] ?? -1.8);
        $gapSync = (float) ($insights['gap_sync'] ?? -2.4);

        $matrixZone = $insights['matrix_zone'] ?? 'capability';
        $stats = $insights['statistics'] ?? [];
        $dist = $insights['distribution'] ?? [];
        $sync = $insights['cq_sync'] ?? [];

        // Matrix coordinates in percent (0-10 -> 0-100%)
        $xPct = min(92, max(8, ($cqScore / 10) * 100));
        $yPct = min(92, max(8, 100 - (($syncScore / 10) * 100)));

        // Benchmark coordinates in percent
        $bmXPct = ($benchmarkCQ / 10) * 100;
        $bmYPct = 100 - (($benchmarkSync / 10) * 100);

        // Speedometer needle angle for sync (piecewise mapping)
        if ($syncScore <= 0) {
            $syncNeedleAngle = -90;
        } elseif ($syncScore <= 2.0) {
            $syncNeedleAngle = -90 + (max(0, $syncScore - 1.0) / 1.0) * 36;
        } elseif ($syncScore <= 4.0) {
            $syncNeedleAngle = -54 + (($syncScore - 2.0) / 2.0) * 36;
        } elseif ($syncScore <= 6.0) {
            $syncNeedleAngle = -18 + (($syncScore - 4.0) / 2.0) * 36;
        } elseif ($syncScore <= 8.0) {
            $syncNeedleAngle = 18 + (($syncScore - 6.0) / 2.0) * 36;
        } else {
            $syncNeedleAngle = 54 + (min(2.0, $syncScore - 8.0) / 2.0) * 36;
        }
        $syncNeedleAngle = round(max(-90, min(90, $syncNeedleAngle)), 2);

        // Speedometer needle angle for team maturity
        $clampedCQ = max(1.0, min(10.0, $cqScore > 0 ? $cqScore : 5.0));
        $cqNeedleAngle = -90 + (($clampedCQ - 1.0) / 9.0) * 180;

        // Dynamic gap description matching reference card
        if ($gapCQ < 0 && $gapSync < 0) {
            $gapDescription = 'Need to strengthen both capability and synchronisation to reach the target zone.';
        } elseif ($gapCQ < 0 && $gapSync >= 0) {
            $gapDescription = 'Need to strengthen capability while maintaining strong team synchronisation.';
        } elseif ($gapCQ >= 0 && $gapSync < 0) {
            $gapDescription = 'Strong capability present; need to strengthen synchronisation to reach the target zone.';
        } else {
            $gapDescription = 'Target benchmark achieved across both capability and synchronisation.';
        }

        $syncPillClass = match ($sync['maturity_level'] ?? 'Aligned') {
            'Divergent' => 'bg-[#ffd2dc] text-[#0a0f37]',
            'Fragmented' => 'bg-[#fed7aa] text-[#0a0f37]',
            default => 'bg-[#d4fce6] text-[#0a0f37]',
        };
    @endphp

    <div class="space-y-6 sm:space-y-8" x-data="{ activeTab: 'summary' }">
        <!-- Top Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('participant.assessments.index', ['survey_id' => $survey->id]) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Assessments</span>
            </a>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Team Public Report • Zero Names Exposed</span>
                </span>
                
                <a href="{{ route('participant.assessments.report', $survey) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 shadow-2xs transition">
                    <i data-lucide="award" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span>My Confidential Report</span>
                </a>
            </div>
        </div>

        <!-- Document Main Header -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs relative">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 border-b border-slate-100 pb-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-black tracking-tight text-indigo-900">change<span class="text-indigo-600">quo</span></span>
                        <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">• Unlocking Possibilities</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Team ChangeQuo Report
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
                        Capability × Synchronisation • Where We Stand and How We Move Forward ({{ $insights['cohort_size'] }} Team Members)
                    </p>
                </div>

                <div class="text-left md:text-right space-y-1">
                    <div class="text-xs font-bold text-slate-700">Team: <span class="text-indigo-900 font-black">{{ $survey->title }}</span></div>
                    <div class="text-xs text-slate-500">Team Size: <span class="font-bold text-slate-800">{{ $insights['cohort_size'] }}</span> • Assessed Date: {{ $survey->published_at ? $survey->published_at->format('d M Y') : now()->format('d M Y') }}</div>
                    @if($survey->company)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100 mt-1">
                            <i data-lucide="building-2" class="w-3 h-3 text-indigo-500"></i>
                            {{ $survey->company->name }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- View Navigation Tabs (Images 2, 3, 4) -->
            <div class="flex items-center gap-2 pt-6 overflow-x-auto">
                <button type="button" 
                        @click="activeTab = 'summary'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeTab === 'summary' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>1. Summary & Position Matrix</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'sync'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeTab === 'sync' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>2. Team CQ Sync Report (3 Dimensions)</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'capability'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeTab === 'capability' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>3. Capability & Score Distribution</span>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: SUMMARY REPORT & TEAM POSITION MATRIX (MATCHING IMAGE 2) -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'summary'" class="space-y-4 sm:space-y-5" x-transition>
            <!-- 4 Top Cards (Matching Reference Photo) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- 1. Team CQ (Group Score) -->
                <div class="rounded-2xl p-3.5 shadow-2xs flex items-start gap-2.5 sm:gap-3" style="background-color: #eef7fe !important; border: 1px solid #d7ebfc !important;">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background-color: #d0e5ff !important;">
                        <svg class="w-5 h-5 text-[#0f78ec]" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="14" width="3.5" height="7" rx="1.75" />
                            <rect x="9.75" y="8.5" width="3.5" height="12.5" rx="1.75" />
                            <rect x="16.5" y="3" width="3.5" height="18" rx="1.75" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-black text-[#0a0f37] leading-tight">Team CQ</div>
                        <div class="text-[10px] font-bold text-[#0a0f37] leading-tight mt-0.5">(Group Score)</div>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl font-black text-[#0a0f37] tracking-tight">{{ number_format($cqScore, 1) }}</span>
                            <span class="text-[11px] font-semibold text-slate-500">/ 10</span>
                        </div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap shadow-2xs" style="background-color: #fef08a !important; color: #0a0f37 !important;">
                                {{ $insights['group_transition_label'] ?? 'Change Supporter → Driver' }}
                            </span>
                        </div>
                        <p class="mt-1 text-[10.5px] text-[#4b5585] leading-tight">Capability to navigate change</p>
                    </div>
                </div>

                <!-- 2. Team CQ Sync -->
                <div class="rounded-2xl p-3.5 shadow-2xs flex items-start gap-2.5 sm:gap-3" style="background-color: #f4f2fd !important; border: 1px solid #e7ddfb !important;">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background-color: #e4d5ff !important;">
                        <svg class="w-5 h-5 text-[#6f01d2]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            <path d="M4.5 10.5c1.38 0 2.5-1.12 2.5-2.5S5.88 5.5 4.5 5.5 2 6.62 2 8s1.12 2.5 2.5 2.5zm0 1.5C2.83 12 0 12.83 0 14.5V17h5v-1.5c0-.98.39-1.87 1.03-2.56C5.41 12.35 4.88 12 4.5 12z" opacity="0.85"/>
                            <path d="M19.5 10.5c1.38 0 2.5-1.12 2.5-2.5s-1.12-2.5-2.5-2.5-2.5 1.12-2.5 2.5 1.12 2.5 2.5 2.5zm0 1.5c-.38 0-.91.35-1.53.94.64.69 1.03 1.58 1.03 2.56V17h5v-2.5c0-1.67-2.83-2.5-4.5-2.5z" opacity="0.85"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-black text-[#0a0f37] leading-tight">Team CQ Sync</div>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl font-black text-[#0a0f37] tracking-tight">{{ number_format($syncScore, 1) }}</span>
                            <span class="text-[11px] font-semibold text-slate-500">/ 10</span>
                        </div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap shadow-2xs" style="background-color: #d4fce6 !important; color: #0a0f37 !important;">
                                {{ $sync['maturity_level'] ?? 'Aligned' }}
                            </span>
                        </div>
                        <p class="mt-1 text-[10.5px] text-[#4b5585] leading-tight">How well we see, agree and act together</p>
                    </div>
                </div>

                <!-- 3. Expected / Benchmark -->
                <div class="rounded-2xl p-3.5 shadow-2xs flex items-start gap-2.5 sm:gap-3" style="background-color: #f2fbf6 !important; border: 1px solid #daf5e6 !important;">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background-color: #d4fce6 !important;">
                        <svg class="w-5 h-5 text-[#047857]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <circle cx="12" cy="12" r="5"/>
                            <circle cx="12" cy="12" r="2" fill="currentColor"/>
                            <path d="M19 5l-5 5"/>
                            <path d="M15 5h4v4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-black text-[#0a0f37] leading-tight">Expected / Benchmark</div>
                        <div class="mt-1 flex items-baseline gap-1 whitespace-nowrap">
                            <span class="text-[11px] font-bold text-[#0a0f37]">CQ:</span>
                            <span class="text-base font-black tracking-tight" style="color: #047857 !important;">{{ number_format($benchmarkCQ, 1) }}</span>
                            <span class="text-slate-300 mx-1 text-xs font-light">|</span>
                            <span class="text-[11px] font-bold text-[#0a0f37]">CQ Sync:</span>
                            <span class="text-base font-black tracking-tight" style="color: #047857 !important;">{{ number_format($benchmarkSync, 1) }}</span>
                        </div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap shadow-2xs" style="background-color: #d4fce6 !important; color: #0a0f37 !important;">
                                Change Champion & Unified
                            </span>
                        </div>
                        <p class="mt-1 text-[10.5px] text-[#4b5585] leading-tight">High capability with strong synchronisation</p>
                    </div>
                </div>

                <!-- 4. Gaps to Benchmark -->
                <div class="rounded-2xl p-3.5 shadow-2xs flex items-start gap-2.5 sm:gap-3" style="background-color: #fef2f2 !important; border: 1px solid #fcd5dc !important;">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background-color: #ffd2dc !important;">
                        <svg class="w-5 h-5 text-[#ed082d]" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="14" width="3.5" height="7" rx="1.75" />
                            <rect x="9.75" y="8.5" width="3.5" height="12.5" rx="1.75" />
                            <rect x="16.5" y="3" width="3.5" height="18" rx="1.75" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-black text-[#0a0f37] leading-tight">Gaps to Benchmark</div>
                        <div class="mt-1 flex items-baseline gap-1 whitespace-nowrap">
                            <span class="text-[11px] font-bold text-[#0a0f37]">CQ:</span>
                            <span class="text-base font-black tracking-tight" style="color: {{ $gapCQ < 0 ? '#ed082d' : '#047857' }} !important;">
                                {{ $gapCQ > 0 ? '+'.number_format($gapCQ, 1) : number_format($gapCQ, 1) }}
                            </span>
                            <span class="text-slate-300 mx-1 text-xs font-light">|</span>
                            <span class="text-[11px] font-bold text-[#0a0f37]">CQ Sync:</span>
                            <span class="text-base font-black tracking-tight" style="color: {{ $gapSync < 0 ? '#ed082d' : '#047857' }} !important;">
                                {{ $gapSync > 0 ? '+'.number_format($gapSync, 1) : number_format($gapSync, 1) }}
                            </span>
                        </div>
                        <p class="mt-1 text-[10.5px] text-[#4b5585] leading-tight">
                            {{ $gapDescription }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section: Team Position Matrix (Full Row) -->
            <div class="w-full">
                <x-team-position-matrix :cq-score="$cqScore" :sync-score="$syncScore" :benchmark-cq="$benchmarkCQ" :benchmark-sync="$benchmarkSync" />
            </div>

            <!-- Section: What This Means & Path to Opportunity Zone (2-Column Row) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
                <!-- What This Means Card (Matching Reference Design) -->
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center shrink-0" style="background-color: #f4f2fd !important;">
                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-[#6f01d2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
                                <path d="M9 18h6"/>
                                <path d="M10 22h4"/>
                            </svg>
                        </div>
                        <h3 class="text-sm sm:text-base font-black text-[#0a0f37] tracking-tight">What This Means</h3>
                    </div>

                    <div class="space-y-3 sm:space-y-3.5 flex-1 flex flex-col justify-around py-0.5">
                        <!-- 1. Current Position -->
                        <div class="flex items-start gap-3 sm:gap-3.5">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5 shadow-2xs" style="background-color: #fef08a !important;">
                                <svg class="w-4.5 h-4.5 text-[#d97706]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9"/>
                                    <circle cx="12" cy="12" r="5"/>
                                    <circle cx="12" cy="12" r="2" fill="currentColor"/>
                                    <path d="M19 5l-5 5"/>
                                    <path d="M15 5h4v4"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-black text-xs sm:text-[13.5px] leading-tight tracking-tight text-[#d97706]">
                                    Current Position: {{ $insights['matrix_zone_name'] }}
                                    <span class="font-bold">({{ number_format($cqScore, 1) }}, {{ number_format($syncScore, 1) }})</span>
                                </h4>
                                <p class="mt-1 text-[11px] sm:text-xs text-[#4b5585] leading-relaxed">
                                    {{ $insights['matrix_zone_subtitle'] }}
                                </p>
                            </div>
                        </div>

                        <!-- 2. Key Blind Spot -->
                        <div class="flex items-start gap-3 sm:gap-3.5">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5 shadow-2xs" style="background-color: #ffd2dc !important;">
                                <svg class="w-4.5 h-4.5 text-[#ed082d]" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10.75 4a1.25 1.25 0 0 1 2.5 0v8.5a1.25 1.25 0 0 1-2.5 0V4zM12 17.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-black text-xs sm:text-[13.5px] leading-tight tracking-tight text-[#b91c1c]">
                                    Key Blind Spot
                                </h4>
                                <p class="mt-1 text-[11px] sm:text-xs text-[#4b5585] leading-relaxed">
                                    {{ $insights['matrix_blind_spot'] }}
                                </p>
                            </div>
                        </div>

                        <!-- 3. Opportunity -->
                        <div class="flex items-start gap-3 sm:gap-3.5">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5 shadow-2xs" style="background-color: #d4fce6 !important;">
                                <svg class="w-4.5 h-4.5 text-[#047857]" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 3.5l-6 6h3.5v9h5v-9H18l-6-6z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-black text-xs sm:text-[13.5px] leading-tight tracking-tight text-[#047857]">
                                    Opportunity
                                </h4>
                                <p class="mt-1 text-[11px] sm:text-xs text-[#4b5585] leading-relaxed">
                                    {{ $insights['matrix_opportunity'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Path to Opportunity Zone Trajectory Card (Matching Image) -->
                <x-path-opportunity-zone :cq-score="$cqScore" :sync-score="$syncScore" :benchmark-cq="$benchmarkCQ" :benchmark-sync="$benchmarkSync" :insights="$insights" />
            </div>

            <!-- Section: 30 – 60 – 90 Day Plan (Full Width Matching Reference Template) -->
            <x-plan-30-60-90 :insights="$insights" />

            <!-- Bottom Row: Strategic Recommendations & Expected Outcomes (2 Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                <!-- 1. Strategic Recommendations -->
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-4 flex flex-col justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center shrink-0" style="background-color: #f4f2fd !important;">
                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-[#6f01d2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <h3 class="text-sm sm:text-base font-black text-[#0a0f37] tracking-tight">Strategic Recommendations</h3>
                    </div>
                    <div class="space-y-3 sm:space-y-3.5 flex-1 flex flex-col justify-around py-0.5">
                        @foreach($insights['strategic_recommendations'] ?? [] as $rec)
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full text-white font-black text-[11px] flex items-center justify-center shrink-0 mt-0.5 shadow-2xs" style="background-color: #6f01d2 !important;">
                                    {{ $rec['number'] }}
                                </span>
                                <div>
                                    <h4 class="text-xs sm:text-[12.5px] font-bold text-[#0a0f37] leading-snug">{{ $rec['title'] }}</h4>
                                    <p class="text-[10.5px] sm:text-[11.5px] text-[#4b5585] leading-relaxed mt-0.5">{{ $rec['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Expected Outcomes (Matching Reference Design) -->
                <x-expected-outcomes :outcomes="$insights['expected_outcomes'] ?? []" />
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 2: TEAM CQ SYNC DEEP DIVE REPORT (MATCHING IMAGE 3)        -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'sync'" class="space-y-6 sm:space-y-8" x-transition>
            <!-- Top 4 Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs">
                    <span class="text-xs font-bold text-slate-400">Team Size</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $insights['cohort_size'] }}</div>
                    <span class="text-[11px] text-slate-400">Members assessed</span>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs">
                    <span class="text-xs font-bold text-slate-400">Overall CQ Sync Score</span>
                    <div class="text-3xl font-black text-indigo-900 mt-1">{{ number_format($syncScore, 1) }} <span class="text-sm text-slate-400">/ 10</span></div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full {{ $sync['badge'] ?? 'bg-emerald-50 text-emerald-800' }} mt-1">
                        {{ $sync['maturity_level'] ?? 'Aligned' }}
                    </span>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs">
                    <span class="text-xs font-bold text-slate-400">Expected / Benchmark</span>
                    <div class="text-3xl font-black text-emerald-700 mt-1">8.0 <span class="text-sm text-slate-400">/ 10</span></div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 mt-1">
                        Unified Target
                    </span>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs">
                    <span class="text-xs font-bold text-slate-400">Gap to Benchmark</span>
                    <div class="text-3xl font-black text-rose-600 mt-1">{{ $gapSync }}</div>
                    <span class="text-[11px] text-slate-400">Team needs stronger synchronisation</span>
                </div>
            </div>

            <!-- Middle Row: Maturity Gauge, Growth Journey, 3 Sync Dimensions (Image 3) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Maturity Gauge (4 cols) -->
                <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col items-center justify-between">
                    <div class="text-center w-full">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Team CQ Sync Maturity Level</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Where does team stand on alignment to see, agree and act together?</p>
                    </div>

                    <div class="w-full max-w-[260px] aspect-[300/165] relative flex items-end justify-center my-4"
                         x-data="{ mounted: false }"
                         x-init="setTimeout(() => mounted = true, 50)">
                        <svg viewBox="0 0 300 165" class="w-full h-full overflow-visible select-none">
                            <path d="M 15,150 A 135,135 0 0,1 42.64,70.64 L 81.33,100.16 A 85,85 0 0,0 65,150 Z" fill="#EF4444" stroke="#FFFFFF" stroke-width="2"/>
                            <path d="M 42.64,70.64 A 135,135 0 0,1 110.15,21.61 L 123.72,69.16 A 85,85 0 0,0 81.33,100.16 Z" fill="#FB923C" stroke="#FFFFFF" stroke-width="2"/>
                            <path d="M 110.15,21.61 A 135,135 0 0,1 189.85,21.61 L 176.28,69.16 A 85,85 0 0,0 123.72,69.16 Z" fill="#34D399" stroke="#FFFFFF" stroke-width="2"/>
                            <path d="M 189.85,21.61 A 135,135 0 0,1 257.36,70.64 L 218.67,100.16 A 85,85 0 0,0 176.28,69.16 Z" fill="#FBBF24" stroke="#FFFFFF" stroke-width="2"/>
                            <path d="M 257.36,70.64 A 135,135 0 0,1 285,150 L 235,150 A 85,85 0 0,0 218.67,100.16 Z" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2"/>

                            <g transform="translate(150, 150)">
                                <g :style="`transform: rotate(${mounted ? {{ $syncNeedleAngle }} : -90}deg); transition: transform 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);`">
                                    <polygon points="-3.5,0 0,-104 3.5,0" fill="#1E293B"/>
                                    <circle cx="0" cy="-104" r="2.5" fill="#10B981"/>
                                    <circle cx="0" cy="0" r="7" fill="#1E293B"/>
                                    <circle cx="0" cy="0" r="2.5" fill="#FFFFFF"/>
                                </g>
                            </g>

                            <circle cx="150" cy="142" r="42" fill="#FFFFFF" fill-opacity="0.95"/>
                            <text x="150" y="126" text-anchor="middle" font-size="8" font-weight="800" fill="#64748B" letter-spacing="0.05em">TEAM CQ SYNC</text>
                            <text x="150" y="148" text-anchor="middle" font-size="22" font-weight="900" fill="#0F172A">{{ number_format($syncScore, 1) }}</text>
                        </svg>
                    </div>

                    <span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-full {{ $sync['badge'] ?? 'bg-emerald-50 text-emerald-800' }}">
                        {{ $sync['maturity_level'] ?? 'Aligned' }}
                    </span>
                </div>

                <!-- 3 Sync Dimensions Horizontal Bars (8 cols) -->
                <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-6">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                            Team View across the 3 CQ Sync Dimensions
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">Average team rating (out of 10) for each group synchronization dimension</p>
                    </div>

                    <div class="space-y-5 pt-2">
                        @foreach($sync['dimensions'] ?? [] as $key => $dim)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-slate-900">{{ $dim['name'] }}</span>
                                        <span class="text-slate-400 font-normal">({{ $dim['description'] }})</span>
                                    </div>
                                    <div class="font-black text-slate-800">
                                        {{ number_format($dim['score'], 1) }} <span class="text-slate-400 font-normal text-[11px]">/ 10</span>
                                    </div>
                                </div>

                                <div class="relative w-full bg-slate-100 rounded-full h-4 overflow-hidden">
                                    <!-- Progress fill -->
                                    <div class="h-4 rounded-full transition-all duration-700"
                                         style="width: {{ $dim['percentage'] }}%; background-color: {{ $dim['color'] }};"></div>
                                    
                                    <!-- 8.0 Target Benchmark Marker Line -->
                                    <div class="absolute top-0 bottom-0 w-0.5 bg-slate-900 z-10 opacity-70"
                                         style="left: 80%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-3 border-t border-slate-100">
                        <span class="flex items-center gap-1.5 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Team Score (Average)
                        </span>
                        <span class="flex items-center gap-1.5 font-bold">
                            <span class="w-2 h-2 rounded-full bg-slate-900"></span> Benchmark (8.0 Target)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Key Insights & Recommendations (Image 3) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Key Insights -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="lightbulb" class="w-5 h-5 text-amber-500"></i>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">Key Insights</h4>
                    </div>

                    <div class="space-y-2.5 text-xs text-slate-700">
                        @foreach($sync['key_insights'] ?? [] as $idx => $insight)
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-black text-[11px] flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $idx + 1 }}
                                </span>
                                <span class="leading-relaxed">{{ $insight }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Recommendations -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="target" class="w-5 h-5 text-indigo-600"></i>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">Top Recommendations</h4>
                    </div>

                    <div class="space-y-3">
                        @foreach($sync['recommendations'] ?? [] as $rec)
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-black text-xs flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $rec['number'] }}
                                </span>
                                <div>
                                    <h5 class="text-xs font-bold text-slate-900">{{ $rec['title'] }}</h5>
                                    <p class="text-[11px] text-slate-500 leading-relaxed">{{ $rec['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 3: CAPABILITY & DISTRIBUTION REPORT (MATCHING IMAGE 4)     -->
        <!-- ============================================================= -->
        <div x-show="activeTab === 'capability'" class="space-y-6 sm:space-y-8" x-transition>
            <!-- Top 5 Metric Cards (Image 4) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400">Team Size</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ $insights['cohort_size'] }}</div>
                    <span class="text-[10px] text-slate-400">Employees assessed</span>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400">Average (Mean)</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['mean'] ?? $cqScore, 1) }} <span class="text-xs text-slate-400">/ 10</span></div>
                    <span class="text-[10px] font-bold text-indigo-700">{{ $insights['group_display_name'] ?? 'Supporter' }}</span>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400">Median CQ Score</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['median'] ?? $cqScore, 1) }} <span class="text-xs text-slate-400">/ 10</span></div>
                    <span class="text-[10px] font-bold text-indigo-700">Supporter</span>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400">Benchmark Target</span>
                    <div class="text-2xl font-black text-emerald-700 mt-1">8.0 <span class="text-xs text-slate-400">/ 10</span></div>
                    <span class="text-[10px] font-bold text-emerald-700">Champion Level</span>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400">Gap to Benchmark</span>
                    <div class="text-2xl font-black text-rose-600 mt-1">{{ $gapCQ }}</div>
                    <span class="text-[10px] text-slate-400">Needs strengthening</span>
                </div>
            </div>

            <!-- Middle Row: Distribution Histogram & Key Statistics (Image 4) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Histogram Spread across 5 Archetypes (7 cols) -->
                <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900">Team CQ Distribution</h3>
                            <p class="text-xs text-slate-400">Spread of normalised CQ scores across {{ $insights['cohort_size'] }} employees</p>
                        </div>
                        <div class="text-xs text-slate-500 font-bold">
                            Mean: {{ number_format($stats['mean'] ?? $cqScore, 1) }} • Median: {{ number_format($stats['median'] ?? $cqScore, 1) }}
                        </div>
                    </div>

                    <!-- Histogram Bars -->
                    <div class="pt-6 space-y-4">
                        <div class="grid grid-cols-5 gap-2 h-44 items-end border-b border-slate-200 pb-2">
                            @foreach($dist as $d)
                                <div class="flex flex-col items-center gap-1 h-full justify-end">
                                    <span class="text-[11px] font-extrabold text-slate-700">{{ $d['count'] }}</span>
                                    <div class="w-full rounded-t-xl transition-all duration-700"
                                         style="height: {{ max(10, min(100, $d['percentage'])) }}%; background-color: {{ $d['color'] }}; opacity: 0.85;"></div>
                                </div>
                            @endforeach
                        </div>

                        <!-- 5 Archetype Range Labels -->
                        <div class="grid grid-cols-5 gap-2 text-center text-xs">
                            @foreach($dist as $d)
                                <div class="p-2 rounded-xl border border-slate-100 bg-slate-50/50">
                                    <span class="block font-black text-slate-900 text-xs">{{ $d['name'] }}</span>
                                    <span class="block text-[10px] text-slate-400">{{ $d['range'] }}</span>
                                    <span class="block text-[11px] font-extrabold text-slate-700 mt-1">{{ $d['percentage'] }}% ({{ $d['count'] }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Key Statistics Table (5 cols) -->
                <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                    <h3 class="text-base font-black text-slate-900">Key Statistics</h3>
                    
                    <div class="divide-y divide-slate-100 text-xs">
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Mean (Average)</span>
                            <span class="font-black text-slate-900">{{ number_format($stats['mean'] ?? 0, 1) }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Median</span>
                            <span class="font-black text-slate-900">{{ number_format($stats['median'] ?? 0, 1) }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Standard Deviation</span>
                            <span class="font-black text-slate-900">{{ number_format($stats['std_dev'] ?? 0, 1) }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Highest Score</span>
                            <span class="font-black text-emerald-700">{{ number_format($stats['highest'] ?? 0, 1) }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Lowest Score</span>
                            <span class="font-black text-rose-700">{{ number_format($stats['lowest'] ?? 0, 1) }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Team Benchmark (Target)</span>
                            <span class="font-black text-emerald-700">8.0</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between">
                            <span class="text-slate-500">Gap to Benchmark</span>
                            <span class="font-black text-rose-600">{{ $gapCQ }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Team Strengths & Areas of Concern (Image 4) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Team Strengths -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="thumbs-up" class="w-5 h-5 text-emerald-600"></i>
                        <h4 class="text-sm font-black text-emerald-950 uppercase tracking-wider">Team Strengths</h4>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-700">
                        @foreach($insights['team_strengths'] ?? [] as $str)
                            <li class="flex items-start gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                <span>{{ $str }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Areas of Concern -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600"></i>
                        <h4 class="text-sm font-black text-rose-950 uppercase tracking-wider">Areas of Concern</h4>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-700">
                        @foreach($insights['areas_of_concern'] ?? [] as $concern)
                            <li class="flex items-start gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                <span>{{ $concern }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- LEADERSHIP SIGN-OFF STATUS (READ-ONLY FOR PARTICIPANTS)       -->
        <!-- ============================================================= -->
        @php
            $signOff = $insights['sign_off'] ?? [];
            $status = $signOff['status'] ?? 'pending';
        @endphp
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        Action Plan Governance
                    </span>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 mt-1">Leadership Enablement Sign-Off</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Executive sponsorship and validation status for the team roadmap.</p>
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                    {{ match($status) {
                        'approved' => 'bg-emerald-50 text-emerald-800 border border-emerald-200',
                        'needs_review' => 'bg-amber-50 text-amber-800 border border-amber-200',
                        default => 'bg-slate-100 text-slate-700 border border-slate-200'
                    } }}">
                    <i data-lucide="{{ $status === 'approved' ? 'check-circle-2' : 'clock' }}" class="w-3.5 h-3.5"></i>
                    <span>Status: {{ ucwords(str_replace('_', ' ', $status)) }}</span>
                </span>
            </div>

            @if($status === 'approved')
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-950 flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="font-black text-sm">Action Plan Formally Approved</h4>
                        <p class="mt-0.5">Signed off by <strong>{{ $signOff['lead'] }}</strong> on {{ $signOff['signed_off_at'] ? \Carbon\Carbon::parse($signOff['signed_off_at'])->format('d M Y, h:i A') : 'N/A' }}.</p>
                        @if(!empty($signOff['notes']))
                            <p class="mt-2 text-slate-700 bg-white/80 p-3 rounded-xl border border-emerald-200/60 font-mono text-[11px]">{{ $signOff['notes'] }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                    <span>The leadership action plan is currently being finalized by team leads.</span>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
