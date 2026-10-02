@props([
    'percentage' => 0.0,
    'category' => 'Pending',
    'score' => null,
    'maxScore' => null,
    'size' => 'md',
    'onlyGauge' => false,
])

@php
    $pct = max(0, min(100, (float) $percentage));
    
    // Needle orientation:
    // Base needle polygon points straight UP (0 deg = 50% mark).
    // 0%   => -90 deg (Left)
    // 50%  => 0 deg (Straight Up)
    // 100% => +90 deg (Right)
    $needleAngle = round(-90 + ($pct * 1.8), 3);
    
    // Circumference calculation for stroke-dasharray (r = 105, semi-circle length = PI * 105 ≈ 329.867)
    $radius = 105;
    $semiCircumference = 329.867;
    $dashOffset = round($semiCircumference * (1 - ($pct / 100)), 3);

    $catService = app(\App\Services\AssessmentCategoryService::class);
    $categoryConfig = [
        'emoji' => $category === 'Pending' ? '⏳' : $catService->getEmoji($category),
        'color' => $category === 'Pending' ? '#9CA3AF' : $catService->getColorHex($category),
        'badge' => $category === 'Pending' ? 'bg-gray-50 text-gray-700 border-gray-200 ring-gray-600/20' : $catService->getBadgeClass($category),
    ];
    $allCats = $catService->getAllCategories();
@endphp

@if($onlyGauge)
    <!-- Pure Gauge Mode (First Section) -->
    <div class="w-full flex flex-col items-center justify-center select-none"
         x-data="{ mounted: false }"
         x-init="setTimeout(() => mounted = true, 50)">
        
        <div class="relative w-full aspect-[2/1] overflow-visible flex items-end justify-center">
            <svg viewBox="0 0 320 160" class="w-full h-auto overflow-visible select-none">
                <defs>
                    <filter id="needleShadowGauge" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="2.5" flood-opacity="0.25"/>
                    </filter>
                </defs>

                <!-- Background Outer Track (Center: 160, 140, r=105) -->
                <path d="M 55 140 A 105 105 0 0 1 265 140"
                      fill="none"
                      stroke="#F1F5F9"
                      stroke-width="24"
                      stroke-linecap="round" />

                <!-- Distinct 5 Category Segments (36 deg each) -->
                <!-- 1. Apple (0–20%) -->
                <path d="M 55.00 140.00 A 105 105 0 0 1 75.05 78.28"
                      fill="none"
                      stroke="#10B981"
                      stroke-width="20"
                      stroke-linecap="round"
                      opacity="0.30" />

                <!-- 2. Orange (>20–40%) -->
                <path d="M 75.05 78.28 A 105 105 0 0 1 127.55 40.14"
                      fill="none"
                      stroke="#F97316"
                      stroke-width="20"
                      opacity="0.30" />

                <!-- 3. Tomato (>40–60%) -->
                <path d="M 127.55 40.14 A 105 105 0 0 1 192.45 40.14"
                      fill="none"
                      stroke="#EF4444"
                      stroke-width="20"
                      opacity="0.30" />

                <!-- 4. Lemon (>60–80%) -->
                <path d="M 192.45 40.14 A 105 105 0 0 1 244.95 78.28"
                      fill="none"
                      stroke="#F59E0B"
                      stroke-width="20"
                      opacity="0.30" />

                <!-- 5. Cucumber (>80–100%) -->
                <path d="M 244.95 78.28 A 105 105 0 0 1 265.00 140.00"
                      fill="none"
                      stroke="#059669"
                      stroke-width="20"
                      stroke-linecap="round"
                      opacity="0.30" />

                <!-- Active Score Track Fill from 0% up to current % -->
                <path d="M 55 140 A 105 105 0 0 1 265 140"
                      fill="none"
                      stroke="{{ $categoryConfig['color'] }}"
                      stroke-width="20"
                      stroke-linecap="round"
                      stroke-dasharray="329.867"
                      :stroke-dashoffset="mounted ? {{ $dashOffset }} : 329.867"
                      class="transition-all duration-1000 ease-out" />

                <!-- White Boundary Dividers -->
                <line x1="83.95" y1="84.74" x2="66.15" y2="71.83" stroke="#FFFFFF" stroke-width="3" />
                <line x1="130.95" y1="50.60" x2="124.15" y2="29.68" stroke="#FFFFFF" stroke-width="3" />
                <line x1="189.05" y1="50.60" x2="195.85" y2="29.68" stroke="#FFFFFF" stroke-width="3" />
                <line x1="236.05" y1="84.74" x2="253.85" y2="71.83" stroke="#FFFFFF" stroke-width="3" />

                <!-- Tick Labels on Arc -->
                <text x="36" y="150" font-size="10" font-weight="700" fill="#94A3B8" text-anchor="middle">0%</text>
                <text x="52" y="68" font-size="10" font-weight="700" fill="#94A3B8" text-anchor="middle">20%</text>
                <text x="116" y="20" font-size="10" font-weight="700" fill="#94A3B8" text-anchor="middle">40%</text>
                <text x="204" y="20" font-size="10" font-weight="700" fill="#94A3B8" text-anchor="middle">60%</text>
                <text x="268" y="68" font-size="10" font-weight="700" fill="#94A3B8" text-anchor="middle">80%</text>
                <text x="284" y="150" font-size="10" font-weight="700" fill="#94A3B8" text-anchor="middle">100%</text>

                <!-- Gauge Needle / Pointer (rotates around 160, 140) -->
                <g transform="translate(160, 140)">
                    <g :style="`transform: rotate(${mounted ? {{ $needleAngle }} : -90}deg); transition: transform 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);`"
                       filter="url(#needleShadowGauge)">
                        <!-- Needle blade -->
                        <polygon points="-5,-12 0,-106 5,-12" fill="#1E293B" />
                        <!-- Center pivot cap -->
                        <circle cx="0" cy="0" r="11" fill="#1E293B" />
                        <circle cx="0" cy="0" r="4.5" fill="{{ $categoryConfig['color'] }}" />
                        <!-- Needle tip bead -->
                        <circle cx="0" cy="-106" r="3.5" fill="{{ $categoryConfig['color'] }}" />
                    </g>
                </g>
            </svg>
        </div>
    </div>
@else
    <!-- Standalone Card Mode with all embedded stats -->
    <div class="w-[300px] h-[200px] flex flex-col justify-between items-center px-4 py-2.5 bg-white rounded-2xl shadow-xs border border-slate-200/90 mx-auto select-none overflow-hidden shrink-0"
         x-data="{ mounted: false }"
         x-init="setTimeout(() => mounted = true, 50)">
        
        <span class="text-[9px] font-bold tracking-wider text-slate-400 uppercase leading-none">360° Score Gauge</span>
        
        <div class="relative w-full aspect-[2.1/1] overflow-visible flex items-end justify-center">
            <svg viewBox="0 0 280 135" class="w-full h-auto overflow-visible select-none">
                <defs>
                    <filter id="needleShadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="1.5" stdDeviation="2" flood-opacity="0.25"/>
                    </filter>
                </defs>

                <path d="M 45 120 A 95 95 0 0 1 235 120" fill="none" stroke="#F1F5F9" stroke-width="20" stroke-linecap="round" />
                <path d="M 45.00 120.00 A 95 95 0 0 1 63.14 64.16" fill="none" stroke="#10B981" stroke-width="16" stroke-linecap="round" opacity="0.25" />
                <path d="M 63.14 64.16 A 95 95 0 0 1 110.64 29.65" fill="none" stroke="#F97316" stroke-width="16" opacity="0.25" />
                <path d="M 110.64 29.65 A 95 95 0 0 1 169.36 29.65" fill="none" stroke="#EF4444" stroke-width="16" opacity="0.25" />
                <path d="M 169.36 29.65 A 95 95 0 0 1 216.86 64.16" fill="none" stroke="#F59E0B" stroke-width="16" opacity="0.25" />
                <path d="M 216.86 64.16 A 95 95 0 0 1 235.00 120.00" fill="none" stroke="#059669" stroke-width="16" stroke-linecap="round" opacity="0.25" />

                <path d="M 45 120 A 95 95 0 0 1 235 120" fill="none" stroke="{{ $categoryConfig['color'] }}" stroke-width="16" stroke-linecap="round" stroke-dasharray="298.451" :stroke-dashoffset="mounted ? {{ $dashOffset }} : 298.451" class="transition-all duration-1000 ease-out" />

                <line x1="70.42" y1="69.45" x2="55.87" y2="58.88" stroke="#FFFFFF" stroke-width="2.5" />
                <line x1="113.42" y1="38.21" x2="107.85" y2="21.09" stroke="#FFFFFF" stroke-width="2.5" />
                <line x1="166.58" y1="38.21" x2="172.15" y2="21.09" stroke="#FFFFFF" stroke-width="2.5" />
                <line x1="209.58" y1="69.45" x2="224.13" y2="58.88" stroke="#FFFFFF" stroke-width="2.5" />

                <text x="32" y="130" font-size="9" font-weight="700" fill="#94A3B8" text-anchor="middle">0%</text>
                <text x="46" y="55" font-size="9" font-weight="700" fill="#94A3B8" text-anchor="middle">20%</text>
                <text x="102" y="14" font-size="9" font-weight="700" fill="#94A3B8" text-anchor="middle">40%</text>
                <text x="178" y="14" font-size="9" font-weight="700" fill="#94A3B8" text-anchor="middle">60%</text>
                <text x="234" y="55" font-size="9" font-weight="700" fill="#94A3B8" text-anchor="middle">80%</text>
                <text x="250" y="130" font-size="9" font-weight="700" fill="#94A3B8" text-anchor="middle">100%</text>

                <g transform="translate(140, 120)">
                    <g :style="`transform: rotate(${mounted ? {{ $needleAngle }} : -90}deg); transition: transform 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);`" filter="url(#needleShadow)">
                        <polygon points="-4,-10 0,-92 4,-10" fill="#1E293B" />
                        <circle cx="0" cy="0" r="9" fill="#1E293B" />
                        <circle cx="0" cy="0" r="3.5" fill="{{ $categoryConfig['color'] }}" />
                        <circle cx="0" cy="-92" r="2.5" fill="{{ $categoryConfig['color'] }}" />
                    </g>
                </g>
            </svg>

            <div class="absolute bottom-0 flex flex-col items-center text-center select-none pointer-events-none">
                <span class="text-2xl font-black tracking-tight text-slate-900 leading-none">
                    {{ number_format($pct, 2) }}<span class="text-sm font-bold text-slate-400">%</span>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border shadow-2xs {{ $categoryConfig['badge'] }}">
                <span>{{ $categoryConfig['emoji'] }}</span>
                <span>{{ $category }}</span>
            </div>
            @if($score !== null && $maxScore !== null)
                <span class="text-[11px] text-slate-500 font-semibold">{{ $score }} / {{ $maxScore }} pts</span>
            @endif
        </div>

        <div class="w-full pt-2 border-t border-slate-100 flex items-center justify-around text-center text-[9px] leading-tight">
            @foreach($allCats as $cItem)
                <div class="flex flex-col items-center">
                    <span>{{ $cItem['emoji'] }}</span>
                    <span class="font-bold text-slate-700 truncate max-w-[55px]">{{ $cItem['name'] }}</span>
                    <span class="text-slate-400 text-[8px]">{{ $cItem['range'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
