@props([
    'insights',
    'score' => null,
])

@php
    $score = (float) ($score ?? $insights['team_cq_score'] ?? $insights['statistics']['mean'] ?? 0.0);

    // Piecewise Needle Rotation Angle on 1.0 - 10.0 scale (5 sectors of 36 deg):
    // 1.0 - 2.0  => -90° to -54° (Resistant)
    // 2.1 - 4.0  => -54° to -18° (Follower)
    // 4.1 - 6.0  => -18° to +18° (Supporter)
    // 6.1 - 8.0  => +18° to +54° (Driver)
    // 8.1 - 10.0 => +54° to +90° (Champion)
    if ($score <= 1.0) {
        $needleAngle = -90;
    } elseif ($score <= 2.0) {
        $needleAngle = -90 + (($score - 1.0) / 1.0) * 36;
    } elseif ($score <= 4.0) {
        $needleAngle = -54 + (($score - 2.0) / 2.0) * 36;
    } elseif ($score <= 6.0) {
        $needleAngle = -18 + (($score - 4.0) / 2.0) * 36;
    } elseif ($score <= 8.0) {
        $needleAngle = 18 + (($score - 6.0) / 2.0) * 36;
    } else {
        $needleAngle = 54 + (min(2.0, $score - 8.0) / 2.0) * 36;
    }
    $needleAngle = round(max(-90, min(90, $needleAngle)), 2);

    // Archetype narrative and styling matching theme template image exactly
    if ($score <= 2.0) {
        $tierName = 'Resistant';
        $levelHeading = 'Change Resistant level';
        $tierColor = '#be123c';
        $tierBg = '#fedee4';
        $tierBorder = '#fecdd3';
        $description = 'The team experiences friction or change fatigue. Focus on foundational trust, open dialogues, and addressing core concerns to begin transitioning to Follower.';
    } elseif ($score <= 4.0) {
        $tierName = 'Follower';
        $levelHeading = 'Change Follower level';
        $tierColor = '#c2410c';
        $tierBg = '#faecd9';
        $tierBorder = '#fed7aa';
        $description = 'The team complies with mandated changes but shows hesitation. Building psychological safety and clear communication will help transition the team to Supporter.';
    } elseif ($score <= 6.2) {
        $tierName = 'Supporter';
        $levelHeading = 'Change Supporter level';
        $tierColor = '#15803d';
        $tierBg = '#f2faf5';
        $tierBorder = '#d1fae5';
        $description = 'The team is generally open to change and willing to contribute. With focused development and alignment, the team can move towards the Driver and Champion levels.';
    } elseif ($score <= 8.0) {
        $tierName = 'Driver';
        $levelHeading = 'Change Driver level';
        $tierColor = '#b45309';
        $tierBg = '#fefce8';
        $tierBorder = '#fde68a';
        $description = 'The team proactively leads change initiatives and problem solves. With continuous empowerment and strategic alignment, the team is on track towards the Champion level.';
    } else {
        $tierName = 'Champion';
        $levelHeading = 'Change Champion level';
        $tierColor = '#1d4ed8';
        $tierBg = '#eff6ff';
        $tierBorder = '#bfdbfe';
        $description = 'The team embodies transformational agility and continuous innovation, serving as role models and mentors across the organization.';
    }

    $filterId = 'maturityNeedleShadow_' . substr(md5('maturity' . $score . ($attributes->get('id') ?? '')), 0, 8);
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-4']) }}>
    <!-- Header matching template -->
    <div>
        <h3 class="text-xl sm:text-2xl font-black text-[#0a0f37] tracking-tight">Team Maturity Level</h3>
        <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Current overall position of the team</p>
    </div>

    <!-- Semicircular Speedometer Arc Gauge (Floating Needle design matching template image) -->
    <div class="w-full max-w-[390px] mx-auto aspect-[400/235] relative flex items-end justify-center select-none pt-2"
         x-data="{ mounted: false }"
         x-init="setTimeout(() => mounted = true, 50)">
        <svg viewBox="0 0 400 230" class="w-full h-full overflow-visible select-none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <!-- Needle Drop Shadow -->
                <filter id="{{ $filterId }}" x="-30%" y="-30%" width="160%" height="160%">
                    <feDropShadow dx="0" dy="2" stdDeviation="2.5" flood-color="#000000" flood-opacity="0.25"/>
                </filter>
            </defs>

            <!-- ============================================================== -->
            <!-- 5 Speedometer Segment Wedges (Center 200, 200 | Ro 180, Ri 108)  -->
            <!-- ============================================================== -->

            <!-- Sector 1: Resistant (Warm Coral Red #f75357) -->
            <path d="M 92,200 L 20,200 A 180,180 0 0,1 54.38,94.20 L 112.63,136.52 A 108,108 0 0,0 92,200 Z" 
                  fill="#f75357" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 2: Follower (Warm Peach/Orange #fba05a) -->
            <path d="M 112.63,136.52 L 54.38,94.20 A 180,180 0 0,1 144.38,28.81 L 166.63,97.29 A 108,108 0 0,0 112.63,136.52 Z" 
                  fill="#fba05a" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 3: Supporter (Soft Mint #8de0a6) -->
            <path d="M 166.63,97.29 L 144.38,28.81 A 180,180 0 0,1 255.62,28.81 L 233.37,97.29 A 108,108 0 0,0 166.63,97.29 Z" 
                  fill="#8de0a6" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 4: Driver (Warm Yellow #fddb58) -->
            <path d="M 233.37,97.29 L 255.62,28.81 A 180,180 0 0,1 345.62,94.20 L 287.37,136.52 A 108,108 0 0,0 233.37,97.29 Z" 
                  fill="#fddb58" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 5: Champion (Vibrant Sky Blue #0082fb) -->
            <path d="M 287.37,136.52 L 345.62,94.20 A 180,180 0 0,1 380,200 L 308,200 A 108,108 0 0,0 287.37,136.52 Z" 
                  fill="#0082fb" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- ============================================================== -->
            <!-- 5 Archetype Text Labels Centered inside each Sector             -->
            <!-- ============================================================== -->
            <text x="70" y="160" text-anchor="middle" font-size="13" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Resistant</text>
            <text x="120" y="90" text-anchor="middle" font-size="13" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Follower</text>
            <text x="200" y="62" text-anchor="middle" font-size="13" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Supporter</text>
            <text x="280" y="90" text-anchor="middle" font-size="13" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Driver</text>
            <text x="330" y="160" text-anchor="middle" font-size="13" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Champion</text>

            <!-- Inner Light Grey Arched Track (Ro 108, Ri 94) -->
            <path d="M 92,200 A 108,108 0 0,1 308,200 L 294,200 A 94,94 0 0,0 106,200 Z" fill="#edf0f5" />

            <!-- Inner White Hub Dome (Clean canvas for numeric score) -->
            <circle cx="200" cy="200" r="93" fill="#ffffff" />

            <!-- ============================================================== -->
            <!-- Dynamic Floating Needle (Base floats on track, NEVER blocks score) -->
            <!-- ============================================================== -->
            <g :style="mounted ? 'transform: rotate({{ $needleAngle }}deg); transition: transform 1.2s cubic-bezier(0.34, 1.4, 0.64, 1);' : 'transform: rotate(-90deg);'"
               style="transform-origin: 200px 200px; transform: rotate({{ $needleAngle }}deg);"
               filter="url(#{{ $filterId }})">
                <!-- Floating needle body starting from hub at track radius ~100 to tip at radius 176 -->
                <path d="M 191.5 100 L 198.5 24 A 1.5 1.5 0 0 1 201.5 24 L 208.5 100 A 8.5 8.5 0 1 1 191.5 100 Z" fill="#001452" />
                <!-- White circular hole inside the hub -->
                <circle cx="200" cy="100" r="3.5" fill="#ffffff" />
            </g>

            <!-- Central Numeric Score Display (Unobstructed & 100% visible) -->
            <text x="200" y="164" text-anchor="middle" font-size="46" font-weight="900" fill="#0a0f37" letter-spacing="-1.5" font-family="ui-sans-serif, system-ui, sans-serif">
                {{ number_format($score, 1) }}
            </text>
            <text x="200" y="190" text-anchor="middle" font-size="16" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">
                / 10
            </text>
        </svg>
    </div>

    <!-- Bottom Insight Box (Matching Template Design) -->
    <div class="mt-2 p-4 sm:p-5 rounded-2xl text-center space-y-2 border transition-colors shadow-2xs"
         style="background-color: {{ $tierBg }} !important; border-color: {{ $tierBorder }} !important;">
        <div>
            <p class="text-xs sm:text-sm font-black text-[#0a0f37]">Your team is at the</p>
            <h4 class="text-base sm:text-lg font-black tracking-tight mt-0.5" style="color: {{ $tierColor }} !important;">
                {{ $levelHeading }}
            </h4>
        </div>
        <p class="text-xs text-slate-700 leading-relaxed max-w-sm mx-auto font-medium">
            {{ $description }}
        </p>
    </div>
</div>
