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
                <a href="{{ route('admin.surveys.export-pdf', ['survey' => $survey, 'auto_print' => 1]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-2xs transition">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Download Report PDF</span>
                </a>

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
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-2xs transition">
                    <i data-lucide="check-square" class="w-3.5 h-3.5 text-indigo-600"></i>
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
                    <span>2. Team Report (3 Dimensions)</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'capability'"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer shrink-0"
                        :class="activeTab === 'capability' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>3. ChangeQuo & Score Distribution</span>
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

            <!-- Key Statistics Horizontal Strip (Matching Template Design) -->
            <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
                    <h4 class="text-sm font-black text-[#0a0f37] tracking-tight">Key Statistics</h4>
                    <span class="text-[11px] text-slate-400 font-medium">Cohort size: {{ $insights['cohort_size'] }} employees</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 sm:gap-4 text-center">
                    <!-- 1. Mean (Average) -->
                    <div class="p-3 rounded-2xl border transition-colors flex flex-col justify-between"
                         style="background-color: #edf5fe !important; border-color: #dbeafe !important;">
                        <span class="text-slate-600 block text-[11px] font-bold">Mean (Average)</span>
                        <span class="text-[#0a0f37] font-black text-lg sm:text-xl mt-1">{{ number_format($stats['mean'] ?? 0, 1) }}</span>
                    </div>

                    <!-- 2. Median -->
                    <div class="p-3 rounded-2xl border transition-colors flex flex-col justify-between"
                         style="background-color: #f8fafc !important; border-color: #f1f5f9 !important;">
                        <span class="text-slate-600 block text-[11px] font-bold">Median</span>
                        <span class="text-[#0a0f37] font-black text-lg sm:text-xl mt-1">{{ number_format($stats['median'] ?? 0, 1) }}</span>
                    </div>

                    <!-- 3. Standard Deviation -->
                    <div class="p-3 rounded-2xl border transition-colors flex flex-col justify-between"
                         style="background-color: #f8fafc !important; border-color: #f1f5f9 !important;">
                        <span class="text-slate-600 block text-[11px] font-bold leading-tight">Standard Deviation</span>
                        <span class="text-[#0a0f37] font-black text-lg sm:text-xl mt-1">{{ number_format($stats['std_dev'] ?? 0, 1) }}</span>
                    </div>

                    <!-- 4. Highest Score -->
                    <div class="p-3 rounded-2xl border transition-colors flex flex-col justify-between"
                         style="background-color: #f8fafc !important; border-color: #f1f5f9 !important;">
                        <span class="text-slate-600 block text-[11px] font-bold">Highest Score</span>
                        <span class="text-[#0a0f37] font-black text-lg sm:text-xl mt-1">{{ number_format($stats['highest'] ?? 0, 1) }}</span>
                    </div>

                    <!-- 5. Lowest Score -->
                    <div class="p-3 rounded-2xl border transition-colors flex flex-col justify-between"
                         style="background-color: #f8fafc !important; border-color: #f1f5f9 !important;">
                        <span class="text-slate-600 block text-[11px] font-bold">Lowest Score</span>
                        <span class="text-[#0a0f37] font-black text-lg sm:text-xl mt-1">{{ number_format($stats['lowest'] ?? 0, 1) }}</span>
                    </div>

                    <!-- 6. Team Benchmark (Target) -->
                    <div class="p-3 rounded-2xl border transition-colors flex flex-col justify-between"
                         style="background-color: #ecfbf3 !important; border-color: #d1fae5 !important;">
                        <span class="text-slate-600 block text-[11px] font-bold leading-tight">Team Benchmark (Target)</span>
                        <span class="text-[#15803d] font-black text-lg sm:text-xl mt-1">8.0</span>
                    </div>

                    <!-- 7. Gap to Benchmark -->
                    <div class="p-3 rounded-2xl border transition-colors flex flex-col justify-between"
                         style="background-color: #feeff1 !important; border-color: #fecdd3 !important;">
                        <span class="text-slate-600 block text-[11px] font-bold leading-tight">Gap to Benchmark</span>
                        <span class="text-[#dc2626] font-black text-lg sm:text-xl mt-1">{{ $gapCQ }}</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Team Strengths, Areas of Concern & Top Recommendations (Matching Theme Template) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                <!-- 1. Team Strengths Card -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-2xs"
                             style="background-color: #e3f8ea !important;">
                            <svg class="w-5 h-5 text-[#029656]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-[#0a0f37] tracking-tight">Team Strengths</h3>
                    </div>

                    <ul class="space-y-3.5 flex-1 flex flex-col justify-around py-1">
                        @foreach($insights['team_strengths'] ?? [] as $str)
                            <li class="flex items-start gap-2.5">
                                <div class="w-4.5 h-4.5 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                     style="background-color: #029656 !important;">
                                    <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </div>
                                <span class="text-xs sm:text-[13px] text-[#334155] font-medium leading-relaxed">{{ $str }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- 2. Areas of Concern Card -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-2xs"
                             style="background-color: #fddcdf !important;">
                            <svg class="w-5 h-5 text-[#e11d48]" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="3" y="14" width="4" height="7" rx="1"/>
                                <rect x="10" y="8" width="4" height="13" rx="1"/>
                                <rect x="17" y="3" width="4" height="18" rx="1"/>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-[#0a0f37] tracking-tight">Areas of Concern</h3>
                    </div>

                    <ul class="space-y-3.5 flex-1 flex flex-col justify-around py-1">
                        @foreach($insights['areas_of_concern'] ?? [] as $concern)
                            <li class="flex items-start gap-2.5">
                                <div class="w-4.5 h-4.5 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                     style="background-color: #e11d48 !important;">
                                    <span class="text-white font-black text-[11px] leading-none">!</span>
                                </div>
                                <span class="text-xs sm:text-[13px] text-[#334155] font-medium leading-relaxed">{{ $concern }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- 3. Top Recommendations Card -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 shadow-2xs"
                             style="background-color: #f3e8ff !important;">
                            <svg class="w-5 h-5 text-[#7616c1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <circle cx="12" cy="12" r="6"/>
                                <circle cx="12" cy="12" r="2"/>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-[#0a0f37] tracking-tight">Top Recommendations</h3>
                    </div>

                    <div class="space-y-3.5 flex-1 flex flex-col justify-around py-1">
                        @php
                            $recBadges = [
                                1 => ['bg' => '#3b82f6', 'text' => '#ffffff'], // Blue
                                2 => ['bg' => '#f97316', 'text' => '#ffffff'], // Orange
                                3 => ['bg' => '#10b981', 'text' => '#ffffff'], // Green
                            ];
                        @endphp
                        @foreach($insights['top_recommendations'] ?? $insights['strategic_recommendations'] ?? [] as $rec)
                            @php
                                $badge = $recBadges[$rec['number'] ?? 1] ?? ['bg' => '#3b82f6', 'text' => '#ffffff'];
                            @endphp
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 font-black text-xs shadow-2xs mt-0.5"
                                     style="background-color: {{ $badge['bg'] }} !important; color: {{ $badge['text'] }} !important;">
                                    {{ $rec['number'] }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs sm:text-[13px] font-black text-[#0a0f37] leading-snug">
                                        {{ $rec['title'] }}
                                    </h4>
                                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium leading-relaxed mt-0.5">
                                        {{ $rec['description'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
                        Formal executive validation of the 30-60-90 day team change enablement roadmap. Add multiple status reviews & leadership notes.
                    </p>
                </div>

                @php
                    $signOffs = $insights['sign_offs'] ?? [];
                    $latestSignOff = !empty($signOffs) ? $signOffs[0] : ($insights['sign_off'] ?? []);
                    $latestStatus = $latestSignOff['status'] ?? 'pending';
                    $hasApproved = !empty($insights['has_approved_sign_off']);
                @endphp
                <div class="flex items-center gap-2">
                    @if($hasApproved)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Approved for Team</span>
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                        {{ match($latestStatus) {
                            'approved' => 'bg-emerald-50 text-emerald-800 border border-emerald-200',
                            'needs_review' => 'bg-amber-50 text-amber-800 border border-amber-200',
                            default => 'bg-slate-100 text-slate-700 border border-slate-200'
                        } }}">
                        <i data-lucide="{{ $latestStatus === 'approved' ? 'check-circle-2' : 'clock' }}" class="w-3.5 h-3.5"></i>
                        <span>Latest: {{ ucwords(str_replace('_', ' ', $latestStatus)) }}</span>
                    </span>
                </div>
            </div>

            <!-- Sign-Off History List (Multiple Notes & Statuses) -->
            @if(!empty($signOffs))
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500">Sign-Off & Governance History ({{ count($signOffs) }})</h4>
                    <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                        @foreach($signOffs as $item)
                            <div x-data="{ editing: false }" class="p-4 rounded-2xl border text-xs flex flex-col gap-2.5 transition
                                {{ $item['status'] === 'approved' 
                                    ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950' 
                                    : ($item['status'] === 'needs_review' 
                                        ? 'bg-amber-50/40 border-amber-200 text-amber-950' 
                                        : 'bg-slate-50 border-slate-200 text-slate-800') }}">
                                
                                <div x-show="!editing" class="space-y-2.5">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="{{ $item['status'] === 'approved' ? 'check-circle' : ($item['status'] === 'needs_review' ? 'alert-circle' : 'clock') }}" 
                                               class="w-4 h-4 {{ $item['status'] === 'approved' ? 'text-emerald-600' : ($item['status'] === 'needs_review' ? 'text-amber-600' : 'text-slate-500') }} shrink-0"></i>
                                            <span class="font-extrabold text-slate-900">{{ $item['lead'] }}</span>
                                            @if(!empty($item['user_name']) && $item['user_name'] !== $item['lead'])
                                                <span class="text-[10px] text-slate-400">by {{ $item['user_name'] }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize
                                                {{ match($item['status']) {
                                                    'approved' => 'bg-emerald-100 text-emerald-800',
                                                    'needs_review' => 'bg-amber-100 text-amber-800',
                                                    default => 'bg-slate-200 text-slate-700'
                                                } }}">
                                                {{ ucwords(str_replace('_', ' ', $item['status'])) }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 font-medium">
                                                @if(!empty($item['signed_off_at']))
                                                    {{ is_string($item['signed_off_at']) ? $item['signed_off_at'] : ($item['signed_off_at'] instanceof \DateTimeInterface ? $item['signed_off_at']->format('d M Y, h:i A') : 'N/A') }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>

                                            <div class="flex items-center gap-1 ml-2">
                                                <button type="button" @click="editing = true; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })" 
                                                        title="Edit this sign-off"
                                                        class="p-1 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-white/80 transition cursor-pointer">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form method="POST" action="{{ route('admin.surveys.sign-off.destroy', [$survey, $item['id']]) }}" 
                                                      onsubmit="return confirm('Are you sure you want to delete this leadership sign-off entry?');" 
                                                      class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            title="Delete this sign-off"
                                                            class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!empty($item['notes']))
                                        <div class="bg-white/90 p-3 rounded-xl border border-slate-200/80 text-[11px] text-slate-700 whitespace-pre-wrap leading-relaxed shadow-2xs">
                                            {{ $item['notes'] }}
                                        </div>
                                    @endif
                                </div>

                                <div x-show="editing" x-cloak class="pt-1">
                                    <form method="POST" action="{{ route('admin.surveys.sign-off.update', [$survey, $item['id']]) }}" class="space-y-3 bg-white p-4 rounded-xl border border-indigo-200 shadow-sm">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                                            <span class="font-bold text-xs text-indigo-900">Edit Sign-Off Note</span>
                                            <button type="button" @click="editing = false" class="text-slate-400 hover:text-slate-600 text-[11px] font-bold cursor-pointer">Cancel</button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Executive Sponsor / Lead</label>
                                                <input type="text" name="sign_off_lead" value="{{ $item['lead'] }}" required
                                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                                            </div>
                                            <div>
                                                <label class="block font-bold text-slate-700 mb-1">Status</label>
                                                <select name="sign_off_status" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                                                    <option value="approved" {{ $item['status'] === 'approved' ? 'selected' : '' }}>Approved (Authorize Sprints)</option>
                                                    <option value="needs_review" {{ $item['status'] === 'needs_review' ? 'selected' : '' }}>Needs Review (Revise Priorities)</option>
                                                    <option value="pending" {{ $item['status'] === 'pending' ? 'selected' : '' }}>Pending Leadership Review</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-700 mb-1 text-xs">Executive Notes & Guidance</label>
                                            <textarea name="sign_off_notes" rows="3" required
                                                      class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">{{ $item['notes'] }}</textarea>
                                        </div>
                                        <div class="flex items-center justify-end gap-2 pt-1">
                                            <button type="button" @click="editing = false" 
                                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                                                Cancel
                                            </button>
                                            <button type="submit" 
                                                    class="px-4 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition cursor-pointer">
                                                Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Add New Sign-Off / Status Entry Form -->
            <form method="POST" action="{{ route('admin.surveys.sign-off', $survey) }}" class="space-y-4 pt-2 border-t border-slate-100">
                @csrf
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Add Sign-Off / Review Note</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Executive Sponsor / Leadership Lead</label>
                        <input type="text" name="sign_off_lead" 
                               value="{{ old('sign_off_lead', Auth::user()->name) }}" 
                               required
                               placeholder="e.g. Sarah Jenkins (Head of Product)"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Decision / Status</label>
                        <select name="sign_off_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                            <option value="approved">Approved (Authorize 30-60-90 Day Sprints)</option>
                            <option value="needs_review">Needs Review (Revise Priorities)</option>
                            <option value="pending">Pending Leadership Review</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1 text-xs">Executive Notes & Guidance</label>
                    <textarea name="sign_off_notes" rows="3" 
                              required
                              placeholder="Record strategic commentary, feedback, or resource allocations for this review milestone..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">{{ old('sign_off_notes') }}</textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition cursor-pointer">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Add Leadership Sign-Off Note</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
