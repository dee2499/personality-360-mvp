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

    <div class="space-y-6 sm:space-y-8" x-data="{ activeTab: '{{ request()->query('tab', 'summary') }}' }">
        <!-- Top Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('admin.surveys.show', $survey) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Survey Overview</span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.surveys.team-sync', $survey) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-purple-900 bg-purple-50 hover:bg-purple-100 border border-purple-200 shadow-2xs transition">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600"></i>
                    <span>Team CQ Sync Executive Report</span>
                </a>

                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Confidential • Zero Names Exposed</span>
                </span>
                
                <a href="#sign-off-panel" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-2xs transition">
                    <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                    <span>Leadership Sign-Off</span>
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
            <!-- Top 5 Metric Cards (Matching Theme Template) -->
            <x-team-insights-metric-cards :insights="$insights" />

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
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 mt-1">
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
                <x-team-sync-maturity-gauge :score="$syncScore" :maturity-level="$sync['maturity_level'] ?? null" class="lg:col-span-4" />

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
            <!-- Top 5 Metric Cards (Matching Theme Template) -->
            <x-team-insights-metric-cards :insights="$insights" />

            <!-- Middle Row: Distribution Histogram & Team Maturity Level Meter (Matching Template Design) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                <!-- Team CQ Distribution (Matching Theme Template) -->
                <x-team-cq-distribution :insights="$insights" class="lg:col-span-7 flex flex-col justify-between" />

                <!-- Team Maturity Level Gauge (Matching Theme Template Image) -->
                <x-team-maturity-level-gauge :insights="$insights" class="lg:col-span-5 flex flex-col justify-between" />
            </div>

            <!-- Key Statistics Horizontal Strip -->
            <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">Capability Key Statistics</h4>
                    <span class="text-[11px] text-slate-400 font-medium">Cohort size: {{ $insights['cohort_size'] }} employees</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Mean (Average)</span>
                        <span class="text-slate-900 font-black text-base">{{ number_format($stats['mean'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Median</span>
                        <span class="text-slate-900 font-black text-base">{{ number_format($stats['median'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Std. Dev.</span>
                        <span class="text-slate-900 font-black text-base">{{ number_format($stats['std_dev'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Highest</span>
                        <span class="text-emerald-700 font-black text-base">{{ number_format($stats['highest'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Lowest</span>
                        <span class="text-rose-700 font-black text-base">{{ number_format($stats['lowest'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Benchmark</span>
                        <span class="text-emerald-700 font-black text-base">8.0</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Gap</span>
                        <span class="text-rose-600 font-black text-base">{{ $gapCQ }}</span>
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
        <!-- LEADERSHIP SIGN-OFF PANEL (FOR ADMINS)                        -->
        <!-- ============================================================= -->
        <div id="sign-off-panel" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        Governance Protocol
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">Leadership Action Plan Sign-Off</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Formal executive validation of the 30-60-90 day team change enablement roadmap.
                    </p>
                </div>

                @php
                    $signOff = $insights['sign_off'] ?? [];
                    $status = $signOff['status'] ?? 'pending';
                @endphp
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
            @endif

            <form method="POST" action="{{ route('admin.surveys.sign-off', $survey) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Executive Sponsor / Leadership Lead</label>
                        <input type="text" name="sign_off_lead" 
                               value="{{ old('sign_off_lead', $signOff['lead'] ?? Auth::user()->name) }}" 
                               required
                               placeholder="e.g. Sarah Jenkins (Head of Product)"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Approval Decision</label>
                        <select name="sign_off_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                            <option value="approved" {{ old('sign_off_status', $status) === 'approved' ? 'selected' : '' }}>Approved (Authorize 30-60-90 Day Sprints)</option>
                            <option value="needs_review" {{ old('sign_off_status', $status) === 'needs_review' ? 'selected' : '' }}>Needs Review (Revise Priorities)</option>
                            <option value="pending" {{ old('sign_off_status', $status) === 'pending' ? 'selected' : '' }}>Pending Leadership Review</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1 text-xs">Executive Notes & Guidance (Optional)</label>
                    <textarea name="sign_off_notes" rows="3" 
                              placeholder="Record strategic commentary or resource allocations for the enablement plan..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">{{ old('sign_off_notes', $signOff['notes'] ?? '') }}</textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition cursor-pointer">
                        Record Leadership Sign-Off
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
