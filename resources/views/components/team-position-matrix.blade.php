@props([
    'insights' => null,
    'cqScore' => null,
    'syncScore' => null,
    'benchmarkCq' => null,
    'benchmarkSync' => null,
])

@php
    $cq = (float) ($cqScore ?? ($insights['team_cq_score'] ?? 0.0));
    $sync = (float) ($syncScore ?? ($insights['team_cq_sync_score'] ?? 0.0));
    $bmCq = (float) ($benchmarkCq ?? ($insights['benchmark_cq'] ?? 8.0));
    $bmSync = (float) ($benchmarkSync ?? ($insights['benchmark_sync'] ?? 8.0));

    // Grid coordinates:
    // X goes from 0 to 10 across 610px (X = 230 to 840) => 61px per unit
    // Y goes from 0 to 10 across 460px (Y = 40 [score 10] to 500 [score 0]) => 46px per unit
    $curX = round(max(230, min(840, 230 + ($cq * 61))), 2);
    $curY = round(max(40, min(500, 500 - ($sync * 46))), 2);

    $bmX = round(max(230, min(840, 230 + ($bmCq * 61))), 2);
    $bmY = round(max(40, min(500, 500 - ($bmSync * 46))), 2);

    // Intelligent tooltip positioning with collision avoidance for the Goal/Benchmark (bmX, bmY)
    $tooltipW = 142;
    $tooltipH = 50;

    $candidates = [
        // 1. Right of point
        ['x' => $curX + 18, 'y' => $curY - 24],
        // 2. Left of point
        ['x' => $curX - $tooltipW - 18, 'y' => $curY - 24],
        // 3. Below point
        ['x' => $curX - ($tooltipW / 2), 'y' => $curY + 22],
        // 4. Above point
        ['x' => $curX - ($tooltipW / 2), 'y' => $curY - $tooltipH - 22],
        // 5. Bottom-Left
        ['x' => $curX - $tooltipW - 18, 'y' => $curY + 16],
        // 6. Top-Left
        ['x' => $curX - $tooltipW - 18, 'y' => $curY - $tooltipH - 10],
    ];

    $tooltipX = $curX - $tooltipW - 18; // safe default
    $tooltipY = $curY - 24;

    foreach ($candidates as $cand) {
        $tx = $cand['x'];
        $ty = $cand['y'];

        // Check grid boundary constraints (Grid: X 230-840, Y 40-500)
        if ($tx < 232 || ($tx + $tooltipW) > 838 || $ty < 42 || ($ty + $tooltipH) > 498) {
            continue;
        }

        // Check collision with Benchmark / Goal star circle (bmX, bmY with 28px buffer)
        $collidesWithGoal = !(
            ($tx + $tooltipW) < ($bmX - 28) ||
            $tx > ($bmX + 28) ||
            ($ty + $tooltipH) < ($bmY - 28) ||
            $ty > ($bmY + 28)
        );

        if (! $collidesWithGoal) {
            $tooltipX = $tx;
            $tooltipY = $ty;
            break;
        }
    }
@endphp

<div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-3">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight">Team Position Matrix</h3>
            <p class="text-[11px] sm:text-xs font-medium text-slate-400">CQ Group Score vs CQ Sync Score</p>
        </div>
        <span class="text-[10px] sm:text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200/80 shadow-2xs">
            Benchmark ({{ number_format($bmCq, 1) }}, {{ number_format($bmSync, 1) }}) ★
        </span>
    </div>

    <!-- Responsive SVG Chart Container -->
    <div class="relative w-full max-w-[760px] mx-auto overflow-hidden">
        <svg class="w-full h-auto select-none" viewBox="0 0 880 625" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="matrixBadgeShadow" x="-10%" y="-10%" width="120%" height="120%" filterUnits="userSpaceOnUse">
                    <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.12" />
                </filter>
                <filter id="matrixMarkerGlow" x="-50%" y="-50%" width="200%" height="200%">
                    <feDropShadow dx="0" dy="2" stdDeviation="4" flood-color="#4338ca" flood-opacity="0.35"/>
                </filter>
            </defs>

            <!-- Y-Axis Vertical Label -->
            <g transform="translate(30, 270) rotate(-90)">
                <text text-anchor="middle" font-size="14" font-weight="900" fill="#0f172a" letter-spacing="0.5">CQ Sync Score</text>
                <text text-anchor="middle" y="16" font-size="11" font-weight="600" fill="#64748b">(Higher = More Synchronised)</text>
            </g>

            <!-- Y-Axis Tiers Badges (Left of Grid: X = 80 to 175) -->
            <!-- Tier 5: Unified (8.1 - 10) -->
            <rect x="80" y="45" width="95" height="42" rx="8" fill="#e0f2fe" stroke="#bae6fd" stroke-width="1.2"/>
            <text x="127" y="63" text-anchor="middle" font-size="11" font-weight="800" fill="#0369a1">Unified</text>
            <text x="127" y="78" text-anchor="middle" font-size="10" font-weight="600" fill="#0284c7">(8.1 – 10)</text>

            <!-- Tier 4: Synchronised (6.1 - 8.0) -->
            <rect x="80" y="145" width="95" height="42" rx="8" fill="#ffedd5" stroke="#fed7aa" stroke-width="1.2"/>
            <text x="127" y="163" text-anchor="middle" font-size="11" font-weight="800" fill="#c2410c">Synchronised</text>
            <text x="127" y="178" text-anchor="middle" font-size="10" font-weight="600" fill="#ea580c">(6.1 – 8.0)</text>

            <!-- Tier 3: Aligned (4.1 - 6.0) -->
            <rect x="80" y="245" width="95" height="42" rx="8" fill="#dcfce7" stroke="#bbf7d0" stroke-width="1.2"/>
            <text x="127" y="263" text-anchor="middle" font-size="11" font-weight="800" fill="#15803d">Aligned</text>
            <text x="127" y="278" text-anchor="middle" font-size="10" font-weight="600" fill="#16a34a">(4.1 – 6.0)</text>

            <!-- Tier 2: Fragmented (2.1 - 4.0) -->
            <rect x="80" y="345" width="95" height="42" rx="8" fill="#fee2e2" stroke="#fecaca" stroke-width="1.2"/>
            <text x="127" y="363" text-anchor="middle" font-size="11" font-weight="800" fill="#b91c1c">Fragmented</text>
            <text x="127" y="378" text-anchor="middle" font-size="10" font-weight="600" fill="#dc2626">(2.1 – 4.0)</text>

            <!-- Tier 1: Divergent (1.0 - 2.0) -->
            <rect x="80" y="445" width="95" height="42" rx="8" fill="#ffe4e6" stroke="#fecdd3" stroke-width="1.2"/>
            <text x="127" y="463" text-anchor="middle" font-size="11" font-weight="800" fill="#be123c">Divergent</text>
            <text x="127" y="478" text-anchor="middle" font-size="10" font-weight="600" fill="#e11d48">(1.0 – 2.0)</text>

            <!-- Y-Axis Numeric Labels (215 to 225) -->
            <text x="215" y="45" text-anchor="end" font-size="13" font-weight="700" fill="#334155">10</text>
            <text x="215" y="137" text-anchor="end" font-size="13" font-weight="700" fill="#334155">8</text>
            <text x="215" y="229" text-anchor="end" font-size="13" font-weight="700" fill="#334155">6</text>
            <text x="215" y="321" text-anchor="end" font-size="13" font-weight="700" fill="#334155">4</text>
            <text x="215" y="413" text-anchor="end" font-size="13" font-weight="700" fill="#334155">2</text>
            <text x="215" y="505" text-anchor="end" font-size="13" font-weight="700" fill="#334155">0</text>

            <!-- 4 MATRIX QUADRANTS -->
            <!-- Top Left: POTENTIAL ZONE (X: 230 to 596, Y: 40 to 245) -->
            <rect x="230" y="40" width="366" height="205" fill="#dbeafe" fill-opacity="0.85" />
            <g transform="translate(250, 68)">
                <text font-size="13" font-weight="900" fill="#1e3a8a" letter-spacing="0.5">POTENTIAL ZONE</text>
                <text y="18" font-size="11" font-weight="700" fill="#1d4ed8">(High Sync, Moderate CQ)</text>
                <text y="48" font-size="11" font-weight="600" fill="#1e40af">Strong alignment but</text>
                <text y="64" font-size="11" font-weight="600" fill="#1e40af">capability needs to grow.</text>
            </g>

            <!-- Top Right: OPPORTUNITY ZONE (X: 596 to 840, Y: 40 to 245) -->
            <rect x="596" y="40" width="244" height="205" fill="#d1fae5" fill-opacity="0.85" />
            <g transform="translate(616, 68)">
                <text font-size="13" font-weight="900" fill="#065f46" letter-spacing="0.5">OPPORTUNITY ZONE</text>
                <text y="18" font-size="11" font-weight="700" fill="#047857">(High CQ, High Sync)</text>
                <text y="48" font-size="11" font-weight="600" fill="#064e3b">Ideal state: High capability</text>
                <text y="64" font-size="11" font-weight="600" fill="#064e3b">and strong synchronisation.</text>
                <text y="80" font-size="11" font-weight="600" fill="#064e3b">Drive bigger impact.</text>
            </g>

            <!-- Bottom Left: RISK ZONE (X: 230 to 596, Y: 245 to 500) -->
            <rect x="230" y="245" width="366" height="255" fill="#fee2e2" fill-opacity="0.85" />
            <g transform="translate(250, 360)">
                <text font-size="13" font-weight="900" fill="#991b1b" letter-spacing="0.5">RISK ZONE</text>
                <text y="18" font-size="11" font-weight="700" fill="#b91c1c">(Low CQ, Low Sync)</text>
                <text y="48" font-size="11" font-weight="600" fill="#7f1d1d">Both capability and alignment</text>
                <text y="64" font-size="11" font-weight="600" fill="#7f1d1d">need significant attention.</text>
            </g>

            <!-- Bottom Right: CAPABILITY ZONE (X: 596 to 840, Y: 245 to 500) -->
            <rect x="596" y="245" width="244" height="255" fill="#fef3c7" fill-opacity="0.85" />
            <g transform="translate(616, 360)">
                <text font-size="13" font-weight="900" fill="#92400e" letter-spacing="0.5">CAPABILITY ZONE</text>
                <text y="18" font-size="11" font-weight="700" fill="#b45309">(High CQ, Low Sync)</text>
                <text y="48" font-size="11" font-weight="600" fill="#78350f">Good capability but</text>
                <text y="64" font-size="11" font-weight="600" fill="#78350f">alignment gaps are limiting</text>
                <text y="80" font-size="11" font-weight="600" fill="#78350f">collective impact.</text>
            </g>

            <!-- Grid Outer Border & White Dividing Lines -->
            <rect x="230" y="40" width="610" height="460" fill="none" stroke="#64748b" stroke-width="1.5" />
            <line x1="596" y1="40" x2="596" y2="500" stroke="#ffffff" stroke-width="3" />
            <line x1="230" y1="245" x2="840" y2="245" stroke="#ffffff" stroke-width="3" />

            <!-- BENCHMARK (Dynamic bmX, bmY) -->
            <line x1="{{ $bmX }}" y1="40" x2="{{ $bmX }}" y2="500" stroke="#059669" stroke-width="2.5" stroke-dasharray="6 6" />
            <line x1="230" y1="{{ $bmY }}" x2="840" y2="{{ $bmY }}" stroke="#059669" stroke-width="2.5" stroke-dasharray="6 6" />
            
            <!-- Benchmark Blue Star Icon -->
            <g transform="translate({{ $bmX }}, {{ $bmY }})">
                <circle cx="0" cy="0" r="16" fill="#0284c7" stroke="#ffffff" stroke-width="2.5" filter="url(#matrixBadgeShadow)"/>
                <path d="M0 -8.5 L2.5 -3.2 L8.2 -2.4 L4.1 1.6 L5.1 7.2 L0 4.5 L-5.1 7.2 L-4.1 1.6 L-8.2 -2.4 L-2.5 -3.2 Z" fill="#ffffff"/>
            </g>

            <!-- CURRENT POSITION (Dynamic curX, curY) -->
            <line x1="{{ $curX }}" y1="{{ $curY }}" x2="{{ $curX }}" y2="500" stroke="#581c87" stroke-width="2.2" stroke-dasharray="5 5" />
            <line x1="230" y1="{{ $curY }}" x2="{{ $curX }}" y2="{{ $curY }}" stroke="#581c87" stroke-width="2.2" stroke-dasharray="5 5" />

            <!-- Violet Pill Badges on Axes -->
            <!-- Y-Axis Badge -->
            <g transform="translate(230, {{ $curY }})">
                <rect x="-48" y="-13" width="44" height="26" rx="6" fill="#581c87" filter="url(#matrixBadgeShadow)"/>
                <text x="-26" y="4" text-anchor="middle" font-size="12" font-weight="900" fill="#ffffff">{{ number_format($sync, 1) }}</text>
            </g>
            <!-- X-Axis Badge -->
            <g transform="translate({{ $curX }}, 500)">
                <rect x="-22" y="4" width="44" height="26" rx="6" fill="#581c87" filter="url(#matrixBadgeShadow)"/>
                <text x="0" y="21" text-anchor="middle" font-size="12" font-weight="900" fill="#ffffff">{{ number_format($cq, 1) }}</text>
            </g>

            <!-- Current Position Purple Marker -->
            <circle cx="{{ $curX }}" cy="{{ $curY }}" r="11" fill="#581c87" stroke="#ffffff" stroke-width="3" filter="url(#matrixMarkerGlow)"/>

            <!-- Tooltip Card next to Current Position -->
            <g transform="translate({{ $tooltipX }}, {{ $tooltipY }})">
                <rect x="0" y="0" width="138" height="50" rx="10" fill="#ffffff" stroke="#e0e7ff" stroke-width="1.5" filter="url(#matrixBadgeShadow)"/>
                <text x="14" y="22" font-size="11" font-weight="900" fill="#1e1b4b">Current Position</text>
                <text x="14" y="40" font-size="12" font-weight="800" fill="#4338ca">({{ number_format($cq, 1) }}, {{ number_format($sync, 1) }})</text>
            </g>

            <!-- X-Axis Numeric Ticks -->
            <text x="230" y="522" text-anchor="middle" font-size="13" font-weight="700" fill="#334155">0</text>
            <text x="352" y="522" text-anchor="middle" font-size="13" font-weight="700" fill="#334155">2</text>
            <text x="474" y="522" text-anchor="middle" font-size="13" font-weight="700" fill="#334155">4</text>
            <text x="596" y="522" text-anchor="middle" font-size="13" font-weight="700" fill="#334155">6</text>
            <text x="718" y="522" text-anchor="middle" font-size="13" font-weight="700" fill="#334155">8</text>
            <text x="840" y="522" text-anchor="middle" font-size="13" font-weight="700" fill="#334155">10</text>

            <!-- X-Axis Capability Bands (Bottom: Y = 540 to 582) -->
            <!-- Band 1: Resistant (1.0 - 2.0) -->
            <rect x="230" y="540" width="118" height="42" rx="8" fill="#ffe4e6" stroke="#fecdd3" stroke-width="1.2"/>
            <text x="289" y="558" text-anchor="middle" font-size="11" font-weight="800" fill="#be123c">Resistant</text>
            <text x="289" y="573" text-anchor="middle" font-size="10" font-weight="600" fill="#e11d48">(1.0 – 2.0)</text>

            <!-- Band 2: Follower (2.1 - 4.0) -->
            <rect x="352" y="540" width="118" height="42" rx="8" fill="#ffedd5" stroke="#fed7aa" stroke-width="1.2"/>
            <text x="411" y="558" text-anchor="middle" font-size="11" font-weight="800" fill="#c2410c">Follower</text>
            <text x="411" y="573" text-anchor="middle" font-size="10" font-weight="600" fill="#ea580c">(2.1 – 4.0)</text>

            <!-- Band 3: Supporter (4.1 - 6.0) -->
            <rect x="474" y="540" width="118" height="42" rx="8" fill="#dcfce7" stroke="#bbf7d0" stroke-width="1.2"/>
            <text x="533" y="558" text-anchor="middle" font-size="11" font-weight="800" fill="#15803d">Supporter</text>
            <text x="533" y="573" text-anchor="middle" font-size="10" font-weight="600" fill="#16a34a">(4.1 – 6.0)</text>

            <!-- Band 4: Driver (6.1 - 8.0) -->
            <rect x="596" y="540" width="118" height="42" rx="8" fill="#fef3c7" stroke="#fde68a" stroke-width="1.2"/>
            <text x="655" y="558" text-anchor="middle" font-size="11" font-weight="800" fill="#b45309">Driver</text>
            <text x="655" y="573" text-anchor="middle" font-size="10" font-weight="600" fill="#d97706">(6.1 – 8.0)</text>

            <!-- Band 5: Champion (8.1 - 10) -->
            <rect x="718" y="540" width="122" height="42" rx="8" fill="#e0f2fe" stroke="#bae6fd" stroke-width="1.2"/>
            <text x="779" y="558" text-anchor="middle" font-size="11" font-weight="800" fill="#0369a1">Champion</text>
            <text x="779" y="573" text-anchor="middle" font-size="10" font-weight="600" fill="#0284c7">(8.1 – 10)</text>

            <!-- Bottom X-Axis Label -->
            <text x="535" y="605" text-anchor="middle" font-size="14" font-weight="900" fill="#0f172a" letter-spacing="0.5">CQ Group Score</text>
            <text x="535" y="618" text-anchor="middle" font-size="11" font-weight="600" fill="#64748b">(Higher = Greater Change Capability)</text>
        </svg>
    </div>
</div>
