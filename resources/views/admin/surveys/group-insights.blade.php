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

        // Speedometer needle angle for sync (-90deg at 1.0, 90deg at 10.0)
        $clampedSync = max(1.0, min(10.0, $syncScore > 0 ? $syncScore : 5.0));
        $syncNeedleAngle = -90 + (($clampedSync - 1.0) / 9.0) * 180;

        // Speedometer needle angle for team maturity
        $clampedCQ = max(1.0, min(10.0, $cqScore > 0 ? $cqScore : 5.0));
        $cqNeedleAngle = -90 + (($clampedCQ - 1.0) / 9.0) * 180;
    @endphp

    <div class="space-y-6 sm:space-y-8" x-data="{ activeTab: 'summary' }">
        <!-- Top Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('admin.surveys.show', $survey) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Survey Overview</span>
            </a>

            <div class="flex items-center gap-3">
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
        <div x-show="activeTab === 'summary'" class="space-y-6 sm:space-y-8" x-transition>
            <!-- 4 Top Cards (Image 2) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Team CQ (Group Score) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-wider text-indigo-600">Team CQ</span>
                            <span class="block text-xs font-bold text-slate-400">(Group Score)</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="my-3">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-slate-900">{{ number_format($cqScore, 1) }}</span>
                            <span class="text-sm font-bold text-slate-400">/ 10</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 mt-2">
                            {{ $insights['group_display_name'] ?? 'Change Supporter' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">Capability to navigate change</p>
                </div>

                <!-- 2. Team CQ Sync -->
                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-wider text-purple-600">Team CQ Sync</span>
                            <span class="block text-xs font-bold text-slate-400">(Alignment Index)</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="my-3">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-slate-900">{{ number_format($syncScore, 1) }}</span>
                            <span class="text-sm font-bold text-slate-400">/ 10</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 mt-2">
                            {{ $sync['maturity_level'] ?? 'Aligned' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">How well we see, agree and act together</p>
                </div>

                <!-- 3. Expected / Benchmark -->
                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-wider text-emerald-600">Expected / Benchmark</span>
                            <span class="block text-xs font-bold text-slate-400">Target Standard</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="target" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="my-3">
                        <div class="text-lg font-black text-slate-800">
                            CQ: <span class="text-slate-900">8.0</span> <span class="text-slate-300 mx-1">|</span> CQ Sync: <span class="text-slate-900">8.0</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 mt-2">
                            Change Champion & Unified
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">High capability with strong synchronisation</p>
                </div>

                <!-- 4. Gaps to Benchmark -->
                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-wider text-rose-600">Gaps to Benchmark</span>
                            <span class="block text-xs font-bold text-slate-400">Distance to Target</span>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i data-lucide="trending-down" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="my-3">
                        <div class="text-lg font-black text-rose-700">
                            CQ: <span class="text-rose-600">{{ $gapCQ }}</span> <span class="text-slate-300 mx-1">|</span> CQ Sync: <span class="text-rose-600">{{ $gapSync }}</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-800 border border-rose-200 mt-2">
                            Capability & Sync Deficits
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100">Need to strengthen capability & synchronisation</p>
                </div>
            </div>

            <!-- Middle Row: Team Position Matrix (Image 2) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- 2x2 Team Position Matrix (7 cols) -->
                <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900">Team Position Matrix</h3>
                            <p class="text-xs text-slate-400">CQ Group Score vs CQ Sync Score</p>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                            Benchmark (8.0, 8.0) ★
                        </span>
                    </div>

                    <!-- 2x2 Visual Grid -->
                    <div class="relative pt-2">
                        <!-- Y-Axis Label -->
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>Unified (8.1–10)</span>
                            <span class="text-slate-500 font-extrabold">CQ Sync Score (Higher = More Synchronised)</span>
                            <span>Divergent (1–2)</span>
                        </div>

                        <div class="relative aspect-[4/3] rounded-2xl border border-slate-300 overflow-hidden shadow-2xs">
                            <!-- 4 Quadrant Backgrounds -->
                            <div class="absolute inset-0 grid grid-cols-2 grid-rows-2">
                                <!-- Top-Left: Potential Zone -->
                                <div class="bg-blue-50/60 p-4 border-r border-b border-dashed border-slate-300 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-xs font-black text-blue-900 uppercase tracking-wider">POTENTIAL ZONE</h4>
                                        <span class="text-[10px] font-bold text-blue-700">(High Sync, Moderate CQ)</span>
                                    </div>
                                    <p class="text-[10px] text-blue-800 leading-tight">Strong alignment but capability needs to grow.</p>
                                </div>

                                <!-- Top-Right: Opportunity Zone -->
                                <div class="bg-emerald-50/70 p-4 border-b border-dashed border-slate-300 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-xs font-black text-emerald-900 uppercase tracking-wider">OPPORTUNITY ZONE</h4>
                                        <span class="text-[10px] font-bold text-emerald-700">(High CQ, High Sync)</span>
                                    </div>
                                    <p class="text-[10px] text-emerald-800 leading-tight">Ideal state: High capability and strong synchronisation. Drive bigger impact.</p>
                                </div>

                                <!-- Bottom-Left: Risk Zone -->
                                <div class="bg-rose-50/60 p-4 border-r border-dashed border-slate-300 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-xs font-black text-rose-900 uppercase tracking-wider">RISK ZONE</h4>
                                        <span class="text-[10px] font-bold text-rose-700">(Low CQ, Low Sync)</span>
                                    </div>
                                    <p class="text-[10px] text-rose-800 leading-tight">Both capability and alignment need significant attention.</p>
                                </div>

                                <!-- Bottom-Right: Capability Zone -->
                                <div class="bg-amber-50/60 p-4 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-xs font-black text-amber-900 uppercase tracking-wider">CAPABILITY ZONE</h4>
                                        <span class="text-[10px] font-bold text-amber-700">(High CQ, Low Sync)</span>
                                    </div>
                                    <p class="text-[10px] text-amber-800 leading-tight">Good capability but alignment gaps are limiting collective impact.</p>
                                </div>
                            </div>

                            <!-- Target Benchmark Marker (8.0, 8.0) -->
                            <div class="absolute z-20 pointer-events-none"
                                 style="left: {{ $bmXPct }}%; top: {{ $bmYPct }}%; transform: translate(-50%, -50%);">
                                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-md">
                                    ★
                                </div>
                            </div>

                            <!-- Current Position Marker (Image 2) -->
                            <div class="absolute z-30 pointer-events-none transition-all duration-700"
                                 style="left: {{ $xPct }}%; top: {{ $yPct }}%; transform: translate(-50%, -50%);">
                                <div class="w-6 h-6 rounded-full bg-indigo-900 text-white font-black text-[10px] flex items-center justify-center shadow-lg border-2 border-white">
                                    ●
                                </div>
                                <div class="bg-indigo-900 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-md whitespace-nowrap shadow-md mt-1 -translate-x-1/3">
                                    Current Position ({{ number_format($cqScore, 1) }}, {{ number_format($syncScore, 1) }})
                                </div>
                            </div>
                        </div>

                        <!-- X-Axis Label -->
                        <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                            <span>Resistant (1–2)</span>
                            <span class="text-slate-500 font-extrabold">CQ Group Score (Higher = Greater Capability)</span>
                            <span>Champion (8.1–10)</span>
                        </div>
                    </div>
                </div>

                <!-- Right Side: What This Means & Path to Opportunity Zone (5 cols) -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- What This Means Card -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                        <div class="flex items-center gap-2">
                            <i data-lucide="lightbulb" class="w-5 h-5 text-amber-500"></i>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">What This Means</h3>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-1">
                                <div class="font-extrabold text-amber-950 flex items-center gap-1.5">
                                    <i data-lucide="compass" class="w-3.5 h-3.5 text-amber-600"></i>
                                    <span>Current: {{ $insights['matrix_zone_name'] }} ({{ number_format($cqScore, 1) }}, {{ number_format($syncScore, 1) }})</span>
                                </div>
                                <p class="text-slate-700 leading-relaxed">{{ $insights['matrix_zone_subtitle'] }}</p>
                            </div>

                            <div class="p-3 rounded-2xl bg-rose-50/60 border border-rose-200/70 space-y-1">
                                <div class="font-extrabold text-rose-950 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                                    <span>Key Blind Spot</span>
                                </div>
                                <p class="text-slate-700 leading-relaxed">{{ $insights['matrix_blind_spot'] }}</p>
                            </div>

                            <div class="p-3 rounded-2xl bg-emerald-50/60 border border-emerald-200/70 space-y-1">
                                <div class="font-extrabold text-emerald-950 flex items-center gap-1.5">
                                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Opportunity</span>
                                </div>
                                <p class="text-slate-700 leading-relaxed">{{ $insights['matrix_opportunity'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Path to Opportunity Zone Trajectory Card -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="navigation" class="w-5 h-5 text-indigo-600"></i>
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Path to Opportunity Zone</h4>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-700">Target (8.0, 8.0)</span>
                        </div>

                        <div class="grid grid-cols-4 gap-2 pt-2 text-center text-xs">
                            @foreach(['Current' => [$cqScore, $syncScore], '30 Days' => [round($cqScore + 0.3, 1), round($syncScore + 0.6, 1)], '60 Days' => [round($cqScore + 0.8, 1), round($syncScore + 1.2, 1)], '90 Days' => [8.0, 8.0]] as $label => $pts)
                                <div class="p-2.5 rounded-xl border {{ $loop->last ? 'bg-emerald-50 border-emerald-300 font-extrabold text-emerald-900' : 'bg-slate-50 border-slate-200' }}">
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase">{{ $label }}</span>
                                    <span class="font-black text-xs mt-0.5 block">({{ $pts[0] }}, {{ $pts[1] }})</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 pt-1 leading-relaxed">
                            Focus on targeted actions to improve synchronisation while continuing to build capability.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Strategic Recommendations, 30-60-90 Day Plan, Expected Outcomes (Image 2) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. Strategic Recommendations -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-indigo-600"></i>
                        <h4 class="text-sm font-black text-slate-900">Strategic Recommendations</h4>
                    </div>
                    <div class="space-y-3">
                        @foreach($insights['strategic_recommendations'] ?? [] as $rec)
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-black text-xs flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $rec['number'] }}
                                </span>
                                <div>
                                    <h5 class="text-xs font-bold text-slate-900">{{ $rec['title'] }}</h5>
                                    <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">{{ $rec['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. 30 - 60 - 90 Day Plan -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-amber-500"></i>
                        <h4 class="text-sm font-black text-slate-900">30 – 60 – 90 Day Plan</h4>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-2xl bg-amber-50/60 border border-amber-200/80 space-y-1">
                            <span class="font-extrabold text-amber-950 block text-xs">First 30 Days: Align & Engage</span>
                            <ul class="text-[11px] text-slate-600 list-disc list-inside space-y-0.5">
                                <li>Conduct team alignment workshop (See, Agree, Act)</li>
                                <li>Clarify key change priorities and expected outcomes</li>
                            </ul>
                        </div>

                        <div class="p-3 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-1">
                            <span class="font-extrabold text-blue-950 block text-xs">Next 60 Days: Build & Act Together</span>
                            <ul class="text-[11px] text-slate-600 list-disc list-inside space-y-0.5">
                                <li>Run focused capability building sessions</li>
                                <li>Execute cross-functional commitments</li>
                            </ul>
                        </div>

                        <div class="p-3 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 space-y-1">
                            <span class="font-extrabold text-emerald-950 block text-xs">Next 90 Days: Scale & Institutionalise</span>
                            <ul class="text-[11px] text-slate-600 list-disc list-inside space-y-0.5">
                                <li>Review progress and measure improvements in CQ</li>
                                <li>Embed successful practices into standard rituals</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 3. Expected Outcomes -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="award" class="w-5 h-5 text-emerald-600"></i>
                        <h4 class="text-sm font-black text-slate-900">Expected Outcomes</h4>
                    </div>

                    <div class="space-y-3">
                        @foreach($insights['expected_outcomes'] ?? [] as $outcome)
                            <div class="flex items-start gap-3 p-2.5 rounded-2xl bg-slate-50 border border-slate-100">
                                <div class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs text-slate-700 font-semibold leading-snug mt-0.5">{{ $outcome['title'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
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
                <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col items-center justify-between">
                    <div class="text-center w-full">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Team CQ Sync Maturity Level</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">Where does team stand on alignment to see, agree and act together?</p>
                    </div>

                    <div class="w-full max-w-[260px] aspect-[2/1] relative flex items-end justify-center my-4">
                        <svg viewBox="0 0 300 170" class="w-full h-full overflow-visible">
                            <!-- 5 Gauge Arcs -->
                            <path d="M 30,150 A 120,120 0 0,1 67.08,52.92" fill="none" stroke="#EF4444" stroke-width="24" stroke-linecap="round"/>
                            <path d="M 68,52 A 120,120 0 0,1 118.89,31.7" fill="none" stroke="#F97316" stroke-width="24"/>
                            <path d="M 120,31.5 A 120,120 0 0,1 180,31.5" fill="none" stroke="#10B981" stroke-width="24"/>
                            <path d="M 181.11,31.7 A 120,120 0 0,1 232,52" fill="none" stroke="#F59E0B" stroke-width="24"/>
                            <path d="M 232.92,52.92 A 120,120 0 0,1 270,150" fill="none" stroke="#3B82F6" stroke-width="24" stroke-linecap="round"/>

                            <g transform="translate(150, 150) rotate({{ $syncNeedleAngle }})">
                                <path d="M -4,0 L -1,-110 L 0,-118 L 1,-110 L 4,0 Z" fill="#1E293B"/>
                                <circle cx="0" cy="0" r="7" fill="#0F172A"/>
                                <circle cx="0" cy="0" r="3" fill="#FFFFFF"/>
                            </g>

                            <text x="150" y="130" text-anchor="middle" font-size="8" font-weight="700" fill="#64748B">Team CQ Sync</text>
                            <text x="150" y="152" text-anchor="middle" font-size="24" font-weight="900" fill="#0F172A">{{ number_format($syncScore, 1) }}</text>
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
