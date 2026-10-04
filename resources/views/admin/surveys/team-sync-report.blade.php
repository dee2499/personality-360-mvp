<x-layouts.app>
    @php
        $sync = $insights['cq_sync'] ?? [];
        $syncScore = (float) ($sync['score'] ?? $insights['team_cq_sync_score'] ?? 0.0);
        $benchmarkSync = (float) ($sync['benchmark'] ?? 8.0);
        $gapSync = (float) ($sync['gap_to_benchmark'] ?? ($syncScore - $benchmarkSync));
        $dimensions = $sync['dimensions'] ?? [];

        $seeScore = (float) ($dimensions['see_together']['score'] ?? 0.0);
        $agreeScore = (float) ($dimensions['agree_together']['score'] ?? 0.0);
        $actScore = (float) ($dimensions['act_together']['score'] ?? 0.0);

        // Next target calculation: if below 6.0, target 6.5. If between 6.0 and 7.5, target 7.8. Else 8.5.
        $nextTarget = $syncScore < 6.0 ? 6.5 : ($syncScore < 7.5 ? 7.8 : 8.5);

        // Speedometer Needle Angle (-90 deg at 1.0, +90 deg at 10.0)
        $clampedSync = max(1.0, min(10.0, $syncScore > 0 ? $syncScore : 5.0));
        $syncNeedleAngle = -90 + (($clampedSync - 1.0) / 9.0) * 180;

        // Growth Journey coordinates
        // S-curve from x=50,y=180 (Divergent) to x=450,y=35 (Unified)
        // SVG viewBox: 0 0 500 220. Y: 0 at top (score 10 = y 35, score 0 = y 195)
        $scoreToY = fn($val) => round(195 - (($val / 10) * 160), 1);
        $scoreToX = fn($val) => round(50 + ((max(1.0, min(10.0, $val)) - 1.0) / 9.0) * 400, 1);

        $currentX = $scoreToX($syncScore);
        $currentY = $scoreToY($syncScore);
        $targetX = $scoreToX($nextTarget);
        $targetY = $scoreToY($nextTarget);
        $benchmarkX = $scoreToX(8.0);
        $benchmarkY = $scoreToY(8.0);
    @endphp

    <div class="space-y-6">
        <!-- Top Navigation & Action Controls (Hidden on print) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div class="flex items-center gap-2 flex-wrap">
                @if($company)
                    <a href="{{ route('admin.companies.show', $company) }}" 
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Back to {{ $company->name }}</span>
                    </a>
                    <span class="text-slate-300">•</span>
                @endif
                <a href="{{ route('admin.surveys.show', $survey) }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    <span>Survey Overview</span>
                </a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('admin.surveys.group-insights', $survey) }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition font-bold">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span>Group Position Matrix & Strategy</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-2xs transition cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Print Report</span>
                </button>

                <a href="{{ route('admin.surveys.group-insights', ['survey' => $survey, 'tab' => 'summary']) }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-2xs transition">
                    <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                    <span>Position Matrix</span>
                </a>
            </div>
        </div>

        <!-- Main Report Canvas Container (Matches media_1791104045986.jpg) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 lg:p-10 space-y-6 print:border-none print:shadow-none print:p-2">
            
            <!-- 1. Header (Logo, Title, Metadata & Confidentiality) -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 pb-6 border-b border-slate-100">
                <!-- Left: changequo logo -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Butterfly Icon -->
                    <div class="w-12 h-12 flex items-center justify-center text-purple-700 shrink-0">
                        <svg class="w-11 h-11" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Left Wing upper -->
                            <path d="M48 48 C38 20, 10 15, 8 32 C6 48, 30 50, 48 50 Z" fill="#7C3AED" opacity="0.95"/>
                            <!-- Left Wing lower -->
                            <path d="M48 52 C32 54, 16 66, 20 80 C24 92, 42 78, 48 56 Z" fill="#9333EA" opacity="0.85"/>
                            <!-- Right Wing upper -->
                            <path d="M52 48 C62 20, 90 15, 92 32 C94 48, 70 50, 52 50 Z" fill="#7C3AED" opacity="0.95"/>
                            <!-- Right Wing lower -->
                            <path d="M52 52 C68 54, 84 66, 80 80 C76 92, 58 78, 52 56 Z" fill="#9333EA" opacity="0.85"/>
                            <!-- Butterfly Body -->
                            <ellipse cx="50" cy="50" rx="3" ry="22" fill="#581C87"/>
                            <circle cx="50" cy="24" r="3.5" fill="#581C87"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-black tracking-tight text-purple-950 leading-none">
                            change<span class="text-purple-600">quo</span>
                        </div>
                        <div class="text-[11px] font-bold tracking-tight text-purple-700 mt-1">
                            Unlocking Possibilities
                        </div>
                    </div>
                </div>

                <!-- Center: Report Title -->
                <div class="flex-1 lg:px-6">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                        Team CQ Sync Report
                    </h1>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 mt-1">
                        How well is your team aligned to see, agree and act on change together?
                    </p>
                </div>

                <!-- Right: Metadata & Confidential Tag -->
                <div class="flex items-center gap-4 shrink-0 flex-wrap sm:flex-nowrap">
                    <div class="text-xs space-y-0.5 text-slate-600">
                        <div><span class="text-slate-400 font-semibold">Team:</span> <strong class="text-slate-900">{{ $survey->title }}</strong></div>
                        <div><span class="text-slate-400 font-semibold">Team Size:</span> <strong class="text-slate-900">{{ $insights['cohort_size'] }}</strong></div>
                        <div><span class="text-slate-400 font-semibold">Assessment Date:</span> <strong class="text-slate-900">{{ $survey->published_at ? $survey->published_at->format('d M Y') : now()->format('d M Y') }}</strong></div>
                    </div>

                    <!-- Confidential Badge -->
                    <div class="p-2.5 rounded-2xl bg-purple-50 border border-purple-200/80 text-purple-900 flex items-center gap-2.5 max-w-[210px]">
                        <div class="w-7 h-7 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="text-[10px] leading-tight">
                            <div class="font-black text-purple-950 uppercase tracking-wider">Confidential</div>
                            <div class="text-purple-700 font-medium mt-0.5">For internal use only. Not to be shared outside the team/organisation.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Top 4 Metric KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- KPI 1: Team Size -->
                <div class="p-4 sm:p-5 rounded-2xl bg-purple-50/40 border border-purple-100 flex items-center gap-4 shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-500 block">Team Size</span>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight mt-0.5">
                            {{ $insights['cohort_size'] }}
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium">Members assessed</span>
                    </div>
                </div>

                <!-- KPI 2: Overall CQ Sync Score -->
                <div class="p-4 sm:p-5 rounded-2xl bg-blue-50/40 border border-blue-100 flex items-center gap-4 shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                        <i data-lucide="users-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-500 block">Overall CQ Sync Score</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($syncScore, 1) }}</span>
                            <span class="text-xs font-bold text-slate-400">/ 10</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 mt-1">
                            {{ $sync['maturity_level'] ?? 'Aligned' }}
                        </span>
                    </div>
                </div>

                <!-- KPI 3: Expected / Benchmark -->
                <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50/40 border border-emerald-100 flex items-center gap-4 shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <i data-lucide="target" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-500 block">Expected / Benchmark</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-2xl sm:text-3xl font-black text-emerald-800">8.0</span>
                            <span class="text-xs font-bold text-slate-400">/ 10</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 mt-1">
                            Unified
                        </span>
                    </div>
                </div>

                <!-- KPI 4: Gap to Benchmark -->
                <div class="p-4 sm:p-5 rounded-2xl bg-rose-50/40 border border-rose-100 flex items-center gap-4 shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-500 block">Gap to Benchmark</span>
                        <div class="text-2xl sm:text-3xl font-black {{ $gapSync < 0 ? 'text-rose-600' : 'text-emerald-600' }} leading-tight mt-0.5">
                            {{ $gapSync > 0 ? '+'.$gapSync : $gapSync }}
                        </div>
                        <span class="text-[10px] text-slate-500 font-medium leading-tight block mt-1">
                            Team needs stronger synchronisation to reach benchmark.
                        </span>
                    </div>
                </div>
            </div>

            <!-- 3. Middle Row: 3 Visual Charts (Maturity Gauge, Growth Journey, 3 Dimensions) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                
                <!-- Column 1: Team CQ Sync Maturity Level (5-Sector Speedometer Gauge) -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">Team CQ Sync Maturity Level</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Where does your team stand on alignment to see, agree and act together?</p>
                    </div>

                    <!-- Gauge Graphic -->
                    <div class="w-full max-w-[300px] aspect-[2/1] relative flex items-end justify-center mx-auto my-3">
                        <svg viewBox="0 0 300 170" class="w-full h-full overflow-visible">
                            <!-- Defs for gauge shadows and styling -->
                            <defs>
                                <filter id="needle-shadow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="1" dy="2" stdDeviation="2" flood-color="#000" flood-opacity="0.3"/>
                                </filter>
                            </defs>

                            <!-- 5 Gauge Arcs (Radius = 120, Center = 150, 150) -->
                            <!-- Sector 1: Divergent (1.0-2.0, Red #EF4444) -->
                            <path d="M 30,150 A 120,120 0 0,1 67.08,52.92" fill="none" stroke="#EF4444" stroke-width="26" stroke-linecap="round"/>
                            <!-- Sector 2: Fragmented (2.1-4.0, Orange #FB923C) -->
                            <path d="M 68,52 A 120,120 0 0,1 118.89,31.7" fill="none" stroke="#FB923C" stroke-width="26"/>
                            <!-- Sector 3: Aligned (4.1-6.0, Green #10B981) -->
                            <path d="M 120,31.5 A 120,120 0 0,1 180,31.5" fill="none" stroke="#10B981" stroke-width="26"/>
                            <!-- Sector 4: Synchronised (6.1-8.0, Amber #F59E0B) -->
                            <path d="M 181.11,31.7 A 120,120 0 0,1 232,52" fill="none" stroke="#F59E0B" stroke-width="26"/>
                            <!-- Sector 5: Unified (8.1-10.0, Blue #3B82F6) -->
                            <path d="M 232.92,52.92 A 120,120 0 0,1 270,150" fill="none" stroke="#3B82F6" stroke-width="26" stroke-linecap="round"/>

                            <!-- Sector Labels and Icons inside the arcs -->
                            <!-- Divergent -->
                            <text x="44" y="115" text-anchor="middle" font-size="7" font-weight="800" fill="#FFFFFF">Divergent</text>
                            <text x="44" y="123" text-anchor="middle" font-size="6" font-weight="700" fill="#FEE2E2">1.0 – 2.0</text>
                            
                            <!-- Fragmented -->
                            <text x="96" y="58" text-anchor="middle" font-size="7" font-weight="800" fill="#FFFFFF">Fragmented</text>
                            <text x="96" y="66" text-anchor="middle" font-size="6" font-weight="700" fill="#FFEDD5">2.1 – 4.0</text>

                            <!-- Aligned -->
                            <text x="150" y="44" text-anchor="middle" font-size="7.5" font-weight="800" fill="#FFFFFF">Aligned</text>
                            <text x="150" y="52" text-anchor="middle" font-size="6" font-weight="700" fill="#D1FAE5">4.1 – 6.0</text>

                            <!-- Synchronised -->
                            <text x="204" y="58" text-anchor="middle" font-size="7" font-weight="800" fill="#FFFFFF">Synchronised</text>
                            <text x="204" y="66" text-anchor="middle" font-size="6" font-weight="700" fill="#FEF3C7">6.1 – 8.0</text>

                            <!-- Unified -->
                            <text x="256" y="115" text-anchor="middle" font-size="7" font-weight="800" fill="#FFFFFF">Unified</text>
                            <text x="256" y="123" text-anchor="middle" font-size="6" font-weight="700" fill="#DBEAFE">8.1 – 10.0</text>

                            <!-- Center Rotating Speedometer Needle -->
                            <g transform="translate(150, 150) rotate({{ $syncNeedleAngle }})" filter="url(#needle-shadow)">
                                <path d="M -5,0 L -1.5,-105 L 0,-115 L 1.5,-105 L 5,0 Z" fill="#1E293B"/>
                                <circle cx="0" cy="0" r="8" fill="#0F172A"/>
                                <circle cx="0" cy="0" r="3.5" fill="#FFFFFF"/>
                            </g>
                        </svg>
                    </div>

                    <!-- Score display at bottom center of gauge -->
                    <div class="text-center pt-1 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Team CQ Sync Score</span>
                        <div class="flex items-baseline justify-center gap-1 mt-0.5">
                            <span class="text-3xl font-black text-slate-900">{{ number_format($syncScore, 1) }}</span>
                            <span class="text-sm font-bold text-slate-400">/ 10</span>
                        </div>
                        <div class="mt-1.5">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-black uppercase tracking-wider {{ $sync['badge'] ?? 'bg-emerald-100 text-emerald-800' }}">
                                {{ $sync['maturity_level'] ?? 'Aligned' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Team CQ Sync Growth Journey (S-Curve Chart) -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">Team CQ Sync Growth Journey</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Your current position and path to the benchmark</p>
                    </div>

                    <!-- S-Curve Chart Area -->
                    <div class="w-full relative py-2">
                        <svg viewBox="0 0 500 240" class="w-full h-auto overflow-visible">
                            <defs>
                                <!-- Area gradient under curve -->
                                <linearGradient id="curveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#EF4444" stop-opacity="0.15"/>
                                    <stop offset="25%" stop-color="#FB923C" stop-opacity="0.2"/>
                                    <stop offset="50%" stop-color="#10B981" stop-opacity="0.25"/>
                                    <stop offset="75%" stop-color="#F59E0B" stop-opacity="0.25"/>
                                    <stop offset="100%" stop-color="#3B82F6" stop-opacity="0.35"/>
                                </linearGradient>

                                <!-- Line gradient along curve -->
                                <linearGradient id="lineGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#EF4444"/>
                                    <stop offset="25%" stop-color="#FB923C"/>
                                    <stop offset="50%" stop-color="#10B981"/>
                                    <stop offset="75%" stop-color="#F59E0B"/>
                                    <stop offset="100%" stop-color="#3B82F6"/>
                                </linearGradient>

                                <marker id="arrow" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                    <path d="M 0 0 L 10 5 L 0 10 z" fill="#3B82F6" />
                                </marker>
                            </defs>

                            <!-- Background Grid Lines -->
                            <line x1="45" y1="35" x2="480" y2="35" stroke="#F1F5F9" stroke-width="1"/>
                            <line x1="45" y1="67" x2="480" y2="67" stroke="#F1F5F9" stroke-width="1"/>
                            <line x1="45" y1="99" x2="480" y2="99" stroke="#F1F5F9" stroke-width="1"/>
                            <line x1="45" y1="131" x2="480" y2="131" stroke="#F1F5F9" stroke-width="1"/>
                            <line x1="45" y1="163" x2="480" y2="163" stroke="#F1F5F9" stroke-width="1"/>
                            <line x1="45" y1="195" x2="480" y2="195" stroke="#CBD5E1" stroke-width="1.5"/>

                            <!-- Y Axis Label -->
                            <text x="-115" y="14" transform="rotate(-90)" text-anchor="middle" font-size="8" font-weight="700" fill="#94A3B8">CQ Sync Score</text>

                            <!-- Y-Axis Value Labels -->
                            <text x="35" y="38" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">10</text>
                            <text x="35" y="70" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">8</text>
                            <text x="35" y="102" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">6</text>
                            <text x="35" y="134" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">4</text>
                            <text x="35" y="166" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">2</text>
                            <text x="35" y="198" text-anchor="end" font-size="8" font-weight="700" fill="#94A3B8">0</text>

                            <!-- Gradient Area Fill beneath S-Curve -->
                            <path d="M 50,195 L 60,185 C 130,175 190,145 270,105 C 340,70 410,48 465,36 L 465,195 Z" fill="url(#curveGradient)"/>

                            <!-- Main S-Curve Line -->
                            <path d="M 50,186 C 130,175 190,145 270,105 C 340,70 410,48 470,36" fill="none" stroke="url(#lineGrad)" stroke-width="4.5" stroke-linecap="round" marker-end="url(#arrow)"/>

                            <!-- Milestones on Curve -->
                            <!-- Point 1: Divergent (x≈65, y≈184) -->
                            <circle cx="65" cy="184" r="5" fill="#EF4444" stroke="#FFFFFF" stroke-width="2"/>

                            <!-- Point 2: Fragmented (x≈150, y≈155) -->
                            <circle cx="150" cy="155" r="5" fill="#FB923C" stroke="#FFFFFF" stroke-width="2"/>

                            <!-- Point 3: Current Score Milestone -->
                            <g>
                                <!-- Callout Box -->
                                <rect x="{{ $currentX - 28 }}" y="{{ $currentY - 32 }}" width="56" height="22" rx="6" fill="#ECFDF5" stroke="#A7F3D0" stroke-width="1.2"/>
                                <text x="{{ $currentX }}" y="{{ $currentY - 22 }}" text-anchor="middle" font-size="7" font-weight="800" fill="#065F46">Current</text>
                                <text x="{{ $currentX }}" y="{{ $currentY - 13 }}" text-anchor="middle" font-size="8.5" font-weight="900" fill="#047857">{{ number_format($syncScore, 1) }}</text>
                                <!-- Arrow pointer down -->
                                <polygon points="{{ $currentX - 4 }},{{ $currentY - 10 }} {{ $currentX + 4 }},{{ $currentY - 10 }} {{ $currentX }},{{ $currentY - 5 }}" fill="#047857"/>
                                <!-- Dot on curve -->
                                <circle cx="{{ $currentX }}" cy="{{ $currentY }}" r="6" fill="#10B981" stroke="#FFFFFF" stroke-width="2.5"/>
                            </g>

                            <!-- Point 4: Next Target Milestone -->
                            <g>
                                <!-- Callout Box -->
                                <rect x="{{ $targetX - 30 }}" y="{{ $targetY - 32 }}" width="60" height="22" rx="6" fill="#FEF3C7" stroke="#FDE68A" stroke-width="1.2"/>
                                <text x="{{ $targetX }}" y="{{ $targetY - 22 }}" text-anchor="middle" font-size="7" font-weight="800" fill="#92400E">Next Target</text>
                                <text x="{{ $targetX }}" y="{{ $targetY - 13 }}" text-anchor="middle" font-size="8.5" font-weight="900" fill="#B45309">{{ number_format($nextTarget, 1) }}</text>
                                <polygon points="{{ $targetX - 4 }},{{ $targetY - 10 }} {{ $targetX + 4 }},{{ $targetY - 10 }} {{ $targetX }},{{ $targetY - 5 }}" fill="#B45309"/>
                                <circle cx="{{ $targetX }}" cy="{{ $targetY }}" r="6" fill="#F59E0B" stroke="#FFFFFF" stroke-width="2.5"/>
                            </g>

                            <!-- Point 5: Benchmark Milestone -->
                            <g>
                                <!-- Callout Box -->
                                <rect x="{{ $benchmarkX - 30 }}" y="{{ $benchmarkY - 32 }}" width="60" height="22" rx="6" fill="#DBEAFE" stroke="#BFDBFE" stroke-width="1.2"/>
                                <text x="{{ $benchmarkX }}" y="{{ $benchmarkY - 22 }}" text-anchor="middle" font-size="7" font-weight="800" fill="#1E40AF">Benchmark</text>
                                <text x="{{ $benchmarkX }}" y="{{ $benchmarkY - 13 }}" text-anchor="middle" font-size="8.5" font-weight="900" fill="#1D4ED8">8.0</text>
                                <polygon points="{{ $benchmarkX - 4 }},{{ $benchmarkY - 10 }} {{ $benchmarkX + 4 }},{{ $benchmarkY - 10 }} {{ $benchmarkX }},{{ $benchmarkY - 5 }}" fill="#1D4ED8"/>
                                <circle cx="{{ $benchmarkX }}" cy="{{ $benchmarkY }}" r="6" fill="#3B82F6" stroke="#FFFFFF" stroke-width="2.5"/>
                            </g>

                            <!-- X Axis Stage Labels -->
                            <text x="65" y="210" text-anchor="middle" font-size="7" font-weight="800" fill="#475569">Divergent</text>
                            <text x="65" y="218" text-anchor="middle" font-size="6" font-weight="600" fill="#94A3B8">1.0 – 2.0</text>

                            <text x="150" y="210" text-anchor="middle" font-size="7" font-weight="800" fill="#475569">Fragmented</text>
                            <text x="150" y="218" text-anchor="middle" font-size="6" font-weight="600" fill="#94A3B8">2.1 – 4.0</text>

                            <text x="245" y="210" text-anchor="middle" font-size="7" font-weight="800" fill="#475569">Aligned</text>
                            <text x="245" y="218" text-anchor="middle" font-size="6" font-weight="600" fill="#94A3B8">4.1 – 6.0</text>

                            <text x="345" y="210" text-anchor="middle" font-size="7" font-weight="800" fill="#475569">Synchronised</text>
                            <text x="345" y="218" text-anchor="middle" font-size="6" font-weight="600" fill="#94A3B8">6.1 – 8.0</text>

                            <text x="445" y="210" text-anchor="middle" font-size="7" font-weight="800" fill="#475569">Unified</text>
                            <text x="445" y="218" text-anchor="middle" font-size="6" font-weight="600" fill="#94A3B8">8.1 – 10.0</text>
                        </svg>
                    </div>

                    <!-- Bottom Journey Slider -->
                    <div class="pt-3 border-t border-slate-100">
                        <!-- Gradient Bar -->
                        <div class="h-2 w-full rounded-full bg-gradient-to-r from-rose-500 via-emerald-500 to-blue-500 opacity-80"></div>
                        <div class="flex items-center justify-between text-[10px] mt-1.5 font-bold">
                            <div class="text-left text-rose-700">
                                <span class="uppercase tracking-wider block font-black">Low Synchronisation</span>
                                <span class="text-slate-400 font-normal">Different views, inconsistent action</span>
                            </div>
                            <div class="text-center text-slate-500 uppercase tracking-widest text-[9px]">
                                Team Development Journey
                            </div>
                            <div class="text-right text-blue-700">
                                <span class="uppercase tracking-wider block font-black">High Synchronisation</span>
                                <span class="text-slate-400 font-normal">Shared understanding, collective action</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Team View across 3 CQ Sync Dimensions -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">Team View across the 3 CQ Sync Dimensions</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Average team score (out of 10) for each question</p>
                    </div>

                    <!-- 3 Dimension Bars Area -->
                    <div class="space-y-6 my-auto py-2">
                        <!-- Dimension 1: See Together -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                                    <!-- Binoculars / Glasses Icon -->
                                    <i data-lucide="glasses" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 leading-tight">See Together</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Shared understanding of the change</div>
                                </div>
                            </div>

                            <div class="relative w-full bg-slate-100 rounded-full h-6 overflow-hidden">
                                <!-- Horizontal Fill -->
                                <div class="h-full rounded-full transition-all duration-700 flex items-center justify-end pr-3"
                                     style="width: {{ max(12, min(100, $seeScore * 10)) }}%; background-color: #8B5CF6;">
                                    <span class="text-xs font-black text-white leading-none">{{ number_format($seeScore, 1) }}</span>
                                </div>
                                <!-- Benchmark 8.0 Marker Line -->
                                <div class="absolute top-0 bottom-0 w-0.5 border-l-2 border-dashed border-slate-700 z-10" style="left: 80%;">
                                    <span class="absolute -top-3.5 -translate-x-1/2 text-[9px] font-black text-slate-700">8.0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dimension 2: Agree Together -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                                    <i data-lucide="handshake" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 leading-tight">Agree Together</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Common direction and priorities</div>
                                </div>
                            </div>

                            <div class="relative w-full bg-slate-100 rounded-full h-6 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 flex items-center justify-end pr-3"
                                     style="width: {{ max(12, min(100, $agreeScore * 10)) }}%; background-color: #38BDF8;">
                                    <span class="text-xs font-black text-white leading-none">{{ number_format($agreeScore, 1) }}</span>
                                </div>
                                <div class="absolute top-0 bottom-0 w-0.5 border-l-2 border-dashed border-slate-700 z-10" style="left: 80%;">
                                    <span class="absolute -top-3.5 -translate-x-1/2 text-[9px] font-black text-slate-700">8.0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dimension 3: Act Together -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <i data-lucide="users" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-900 leading-tight">Act Together</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Collective commitment and action</div>
                                </div>
                            </div>

                            <div class="relative w-full bg-slate-100 rounded-full h-6 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 flex items-center justify-end pr-3"
                                     style="width: {{ max(12, min(100, $actScore * 10)) }}%; background-color: #10B981;">
                                    <span class="text-xs font-black text-white leading-none">{{ number_format($actScore, 1) }}</span>
                                </div>
                                <div class="absolute top-0 bottom-0 w-0.5 border-l-2 border-dashed border-slate-700 z-10" style="left: 80%;">
                                    <span class="absolute -top-3.5 -translate-x-1/2 text-[9px] font-black text-slate-700">8.0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center justify-end gap-6 text-[11px] text-slate-500 pt-3 border-t border-slate-100">
                        <span class="flex items-center gap-1.5 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-950"></span>
                            <span>Team Score (Average)</span>
                        </span>
                        <span class="flex items-center gap-1.5 font-bold">
                            <span class="w-3 border-b-2 border-dashed border-slate-800"></span>
                            <span>Benchmark (Target)</span>
                        </span>
                    </div>
                </div>

            </div>

            <!-- 4. Bottom Row: 3 Commentary Panels (Key Insights, What This Means, Top Recommendations) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                
                <!-- Panel 1: Key Insights -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                            <i data-lucide="lightbulb" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 tracking-tight">Key Insights</h4>
                    </div>

                    <div class="space-y-3 text-xs text-slate-600 leading-relaxed">
                        @foreach($sync['key_insights'] ?? [] as $idx => $insight)
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white font-black text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                    {{ $idx + 1 }}
                                </span>
                                <span>{{ $insight }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Panel 2: What This Means -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                            <i data-lucide="target" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 tracking-tight">What This Means</h4>
                    </div>

                    <div class="space-y-3.5 text-xs">
                        <!-- Positive Foundation -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-emerald-950 text-xs">Positive Foundation</h5>
                                <p class="text-slate-600 text-[11px] mt-0.5 leading-relaxed">
                                    The team is generally open to change and has a shared understanding (See Together).
                                </p>
                            </div>
                        </div>

                        <!-- Execution Gap -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-amber-400 text-amber-950 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                <span class="font-black text-xs">!</span>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-amber-950 text-xs">Execution Gap</h5>
                                <p class="text-slate-600 text-[11px] mt-0.5 leading-relaxed">
                                    Differences in priorities and interpretation are limiting stronger collective action (Agree & Act Together).
                                </p>
                            </div>
                        </div>

                        <!-- Opportunity -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <h5 class="font-extrabold text-rose-950 text-xs">Opportunity</h5>
                                <p class="text-slate-600 text-[11px] mt-0.5 leading-relaxed">
                                    With focused alignment, the team can move towards the Synchronised and Unified levels and close the {{ abs($gapSync) }} point gap.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Top Recommendations -->
                <div class="p-5 sm:p-6 rounded-3xl border border-slate-200 bg-white shadow-2xs space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                            <i data-lucide="bar-chart" class="w-4 h-4"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 tracking-tight">Top Recommendations</h4>
                    </div>

                    <div class="space-y-3 text-xs text-slate-600">
                        @php
                            $recColors = [
                                1 => 'bg-purple-600',
                                2 => 'bg-blue-600',
                                3 => 'bg-amber-600',
                                4 => 'bg-emerald-600',
                            ];
                        @endphp
                        @foreach($sync['recommendations'] ?? [] as $rec)
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-full {{ $recColors[$rec['number']] ?? 'bg-indigo-600' }} text-white font-black text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
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

            </div>

        </div>
    </div>
</x-layouts.app>
