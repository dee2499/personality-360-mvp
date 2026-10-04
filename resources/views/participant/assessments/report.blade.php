<x-layouts.app>
    @php
        $cqScore = (float) ($cq['overall_cq_score'] ?? 0.0);
        $selfScore = (float) ($cq['self_score'] ?? 0.0);
        $peerScore = (float) ($cq['peer_score'] ?? 0.0);
        $profileName = $cq['profile_name'] ?? 'Driver';
        $profileDisplayName = $cq['profile_display_name'] ?? 'Change Driver';
        $profileRange = $cq['profile_range'] ?? '6.1 – 8.0';
        $profileColor = $cq['profile_color'] ?? '#F59E0B';
        $profileEmoji = $cq['profile_emoji'] ?? '🚀';
        $matrix = $cq['matrix'] ?? [];

        // Needle rotation angle for 1.0 to 10.0 on semicircle:
        // Angle spans 180 degrees (-90deg at 1.0, 0deg at 5.5, +90deg at 10.0)
        $clampedScore = max(1.0, min(10.0, $cqScore > 0 ? $cqScore : 5.0));
        $needleAngle = -90 + (($clampedScore - 1.0) / 9.0) * 180;

        // Circular donut stroke dash for Self and Peer rings (r=42, circumference ~ 263.89)
        $circumference = 2 * 3.14159 * 42;
        $selfPct = max(0, min(100, ($selfScore / 10) * 100));
        $peerPct = max(0, min(100, ($peerScore / 10) * 100));
        $selfDashOffset = $circumference - ($selfPct / 100) * $circumference;
        $peerDashOffset = $circumference - ($peerPct / 100) * $circumference;

        // Matrix coordinates (percentages from 0 to 100)
        $matrixXPercent = $matrix['x_percent'] ?? round((($selfScore - 1.0) / 9.0) * 100, 1);
        $matrixYPercent = $matrix['y_percent'] ?? round(100 - ((($peerScore - 1.0) / 9.0) * 100), 1);
        $matrixQuadrant = $matrix['quadrant'] ?? 'perception_gap';

        // Color theme mappings based on current profile
        $themeCardBg = match(strtolower($profileName)) {
            'resistant', 'resistor' => 'bg-rose-50/70 border-rose-200 text-rose-950',
            'follower' => 'bg-orange-50/70 border-orange-200 text-orange-950',
            'supporter' => 'bg-emerald-50/70 border-emerald-200 text-emerald-950',
            'driver', 'initiator' => 'bg-amber-50/70 border-amber-200 text-amber-950',
            'champion', 'achiever' => 'bg-blue-50/70 border-blue-200 text-blue-950',
            default => 'bg-amber-50/70 border-amber-200 text-amber-950',
        };
        $themeBadgeBg = match(strtolower($profileName)) {
            'resistant', 'resistor' => 'bg-rose-500 text-white',
            'follower' => 'bg-orange-500 text-white',
            'supporter' => 'bg-emerald-500 text-white',
            'driver', 'initiator' => 'bg-amber-500 text-white',
            'champion', 'achiever' => 'bg-blue-600 text-white',
            default => 'bg-amber-500 text-white',
        };
        $themeTextColor = match(strtolower($profileName)) {
            'resistant', 'resistor' => 'text-rose-600',
            'follower' => 'text-orange-600',
            'supporter' => 'text-emerald-600',
            'driver', 'initiator' => 'text-amber-600',
            'champion', 'achiever' => 'text-blue-600',
            default => 'text-amber-600',
        };
    @endphp

    <div class="max-w-4xl mx-auto space-y-6 sm:space-y-8 pb-12 print:p-0 print:max-w-full">
        <!-- Top Action Bar (Back, Survey Switcher & Print) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('participant.assessments.index', ['survey_id' => $survey->id]) }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Assessments</span>
                </a>

                @if(isset($availableSurveys) && $availableSurveys->count() > 1)
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" 
                                @click="open = !open" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-indigo-600"></i>
                            <span class="max-w-[160px] truncate">{{ $survey->title }}</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                        </button>
                        <div x-show="open" 
                             @click.outside="open = false" 
                             x-cloak 
                             class="absolute left-0 mt-1 w-64 bg-white rounded-2xl border border-slate-200 shadow-xl z-30 py-2 divide-y divide-slate-100">
                            @foreach($availableSurveys as $s)
                                <a href="{{ route('participant.assessments.report', $s) }}" 
                                   class="block px-4 py-2.5 text-xs hover:bg-indigo-50/50 transition {{ $s->id === $survey->id ? 'font-bold text-indigo-700 bg-indigo-50/70' : 'text-slate-700' }}">
                                    {{ $s->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('participant.surveys.group-insights', $survey) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span>Team Insights</span>
                </a>

                <button type="button" 
                        onclick="window.print()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition shadow-2xs">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MAIN ONE-PAGE INDIVIDUAL CQ REPORT CONTAINER (Matching Image 1)          -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 space-y-8 relative overflow-hidden print:border-none print:shadow-none print:p-0">
            
            <!-- 1. Header / Identity Section -->
            <div class="space-y-6 border-b border-slate-100 pb-6">
                <!-- Top Brand Row -->
                <div class="flex items-center justify-between">
                    <!-- Brand: changequo + butterfly logo -->
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 text-indigo-600 flex items-center justify-center">
                            <!-- Custom Butterfly SVG matching changequo brand logo -->
                            <svg viewBox="0 0 48 48" class="w-9 h-9 fill-none stroke-current" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M 24,12 C 20,4 6,8 10,22 C 12,28 20,26 24,24" class="text-indigo-500 stroke-indigo-500 fill-indigo-500/10"/>
                                <path d="M 24,12 C 28,4 42,8 38,22 C 36,28 28,26 24,24" class="text-indigo-500 stroke-indigo-500 fill-indigo-500/10"/>
                                <path d="M 24,24 C 18,28 10,34 14,42 C 18,46 22,38 24,32" class="text-indigo-600 stroke-indigo-600 fill-indigo-600/10"/>
                                <path d="M 24,24 C 30,28 38,34 34,42 C 30,46 26,38 24,32" class="text-indigo-600 stroke-indigo-600 fill-indigo-600/10"/>
                                <line x1="24" y1="10" x2="24" y2="38" stroke="#1E1B4B" stroke-width="2.5"/>
                                <circle cx="24" cy="9" r="2" fill="#1E1B4B"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-none">
                                change<span class="text-indigo-600">quo</span>
                            </span>
                            <span class="text-[10px] font-bold text-indigo-600 tracking-wider uppercase mt-1">
                                Unlocking Possibilities
                            </span>
                        </div>
                    </div>

                    <!-- Right Header Title -->
                    <div class="text-right space-y-0.5">
                        <span class="block text-xs sm:text-sm font-black tracking-wider uppercase text-slate-900">
                            INDIVIDUAL CQ REPORT
                        </span>
                        <span class="block text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            YOUR CHANGE JOURNEY. A BRIGHTER YOU.
                        </span>
                    </div>
                </div>

                <!-- Greeting & Assessment Date Sub-Row -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
                    <div class="flex items-center gap-3.5">
                        <!-- User Initials Circle -->
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-700 font-extrabold text-base flex items-center justify-center shrink-0 border border-blue-200">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-xl font-black text-slate-900 leading-tight">
                                Hi {{ $user->name }},
                            </h1>
                            <p class="text-xs text-slate-500 mt-0.5 leading-snug">
                                Here is your personalised ChangeQuo (CQ) report.<br class="hidden sm:inline">
                                Based on your self-assessment and feedback from your team.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 sm:self-start bg-slate-50 px-3.5 py-2 rounded-2xl border border-slate-100">
                        <div class="w-7 h-7 rounded-xl bg-white text-slate-500 flex items-center justify-center border border-slate-200/60 shadow-2xs">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Assessment Date</span>
                            <span class="block text-xs font-black text-slate-800">
                                {{ $survey->published_at ? $survey->published_at->format('d M Y') : now()->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 2. Your ChangeQuo Profile (Speedometer Gauge Section)                      -->
            <!-- ========================================================================= -->
            <div class="space-y-6">
                <!-- Section Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                            Your <span class="text-indigo-600">ChangeQuo</span> Profile
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Your current position in the change journey</p>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                        <span>Score is on a scale of 1 – 10</span>
                        <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                    </div>
                </div>

                <!-- Semicircular Speedometer Gauge Canvas -->
                <div class="bg-gradient-to-b from-slate-50/80 via-white to-white rounded-3xl p-6 sm:p-8 border border-slate-100 flex flex-col items-center relative overflow-hidden">
                    <div class="w-full max-w-[460px] aspect-[2.1/1] relative flex items-end justify-center">
                        <svg viewBox="0 0 340 180" class="w-full h-full overflow-visible">
                            <defs>
                                <filter id="needle-shadow" x="-30%" y="-30%" width="160%" height="160%">
                                    <feDropShadow dx="0" dy="3" stdDeviation="4" flood-opacity="0.25"/>
                                </filter>
                            </defs>

                            <!-- ========================================== -->
                            <!-- 5 Speedometer Colored Arcs (Equal 36° each) -->
                            <!-- Center at (170, 160), Radius 125, Width 30  -->
                            <!-- ========================================== -->

                            <!-- 1. Resistant (Red): 180° to 144° (1.0 – 2.0) -->
                            <path d="M 45,160 A 125,125 0 0,1 68.9,86.5" 
                                  fill="none" stroke="#EF4444" stroke-width="32" stroke-linecap="round"/>

                            <!-- 2. Follower (Orange): 144° to 108° (2.1 – 4.0) -->
                            <path d="M 70,85 A 125,125 0 0,1 131.4,41.1" 
                                  fill="none" stroke="#F97316" stroke-width="32"/>

                            <!-- 3. Supporter (Green): 108° to 72° (4.1 – 6.0) -->
                            <path d="M 133,40.5 A 125,125 0 0,1 207,40.5" 
                                  fill="none" stroke="#10B981" stroke-width="32"/>

                            <!-- 4. Driver (Amber/Yellow): 72° to 36° (6.1 – 8.0) -->
                            <path d="M 208.6,41.1 A 125,125 0 0,1 270,85" 
                                  fill="none" stroke="#F59E0B" stroke-width="32"/>

                            <!-- 5. Champion (Blue): 36° to 0° (8.1 – 10.0) -->
                            <path d="M 271.1,86.5 A 125,125 0 0,1 295,160" 
                                  fill="none" stroke="#3B82F6" stroke-width="32" stroke-linecap="round"/>

                            <!-- Inner White Cutout Arc (Radius 88) -->
                            <path d="M 78,160 A 92,92 0 0,1 262,160" fill="#FFFFFF" stroke="#F8FAFC" stroke-width="2"/>

                            <!-- ========================================== -->
                            <!-- Segment Icons on Arcs                      -->
                            <!-- ========================================== -->
                            <!-- Resistant Shield -->
                            <g transform="translate(56, 120)" text-anchor="middle" fill="#FFFFFF">
                                <path d="M -5,-6 L 5,-6 L 5,1 Q 5,7 0,9 Q -5,7 -5,1 Z" fill="#FFFFFF"/>
                            </g>
                            <!-- Follower People -->
                            <g transform="translate(98, 62)" text-anchor="middle" fill="#FFFFFF">
                                <circle cx="-3" cy="-4" r="2.5"/>
                                <path d="M -6,3 C -6,0 0,0 0,3"/>
                                <circle cx="3" cy="-4" r="2.5"/>
                                <path d="M 0,3 C 0,0 6,0 6,3"/>
                            </g>
                            <!-- Supporter Sprout -->
                            <g transform="translate(170, 24)" text-anchor="middle" fill="#FFFFFF">
                                <path d="M 0,6 L 0,-2 C 0,-6 -6,-6 -6,-2 C -6,2 0,6 0,6 Z"/>
                                <path d="M 0,6 L 0,-2 C 0,-6 6,-6 6,-2 C 6,2 0,6 0,6 Z"/>
                            </g>
                            <!-- Driver Rocket -->
                            <g transform="translate(242, 62)" text-anchor="middle" fill="#FFFFFF">
                                <path d="M 0,-6 C 4,-4 5,2 4,6 L -4,6 C -5,2 -4,-4 0,-6 Z"/>
                                <path d="M -4,3 L -7,6 L -4,6 Z"/>
                                <path d="M 4,3 L 7,6 L 4,6 Z"/>
                            </g>
                            <!-- Champion Mountain Flag -->
                            <g transform="translate(284, 120)" text-anchor="middle" fill="#FFFFFF">
                                <path d="M -6,6 L 0,-4 L 6,6 Z"/>
                                <line x1="0" y1="-4" x2="0" y2="-9" stroke="#FFFFFF" stroke-width="1.2"/>
                                <polygon points="0,-9 5,-7 0,-5" fill="#FFFFFF"/>
                            </g>

                            <!-- ========================================== -->
                            <!-- Segment Labels & Ranges                    -->
                            <!-- ========================================== -->
                            <!-- 1. Resistant -->
                            <g transform="translate(68, 142)" text-anchor="middle">
                                <text x="0" y="0" font-size="8.5" font-weight="800" fill="#991B1B">Resistant</text>
                                <text x="0" y="10" font-size="7.5" font-weight="600" fill="#DC2626">1.0 – 2.0</text>
                            </g>
                            <!-- 2. Follower -->
                            <g transform="translate(108, 92)" text-anchor="middle">
                                <text x="0" y="0" font-size="8.5" font-weight="800" fill="#C2410C">Follower</text>
                                <text x="0" y="10" font-size="7.5" font-weight="600" fill="#EA580C">2.1 – 4.0</text>
                            </g>
                            <!-- 3. Supporter -->
                            <g transform="translate(170, 68)" text-anchor="middle">
                                <text x="0" y="0" font-size="8.5" font-weight="800" fill="#065F46">Supporter</text>
                                <text x="0" y="10" font-size="7.5" font-weight="600" fill="#059669">4.1 – 6.0</text>
                            </g>
                            <!-- 4. Driver -->
                            <g transform="translate(232, 92)" text-anchor="middle">
                                <text x="0" y="0" font-size="8.5" font-weight="800" fill="#92400E">Driver</text>
                                <text x="0" y="10" font-size="7.5" font-weight="600" fill="#D97706">6.1 – 8.0</text>
                            </g>
                            <!-- 5. Champion -->
                            <g transform="translate(272, 142)" text-anchor="middle">
                                <text x="0" y="0" font-size="8.5" font-weight="800" fill="#1E40AF">Champion</text>
                                <text x="0" y="10" font-size="7.5" font-weight="600" fill="#2563EB">8.1 – 10.0</text>
                            </g>

                            <!-- ========================================== -->
                            <!-- The Speedometer Needle (Pivot at 170, 160) -->
                            <!-- ========================================== -->
                            <g transform="translate(170, 160) rotate({{ $needleAngle }})" filter="url(#needle-shadow)">
                                <path d="M -4.5,0 L -1.5,-122 L 0,-130 L 1.5,-122 L 4.5,0 Z" fill="#0F172A"/>
                                <circle cx="0" cy="0" r="9" fill="#0F172A"/>
                                <circle cx="0" cy="0" r="4.5" fill="#FFFFFF"/>
                            </g>

                            <!-- ========================================== -->
                            <!-- Center Score Readout                       -->
                            <!-- ========================================== -->
                            <text x="170" y="128" text-anchor="middle" font-size="8.5" font-weight="700" fill="#64748B" letter-spacing="0.5">Your ChangeQuo Score</text>
                            <text x="170" y="152" text-anchor="middle" font-size="30" font-weight="900" fill="#0F172A">{{ number_format($cqScore, 1) }}</text>
                            <text x="170" y="167" text-anchor="middle" font-size="12" font-weight="800" fill="#1E1B4B">{{ $profileName }}</text>
                        </svg>
                    </div>

                    <!-- Profile Explanation Banner (Matching Image 1) -->
                    <div class="w-full mt-4 p-5 sm:p-6 rounded-2xl border flex flex-col md:flex-row md:items-center md:justify-between gap-5 {{ $themeCardBg }}">
                        <div class="flex items-start gap-4">
                            <!-- Circular Archetype Badge -->
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-xs text-xl {{ $themeBadgeBg }}">
                                {{ $profileEmoji }}
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base font-black">
                                    You are a <span class="{{ $themeTextColor }}">{{ $profileDisplayName }}</span>
                                </h3>
                                <p class="text-xs text-slate-700 leading-relaxed max-w-2xl">
                                    {{ $cq['profile_description'] }}
                                </p>
                            </div>
                        </div>

                        <!-- Right CQ Range Box -->
                        <div class="shrink-0 bg-white/90 backdrop-blur-xs px-5 py-3 rounded-2xl border border-black/5 text-left md:text-right shadow-2xs">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Your CQ Range</span>
                            <span class="text-2xl font-black {{ $themeTextColor }} tracking-tight">
                                {{ $profileRange }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 3. Two Score Cards: Self Score vs Peer Score (Average) (Matching Image 1)  -->
            <!-- ========================================================================= -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Card 1: Your Self Score -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/60">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">Your Self Score</h3>
                                <p class="text-xs text-slate-400 mt-0.5">What you feel about yourself</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">
                            <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                            <span>Confidential</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <!-- Circular Donut SVG Ring (Blue) -->
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
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400">
                                <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                <span>Confidential</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                For your self consumption only. Not to be shared with others.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Peer Score (Average) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100/60">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">Peer Score (Average)</h3>
                                <p class="text-xs text-slate-400 mt-0.5">What others think about you</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">
                            <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                            <span>Confidential</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <!-- Circular Donut SVG Ring (Amber/Orange) -->
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
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400">
                                <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                <span>Confidential</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                For your self consumption only. Not to be shared with others.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- 4. Bottom Row: Left 2x2 Matrix & Right Key Insights (Matching Image 1)    -->
            <!-- ========================================================================= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                
                <!-- Left Column: Self vs Peer Insight 2x2 Coordinate Grid -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="w-5 h-5 text-indigo-600"></i>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                Self vs Peer Insight
                            </h3>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Understand your strength and blind spots</p>
                    </div>

                    <!-- 2x2 Matrix Coordinate Chart with Axis Labels -->
                    <div class="relative pt-2 pb-1">
                        <!-- Y-Axis Header: High (8-10) -->
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>High (8–10)</span>
                            <span class="text-slate-400 normal-case font-medium"></span>
                        </div>

                        <div class="relative flex">
                            <!-- Left Y-Axis Label (Rotated or Vertical) -->
                            <div class="w-8 shrink-0 flex items-center justify-center relative">
                                <span class="text-[9px] font-extrabold text-slate-500 uppercase tracking-wider -rotate-90 whitespace-nowrap absolute">
                                    Peer Score (What others think)
                                </span>
                            </div>

                            <!-- The 2x2 Grid Canvas -->
                            <div class="flex-1 relative aspect-square max-h-[300px] bg-slate-50/50 rounded-2xl border border-slate-200 overflow-hidden">
                                <!-- Horizontal & Vertical Grid Divider Lines -->
                                <div class="absolute inset-x-0 top-1/2 border-b border-dashed border-slate-300 z-0"></div>
                                <div class="absolute inset-y-0 left-1/2 border-r border-dashed border-slate-300 z-0"></div>

                                <div class="grid grid-cols-2 grid-rows-2 h-full w-full relative z-10 p-1.5 gap-1.5">
                                    <!-- Quadrant 1 (Top-Left): Undervalued Potential -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'undervalued_potential' ? 'bg-amber-100/80 border-amber-400 shadow-xs ring-2 ring-amber-400/30' : 'bg-amber-50/50 border-amber-200/50' }}">
                                        <div>
                                            <h4 class="font-black text-amber-950 text-xs">Undervalued Potential</h4>
                                            <p class="text-[10px] text-amber-900/80 mt-1 leading-tight">
                                                Others see you stronger than you see yourself. Build confidence.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quadrant 2 (Top-Right): Aligned Strength -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'aligned_strength' ? 'bg-emerald-100/80 border-emerald-400 shadow-xs ring-2 ring-emerald-400/30' : 'bg-emerald-50/50 border-emerald-200/50' }}">
                                        <div>
                                            <h4 class="font-black text-emerald-950 text-xs">Aligned Strength</h4>
                                            <p class="text-[10px] text-emerald-900/80 mt-1 leading-tight">
                                                You and others see you similarly. Keep doing what works.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quadrant 3 (Bottom-Left): Key Development Area -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'key_development' ? 'bg-rose-100/80 border-rose-400 shadow-xs ring-2 ring-rose-400/30' : 'bg-rose-50/50 border-rose-200/50' }}">
                                        <div>
                                            <h4 class="font-black text-rose-950 text-xs">Key Development Area</h4>
                                            <p class="text-[10px] text-rose-900/80 mt-1 leading-tight">
                                                Both you and others see gaps. Focus on building core change capabilities.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quadrant 4 (Bottom-Right): Perception Gap -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'perception_gap' ? 'bg-blue-100/80 border-blue-400 shadow-xs ring-2 ring-blue-400/30' : 'bg-blue-50/50 border-blue-200/50' }}">
                                        <div>
                                            <h4 class="font-black text-blue-950 text-xs">Perception Gap</h4>
                                            <p class="text-[10px] text-blue-900/80 mt-1 leading-tight">
                                                You see yourself stronger than others currently experience. Increase visibility and collaboration.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Plotted Coordinate Dot and Dashed Guide Lines (Matching Image 1) -->
                                @if($selfScore > 0 && $peerScore > 0)
                                    <!-- Dashed Line to Left Y-Axis -->
                                    <div class="absolute border-t border-dashed border-indigo-900/80 pointer-events-none z-20"
                                         style="left: 0; top: {{ $matrixYPercent }}%; width: {{ $matrixXPercent }}%;"></div>

                                    <!-- Dashed Line to Bottom X-Axis -->
                                    <div class="absolute border-l border-dashed border-indigo-900/80 pointer-events-none z-20"
                                         style="left: {{ $matrixXPercent }}%; top: {{ $matrixYPercent }}%; bottom: 0;"></div>

                                    <!-- Coordinate Position Dot -->
                                    <div class="absolute pointer-events-none z-30 transition-all duration-700"
                                         style="left: {{ $matrixXPercent }}%; top: {{ $matrixYPercent }}%; transform: translate(-50%, -50%);">
                                        <div class="w-3.5 h-3.5 rounded-full bg-indigo-950 border-2 border-white shadow-md"></div>
                                    </div>

                                    <!-- Y-Axis Score Pill Tag (on left edge) -->
                                    <div class="absolute z-20 pointer-events-none transition-all duration-700 -translate-y-1/2 left-1.5"
                                         style="top: {{ $matrixYPercent }}%;">
                                        <span class="inline-block px-1.5 py-0.5 rounded-md bg-indigo-950 text-white font-extrabold text-[9px] shadow-sm">
                                            {{ number_format($peerScore, 1) }}
                                        </span>
                                    </div>

                                    <!-- X-Axis Score Pill Tag (on bottom edge) -->
                                    <div class="absolute z-20 pointer-events-none transition-all duration-700 -translate-x-1/2 bottom-1.5"
                                         style="left: {{ $matrixXPercent }}%;">
                                        <span class="inline-block px-1.5 py-0.5 rounded-md bg-indigo-950 text-white font-extrabold text-[9px] shadow-sm">
                                            {{ number_format($selfScore, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Y-Axis Bottom Label: Low (1-3) -->
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider pl-8 mt-1">
                            <span>Low (1–3)</span>
                        </div>

                        <!-- X-Axis Labels: Low (1-3) on left, Title in center, High (8-10) on right -->
                        <div class="flex items-center justify-between text-[10px] text-slate-400 uppercase tracking-wider pl-8 pt-1">
                            <span>Low (1–3)</span>
                            <span class="text-slate-500 font-extrabold normal-case text-center">Self Score (What you think about yourself)</span>
                            <span>High (8–10)</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Key Insights & Your Top 3 Recommended Actions -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-6">
                    
                    <!-- Top Sub-Section: Key Insights -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600"></i>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Key Insights</h3>
                        </div>

                        <!-- Strengths List -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i>
                                </span>
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Your Strengths</h4>
                            </div>
                            <ul class="space-y-1.5 pl-1">
                                @forelse($cq['strengths'] ?? [] as $str)
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <span>{{ $str['statement'] ?? $str['dimension'] }}</span>
                                    </li>
                                @empty
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <span>You are proactive in dealing with change.</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <span>You show confidence in navigating uncertainty.</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                        <span>You take ownership and influence others.</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Development Areas List -->
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                                </span>
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Development Areas</h4>
                            </div>
                            <ul class="space-y-1.5 pl-1">
                                @forelse($cq['development_areas'] ?? [] as $dev)
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                        <span>{{ $dev['statement'] ?? $dev['dimension'] }}</span>
                                    </li>
                                @empty
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                        <span>Be more visible in seeking and using support.</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                        <span>Increase consistency in follow-through.</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-xs text-slate-700 leading-snug">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                        <span>Enable and support others more regularly.</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Bottom Sub-Section: Your Top 3 Recommended Actions -->
                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <i data-lucide="target" class="w-5 h-5 text-purple-600"></i>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">
                                Your Top 3 Recommended Actions
                            </h3>
                        </div>

                        <div class="space-y-3">
                            @foreach($cq['recommended_actions'] ?? [] as $action)
                                @php
                                    $num = $action['number'] ?? 1;
                                    $circleColor = match($num) {
                                        1 => 'bg-blue-600 text-white',
                                        2 => 'bg-amber-500 text-white',
                                        3 => 'bg-emerald-600 text-white',
                                        default => 'bg-indigo-600 text-white',
                                    };
                                @endphp
                                <div class="flex items-start gap-3 p-3 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                                    <span class="w-6 h-6 rounded-full {{ $circleColor }} font-black text-xs flex items-center justify-center shrink-0 mt-0.5">
                                        {{ $num }}
                                    </span>
                                    <div class="space-y-0.5 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-xs font-bold text-slate-900">{{ $action['title'] }}</h5>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $action['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- 5. COLLAPSIBLE ACCORDION: 11 CHANGE JOURNEY DIMENSIONS TABLE              -->
        <!-- Provides question-level synthesis, transparent moderation formula,        -->
        <!-- and backward compatibility with feature test suites.                      -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs" 
             x-data="{ expanded: false }">
            <!-- Accordion Toggle Header -->
            <button type="button" 
                    @click="expanded = !expanded" 
                    class="w-full p-6 text-left flex items-center justify-between gap-4 hover:bg-slate-50/50 transition cursor-pointer">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-100">
                            Question-Level Synthesis
                        </span>
                        <span class="text-xs text-slate-400">• {{ $cq['cq3']['category'] ?? 'Calibrated' }}</span>
                    </div>
                    <h3 class="text-base font-black text-slate-900">
                        11 Change Journey Dimensions
                    </h3>
                    <p class="text-xs text-slate-500">
                        Moderation formula: moderated_score = (self_rating + peer_average) / 2
                    </p>
                </div>

                <div class="flex items-center gap-2 text-xs font-bold text-indigo-600 bg-indigo-50/60 px-3.5 py-2 rounded-xl border border-indigo-100 shrink-0">
                    <span x-text="expanded ? 'Hide Dimension Table' : 'View Dimension Table'"></span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                </div>
            </button>

            <!-- Collapsible Table Content -->
            <div x-show="expanded" x-collapse>
                <div class="border-t border-slate-100 overflow-x-auto">
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

    </div>
</x-layouts.app>
