@props([
    'insights',
])

@php
    $stats = $insights['statistics'] ?? [];
    $dist = $insights['distribution'] ?? [];
    $bins = $insights['histogram_bins'] ?? [];
    $cohortSize = $insights['cohort_size'] ?? count($insights['individual_cq_scores'] ?? []);

    $mean = (float) ($stats['mean'] ?? $insights['team_cq_score'] ?? 0.0);
    $median = (float) ($stats['median'] ?? $insights['team_cq_score'] ?? 0.0);
    $benchmark = (float) ($stats['benchmark'] ?? $insights['benchmark_cq'] ?? 8.0);

    // Defensive fallback if histogram_bins was not provided
    if (empty($bins)) {
        $bins = [
            0 => ['from' => 0.0, 'to' => 1.0, 'count' => 0, 'archetype' => 'resistant', 'color' => '#f87171'],
            1 => ['from' => 1.0, 'to' => 2.0, 'count' => 0, 'archetype' => 'resistant', 'color' => '#f87171'],
            2 => ['from' => 2.0, 'to' => 3.0, 'count' => 0, 'archetype' => 'follower', 'color' => '#fb923c'],
            3 => ['from' => 3.0, 'to' => 4.0, 'count' => 0, 'archetype' => 'follower', 'color' => '#fb923c'],
            4 => ['from' => 4.0, 'to' => 5.0, 'count' => 0, 'archetype' => 'supporter', 'color' => '#86efac'],
            5 => ['from' => 5.0, 'to' => 6.0, 'count' => 0, 'archetype' => 'supporter', 'color' => '#86efac'],
            6 => ['from' => 6.0, 'to' => 7.0, 'count' => 0, 'archetype' => 'driver', 'color' => '#fde047'],
            7 => ['from' => 7.0, 'to' => 8.0, 'count' => 0, 'archetype' => 'driver', 'color' => '#fde047'],
            8 => ['from' => 8.0, 'to' => 9.0, 'count' => 0, 'archetype' => 'champion', 'color' => '#60a5fa'],
            9 => ['from' => 9.0, 'to' => 10.0, 'count' => 0, 'archetype' => 'champion', 'color' => '#60a5fa'],
        ];
        foreach ($insights['individual_cq_scores'] ?? [] as $s) {
            if ($s <= 1.0) $bins[0]['count']++;
            elseif ($s <= 2.0) $bins[1]['count']++;
            elseif ($s <= 3.0) $bins[2]['count']++;
            elseif ($s <= 4.0) $bins[3]['count']++;
            elseif ($s <= 5.0) $bins[4]['count']++;
            elseif ($s <= 6.0) $bins[5]['count']++;
            elseif ($s <= 7.0) $bins[6]['count']++;
            elseif ($s <= 8.0) $bins[7]['count']++;
            elseif ($s <= 9.0) $bins[8]['count']++;
            else $bins[9]['count']++;
        }
    }

    // Determine dynamic Y-axis maximum scale
    $binCounts = array_map(fn($b) => (int)($b['count'] ?? 0), $bins);
    $maxCount = !empty($binCounts) ? max($binCounts) : 0;

    if ($maxCount <= 4) {
        $maxY = 4;
        $yTicks = [0, 1, 2, 3, 4];
    } elseif ($maxCount <= 8) {
        $maxY = 8;
        $yTicks = [0, 2, 4, 6, 8];
    } elseif ($maxCount <= 12) {
        $maxY = 12;
        $yTicks = [0, 3, 6, 9, 12];
    } elseif ($maxCount <= 16) {
        $maxY = 16;
        $yTicks = [0, 4, 8, 12, 16];
    } elseif ($maxCount <= 20) {
        $maxY = 20;
        $yTicks = [0, 5, 10, 15, 20];
    } elseif ($maxCount <= 28) {
        $maxY = 28;
        $yTicks = [0, 7, 14, 21, 28];
    } else {
        $step = max(5, (int) ceil($maxCount / 4 / 5) * 5);
        $maxY = $step * 4;
        $yTicks = [0, $step, $step * 2, $step * 3, $maxY];
    }

    // SVG Layout Dimensions
    $chartX = 65;
    $chartY = 55;
    $chartWidth = 670;
    $chartHeight = 225;
    $baselineY = $chartY + $chartHeight; // 280

    // Coordinate helpers
    $scoreToX = fn($score) => $chartX + (max(0.0, min(10.0, (float)$score)) / 10.0) * $chartWidth;
    $countToHeight = fn($cnt) => $maxY > 0 ? ((float)$cnt / $maxY) * $chartHeight : 0;

    $meanX = $scoreToX($mean);
    $medianX = $scoreToX($median);
    $benchmarkX = $scoreToX($benchmark);

    // Collision avoidance for top labels if Mean and Median are close
    $meanLabelX = $meanX;
    $medianLabelX = $medianX;
    $diff = abs($meanX - $medianX);
    if ($diff < 36) {
        $offset = max(14, (36 - $diff) / 2);
        if ($mean <= $median) {
            $meanLabelX = max($chartX + 20, $meanX - $offset);
            $medianLabelX = min($chartX + $chartWidth - 20, $medianX + $offset);
        } else {
            $meanLabelX = min($chartX + $chartWidth - 20, $meanX + $offset);
            $medianLabelX = max($chartX + 20, $medianX - $offset);
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xs space-y-5']) }}>
    <!-- Header: Title and Subtitle matching template -->
    <div>
        <h3 class="text-xl sm:text-2xl font-black text-[#0a0f37] tracking-tight">Team CQ Distribution</h3>
        <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Spread of normalised CQ scores across {{ $cohortSize }} employees</p>
    </div>

    <!-- SVG Histogram Chart -->
    <div class="w-full overflow-x-auto">
        <svg viewBox="0 0 760 350" class="w-full h-auto min-w-[560px] select-none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <!-- Subtle grid style -->
                <style>
                    .hist-grid-line { stroke: #f1f5f9; stroke-width: 1.2; }
                    .hist-axis-line { stroke: #64748b; stroke-width: 1.5; }
                    .hist-tick-mark { stroke: #94a3b8; stroke-width: 1.2; }
                    .hist-tick-text { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 12px; font-weight: 600; fill: #475569; }
                    .hist-axis-title { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 13px; font-weight: 600; fill: #334155; }
                    .hist-marker-title { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 13px; font-weight: 700; }
                    .hist-marker-val { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; font-weight: 800; }
                </style>
            </defs>

            <!-- Horizontal Grid Lines and Y-Axis Ticks -->
            @foreach($yTicks as $tick)
                @php
                    $yPos = $baselineY - ($tick / $maxY) * $chartHeight;
                @endphp
                <line x1="{{ $chartX }}" y1="{{ $yPos }}" x2="{{ $chartX + $chartWidth }}" y2="{{ $yPos }}" class="hist-grid-line" />
                <line x1="{{ $chartX - 5 }}" y1="{{ $yPos }}" x2="{{ $chartX }}" y2="{{ $yPos }}" class="hist-tick-mark" />
                <text x="{{ $chartX - 10 }}" y="{{ $yPos + 4 }}" text-anchor="end" class="hist-tick-text">{{ $tick }}</text>
            @endforeach

            <!-- Vertical Grid Lines and X-Axis Ticks (0 through 10) -->
            @for($i = 0; $i <= 10; $i++)
                @php
                    $xPos = $scoreToX($i);
                @endphp
                <line x1="{{ $xPos }}" y1="{{ $chartY }}" x2="{{ $xPos }}" y2="{{ $baselineY }}" class="hist-grid-line" />
                <line x1="{{ $xPos }}" y1="{{ $baselineY }}" x2="{{ $xPos }}" y2="{{ $baselineY + 6 }}" class="hist-tick-mark" />
                <text x="{{ $xPos }}" y="{{ $baselineY + 22 }}" text-anchor="middle" class="hist-tick-text">{{ $i }}</text>
            @endfor

            <!-- Y-Axis and X-Axis Lines -->
            <line x1="{{ $chartX }}" y1="{{ $chartY }}" x2="{{ $chartX }}" y2="{{ $baselineY }}" class="hist-axis-line" />
            <line x1="{{ $chartX }}" y1="{{ $baselineY }}" x2="{{ $chartX + $chartWidth }}" y2="{{ $baselineY }}" class="hist-axis-line" />

            <!-- Axis Labels -->
            <text transform="rotate(-90)" x="{{ -($chartY + $chartHeight / 2) }}" y="18" text-anchor="middle" class="hist-axis-title">Number of Employees</text>
            <text x="{{ $chartX + $chartWidth / 2 }}" y="{{ $baselineY + 46 }}" text-anchor="middle" class="hist-axis-title">Normalised CQ Score</text>

            <!-- Histogram Bars (10 Bins) -->
            @foreach($bins as $i => $bin)
                @php
                    $cnt = (int) ($bin['count'] ?? 0);
                    $barH = $countToHeight($cnt);
                    $binXStart = $scoreToX($bin['from']);
                    $binXEnd = $scoreToX($bin['to']);
                    $barW = max(4, ($binXEnd - $binXStart) - 2);
                    $barX = $binXStart + 1;
                    $barY = $baselineY - $barH;
                    $barColor = $bin['color'] ?? '#94a3b8';
                @endphp
                @if($cnt > 0)
                    <rect x="{{ round($barX, 1) }}"
                          y="{{ round($barY, 1) }}"
                          width="{{ round($barW, 1) }}"
                          height="{{ round($barH, 1) }}"
                          rx="2"
                          fill="{{ $barColor }}"
                          opacity="0.95">
                        <title>{{ $bin['archetype'] ?? 'Bin' }} ({{ $bin['from'] }} - {{ $bin['to'] }}): {{ $cnt }} employee{{ $cnt === 1 ? '' : 's' }}</title>
                    </rect>
                @endif
            @endforeach

            <!-- Dynamic Indicator Lines & Labels: Mean, Median, Benchmark -->
            
            <!-- Benchmark Indicator Line -->
            @if($benchmark > 0)
                <g>
                    <text x="{{ round($benchmarkX, 1) }}" y="20" text-anchor="middle" class="hist-marker-title" fill="#16a34a">Benchmark</text>
                    <text x="{{ round($benchmarkX, 1) }}" y="36" text-anchor="middle" class="hist-marker-val" fill="#16a34a">{{ number_format($benchmark, 1) }}</text>
                    <line x1="{{ round($benchmarkX, 1) }}" y1="44" x2="{{ round($benchmarkX, 1) }}" y2="{{ $baselineY }}" stroke="#16a34a" stroke-width="1.8" stroke-dasharray="4 4" />
                </g>
            @endif

            <!-- Mean Indicator Line -->
            @if($mean > 0)
                <g>
                    <text x="{{ round($meanLabelX, 1) }}" y="20" text-anchor="middle" class="hist-marker-title" fill="#0284c7">Mean</text>
                    <text x="{{ round($meanLabelX, 1) }}" y="36" text-anchor="middle" class="hist-marker-val" fill="#0284c7">{{ number_format($mean, 1) }}</text>
                    <line x1="{{ round($meanX, 1) }}" y1="44" x2="{{ round($meanX, 1) }}" y2="{{ $baselineY }}" stroke="#0284c7" stroke-width="1.8" stroke-dasharray="4 4" />
                </g>
            @endif

            <!-- Median Indicator Line -->
            @if($median > 0)
                <g>
                    <text x="{{ round($medianLabelX, 1) }}" y="20" text-anchor="middle" class="hist-marker-title" fill="#7c3aed">Median</text>
                    <text x="{{ round($medianLabelX, 1) }}" y="36" text-anchor="middle" class="hist-marker-val" fill="#7c3aed">{{ number_format($median, 1) }}</text>
                    <line x1="{{ round($medianX, 1) }}" y1="44" x2="{{ round($medianX, 1) }}" y2="{{ $baselineY }}" stroke="#7c3aed" stroke-width="1.8" stroke-dasharray="4 4" />
                </g>
            @endif
        </svg>
    </div>

    <!-- Bottom 5-Archetype Distribution Summary Table (Matching Template Design) -->
    <div class="rounded-2xl overflow-hidden border border-slate-200/90 shadow-2xs grid grid-cols-5 text-center">
        <!-- 1. Resistant -->
        <div class="py-3 px-2 sm:px-4 flex flex-col items-center justify-center transition-colors border-r border-white/60"
             style="background-color: #fedee4 !important;">
            <div class="font-black text-xs sm:text-sm tracking-tight" style="color: #881337 !important;">Resistant</div>
            <div class="text-[11px] sm:text-xs font-semibold mt-0.5" style="color: #475569 !important;">1.0 – 2.0</div>
            <div class="text-xs sm:text-sm font-black mt-1" style="color: #be123c !important;">{{ $dist['resistant']['percentage'] ?? 0 }}%</div>
            <div class="text-[11px] sm:text-xs font-black mt-0.5" style="color: #0f172a !important;">({{ $dist['resistant']['count'] ?? 0 }})</div>
        </div>

        <!-- 2. Follower -->
        <div class="py-3 px-2 sm:px-4 flex flex-col items-center justify-center transition-colors border-r border-white/60"
             style="background-color: #faecd9 !important;">
            <div class="font-black text-xs sm:text-sm tracking-tight" style="color: #7c2d12 !important;">Follower</div>
            <div class="text-[11px] sm:text-xs font-semibold mt-0.5" style="color: #475569 !important;">2.1 – 4.0</div>
            <div class="text-xs sm:text-sm font-black mt-1" style="color: #0f172a !important;">{{ $dist['follower']['percentage'] ?? 0 }}%</div>
            <div class="text-[11px] sm:text-xs font-black mt-0.5" style="color: #0f172a !important;">({{ $dist['follower']['count'] ?? 0 }})</div>
        </div>

        <!-- 3. Supporter -->
        <div class="py-3 px-2 sm:px-4 flex flex-col items-center justify-center transition-colors border-r border-white/60"
             style="background-color: #d1efe0 !important;">
            <div class="font-black text-xs sm:text-sm tracking-tight" style="color: #064e3b !important;">Supporter</div>
            <div class="text-[11px] sm:text-xs font-semibold mt-0.5" style="color: #475569 !important;">4.1 – 6.0</div>
            <div class="text-xs sm:text-sm font-black mt-1" style="color: #0f172a !important;">{{ $dist['supporter']['percentage'] ?? 0 }}%</div>
            <div class="text-[11px] sm:text-xs font-black mt-0.5" style="color: #0f172a !important;">({{ $dist['supporter']['count'] ?? 0 }})</div>
        </div>

        <!-- 4. Driver -->
        <div class="py-3 px-2 sm:px-4 flex flex-col items-center justify-center transition-colors border-r border-white/60"
             style="background-color: #f9efcb !important;">
            <div class="font-black text-xs sm:text-sm tracking-tight" style="color: #78350f !important;">Driver</div>
            <div class="text-[11px] sm:text-xs font-semibold mt-0.5" style="color: #475569 !important;">6.1 – 8.0</div>
            <div class="text-xs sm:text-sm font-black mt-1" style="color: #0f172a !important;">{{ $dist['driver']['percentage'] ?? 0 }}%</div>
            <div class="text-[11px] sm:text-xs font-black mt-0.5" style="color: #0f172a !important;">({{ $dist['driver']['count'] ?? 0 }})</div>
        </div>

        <!-- 5. Champion -->
        <div class="py-3 px-2 sm:px-4 flex flex-col items-center justify-center transition-colors"
             style="background-color: #d3e4fd !important;">
            <div class="font-black text-xs sm:text-sm tracking-tight" style="color: #1e3a8a !important;">Champion</div>
            <div class="text-[11px] sm:text-xs font-semibold mt-0.5" style="color: #475569 !important;">8.1 – 10.0</div>
            <div class="text-xs sm:text-sm font-black mt-1" style="color: #0f172a !important;">{{ $dist['champion']['percentage'] ?? 0 }}%</div>
            <div class="text-[11px] sm:text-xs font-black mt-0.5" style="color: #0f172a !important;">({{ $dist['champion']['count'] ?? 0 }})</div>
        </div>
    </div>
</div>
