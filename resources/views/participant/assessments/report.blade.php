<x-layouts.app>
    @php
        $cqScore = (float) ($cq['overall_cq_score'] ?? 0.0);
        $selfScore = (float) ($cq['self_score'] ?? 0.0);
        $peerScore = (float) ($cq['peer_score'] ?? 0.0);
        $profileName = $cq['profile_name'] ?? 'Driver';
        $profileDisplayName = $cq['profile_display_name'] ?? 'Change Driver';
        $profileRange = $cq['profile_range'] ?? '6.1 – 8.0';
        $profileColor = $cq['profile_color'] ?? '#F59E0B';
        $matrix = $cq['matrix'] ?? [];
        $hasData = ($cqScore > 0 || $selfScore > 0 || $peerScore > 0);

        $meterTierName = match($profileName) {
            'Resistor', 'Resistant' => 'Resistant',
            'Follower' => 'Follower',
            'Supporter' => 'Supporter',
            'Initiator', 'Driver' => 'Driver',
            'Achiever', 'Champion' => 'Champion',
            default => $profileName,
        };

        // Calibrated Needle rotation angle on a 1.0 – 10.0 scale (matching theme template gauge):
        // 1.0 (minimum / start of Resistant) => -90 deg
        // 5.5 (center of Supporter) => 0 deg (straight up)
        // 6.9 (Driver) => +28 deg
        // 10.0 (end of Champion) => +90 deg (far right)
        if (! $hasData || $cqScore <= 0) {
            $needleAngle = -90;
        } else {
            $clampedScore = max(1.0, min(10.0, (float) $cqScore));
            $needleAngle = round(-90 + ((($clampedScore - 1.0) / 9.0) * 180), 2);
            $needleAngle = max(-90, min(90, $needleAngle));
        }

        $tierTheme = match($meterTierName) {
            'Resistant' => [
                'bg' => 'bg-rose-50/80',
                'border' => 'border-rose-200/90',
                'text' => 'text-rose-950',
                'accent' => 'text-rose-600',
                'badgeBg' => 'bg-rose-100',
                'badgeText' => 'text-rose-600',
                'badgeBorder' => 'border-rose-200',
                'icon' => 'shield',
            ],
            'Follower' => [
                'bg' => 'bg-orange-50/80',
                'border' => 'border-orange-200/90',
                'text' => 'text-orange-950',
                'accent' => 'text-orange-600',
                'badgeBg' => 'bg-orange-100',
                'badgeText' => 'text-orange-600',
                'badgeBorder' => 'border-orange-200',
                'icon' => 'users',
            ],
            'Supporter' => [
                'bg' => 'bg-emerald-50/80',
                'border' => 'border-emerald-200/90',
                'text' => 'text-emerald-950',
                'accent' => 'text-emerald-600',
                'badgeBg' => 'bg-emerald-100',
                'badgeText' => 'text-emerald-600',
                'badgeBorder' => 'border-emerald-200',
                'icon' => 'sprout',
            ],
            'Champion' => [
                'bg' => 'bg-sky-50/80',
                'border' => 'border-sky-200/90',
                'text' => 'text-sky-950',
                'accent' => 'text-sky-600',
                'badgeBg' => 'bg-sky-100',
                'badgeText' => 'text-sky-600',
                'badgeBorder' => 'border-sky-200',
                'icon' => 'trophy',
            ],
            default => [ // Driver
                'bg' => 'bg-amber-50/80',
                'border' => 'border-amber-200/90',
                'text' => 'text-amber-950',
                'accent' => 'text-amber-600',
                'badgeBg' => 'bg-amber-100',
                'badgeText' => 'text-amber-600',
                'badgeBorder' => 'border-amber-200',
                'icon' => 'rocket',
            ],
        };

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
    @endphp

    <div class="max-w-4xl mx-auto space-y-6 sm:space-y-8 pb-12 print:p-0 print:max-w-full">
        <!-- Top Action Bar (Back, Survey Switcher & Print) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('participant.assessments.index', ['survey_id' => $survey->id, 'view' => 'matrix']) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition shadow-2xs">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-indigo-600"></i>
                    <span>View Detailed Matrix</span>
                </a>

                @if(isset($availableSurveys) && $availableSurveys->count() > 1)
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" 
                                @click="open = !open" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs cursor-pointer">
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
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition shadow-2xs cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MAIN ONE-PAGE INDIVIDUAL CQ REPORT CONTAINER (Matching Image 1)          -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-10 space-y-8 relative overflow-hidden print:border-none print:shadow-none print:p-0">
            
            <!-- 1. Header / Identity Section -->
            <div class="space-y-6 border-b border-slate-100 pb-6">
                <!-- Top Brand Row -->
                <div class="flex items-center justify-between">
                    <!-- Brand: changequo with custom butterfly icon -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 text-purple-700 flex items-center justify-center">
                            <!-- Custom Butterfly SVG matching changequo brand logo -->
                            <svg viewBox="0 0 48 48" class="w-10 h-10 fill-none stroke-current" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M 24,14 C 20,6 6,10 10,22 C 12,28 19,26 24,24" class="text-purple-600 stroke-purple-600 fill-purple-600/10"/>
                                <path d="M 24,14 C 28,6 42,10 38,22 C 36,28 29,26 24,24" class="text-purple-600 stroke-purple-600 fill-purple-600/10"/>
                                <path d="M 24,24 C 18,27 11,33 15,41 C 18,45 22,37 24,31" class="text-purple-700 stroke-purple-700 fill-purple-700/10"/>
                                <path d="M 24,24 C 30,27 37,33 33,41 C 30,45 26,37 24,31" class="text-purple-700 stroke-purple-700 fill-purple-700/10"/>
                                <line x1="24" y1="12" x2="24" y2="38" stroke="#1E1B4B" stroke-width="2.5"/>
                                <circle cx="24" cy="10" r="2" fill="#1E1B4B"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">
                                change<span class="text-purple-700">quo</span>
                            </span>
                            <span class="text-[10px] font-bold text-purple-700 tracking-wider uppercase mt-1">
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
            <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-7 shadow-xs space-y-4">
                <!-- Section Header -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-black tracking-tight text-[#0a0f37]">
                            Your <span class="text-[#6f01d2]">ChangeQuo</span> Profile
                        </h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Your current position in the change journey</p>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium shrink-0 pt-0.5">
                        <span>Score is on a scale of 1 – 10</span>
                        <i data-lucide="info" class="w-4 h-4 text-[#0284c7]"></i>
                    </div>
                </div>

                <!-- Semicircular Speedometer Gauge Canvas -->
                <div class="w-full max-w-[480px] mx-auto aspect-[2/1.05] relative flex items-end justify-center select-none pt-2"
                     x-data="{ mounted: false }"
                     x-init="setTimeout(() => mounted = true, 50)">
                    <svg viewBox="0 0 400 215" class="w-full h-full overflow-visible select-none">
                        <defs>
                            <!-- Needle Drop Shadow -->
                            <filter id="gaugeNeedleShadow" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow dx="0" dy="2.5" stdDeviation="3" flood-opacity="0.25"/>
                            </filter>
                            <!-- Inner Dome Elevation Shadow -->
                            <filter id="hubElevationShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="-2" stdDeviation="4" flood-opacity="0.08"/>
                            </filter>
                        </defs>

                        <!-- ============================================================== -->
                        <!-- 5 Speedometer Segment Wedges (Center 200, 200 | Ro 180, Ri 92) -->
                        <!-- ============================================================== -->

                        <!-- Sector 1: Resistant (Solid Crimson Red #e11d48) -->
                        <path d="M 108.0,200.0 L 20.0,200.0 A 180,180 0 0,1 54.38,94.20 L 127.19,147.10 A 92,92 0 0,0 108.0,200.0 Z" 
                              fill="#e11d48" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

                        <!-- Sector 2: Follower (Soft Peach/Coral #fed7aa) -->
                        <path d="M 127.19,147.10 L 54.38,94.20 A 180,180 0 0,1 144.38,28.80 L 172.19,114.40 A 92,92 0 0,0 127.19,147.10 Z" 
                              fill="#fed7aa" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

                        <!-- Sector 3: Supporter (Soft Mint #bbf7d0) -->
                        <path d="M 172.19,114.40 L 144.38,28.80 A 180,180 0 0,1 255.62,28.80 L 227.81,114.40 A 92,92 0 0,0 172.19,114.40 Z" 
                              fill="#bbf7d0" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

                        <!-- Sector 4: Driver (Warm Sunny Yellow #fde047) -->
                        <path d="M 227.81,114.40 L 255.62,28.80 A 180,180 0 0,1 345.62,94.20 L 272.81,147.10 A 92,92 0 0,0 227.81,114.40 Z" 
                              fill="#fde047" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

                        <!-- Sector 5: Champion (Solid Royal Blue #0284c7) -->
                        <path d="M 272.81,147.10 L 345.62,94.20 A 180,180 0 0,1 380.0,200.0 L 292.0,200.0 A 92,92 0 0,0 272.81,147.10 Z" 
                              fill="#0284c7" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

                        <!-- ============================================================== -->
                        <!-- Sector 1: Resistant (Red) Content                              -->
                        <!-- ============================================================== -->
                        <!-- White Shield Icon with Check -->
                        <g transform="translate(68, 140)">
                            <path d="M 0,-11 L 8.5,-7 L 8.5,2 C 8.5,8 0,12.5 0,12.5 C 0,12.5 -8.5,8 -8.5,2 L -8.5,-7 Z" fill="#FFFFFF"/>
                            <path d="M -3.5,1.5 L -1,4 L 4,-1.5" stroke="#e11d48" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </g>
                        <text x="68" y="165" text-anchor="middle" font-size="11" font-weight="900" fill="#FFFFFF">Resistant</text>
                        <text x="68" y="179" text-anchor="middle" font-size="9.5" font-weight="700" fill="#ffe4e6">1.0 – 2.0</text>

                        <!-- ============================================================== -->
                        <!-- Sector 2: Follower (Peach) Content                            -->
                        <!-- ============================================================== -->
                        <!-- Orange Two-People Group Icon -->
                        <g transform="translate(122, 70)" fill="#ea580c">
                            <circle cx="-4" cy="-4" r="3.2"/>
                            <path d="M -8.5,4 C -8.5,0.5 -1.5,0.5 -1.5,4 Z"/>
                            <circle cx="4" cy="-4" r="3.2"/>
                            <path d="M -0.5,4 C -0.5,0.5 8.5,0.5 8.5,4 Z"/>
                        </g>
                        <text x="122" y="96" text-anchor="middle" font-size="11" font-weight="900" fill="#0a0f37">Follower</text>
                        <text x="122" y="110" text-anchor="middle" font-size="9.5" font-weight="700" fill="#475569">2.1 – 4.0</text>

                        <!-- ============================================================== -->
                        <!-- Sector 3: Supporter (Mint Green) Content                       -->
                        <!-- ============================================================== -->
                        <!-- Green Plant Sprout Icon -->
                        <g transform="translate(200, 42)" fill="#047857">
                            <path d="M 0,10 L 0,0 C 0,-6.5 -8,-6.5 -8,-0.5 C -8,4.5 0,10 0,10 Z"/>
                            <path d="M 0,10 L 0,0 C 0,-6.5 8,-6.5 8,-0.5 C 8,4.5 0,10 0,10 Z"/>
                        </g>
                        <text x="200" y="68" text-anchor="middle" font-size="11" font-weight="900" fill="#0a0f37">Supporter</text>
                        <text x="200" y="82" text-anchor="middle" font-size="9.5" font-weight="700" fill="#475569">4.1 – 6.0</text>

                        <!-- ============================================================== -->
                        <!-- Sector 4: Driver (Yellow) Content                              -->
                        <!-- ============================================================== -->
                        <!-- Amber Angled Rocket Icon -->
                        <g transform="translate(280, 70)">
                            <g transform="rotate(45)" fill="#b45309">
                                <path d="M 0,-10 C 4.5,-7 6.5,1 4.5,6.5 L -4.5,6.5 C -6.5,1 -4.5,-7 0,-10 Z"/>
                                <path d="M -4.5,3 L -9,8 L -4,8 Z"/>
                                <path d="M 4.5,3 L 9,8 L 4,8 Z"/>
                                <circle cx="0" cy="0" r="1.8" fill="#FFFFFF"/>
                            </g>
                        </g>
                        <text x="280" y="96" text-anchor="middle" font-size="11" font-weight="900" fill="#0a0f37">Driver</text>
                        <text x="280" y="110" text-anchor="middle" font-size="9.5" font-weight="700" fill="#475569">6.1 – 8.0</text>

                        <!-- ============================================================== -->
                        <!-- Sector 5: Champion (Royal Blue) Content                        -->
                        <!-- ============================================================== -->
                        <!-- White Mountain with Flag Icon -->
                        <g transform="translate(332, 140)" fill="#FFFFFF">
                            <path d="M -10,10 L 0,-3 L 10,10 Z"/>
                            <line x1="0" y1="-3" x2="0" y2="-12" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>
                            <polygon points="0,-12 8,-9 0,-6"/>
                        </g>
                        <text x="332" y="165" text-anchor="middle" font-size="11" font-weight="900" fill="#FFFFFF">Champion</text>
                        <text x="332" y="179" text-anchor="middle" font-size="9.5" font-weight="700" fill="#e0f2fe">8.1 – 10.0</text>

                        <!-- ============================================================== -->
                        <!-- The Speedometer Needle (Pivot at 200, 200, Base moved UP to r=-86) -->
                        <!-- ============================================================== -->
                        <g transform="translate(200, 200)">
                            <g :style="`transform: rotate(${mounted ? {{ $needleAngle }} : -90}deg); transform-origin: 0 0; transition: transform 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);`"
                               style="transform: rotate({{ $needleAngle }}deg); transform-origin: 0 0;"
                               filter="url(#gaugeNeedleShadow)">
                                <polygon points="-5.5,-86 -1.2,-166 0,-172 1.2,-166 5.5,-86" fill="#0a0f37"/>
                                <polygon points="0,-86 0,-172 1.2,-166 5.5,-86" fill="#1e293b"/>
                                <circle cx="0" cy="-86" r="8.5" fill="#0a0f37"/>
                                <circle cx="0" cy="-86" r="3.5" fill="#FFFFFF"/>
                            </g>
                        </g>

                        <!-- ============================================================== -->
                        <!-- Inner Elevated White Cutout Semicircle (Radius 90 at 200, 200) -->
                        <!-- ============================================================== -->
                        <path d="M 110,200 A 90,90 0 0,1 290,200 Z" fill="#FFFFFF" filter="url(#hubElevationShadow)"/>

                        <!-- ============================================================== -->
                        <!-- Center Readout Inside White Hub (100% Unobstructed)            -->
                        <!-- ============================================================== -->
                        <text x="200" y="138" text-anchor="middle" font-size="10.5" font-weight="700" fill="#64748b" letter-spacing="0.2">Your</text>
                        <text x="200" y="152" text-anchor="middle" font-size="12" font-weight="800" fill="#0a0f37">ChangeQuo Score</text>
                        <text x="200" y="186" text-anchor="middle" font-size="36" font-weight="900" fill="#0a0f37">{{ $hasData && $cqScore > 0 ? number_format($cqScore, 1) : '0.0' }}</text>
                        <text x="200" y="202" text-anchor="middle" font-size="13" font-weight="900" fill="#0a0f37">{{ $hasData && $cqScore > 0 ? $meterTierName : 'Pending' }}</text>
                    </svg>
                </div>

                <!-- Profile Explanation Banner (Matching Theme Template) -->
                <div class="w-full mt-2 p-4 sm:p-5 rounded-2xl border flex flex-col md:flex-row md:items-center md:justify-between gap-4 {{ $tierTheme['bg'] }} {{ $tierTheme['border'] }} {{ $tierTheme['text'] }}">
                    <div class="flex items-start gap-3.5">
                        <!-- Icon Badge -->
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 shadow-2xs {{ $tierTheme['badgeBg'] }} {{ $tierTheme['badgeText'] }} border {{ $tierTheme['badgeBorder'] }}">
                            <i data-lucide="{{ $tierTheme['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-0.5">
                            <h3 class="text-sm sm:text-base font-black">
                                You are a <span class="{{ $tierTheme['accent'] }} font-black">{{ $profileDisplayName }}</span>
                                <span class="sr-only">{{ $profileName }}</span>
                            </h3>
                            <p class="text-xs text-slate-700 leading-relaxed max-w-2xl">
                                {{ $cq['profile_description'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Right CQ Range Box -->
                    <div class="shrink-0 bg-white/95 px-4 py-2.5 rounded-xl border {{ $tierTheme['border'] }} text-left md:text-right shadow-2xs">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Your CQ Range</span>
                        <span class="text-2xl font-black {{ $tierTheme['accent'] }} tracking-tight leading-none mt-0.5 block">
                            {{ $profileRange }}
                        </span>
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
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
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
                                For your self consumption only.<br>Not to be shared with others.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Peer Score (Average) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
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
                                For your self consumption only.<br>Not to be shared with others.
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
                            <i data-lucide="bar-chart-2" class="w-5 h-5 text-purple-600"></i>
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
                                    Peer Score (What others think about you)
                                </span>
                            </div>

                            <!-- The 2x2 Grid Canvas -->
                            <div class="flex-1 relative aspect-square max-h-[300px] bg-slate-50/50 rounded-2xl border border-slate-200 overflow-hidden">
                                <!-- Horizontal & Vertical Grid Divider Lines -->
                                <div class="absolute inset-x-0 top-1/2 border-b border-dashed border-slate-300 z-0"></div>
                                <div class="absolute inset-y-0 left-1/2 border-r border-dashed border-slate-300 z-0"></div>

                                <div class="grid grid-cols-2 grid-rows-2 h-full w-full relative z-10 p-1.5 gap-1.5">
                                    <!-- Quadrant 1 (Top-Left): Undervalued Potential -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'undervalued_potential' ? 'bg-amber-100/80 border-amber-400 shadow-xs ring-2 ring-amber-400/30' : 'bg-amber-50/60 border-amber-200/60' }}">
                                        <div>
                                            <h4 class="font-black text-amber-950 text-xs">Undervalued Potential</h4>
                                            <p class="text-[10px] text-amber-900/80 mt-1 leading-tight">
                                                Others see you stronger than you see yourself. Build confidence.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quadrant 2 (Top-Right): Aligned Strength -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'aligned_strength' ? 'bg-emerald-100/80 border-emerald-400 shadow-xs ring-2 ring-emerald-400/30' : 'bg-emerald-50/60 border-emerald-200/60' }}">
                                        <div>
                                            <h4 class="font-black text-emerald-950 text-xs">Aligned Strength</h4>
                                            <p class="text-[10px] text-emerald-900/80 mt-1 leading-tight">
                                                You and others see you similarly. Keep doing what works.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quadrant 3 (Bottom-Left): Key Development Area -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'key_development' ? 'bg-rose-100/80 border-rose-400 shadow-xs ring-2 ring-rose-400/30' : 'bg-rose-50/60 border-rose-200/60' }}">
                                        <div>
                                            <h4 class="font-black text-rose-950 text-xs">Key Development Area</h4>
                                            <p class="text-[10px] text-rose-900/80 mt-1 leading-tight">
                                                Both you and others see gaps. Focus on building core change capabilities.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quadrant 4 (Bottom-Right): Perception Gap -->
                                    <div class="p-3 rounded-xl flex flex-col justify-between transition border {{ $matrixQuadrant === 'perception_gap' ? 'bg-blue-100/80 border-blue-400 shadow-xs ring-2 ring-blue-400/30' : 'bg-blue-50/60 border-blue-200/60' }}">
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
