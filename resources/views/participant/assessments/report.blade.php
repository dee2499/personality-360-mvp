<x-layouts.app>
    @php
        $cqScore = (float) ($cq['overall_cq_score'] ?? 0.0);
        $selfScore = (float) ($cq['self_score'] ?? 0.0);
        $peerScore = (float) ($cq['peer_score'] ?? 0.0);
        $profileName = $cq['profile_name'] ?? 'Supporter';
        $profileDisplayName = $cq['profile_display_name'] ?? 'Change Supporter';
        $profileRange = $cq['profile_range'] ?? '4.1 – 6.0';
        $profileColor = $cq['profile_color'] ?? '#10B981';
        $matrix = $cq['matrix'] ?? [];

        // Needle rotation angle for 1-10 on semicircle: -90deg at 1.0, 90deg at 10.0
        // Score 1 -> -90 deg, Score 10 -> +90 deg
        $clampedScore = max(1.0, min(10.0, $cqScore > 0 ? $cqScore : 5.0));
        $needleAngle = -90 + (($clampedScore - 1.0) / 9.0) * 180;

        // Self and peer ring stroke dash
        $selfPct = max(0, min(100, ($selfScore / 10) * 100));
        $peerPct = max(0, min(100, ($peerScore / 10) * 100));
        $circumference = 2 * 3.14159 * 42; // r=42 -> ~263.89
        $selfDashOffset = $circumference - ($selfPct / 100) * $circumference;
        $peerDashOffset = $circumference - ($peerPct / 100) * $circumference;
    @endphp

    <div class="max-w-5xl mx-auto space-y-6 sm:space-y-8">
        <!-- Top Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('participant.assessments.index', ['survey_id' => $survey->id]) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Assessments</span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('participant.surveys.group-insights', $survey) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span>View Team CQ Report</span>
                </a>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Confidential</span>
                </div>
            </div>
        </div>

        <!-- Document Header (Matching Image 1) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs relative">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 border-b border-slate-100 pb-6">
                <!-- Brand & Greeting -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-black tracking-tight text-indigo-900">change<span class="text-indigo-600">quo</span></span>
                        <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">• Unlocking Possibilities</span>
                    </div>
                    
                    <div class="flex items-center gap-3 pt-2">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 font-black text-lg flex items-center justify-center shrink-0">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900">
                                Hi {{ $user->name }},
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500">
                                Here is your personalised ChangeQuo (CQ) report based on your self-assessment and feedback from your team.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Header Meta -->
                <div class="text-left md:text-right space-y-1">
                    <span class="block text-xs font-black uppercase tracking-wider text-indigo-900">INDIVIDUAL CQ REPORT</span>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">YOUR CHANGE JOURNEY. A BRIGHTER YOU.</span>
                    <div class="inline-flex items-center gap-1 text-xs text-slate-500 pt-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Assessment Date: {{ $survey->published_at ? $survey->published_at->format('d M Y') : now()->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Profile Speedometer Gauge Section (Matching Image 1) -->
            <div class="pt-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h2 class="text-lg sm:text-xl font-black text-slate-900">
                            Your <span class="text-indigo-600">ChangeQuo</span> Profile
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Your current position in the change journey</p>
                    </div>
                    <div class="inline-flex items-center gap-1 text-xs text-slate-400 font-medium">
                        <span>Score is on a scale of 1 – 10</span>
                        <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                    </div>
                </div>

                <!-- SPEEDOMETER SVG GAUGE -->
                <div class="bg-gradient-to-b from-slate-50/60 to-white rounded-3xl p-6 sm:p-8 border border-slate-100 flex flex-col items-center relative overflow-hidden">
                    <div class="w-full max-w-[420px] aspect-[2/1] relative flex items-end justify-center">
                        <svg viewBox="0 0 300 170" class="w-full h-full overflow-visible">
                            <defs>
                                <filter id="needle-shadow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.25"/>
                                </filter>
                            </defs>

                            <!-- 5 Gauge Colored Arcs -->
                            <!-- Resistant: 1.0 - 2.0 (Red #EF4444) -->
                            <path d="M 30,150 A 120,120 0 0,1 67.08,52.92" fill="none" stroke="#EF4444" stroke-width="26" stroke-linecap="round"/>
                            <!-- Follower: 2.1 - 4.0 (Orange #F97316) -->
                            <path d="M 68,52 A 120,120 0 0,1 118.89,31.7" fill="none" stroke="#F97316" stroke-width="26"/>
                            <!-- Supporter: 4.1 - 6.0 (Green #10B981) -->
                            <path d="M 120,31.5 A 120,120 0 0,1 180,31.5" fill="none" stroke="#10B981" stroke-width="26"/>
                            <!-- Driver: 6.1 - 8.0 (Amber/Yellow #F59E0B) -->
                            <path d="M 181.11,31.7 A 120,120 0 0,1 232,52" fill="none" stroke="#F59E0B" stroke-width="26"/>
                            <!-- Champion: 8.1 - 10.0 (Blue #3B82F6) -->
                            <path d="M 232.92,52.92 A 120,120 0 0,1 270,150" fill="none" stroke="#3B82F6" stroke-width="26" stroke-linecap="round"/>

                            <!-- Inner White Cutout Arc -->
                            <path d="M 65,150 A 85,85 0 0,1 235,150" fill="#FFFFFF" stroke="#F1F5F9" stroke-width="2"/>

                            <!-- Segment Labels & Icons -->
                            <!-- Resistant -->
                            <g transform="translate(62, 115)" text-anchor="middle">
                                <text x="0" y="0" font-size="8" font-weight="800" fill="#991B1B">Resistant</text>
                                <text x="0" y="10" font-size="7" font-weight="600" fill="#B91C1C">1.0 – 2.0</text>
                            </g>
                            <!-- Follower -->
                            <g transform="translate(98, 68)" text-anchor="middle">
                                <text x="0" y="0" font-size="8" font-weight="800" fill="#C2410C">Follower</text>
                                <text x="0" y="10" font-size="7" font-weight="600" fill="#EA580C">2.1 – 4.0</text>
                            </g>
                            <!-- Supporter -->
                            <g transform="translate(150, 48)" text-anchor="middle">
                                <text x="0" y="0" font-size="8" font-weight="800" fill="#047857">Supporter</text>
                                <text x="0" y="10" font-size="7" font-weight="600" fill="#059669">4.1 – 6.0</text>
                            </g>
                            <!-- Driver -->
                            <g transform="translate(202, 68)" text-anchor="middle">
                                <text x="0" y="0" font-size="8" font-weight="800" fill="#B45309">Driver</text>
                                <text x="0" y="10" font-size="7" font-weight="600" fill="#D97706">6.1 – 8.0</text>
                            </g>
                            <!-- Champion -->
                            <g transform="translate(238, 115)" text-anchor="middle">
                                <text x="0" y="0" font-size="8" font-weight="800" fill="#1D4ED8">Champion</text>
                                <text x="0" y="10" font-size="7" font-weight="600" fill="#2563EB">8.1 – 10.0</text>
                            </g>

                            <!-- The Needle -->
                            <g transform="translate(150, 150) rotate({{ $needleAngle }})" filter="url(#needle-shadow)">
                                <path d="M -4,0 L -1,-110 L 0,-118 L 1,-110 L 4,0 Z" fill="#1E293B"/>
                                <circle cx="0" cy="0" r="8" fill="#0F172A"/>
                                <circle cx="0" cy="0" r="4" fill="#FFFFFF"/>
                            </g>

                            <!-- Center Score Readout -->
                            <text x="150" y="125" text-anchor="middle" font-size="8" font-weight="700" fill="#64748B">Your ChangeQuo Score</text>
                            <text x="150" y="148" text-anchor="middle" font-size="28" font-weight="900" fill="#0F172A">{{ number_format($cqScore, 1) }}</text>
                            <text x="150" y="162" text-anchor="middle" font-size="11" font-weight="800" fill="#4338CA">{{ $profileName }}</text>
                        </svg>
                    </div>

                    <!-- Profile Archetype Banner (Matching Image 1) -->
                    <div class="w-full mt-4 bg-amber-50/60 border border-amber-200/80 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs text-xl">
                                {{ $cq['profile_emoji'] ?? '🚀' }}
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base font-black text-amber-950">
                                    You are a <span class="text-amber-800">{{ $profileDisplayName }}</span>
                                </h3>
                                <p class="text-xs text-slate-700 leading-relaxed max-w-2xl">
                                    {{ $cq['profile_description'] }}
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0 bg-white/80 px-4 py-2 rounded-xl border border-amber-200 text-left md:text-right">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Your CQ Range</span>
                            <span class="text-xl font-black text-amber-900">{{ $profileRange }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MIDDLE SECTION: TWO SCORE CARDS (Self Score vs Peer Score Rings) (Matching Image 1) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- 1. Your Self Score Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900">Your Self Score</h3>
                            <p class="text-xs text-slate-400">What you feel about yourself</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">
                        <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                        <span>Confidential</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <!-- Circular Donut Ring -->
                    <div class="relative w-28 h-28 shrink-0 flex items-center justify-center">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="transparent" stroke="#F1F5F9" stroke-width="10"/>
                            <circle cx="50" cy="50" r="42" fill="transparent" stroke="#3B82F6" stroke-width="10"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $selfDashOffset }}"
                                    stroke-linecap="round"
                                    class="transition-all duration-1000"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-slate-900 leading-none">{{ number_format($selfScore, 1) }}</span>
                            <span class="text-[10px] font-bold text-slate-400 mt-0.5">/ 10</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 pl-4 flex-1">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            For your self consumption only. Not to be shared with others.
                        </p>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $cq['cq1']['badge'] }}">
                            {{ $cq['cq1']['category'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Peer Score (Average) Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900">Peer Score (Average)</h3>
                            <p class="text-xs text-slate-400">What others think about you</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">
                        <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                        <span>Confidential</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <!-- Circular Donut Ring -->
                    <div class="relative w-28 h-28 shrink-0 flex items-center justify-center">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="transparent" stroke="#F1F5F9" stroke-width="10"/>
                            <circle cx="50" cy="50" r="42" fill="transparent" stroke="#F59E0B" stroke-width="10"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $peerDashOffset }}"
                                    stroke-linecap="round"
                                    class="transition-all duration-1000"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-slate-900 leading-none">{{ number_format($peerScore, 1) }}</span>
                            <span class="text-[10px] font-bold text-slate-400 mt-0.5">/ 10</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 pl-4 flex-1">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Consensus ratings across {{ $cq['cq2']['completed_count'] ?? 0 }} peer colleague(s). Kept strictly confidential.
                        </p>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $cq['cq2']['badge'] }}">
                            {{ $cq['cq2']['category'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM SECTION: 2x2 MATRIX & KEY INSIGHTS (Matching Image 1) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Card: Self vs Peer 2x2 Matrix -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-indigo-600"></i>
                        <h3 class="text-base sm:text-lg font-black text-slate-900">Self vs Peer Insight</h3>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Understand your strength and blind spots</p>
                </div>

                <!-- 2x2 GRID VISUALIZATION -->
                <div class="relative p-2">
                    <!-- Y Axis Label -->
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>High (8–10)</span>
                        <span class="text-slate-500 font-extrabold">Peer Score (What others think)</span>
                        <span>Low (1–3)</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 relative bg-slate-100 p-2 rounded-2xl border border-slate-200">
                        <!-- Top-Left: Undervalued Potential -->
                        <div class="p-3.5 rounded-xl border text-xs flex flex-col justify-between transition min-h-[120px]
                            {{ ($matrix['quadrant'] ?? '') === 'undervalued_potential' ? 'bg-amber-100/80 border-amber-400 shadow-xs ring-2 ring-amber-400/30' : 'bg-amber-50/50 border-amber-200/60' }}">
                            <div>
                                <h4 class="font-black text-amber-950 text-xs">Undervalued Potential</h4>
                                <p class="text-[10px] text-amber-800/80 mt-1 leading-tight">
                                    Others see you stronger than you see yourself. Build confidence.
                                </p>
                            </div>
                            <span class="text-[9px] font-bold text-amber-700">Self Low • Peer High</span>
                        </div>

                        <!-- Top-Right: Aligned Strength -->
                        <div class="p-3.5 rounded-xl border text-xs flex flex-col justify-between transition min-h-[120px]
                            {{ ($matrix['quadrant'] ?? '') === 'aligned_strength' ? 'bg-emerald-100/80 border-emerald-400 shadow-xs ring-2 ring-emerald-400/30' : 'bg-emerald-50/50 border-emerald-200/60' }}">
                            <div>
                                <h4 class="font-black text-emerald-950 text-xs">Aligned Strength</h4>
                                <p class="text-[10px] text-emerald-800/80 mt-1 leading-tight">
                                    You and others see you similarly. Keep doing what works.
                                </p>
                            </div>
                            <span class="text-[9px] font-bold text-emerald-700">Self High • Peer High</span>
                        </div>

                        <!-- Bottom-Left: Key Development Area -->
                        <div class="p-3.5 rounded-xl border text-xs flex flex-col justify-between transition min-h-[120px]
                            {{ ($matrix['quadrant'] ?? '') === 'key_development' ? 'bg-rose-100/80 border-rose-400 shadow-xs ring-2 ring-rose-400/30' : 'bg-rose-50/50 border-rose-200/60' }}">
                            <div>
                                <h4 class="font-black text-rose-950 text-xs">Key Development Area</h4>
                                <p class="text-[10px] text-rose-800/80 mt-1 leading-tight">
                                    Both you and others see gaps. Focus on building core change capabilities.
                                </p>
                            </div>
                            <span class="text-[9px] font-bold text-rose-700">Self Low • Peer Low</span>
                        </div>

                        <!-- Bottom-Right: Perception Gap -->
                        <div class="p-3.5 rounded-xl border text-xs flex flex-col justify-between transition min-h-[120px]
                            {{ ($matrix['quadrant'] ?? '') === 'perception_gap' ? 'bg-blue-100/80 border-blue-400 shadow-xs ring-2 ring-blue-400/30' : 'bg-blue-50/50 border-blue-200/60' }}">
                            <div>
                                <h4 class="font-black text-blue-950 text-xs">Perception Gap</h4>
                                <p class="text-[10px] text-blue-800/80 mt-1 leading-tight">
                                    You see yourself stronger than others currently experience. Increase visibility.
                                </p>
                            </div>
                            <span class="text-[9px] font-bold text-blue-700">Self High • Peer Low</span>
                        </div>

                        <!-- Plotted Dot for User Position -->
                        @if($selfScore > 0 && $peerScore > 0)
                            <div class="absolute pointer-events-none transition-all duration-700 z-20"
                                 style="left: {{ $matrix['x_percent'] ?? 50 }}%; top: {{ $matrix['y_percent'] ?? 50 }}%; transform: translate(-50%, -50%);">
                                <div class="w-4 h-4 rounded-full bg-indigo-900 border-2 border-white shadow-md flex items-center justify-center animate-ping absolute opacity-75"></div>
                                <div class="w-4 h-4 rounded-full bg-indigo-900 border-2 border-white shadow-md relative"></div>
                            </div>
                        @endif
                    </div>

                    <!-- X Axis Label -->
                    <div class="mt-2 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                        <span>Low (1–3)</span>
                        <span>Self Score (What you think about yourself)</span>
                        <span>High (8–10)</span>
                    </div>
                </div>

                <!-- Matrix Subtitle Banner -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                    <span class="font-black text-slate-900">Current Diagnosis: {{ $matrix['quadrant_title'] ?? 'Aligned Strength' }}</span>
                    <p class="text-slate-600 text-[11px] leading-relaxed">{{ $matrix['quadrant_subtitle'] ?? '' }}</p>
                </div>
            </div>

            <!-- Right Card: Key Insights & Recommendations -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="lightbulb" class="w-5 h-5 text-amber-500"></i>
                        <h3 class="text-base sm:text-lg font-black text-slate-900">Key Insights</h3>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Strengths, growth areas and high-impact actions</p>
                </div>

                <div class="space-y-4">
                    <!-- Your Strengths -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5 text-xs font-black text-emerald-800 uppercase tracking-wider">
                            <i data-lucide="thumbs-up" class="w-4 h-4 text-emerald-600"></i>
                            <span>Your Strengths</span>
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-700">
                            @forelse($cq['strengths'] ?? [] as $str)
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                    <span><strong>{{ $str['dimension'] }}:</strong> Moderated score {{ $str['moderated_score'] }} / 10.</span>
                                </li>
                            @empty
                                <li class="text-slate-400 text-xs italic">Awaiting completed evaluations to identify strengths.</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Development Areas -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-1.5 text-xs font-black text-rose-800 uppercase tracking-wider">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                            <span>Development Areas</span>
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-700">
                            @forelse($cq['development_areas'] ?? [] as $dev)
                                <li class="flex items-start gap-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                    <span><strong>{{ $dev['dimension'] }}:</strong> Moderated score {{ $dev['moderated_score'] }} / 10.</span>
                                </li>
                            @empty
                                <li class="text-slate-400 text-xs italic">Awaiting evaluations to highlight development areas.</li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Top 3 Recommended Actions -->
                    <div class="space-y-2.5 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-1.5 text-xs font-black text-indigo-900 uppercase tracking-wider">
                            <i data-lucide="target" class="w-4 h-4 text-indigo-600"></i>
                            <span>Your Top 3 Recommended Actions</span>
                        </div>

                        <div class="space-y-2">
                            @foreach($cq['recommended_actions'] ?? [] as $action)
                                <div class="p-3 rounded-2xl bg-indigo-50/40 border border-indigo-100 flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-black text-xs flex items-center justify-center shrink-0 mt-0.5">
                                        {{ $action['number'] }}
                                    </span>
                                    <div class="space-y-0.5">
                                        <h5 class="text-xs font-black text-slate-900">{{ $action['title'] }}</h5>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">{{ $action['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODERATED QUESTION BREAKDOWN TABLE (11 Sequential Journey Questions) -->
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs space-y-4">
            <div class="p-6 sm:p-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        Question-Level Synthesis
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">11 Change Journey Dimensions</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        moderated_score = (self_rating + peer_average) / 2
                    </p>
                </div>

                <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-blue-500"></span> Self
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-amber-500"></span> Peer Avg
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-indigo-600"></span> Moderated
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-6 w-12 text-center">#</th>
                            <th class="py-3 px-6">Change Journey Dimension & Question</th>
                            <th class="py-3 px-4 text-center w-24">Self (1-10)</th>
                            <th class="py-3 px-4 text-center w-24">Peer Avg</th>
                            <th class="py-3 px-4 text-center w-28">Moderated</th>
                            <th class="py-3 px-6 w-44">Visual Synthesis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($cq['questions_breakdown'] ?? [] as $q)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6 text-center font-bold text-slate-400">
                                    {{ $q['number'] }}
                                </td>
                                <td class="py-4 px-6 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $q['dimension'] }}
                                        </span>
                                    </div>
                                    <div class="font-semibold text-slate-900 leading-snug">
                                        {{ $q['question_text'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-2 pt-0.5">
                                        <span>1: {{ $q['min_score_description'] }}</span>
                                        <span>•</span>
                                        <span>10: {{ $q['max_score_description'] }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-800">
                                    {{ $q['self_score'] !== null ? number_format($q['self_score'], 1) : '—' }}
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-amber-600">
                                    {{ $q['peer_avg'] !== null ? number_format($q['peer_avg'], 1) : '—' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($q['moderated_score'] !== null)
                                        <span class="inline-flex items-center justify-center font-black text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg px-2.5 py-1 text-xs">
                                            {{ number_format($q['moderated_score'], 1) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full bg-indigo-600" 
                                             style="width: {{ $q['moderated_score'] !== null ? ($q['moderated_score'] * 10) : 0 }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.app>
