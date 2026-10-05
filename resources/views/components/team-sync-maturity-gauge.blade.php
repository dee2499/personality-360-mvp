@props([
    'score' => 0.0,
    'maturityLevel' => null,
    'title' => 'Team CQ Sync Maturity Level',
    'subtitle' => 'Where does your team stand on alignment to see, agree and act together?',
    'card' => true,
    'class' => '',
])

@php
    $score = (float) $score;

    // Piecewise Needle Rotation Angle on 1.0 - 10.0 scale (5 sectors of 36 deg):
    // 1.0 - 2.0  => -90° to -54° (Divergent)
    // 2.1 - 4.0  => -54° to -18° (Fragmented)
    // 4.1 - 6.0  => -18° to +18° (Aligned)
    // 6.1 - 8.0  => +18° to +54° (Synchronised)
    // 8.1 - 10.0 => +54° to +90° (Unified)
    if ($score <= 0) {
        $syncNeedleAngle = -90;
    } elseif ($score <= 2.0) {
        $syncNeedleAngle = -90 + (max(0, $score - 1.0) / 1.0) * 36;
    } elseif ($score <= 4.0) {
        $syncNeedleAngle = -54 + (($score - 2.0) / 2.0) * 36;
    } elseif ($score <= 6.0) {
        $syncNeedleAngle = -18 + (($score - 4.0) / 2.0) * 36;
    } elseif ($score <= 8.0) {
        $syncNeedleAngle = 18 + (($score - 6.0) / 2.0) * 36;
    } else {
        $syncNeedleAngle = 54 + (min(2.0, $score - 8.0) / 2.0) * 36;
    }
    $syncNeedleAngle = round(max(-90, min(90, $syncNeedleAngle)), 2);

    $level = $maturityLevel;
    if (! $level) {
        if ($score <= 2.0) {
            $level = 'Divergent';
        } elseif ($score <= 4.0) {
            $level = 'Fragmented';
        } elseif ($score <= 6.0) {
            $level = 'Aligned';
        } elseif ($score <= 8.0) {
            $level = 'Synchronised';
        } else {
            $level = 'Unified';
        }
    }

    $pillTheme = match(strtolower($level)) {
        'divergent' => ['bg' => '#fee2e2', 'border' => '#fca5a5', 'text' => '#991b1b'],
        'fragmented' => ['bg' => '#ffedd5', 'border' => '#fdba74', 'text' => '#9a3412'],
        'aligned' => ['bg' => '#dcfce7', 'border' => '#86efac', 'text' => '#064e3b'],
        'synchronised', 'synchronized' => ['bg' => '#fef3c7', 'border' => '#fcd34d', 'text' => '#92400e'],
        'unified' => ['bg' => '#e0f2fe', 'border' => '#7dd3fc', 'text' => '#0369a1'],
        default => ['bg' => '#dcfce7', 'border' => '#86efac', 'text' => '#064e3b'],
    };

    $filterId = 'syncNeedleShadow_' . substr(md5($title . $score . ($attributes->get('id') ?? '')), 0, 8);
    $hubShadowId = 'hubElevationShadow_' . substr(md5($title . $score . ($attributes->get('id') ?? '')), 0, 8);
@endphp

@if($card)
<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col justify-between ' . $class]) }}>
    <div class="space-y-0.5">
        <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">{{ $title }}</h3>
        <p class="text-[11px] sm:text-xs text-slate-500 font-medium">{{ $subtitle }}</p>
    </div>
@endif

    <!-- Semicircular Speedometer Gauge Canvas -->
    <div class="w-full max-w-[420px] mx-auto aspect-[400/235] relative flex items-end justify-center select-none pt-2"
         x-data="{ mounted: false }"
         x-init="setTimeout(() => mounted = true, 50)">
        <svg viewBox="0 0 400 235" class="w-full h-full overflow-visible select-none">
            <defs>
                <!-- Needle Drop Shadow -->
                <filter id="{{ $filterId }}" x="-30%" y="-30%" width="160%" height="160%">
                    <feDropShadow dx="0" dy="2.5" stdDeviation="3" flood-opacity="0.28"/>
                </filter>
                <!-- Inner Dome Elevation Glow Shadow -->
                <filter id="{{ $hubShadowId }}" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="-2.5" stdDeviation="4.5" flood-color="#0284c7" flood-opacity="0.12"/>
                </filter>
            </defs>

            <!-- ============================================================== -->
            <!-- 5 Speedometer Segment Wedges (Center 200, 200 | Ro 180, Ri 95) -->
            <!-- ============================================================== -->

            <!-- Sector 1: Divergent (Solid Crimson Red #e11d48) -->
            <path d="M 105.0,200.0 L 20.0,200.0 A 180,180 0 0,1 54.38,94.20 L 123.14,144.16 A 95,95 0 0,0 105.0,200.0 Z" 
                  fill="#e11d48" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 2: Fragmented (Warm Peach/Orange #fca663) -->
            <path d="M 123.14,144.16 L 54.38,94.20 A 180,180 0 0,1 144.38,28.81 L 170.64,109.65 A 95,95 0 0,0 123.14,144.16 Z" 
                  fill="#fca663" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 3: Aligned (Vibrant Mint/Emerald Green #4ade80) -->
            <path d="M 170.64,109.65 L 144.38,28.81 A 180,180 0 0,1 255.62,28.81 L 229.36,109.65 A 95,95 0 0,0 170.64,109.65 Z" 
                  fill="#4ade80" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 4: Synchronised (Sunny Golden Yellow #facc15) -->
            <path d="M 229.36,109.65 L 255.62,28.81 A 180,180 0 0,1 345.62,94.20 L 276.86,144.16 A 95,95 0 0,0 229.36,109.65 Z" 
                  fill="#facc15" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- Sector 5: Unified (Solid Royal Blue #0284c7) -->
            <path d="M 276.86,144.16 L 345.62,94.20 A 180,180 0 0,1 380.0,200.0 L 295.0,200.0 A 95,95 0 0,0 276.86,144.16 Z" 
                  fill="#0284c7" stroke="#ffffff" stroke-width="2.5" stroke-linejoin="round"/>

            <!-- ============================================================== -->
            <!-- Sector 1: Divergent Content                                    -->
            <!-- ============================================================== -->
            <!-- Divergent 3-Arrow Node Icon -->
            <g transform="translate(68, 138) scale(0.9)" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none">
                <circle cx="0" cy="5" r="2.5" fill="#FFFFFF"/>
                <path d="M 0,2 L 0,-11 M 0,-11 L -4,-7 M 0,-11 L 4,-7"/>
                <path d="M -3,5 L -12,0 M -12,0 L -7,-2 M -12,0 L -9,5"/>
                <path d="M 3,5 L 12,0 M 12,0 L 7,-2 M 12,0 L 9,5"/>
            </g>
            <text x="68" y="165" text-anchor="middle" font-size="11" font-weight="900" fill="#FFFFFF">Divergent</text>
            <text x="68" y="179" text-anchor="middle" font-size="9.5" font-weight="700" fill="#ffe4e6">1.0 – 2.0</text>

            <!-- ============================================================== -->
            <!-- Sector 2: Fragmented Content                                  -->
            <!-- ============================================================== -->
            <!-- Fragmented Dumbbell Nodes Icon -->
            <g transform="translate(122, 68)" fill="#451a03" stroke="#451a03" stroke-width="2" stroke-linecap="round">
                <circle cx="-9" cy="-7" r="3.2"/>
                <circle cx="9" cy="-5" r="3.5"/>
                <circle cx="2" cy="7" r="3.2"/>
                <line x1="-7" y1="-5" x2="0" y2="5" stroke-dasharray="2.5,2.5" stroke-width="2.2"/>
                <line x1="7" y1="-3" x2="3" y2="5" stroke-width="2.2"/>
            </g>
            <text x="122" y="96" text-anchor="middle" font-size="11" font-weight="900" fill="#0a0f37">Fragmented</text>
            <text x="122" y="110" text-anchor="middle" font-size="9.5" font-weight="700" fill="#475569">2.1 – 4.0</text>

            <!-- ============================================================== -->
            <!-- Sector 3: Aligned Content                                     -->
            <!-- ============================================================== -->
            <!-- 3 Silhouetted People Group Icon -->
            <g transform="translate(200, 40)" fill="#064e3b">
                <circle cx="0" cy="-5" r="3.4"/>
                <path d="M -5.5,5 C -5.5,1 -2.5,0 0,0 C 2.5,0 5.5,1 5.5,5 Z"/>
                <circle cx="-8" cy="-3.5" r="2.6"/>
                <path d="M -12,5.5 C -12,2.8 -10,2 -8.5,2 C -7,2 -5.5,2.6 -5.5,4.5"/>
                <circle cx="8" cy="-3.5" r="2.6"/>
                <path d="M 5.5,4.5 C 5.5,2.6 7,2 8.5,2 C 10,2 12,2.8 12,5.5"/>
            </g>
            <text x="200" y="66" text-anchor="middle" font-size="11" font-weight="900" fill="#0a0f37">Aligned</text>
            <text x="200" y="80" text-anchor="middle" font-size="9.5" font-weight="700" fill="#064e3b">4.1 – 6.0</text>

            <!-- ============================================================== -->
            <!-- Sector 4: Synchronised Content                                -->
            <!-- ============================================================== -->
            <!-- Interlocking Dual Gears Icon -->
            <g transform="translate(278, 68)" stroke="#78350f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none">
                <circle cx="-5" cy="3" r="4.5" fill="#facc15"/>
                <path d="M -5,-3.5 L -5,-1.5 M -5,7.5 L -5,9.5 M -11.5,3 L -9.5,3 M -0.5,3 L 1.5,3 M -9.5,-1.5 L -8.1,-0.1 M -1.9,6.1 L -0.5,7.5 M -9.5,7.5 L -8.1,6.1 M -1.9,-0.1 L -0.5,-1.5"/>
                <circle cx="6" cy="-4" r="3.5" fill="#facc15"/>
                <path d="M 6,-9.5 L 6,-8 M 6,0 L 6,1.5 M 1.5,-4 L 3,-4 M 9,-4 L 10.5,-4"/>
            </g>
            <text x="278" y="96" text-anchor="middle" font-size="11" font-weight="900" fill="#0a0f37">Synchronised</text>
            <text x="278" y="110" text-anchor="middle" font-size="9.5" font-weight="700" fill="#78350f">6.1 – 8.0</text>

            <!-- ============================================================== -->
            <!-- Sector 5: Unified Content                                      -->
            <!-- ============================================================== -->
            <!-- Team With Summit Flag Icon -->
            <g transform="translate(332, 138)" fill="#FFFFFF" stroke="#FFFFFF" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="-12" x2="3" y2="7" stroke-width="2.2"/>
                <polygon points="3,-12 14,-8 3,-4" stroke-width="1"/>
                <circle cx="0" cy="-2" r="2.8" stroke="none"/>
                <path d="M -4.5,7 C -4.5,3.2 -2,2.5 0,2.5 C 2,2.5 4.5,3.2 4.5,7 Z" stroke="none"/>
                <circle cx="-7" cy="-0.5" r="2.4" stroke="none"/>
                <path d="M -11,7 C -11,4.2 -9,3.5 -7,3.5 C -5.5,3.5 -4.5,4.2 -4.5,6" stroke="none"/>
                <circle cx="7" cy="-0.5" r="2.4" stroke="none"/>
                <path d="M 4.5,6 C 4.5,4.2 5.5,3.5 7,3.5 C 9,3.5 11,4.2 11,7" stroke="none"/>
            </g>
            <text x="332" y="165" text-anchor="middle" font-size="11" font-weight="900" fill="#FFFFFF">Unified</text>
            <text x="332" y="179" text-anchor="middle" font-size="9.5" font-weight="700" fill="#e0f2fe">8.1 – 10.0</text>

            <!-- ============================================================== -->
            <!-- Inner Elevated White Cutout Semicircle (Radius 94 at 200, 200) -->
            <!-- ============================================================== -->
            <path d="M 106,200 A 94,94 0 0,1 294,200 Z" fill="#FFFFFF" filter="url(#{{ $hubShadowId }})"/>

            <!-- ============================================================== -->
            <!-- Center Readout Inside White Hub                                -->
            <!-- ============================================================== -->
            <text x="200" y="146" text-anchor="middle" font-size="11" font-weight="800" fill="#0a0f37" letter-spacing="0.01em">Team CQ Sync Score</text>
            <text x="200" y="184" text-anchor="middle">
                <tspan font-size="38" font-weight="900" fill="#0a0f37">{{ number_format($score, 1) }}</tspan>
                <tspan font-size="20" font-weight="700" fill="#0a0f37" dx="4"> / 10</tspan>
            </text>

            <!-- ============================================================== -->
            <!-- The Speedometer Needle (Pivot at 200, 200)                     -->
            <!-- ============================================================== -->
            <g transform="translate(200, 200)">
                <g :style="`transform: rotate(${mounted ? {{ $syncNeedleAngle }} : -90}deg); transform-origin: 0 0; transition: transform 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);`"
                   style="transform: rotate({{ $syncNeedleAngle }}deg); transform-origin: 0 0;"
                   filter="url(#{{ $filterId }})">
                    <polygon points="-4.5,0 -1,-126 0,-134 1,-126 4.5,0" fill="#0a0f37"/>
                    <polygon points="0,0 0,-134 1,-126 4.5,0" fill="#1e293b"/>
                    <circle cx="0" cy="-20" r="3.2" fill="#FFFFFF"/>
                    <circle cx="0" cy="0" r="8" fill="#0a0f37"/>
                </g>
            </g>

            <!-- ============================================================== -->
            <!-- Centered Active Tier Pill Badge at Bottom Hub Opening          -->
            <!-- ============================================================== -->
            <g transform="translate(200, 206)">
                <rect x="-60" y="-13" width="120" height="26" rx="13" fill="{{ $pillTheme['bg'] }}" stroke="{{ $pillTheme['border'] }}" stroke-width="1"/>
                <text x="0" y="5" text-anchor="middle" font-size="13" font-weight="900" fill="{{ $pillTheme['text'] }}">{{ $level }}</text>
            </g>
        </svg>
    </div>

@if($card)
</div>
@endif
