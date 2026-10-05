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

    // Archetype narrative and styling
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
        $tierBg = '#d1efe0';
        $tierBorder = '#bbf7d0';
        $description = 'The team is generally open to change and willing to contribute. With focused development and alignment, the team can move towards the Driver and Champion levels.';
    } elseif ($score <= 8.0) {
        $tierName = 'Driver';
        $levelHeading = 'Change Driver level';
        $tierColor = '#b45309';
        $tierBg = '#f9efcb';
        $tierBorder = '#fde68a';
        $description = 'The team proactively leads change initiatives and problem solves. With continuous empowerment and strategic alignment, the team is on track towards the Champion level.';
    } else {
        $tierName = 'Champion';
        $levelHeading = 'Change Champion level';
        $tierColor = '#1d4ed8';
        $tierBg = '#d3e4fd';
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

    <!-- Semicircular Speedometer Arc Gauge -->
    <div class="w-full max-w-[380px] mx-auto aspect-[400/240] relative flex items-end justify-center select-none pt-2"
         x-data="{ mounted: false }"
         x-init="setTimeout(() => mounted = true, 50)">
        <svg viewBox="0 0 400 240" class="w-full h-full overflow-visible select-none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <!-- Needle Drop Shadow -->
                <filter id="{{ $filterId }}" x="-30%" y="-30%" width="160%" height="160%">
                    <feDropShadow dx="0" dy="2.5" stdDeviation="3" flood-opacity="0.30"/>
                </filter>
            </defs>

            <!-- ============================================================== -->
            <!-- 5 Speedometer Segment Wedges (Center 200, 200 | Ro 180, Ri 98)  -->
            <!-- ============================================================== -->

            <!-- Sector 1: Resistant (Coral Red #f87171) -->
            <path d="M 102.0,200.0 L 20.0,200.0 A 180,180 0 0,1 54.38,94.20 L 120.72,142.40 A 98,98 0 0,0 102.0,200.0 Z" 
                  fill="#f87171" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 2: Follower (Warm Peach/Orange #fb923c) -->
            <path d="M 120.72,142.40 L 54.38,94.20 A 180,180 0 0,1 144.38,28.81 L 169.72,106.80 A 98,98 0 0,0 120.72,142.40 Z" 
                  fill="#fb923c" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 3: Supporter (Mint/Soft Green #86efac) -->
            <path d="M 169.72,106.80 L 144.38,28.81 A 180,180 0 0,1 255.62,28.81 L 230.28,106.80 A 98,98 0 0,0 169.72,106.80 Z" 
                  fill="#86efac" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 4: Driver (Warm Yellow #fde047) -->
            <path d="M 230.28,106.80 L 255.62,28.81 A 180,180 0 0,1 345.62,94.20 L 279.28,142.40 A 98,98 0 0,0 230.28,106.80 Z" 
                  fill="#fde047" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 5: Champion (Sky Blue #60a5fa) -->
            <path d="M 279.28,142.40 L 345.62,94.20 A 180,180 0 0,1 380.0,200.0 L 298.0,200.0 A 98,98 0 0,0 279.28,142.40 Z" 
                  fill="#60a5fa" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- ============================================================== -->
            <!-- 5 Archetype Text Labels Centered inside each Sector             -->
            <!-- ============================================================== -->
            <text x="68" y="157" text-anchor="middle" font-size="12.5" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Resistant</text>
            <text x="118" y="88" text-anchor="middle" font-size="12.5" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Follower</text>
            <text x="200" y="62" text-anchor="middle" font-size="13" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Supporter</text>
            <text x="282" y="88" text-anchor="middle" font-size="12.5" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Driver</text>
            <text x="332" y="157" text-anchor="middle" font-size="12.5" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">Champion</text>

            <!-- Decorative Inner Arch Rim Track -->
            <path d="M 108 200 A 92 92 0 0 1 292 200" fill="none" stroke="#e2e8f0" stroke-width="10" opacity="0.65" />

            <!-- Inner White Hub Dome -->
            <circle cx="200" cy="200" r="86" fill="#ffffff" />

            <!-- ============================================================== -->
            <!-- Dynamic Speedometer Needle with Circular Cutout Base            -->
            <!-- ============================================================== -->
            <g :style="mounted ? 'transform: rotate({{ $needleAngle }}deg); transition: transform 1.2s cubic-bezier(0.34, 1.4, 0.64, 1);' : 'transform: rotate(-90deg);'"
               style="transform-origin: 200px 200px; transform: rotate({{ $needleAngle }}deg);"
               filter="url(#{{ $filterId }})">
                <!-- Tapered Sharp Needle extending to the outer rim -->
                <path d="M 194 200 L 198.5 28 L 201.5 28 L 206 200 Z" fill="#0a0f37" />
                <!-- Circular Hub with inner hole cutout -->
                <circle cx="200" cy="200" r="14" fill="#0a0f37" />
                <circle cx="200" cy="190" r="4.5" fill="#ffffff" />
            </g>

            <!-- Central Numeric Score Display -->
            <text x="200" y="172" text-anchor="middle" font-size="42" font-weight="900" fill="#0a0f37" letter-spacing="-1" font-family="ui-sans-serif, system-ui, sans-serif">
                {{ number_format($score, 1) }}
            </text>
            <text x="200" y="196" text-anchor="middle" font-size="15" font-weight="800" fill="#0a0f37" font-family="ui-sans-serif, system-ui, sans-serif">
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
