<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $survey->title }} — Executive ChangeQuo & Sync Report PDF</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 10mm 12mm 10mm;
            }
            body {
                background-color: #ffffff !important;
                color: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always !important;
                break-after: page !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .print-border-none {
                border-color: #e2e8f0 !important;
            }
            .shadow-xs, .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl, .shadow-2xs {
                box-shadow: none !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-100 print:bg-white min-h-screen">
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
        $dimensions = $sync['dimensions'] ?? [];

        $seeScore = (float) ($dimensions['see_together']['score'] ?? 0.0);
        $agreeScore = (float) ($dimensions['agree_together']['score'] ?? 0.0);
        $actScore = (float) ($dimensions['act_together']['score'] ?? 0.0);

        // Next target calculation
        $nextTarget = $syncScore < 6.0 ? 6.5 : ($syncScore < 7.5 ? 7.8 : 8.5);

        // Growth Journey coordinates
        $scoreToY = fn($val) => round(195 - (($val / 10) * 160), 1);
        $scoreToX = fn($val) => round(50 + ((max(1.0, min(10.0, $val)) - 1.0) / 9.0) * 400, 1);

        $currentX = $scoreToX($syncScore);
        $currentY = $scoreToY($syncScore);
        $targetX = $scoreToX($nextTarget);
        $targetY = $scoreToY($nextTarget);
        $benchmarkX = $scoreToX(8.0);
        $benchmarkY = $scoreToY(8.0);

        $managerName = $company?->managers?->first()?->name 
            ?? $survey->company?->managers?->first()?->name 
            ?? $survey->creator?->name 
            ?? 'N/A';

        $signOffs = $insights['sign_offs'] ?? [];
        $latestStatus = $survey->sign_off_status ?? ($signOffs[0]['status'] ?? 'pending');
    @endphp

    <!-- Screen-Only Top Control Bar -->
    <div class="no-print sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 sm:px-8 py-3.5 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() ?: route('admin.surveys.group-insights', $survey) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back</span>
            </a>
            <div class="hidden sm:block">
                <span class="text-xs font-bold text-slate-800">Export Report PDF:</span>
                <span class="text-xs text-slate-500">{{ $survey->title }}</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="hidden md:inline-flex text-[11px] text-slate-500 font-medium">
                Tip: In print preview, select <strong>Save as PDF</strong> and check <strong>Background graphics</strong>.
            </span>
            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Save / Download PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Document Canvas -->
    <main class="max-w-5xl mx-auto py-6 sm:py-8 px-3 sm:px-6 print:p-0 print:max-w-none print:w-full space-y-8">
        
        <!-- ========================================================================= -->
        <!-- COVER / HEADER SECTION                                                    -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs print:border-none print:p-4 space-y-6 avoid-break">
            <!-- Brand & Metadata Header -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6 border-b border-slate-200">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('logo.png') }}" alt="ChangeQuo" class="w-9 h-9 object-contain">
                        <span class="text-2xl font-black tracking-tight text-indigo-950">
                            change<span class="text-indigo-600">quo</span>
                        </span>
                        <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">• Unlocking Possibilities</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Executive Team ChangeQuo & Sync Report
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl font-medium">
                        Comprehensive diagnostic on Team Capability × Synchronisation, 3-Dimension alignment, score distribution, and organizational growth path.
                    </p>
                </div>

                <!-- Right Metadata Panel -->
                <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50/80 p-4 rounded-2xl border border-slate-100 shrink-0">
                    <div><span class="text-slate-400 font-semibold">Survey / Cohort:</span> <strong class="text-slate-900">{{ $survey->title }}</strong></div>
                    @if($survey->company || $company)
                        <div><span class="text-slate-400 font-semibold">Organization:</span> <strong class="text-indigo-900">{{ $survey->company?->name ?? $company?->name }}</strong></div>
                    @endif
                    <div><span class="text-slate-400 font-semibold">Cohort Size:</span> <strong class="text-slate-900">{{ $insights['cohort_size'] }} Members</strong></div>
                    <div><span class="text-slate-400 font-semibold">Generated Date:</span> <strong class="text-slate-900">{{ now()->format('d M Y') }}</strong></div>
                    <div class="pt-1">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                            <i data-lucide="lock" class="w-3 h-3 text-slate-500"></i>
                            <span>Confidential • Zero Individual Names Exposed</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Top 5 Aggregate Metric Cards -->
            <div class="avoid-break">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3">1. Executive Score Summary</h3>
                <x-team-insights-metric-cards :insights="$insights" />
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 2: SYNCHRONIZATION MATURITY & GROWTH JOURNEY                      -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs print:border-none print:p-4 space-y-6 avoid-break">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">2. Team Synchronization Maturity & Growth Journey</h2>
                    <p class="text-xs text-slate-500 font-medium">Evaluation against the 8.0 Unified Benchmark</p>
                </div>
                <span class="text-xs font-black px-3 py-1 rounded-full bg-indigo-50 text-indigo-800 border border-indigo-200">
                    Sync Score: {{ number_format($syncScore, 1) }} / 10 ({{ $sync['maturity_level'] ?? 'Aligned' }})
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
                <!-- 5-Sector Gauge -->
                <x-team-sync-maturity-gauge :score="$syncScore" :maturity-level="$sync['maturity_level'] ?? null" />

                <!-- 3 Sync Dimensions Bars -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">Team View across the 3 CQ Sync Dimensions</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Average team score (out of 10) for each core question</p>
                    </div>

                    <div class="space-y-5 my-auto py-3">
                        <!-- See Together -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-extrabold text-slate-900">See Together (Q12)</span>
                                <span class="font-black text-purple-700">{{ number_format($seeScore, 1) }} / 10</span>
                            </div>
                            <div class="relative w-full bg-slate-100 rounded-full h-5 overflow-hidden">
                                <div class="h-full rounded-full flex items-center justify-end pr-2"
                                     style="width: {{ max(10, min(100, $seeScore * 10)) }}%; background-color: #8B5CF6;"></div>
                                <div class="absolute top-0 bottom-0 w-0.5 border-l-2 border-dashed border-slate-800 z-10" style="left: 80%;"></div>
                            </div>
                            <span class="text-[10px] text-slate-400">Common understanding of the key transformation</span>
                        </div>

                        <!-- Agree Together -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-extrabold text-slate-900">Agree Together (Q13)</span>
                                <span class="font-black text-sky-700">{{ number_format($agreeScore, 1) }} / 10</span>
                            </div>
                            <div class="relative w-full bg-slate-100 rounded-full h-5 overflow-hidden">
                                <div class="h-full rounded-full flex items-center justify-end pr-2"
                                     style="width: {{ max(10, min(100, $agreeScore * 10)) }}%; background-color: #38BDF8;"></div>
                                <div class="absolute top-0 bottom-0 w-0.5 border-l-2 border-dashed border-slate-800 z-10" style="left: 80%;"></div>
                            </div>
                            <span class="text-[10px] text-slate-400">Alignment on strategic direction and priorities</span>
                        </div>

                        <!-- Act Together -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-extrabold text-slate-900">Act Together (Q14)</span>
                                <span class="font-black text-emerald-700">{{ number_format($actScore, 1) }} / 10</span>
                            </div>
                            <div class="relative w-full bg-slate-100 rounded-full h-5 overflow-hidden">
                                <div class="h-full rounded-full flex items-center justify-end pr-2"
                                     style="width: {{ max(10, min(100, $actScore * 10)) }}%; background-color: #10B981;"></div>
                                <div class="absolute top-0 bottom-0 w-0.5 border-l-2 border-dashed border-slate-800 z-10" style="left: 80%;"></div>
                            </div>
                            <span class="text-[10px] text-slate-400">Commitment to execute and drive collective outcomes</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-3 border-t border-slate-100">
                        <span class="font-bold text-slate-700">Left: Team Score</span>
                        <span class="font-bold text-slate-900">Dashed Line: 8.0 Benchmark</span>
                    </div>
                </div>
            </div>

            <!-- S-Curve Growth Chart Area -->
            <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs avoid-break">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">Team CQ Sync Growth Journey</h3>
                        <p class="text-[11px] text-slate-500">Path from Divergent to the 8.0 Unified Benchmark</p>
                    </div>
                    <div class="text-xs font-bold text-slate-600">
                        Target Zone: <span class="text-emerald-700 font-extrabold">Unified (8.0+)</span>
                    </div>
                </div>

                <div class="w-full relative py-2">
                    <svg viewBox="0 0 500 230" class="w-full h-auto overflow-visible max-h-[260px]">
                        <defs>
                            <linearGradient id="pdfCurveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#EF4444" stop-opacity="0.15"/>
                                <stop offset="25%" stop-color="#FB923C" stop-opacity="0.2"/>
                                <stop offset="50%" stop-color="#10B981" stop-opacity="0.25"/>
                                <stop offset="75%" stop-color="#F59E0B" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#3B82F6" stop-opacity="0.35"/>
                            </linearGradient>

                            <linearGradient id="pdfLineGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#EF4444"/>
                                <stop offset="25%" stop-color="#FB923C"/>
                                <stop offset="50%" stop-color="#10B981"/>
                                <stop offset="75%" stop-color="#F59E0B"/>
                                <stop offset="100%" stop-color="#3B82F6"/>
                            </linearGradient>
                        </defs>

                        <!-- Background Grid Lines -->
                        <line x1="45" y1="35" x2="480" y2="35" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="45" y1="75" x2="480" y2="75" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="45" y1="115" x2="480" y2="115" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="45" y1="155" x2="480" y2="155" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="45" y1="195" x2="480" y2="195" stroke="#CBD5E1" stroke-width="1.5"/>

                        <text x="35" y="38" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">10</text>
                        <text x="35" y="78" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">8</text>
                        <text x="35" y="118" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">6</text>
                        <text x="35" y="158" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">4</text>
                        <text x="35" y="198" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">0</text>

                        <!-- S-Curve Background Area -->
                        <path d="M 50 195 C 150 195, 200 135, 260 100 C 330 60, 390 40, 460 35 L 460 195 Z" fill="url(#pdfCurveGradient)" />

                        <!-- S-Curve Stroke -->
                        <path d="M 50 195 C 150 195, 200 135, 260 100 C 330 60, 390 40, 460 35" fill="none" stroke="url(#pdfLineGrad)" stroke-width="4.5" stroke-linecap="round"/>

                        <!-- Current Position Pin -->
                        <circle cx="{{ $currentX }}" cy="{{ $currentY }}" r="9" fill="#1E1B4B" stroke="#FFFFFF" stroke-width="2.5" />
                        <circle cx="{{ $currentX }}" cy="{{ $currentY }}" r="3.5" fill="#38BDF8" />
                        <text x="{{ $currentX }}" y="{{ $currentY - 14 }}" text-anchor="middle" font-size="9" font-weight="900" fill="#1E1B4B">
                            Current: {{ number_format($syncScore, 1) }}
                        </text>

                        <!-- Benchmark Pin -->
                        <circle cx="{{ $benchmarkX }}" cy="{{ $benchmarkY }}" r="8" fill="#10B981" stroke="#FFFFFF" stroke-width="2.5" />
                        <text x="{{ $benchmarkX }}" y="{{ $benchmarkY - 12 }}" text-anchor="middle" font-size="9" font-weight="900" fill="#047857">
                            Benchmark (8.0)
                        </text>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Page break for printing -->
        <div class="page-break"></div>

        <!-- ========================================================================= -->
        <!-- SECTION 3: TEAM POSITION MATRIX (CAPABILITY × SYNCHRONISATION)             -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs print:border-none print:p-4 space-y-6 avoid-break">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">3. Team Position Matrix & Opportunity Zone</h2>
                    <p class="text-xs text-slate-500 font-medium">Diagnostic 2×2 mapping Capability against Synchronisation</p>
                </div>
                <div class="text-xs font-bold text-slate-600">
                    Location: <strong class="text-indigo-900">{{ $insights['matrix_zone_name'] }} ({{ number_format($cqScore, 1) }}, {{ number_format($syncScore, 1) }})</strong>
                </div>
            </div>

            <!-- Full 2x2 Matrix Graphic -->
            <div class="w-full avoid-break">
                <x-team-position-matrix :cq-score="$cqScore" :sync-score="$syncScore" :benchmark-cq="$benchmarkCQ" :benchmark-sync="$benchmarkSync" />
            </div>

            <!-- What This Means & Path to Opportunity Zone -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch avoid-break">
                <!-- What This Means Card -->
                <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="compass" class="w-5 h-5 text-indigo-600"></i>
                        <h3 class="text-sm font-black text-slate-900">What This Position Means</h3>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-950">
                            <span class="font-extrabold block text-amber-900">Current Position: {{ $insights['matrix_zone_name'] }}</span>
                            <p class="mt-0.5 leading-relaxed">{{ $insights['matrix_zone_subtitle'] }}</p>
                        </div>

                        <div class="p-3 rounded-2xl bg-rose-50/70 border border-rose-200 text-rose-950">
                            <span class="font-extrabold block text-rose-900">Key Blind Spot</span>
                            <p class="mt-0.5 leading-relaxed">{{ $insights['matrix_blind_spot'] }}</p>
                        </div>

                        <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-950">
                            <span class="font-extrabold block text-emerald-900">Primary Opportunity</span>
                            <p class="mt-0.5 leading-relaxed">{{ $insights['matrix_opportunity'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Path to Opportunity Zone Trajectory Card -->
                <x-path-opportunity-zone :cq-score="$cqScore" :sync-score="$syncScore" :benchmark-cq="$benchmarkCQ" :benchmark-sync="$benchmarkSync" :insights="$insights" />
            </div>

            <!-- 30-60-90 Day Plan -->
            <div class="avoid-break pt-2">
                <x-plan-30-60-90 :insights="$insights" />
            </div>
        </div>

        <!-- Page break for printing -->
        <div class="page-break"></div>

        <!-- ========================================================================= -->
        <!-- SECTION 4: CAPABILITY & SCORE DISTRIBUTION                                -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs print:border-none print:p-4 space-y-6 avoid-break">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">4. ChangeQuo & Score Distribution</h2>
                    <p class="text-xs text-slate-500 font-medium">Cohort demographic sentiment distribution and statistical analysis</p>
                </div>
                <span class="text-xs text-slate-500 font-bold">Total Assessed: {{ $insights['cohort_size'] }} Team Members</span>
            </div>

            <!-- Distribution & Team Maturity Meter -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch avoid-break">
                <x-team-cq-distribution :insights="$insights" class="lg:col-span-7 flex flex-col justify-between" />
                <x-team-maturity-level-gauge :insights="$insights" class="lg:col-span-5 flex flex-col justify-between" />
            </div>

            <!-- Key Statistics Horizontal Strip -->
            <div class="rounded-3xl border border-slate-200 p-5 bg-white shadow-2xs avoid-break">
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3">Key Statistical Indicators</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 text-center">
                    <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-100">
                        <span class="text-slate-500 block text-[10px] font-bold">Mean (Average)</span>
                        <span class="text-slate-900 font-black text-lg mt-0.5 block">{{ number_format($stats['mean'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[10px] font-bold">Median</span>
                        <span class="text-slate-900 font-black text-lg mt-0.5 block">{{ number_format($stats['median'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[10px] font-bold">Std Deviation</span>
                        <span class="text-slate-900 font-black text-lg mt-0.5 block">{{ number_format($stats['std_dev'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[10px] font-bold">Highest Score</span>
                        <span class="text-slate-900 font-black text-lg mt-0.5 block">{{ number_format($stats['highest'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[10px] font-bold">Lowest Score</span>
                        <span class="text-slate-900 font-black text-lg mt-0.5 block">{{ number_format($stats['lowest'] ?? 0, 1) }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                        <span class="text-emerald-700 block text-[10px] font-bold">Target Benchmark</span>
                        <span class="text-emerald-800 font-black text-lg mt-0.5 block">8.0</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-rose-50/70 border border-rose-100">
                        <span class="text-rose-700 block text-[10px] font-bold">Gap to Target</span>
                        <span class="text-rose-700 font-black text-lg mt-0.5 block">{{ $gapCQ }}</span>
                    </div>
                </div>
            </div>

            <!-- Team Strengths & Areas of Concern -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch avoid-break">
                <!-- Team Strengths -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900">Key Team Strengths</h4>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-700">
                        @foreach($insights['team_strengths'] ?? [] as $str)
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-600 font-bold">•</span>
                                <span class="leading-relaxed">{{ $str }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Areas of Concern -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900">Areas Requiring Attention</h4>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-700">
                        @foreach($insights['areas_of_concern'] ?? [] as $con)
                            <li class="flex items-start gap-2">
                                <span class="text-rose-600 font-bold">•</span>
                                <span class="leading-relaxed">{{ $con }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 5: LEADERSHIP SIGN-OFF & GOVERNANCE RECORD                        -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs print:border-none print:p-4 space-y-6 avoid-break">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">5. Leadership Sign-Off & Action Plan Governance</h2>
                    <p class="text-xs text-slate-500 font-medium">Formal organizational accountability and action ownership</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold capitalize
                    {{ match($latestStatus) {
                        'approved' => 'bg-emerald-50 text-emerald-800 border border-emerald-200',
                        'needs_review' => 'bg-amber-50 text-amber-800 border border-amber-200',
                        default => 'bg-slate-100 text-slate-700 border border-slate-200'
                    } }}">
                    Status: {{ ucwords(str_replace('_', ' ', $latestStatus)) }}
                </span>
            </div>

            <!-- Existing Audit Records -->
            @if(!empty($signOffs))
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Recorded Sign-Off History</h4>
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden">
                        @foreach($signOffs as $item)
                            <div class="p-4 bg-slate-50/50 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <span class="font-extrabold text-slate-900">{{ $item['lead'] }}</span>
                                    <span class="text-slate-400 text-[11px] ml-1">({{ ucwords(str_replace('_', ' ', $item['status'])) }})</span>
                                    @if(!empty($item['notes']))
                                        <p class="text-slate-600 mt-1 italic leading-relaxed">"{{ $item['notes'] }}"</p>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 font-medium whitespace-nowrap">
                                    {{ !empty($item['signed_off_at']) ? (is_string($item['signed_off_at']) ? $item['signed_off_at'] : $item['signed_off_at']->format('d M Y')) : 'N/A' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Printed Sign-Off Signatures Box -->
            <div class="p-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50/40 space-y-6">
                <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Executive Signatures & Endorsement
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-4 text-xs">
                    <div class="border-t border-slate-400 pt-2">
                        <span class="font-bold text-slate-800 block">Executive Sponsor</span>
                        <span class="text-[11px] text-slate-400">Signature & Date</span>
                    </div>
                    <div class="border-t border-slate-400 pt-2">
                        <span class="font-bold text-slate-800 block">Change Program Lead</span>
                        <span class="text-[11px] text-slate-400">Signature & Date</span>
                    </div>
                    <div class="border-t border-slate-400 pt-2">
                        <span class="font-bold text-slate-800 block">HR / Transformation Lead</span>
                        <span class="text-[11px] text-slate-400">Signature & Date</span>
                    </div>
                </div>
            </div>

            <!-- Footer Stamp -->
            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-4 border-t border-slate-100">
                <span>ChangeQuo Assessment Platform • Generated for {{ $survey->title }}</span>
                <span>Confidential Executive Document</span>
            </div>
        </div>

    </main>

    <!-- Auto-Print Script when auto_print=1 is present -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
            const params = new URLSearchParams(window.location.search);
            if (params.get('auto_print') === '1' || params.get('print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>
</html>
