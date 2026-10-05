@props([
    'cqScore' => null,
    'syncScore' => null,
    'benchmarkCq' => null,
    'benchmarkSync' => null,
    'insights' => null,
])

@php
    $curCQ = (float) ($cqScore ?? ($insights['team_cq_score'] ?? 0.0));
    $curSync = (float) ($syncScore ?? ($insights['team_cq_sync_score'] ?? 0.0));
    $bmCQ = (float) ($benchmarkCq ?? ($insights['benchmark_cq'] ?? 8.0));
    $bmSync = (float) ($benchmarkSync ?? ($insights['benchmark_sync'] ?? 8.0));

    $gapCQ = $curCQ - $bmCQ;
    $gapSync = $curSync - $bmSync;

    $gapCQTotal = max(0.0, $bmCQ - $curCQ);
    $gapSyncTotal = max(0.0, $bmSync - $curSync);

    // Realistic milestone progression matching the 30-60-90 day curve from reference design:
    if ($gapCQTotal > 0 || $gapSyncTotal > 0) {
        $p30CQ = min($bmCQ, round($curCQ + ($gapCQTotal * 0.17), 1));
        $p30Sync = min($bmSync, round($curSync + ($gapSyncTotal * 0.25), 1));

        $p60CQ = min($bmCQ, round($curCQ + ($gapCQTotal * 0.45), 1));
        $p60Sync = min($bmSync, round($curSync + ($gapSyncTotal * 0.50), 1));

        $p90CQ = min($bmCQ, round($curCQ + ($gapCQTotal * 0.78), 1));
        $p90Sync = min($bmSync, round($curSync + ($gapSyncTotal * 0.75), 1));
    } else {
        $p30CQ = $curCQ;
        $p30Sync = $curSync;
        $p60CQ = $curCQ;
        $p60Sync = $curSync;
        $p90CQ = $curCQ;
        $p90Sync = $curSync;
    }

    $fCurCQ = number_format($curCQ, 1);
    $fCurSync = number_format($curSync, 1);
    $f30CQ = number_format($p30CQ, 1);
    $f30Sync = number_format($p30Sync, 1);
    $f60CQ = number_format($p60CQ, 1);
    $f60Sync = number_format($p60Sync, 1);
    $f90CQ = number_format($p90CQ, 1);
    $f90Sync = number_format($p90Sync, 1);
    $fBmCQ = number_format($bmCQ, 1);
    $fBmSync = number_format($bmSync, 1);

    // Dynamic trajectory guidance callout
    if ($gapCQ < 0 && $gapSync < 0) {
        $trajectoryCallout = 'Focus on targeted actions to improve synchronisation, while continuing to build change capability.';
    } elseif ($gapCQ < 0 && $gapSync >= 0) {
        $trajectoryCallout = 'Focus on targeted actions to build change capability, while maintaining your strong team synchronisation.';
    } elseif ($gapCQ >= 0 && $gapSync < 0) {
        $trajectoryCallout = 'Focus on targeted actions to improve synchronisation to fully leverage your strong change capability.';
    } else {
        $trajectoryCallout = 'Target benchmark achieved across both capability and synchronisation. Continue consistent habits to sustain peak performance.';
    }
@endphp

<div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-4">
    <!-- Header Matching Reference Design -->
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background-color: #f4f2fd !important;">
            <svg class="w-5 h-5 text-[#6f01d2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <circle cx="12" cy="12" r="6"/>
                <circle cx="12" cy="12" r="2" fill="currentColor"/>
                <path d="M19 5l-5 5"/>
                <path d="M15 5h4v4"/>
            </svg>
        </div>
        <h3 class="text-base sm:text-lg font-black text-[#0a0f37] tracking-tight">Path to the Opportunity Zone</h3>
    </div>

    <!-- Interactive / Responsive Trajectory Improvement Chart (1:1 with photo) -->
    <div class="w-full relative">
        <svg viewBox="0 0 430 360" class="w-full h-auto select-none" xmlns="http://www.w3.org/2000/svg" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif">
            <!-- Opportunity Zone dashed box -->
            <rect x="185" y="10" width="235" height="195" rx="14" fill="#e6fded" stroke="#86efac" stroke-width="1.5" stroke-dasharray="5,4" />
            <text x="302" y="38" text-anchor="middle" fill="#065f46" font-size="13" font-weight="800">Opportunity Zone</text>
            <text x="302" y="55" text-anchor="middle" fill="#047857" font-size="11" font-weight="600">(High CQ, High Sync)</text>

            <!-- Trajectory upward curve -->
            <path d="M 50 220 C 110 205, 160 185, 205 169 C 255 149, 290 120, 325 80" fill="none" stroke="#38bdf8" stroke-width="4.5" stroke-linecap="round" />

            <!-- Point 1: Current -->
            <circle cx="50" cy="220" r="11" fill="#6f01d2" stroke="#ffffff" stroke-width="2.5" />
            <text x="50" y="248" text-anchor="middle" fill="#0a0f37" font-size="13" font-weight="900">Current</text>
            <text x="50" y="266" text-anchor="middle" fill="#6f01d2" font-size="13" font-weight="800">({{ $fCurCQ }}, {{ $fCurSync }})</text>

            <!-- Point 2: 30 Days -->
            <text x="124" y="157" text-anchor="middle" fill="#c2410c" font-size="12" font-weight="800">30 Days</text>
            <text x="124" y="173" text-anchor="middle" fill="#c2410c" font-size="11" font-weight="700">({{ $f30CQ }}, {{ $f30Sync }})</text>
            <circle cx="124" cy="193" r="9.5" fill="#f97316" stroke="#ffffff" stroke-width="2.5" />

            <!-- Point 3: 60 Days -->
            <text x="200" y="129" text-anchor="middle" fill="#0284c7" font-size="12" font-weight="800">60 Days</text>
            <text x="200" y="145" text-anchor="middle" fill="#0284c7" font-size="11" font-weight="700">({{ $f60CQ }}, {{ $f60Sync }})</text>
            <circle cx="200" cy="169" r="9.5" fill="#0ea5e9" stroke="#ffffff" stroke-width="2.5" />

            <!-- Point 4: 90 Days -->
            <text x="280" y="95" text-anchor="middle" fill="#047857" font-size="12" font-weight="800">90 Days</text>
            <text x="280" y="111" text-anchor="middle" fill="#047857" font-size="11" font-weight="700">({{ $f90CQ }}, {{ $f90Sync }})</text>
            <circle cx="280" cy="139" r="9.5" fill="#10b981" stroke="#ffffff" stroke-width="2.5" />

            <!-- Point 5: Target Star & Label -->
            <polygon points="325,68 328.5,76.3 336.4,76.3 330,81 332.4,88.5 325,83.8 317.6,88.5 320,81 313.6,76.3 321.5,76.3" fill="#10b981" />
            <text x="345" y="73" text-anchor="start" fill="#065f46" font-size="13" font-weight="900">Target</text>
            <text x="345" y="90" text-anchor="start" fill="#065f46" font-size="13" font-weight="800">({{ $fBmCQ }}, {{ $fBmSync }})</text>

            <!-- Bottom Callout (Inside SVG for perfect alignment) -->
            <foreignObject x="135" y="240" width="285" height="110">
                <div xmlns="http://www.w3.org/1999/xhtml" style="background-color: #f0fbf5; border: 1px solid #d1fae5; border-radius: 1rem; padding: 0.85rem 1rem; font-size: 12px; line-height: 1.45; color: #334155; font-weight: 500;">
                    {{ $trajectoryCallout }}
                </div>
            </foreignObject>
        </svg>
    </div>
</div>
