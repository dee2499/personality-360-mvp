<x-layouts.app>
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Top Back Link & Confidentiality Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('participant.assessments.index', ['survey_id' => $survey->id]) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Assessments</span>
            </a>

            <!-- Confidentiality Notice Tag -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Confidential: For Your Self-Introspection Only</span>
            </div>
        </div>

        <!-- Report Header Card -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($survey->company)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i data-lucide="building-2" class="w-3 h-3 text-indigo-500"></i>
                                {{ $survey->company->name }}
                            </span>
                        @endif
                        <span class="text-xs text-slate-400 font-medium">360° Change Quotient Evaluation</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Individual Change Quotient (CQ) Report
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
                        Survey: <span class="font-bold text-slate-800">{{ $survey->title }}</span> • Evaluated Subject: <span class="font-bold text-slate-800">{{ $user->name }}</span>
                    </p>
                </div>

                <!-- Action Button for Team Insights -->
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('participant.surveys.group-insights', $survey) }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                        <i data-lucide="users" class="w-4 h-4 text-slate-600"></i>
                        <span>View Team Group Insights</span>
                    </a>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500">
                <i data-lucide="info" class="w-4 h-4 text-indigo-600 shrink-0"></i>
                <span>{{ $cq['confidential_notice'] }} Your peer reviewers' names and individual ratings remain anonymous.</span>
            </div>
        </div>

        <!-- THE 4 CHANGE QUOTIENT (CQ) CARDS -->
        <div>
            <div class="mb-4">
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                    Change Quotient Metrics
                </span>
                <h2 class="text-lg font-black text-slate-900 mt-1">Core Assessment Indices (CQ 1 – CQ Sync)</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. CQ 1 (Self) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between relative overflow-hidden">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-indigo-600">CQ 1 (Self)</span>
                            <span class="text-xl">{{ $cq['cq1']['emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ $cq['cq1']['label'] }}</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Personal self-evaluation</p>
                        </div>

                        <div class="pt-2">
                            <div class="text-3xl font-black text-slate-900">
                                {{ $cq['cq1']['percentage'] }}%
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-indigo-600 transition-all duration-700" 
                                     style="width: {{ $cq['cq1']['percentage'] }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] pt-1">
                            <span class="text-slate-500 font-medium">Category:</span>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $cq['cq1']['badge'] }}">
                                {{ $cq['cq1']['category'] }}
                            </span>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100 leading-normal">
                        {{ $cq['cq1']['description'] }}
                    </p>
                </div>

                <!-- 2. CQ 2 (Others) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between relative overflow-hidden">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-600">CQ 2 (Others)</span>
                            <span class="text-xl">{{ $cq['cq2']['emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ $cq['cq2']['label'] }}</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Peer observer consensus</p>
                        </div>

                        <div class="pt-2">
                            <div class="text-3xl font-black text-slate-900">
                                {{ $cq['cq2']['percentage'] }}%
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-emerald-600 transition-all duration-700" 
                                     style="width: {{ $cq['cq2']['percentage'] }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] pt-1">
                            <span class="text-slate-500 font-medium">Reviews Completed:</span>
                            <span class="font-extrabold text-slate-800">
                                {{ $cq['cq2']['completed_count'] }} of {{ $cq['cq2']['total_count'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 font-medium">Category:</span>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $cq['cq2']['badge'] }}">
                                {{ $cq['cq2']['category'] }}
                            </span>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100 leading-normal">
                        {{ $cq['cq2']['description'] }}
                    </p>
                </div>

                <!-- 3. CQ 3 (Normalised) -->
                <div class="bg-white rounded-3xl border border-indigo-200 p-6 shadow-xs flex flex-col justify-between relative overflow-hidden bg-gradient-to-b from-indigo-50/20 to-white">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-indigo-700">CQ 3 (Normalised)</span>
                            <span class="text-xl">{{ $cq['cq3']['emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ $cq['cq3']['label'] }}</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">40% Self + 60% Others</p>
                        </div>

                        <div class="pt-2">
                            <div class="text-3xl font-black text-indigo-900">
                                {{ $cq['cq3']['percentage'] }}%
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-indigo-700 transition-all duration-700" 
                                     style="width: {{ $cq['cq3']['percentage'] }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] pt-1">
                            <span class="text-slate-500 font-medium">Perception Delta:</span>
                            <span class="font-extrabold {{ $cq['cq3']['gap'] >= 0 ? 'text-amber-600' : 'text-blue-600' }}">
                                {{ $cq['cq3']['gap'] > 0 ? '+' : '' }}{{ $cq['cq3']['gap'] }}%
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 font-medium">Category:</span>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $cq['cq3']['badge'] }}">
                                {{ $cq['cq3']['category'] }}
                            </span>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100 leading-normal">
                        {{ $cq['cq3']['description'] }}
                    </p>
                </div>

                <!-- 4. CQ Sync (Team Score) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between relative overflow-hidden">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-600">CQ Sync</span>
                            <span class="text-xl">{{ $cq['cq_sync']['emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ $cq['cq_sync']['label'] }}</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Cohort helpfulness & motivation</p>
                        </div>

                        <div class="pt-2">
                            <div class="text-3xl font-black text-purple-900">
                                {{ $cq['cq_sync']['percentage'] }}%
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                                <div class="h-2 rounded-full bg-purple-600 transition-all duration-700" 
                                     style="width: {{ $cq['cq_sync']['percentage'] }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] pt-1">
                            <span class="text-slate-500 font-medium">Mutual Helpfulness:</span>
                            <span class="font-extrabold text-slate-800">{{ $cq['cq_sync']['mutual_helpfulness'] }}%</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 font-medium">Team Cohesion:</span>
                            <span class="font-extrabold text-slate-800">{{ $cq['cq_sync']['cohesion_rate'] }}%</span>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100 leading-normal">
                        {{ $cq['cq_sync']['insight'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 360° PERCEPTION GAP & ALIGNMENT ANALYSIS -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        Introspection & Gap Analysis
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">Perception Alignment Diagnosis</h3>
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $cq['cq3']['alignment_badge'] }}">
                    <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                    {{ $cq['cq3']['alignment_status'] }}
                </span>
            </div>

            <!-- Alignment Explanation Card -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col md:flex-row md:items-center gap-6">
                <div class="flex-1 space-y-1">
                    <h4 class="text-sm font-bold text-slate-800">Calibrated Perception Summary</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $cq['cq3']['alignment_insight'] }}
                    </p>
                </div>

                <!-- Visual Gap Gauge -->
                <div class="shrink-0 bg-white p-4 rounded-xl border border-slate-200 text-center min-w-[180px]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Self vs Peer Delta</span>
                    <div class="text-2xl font-black mt-1 {{ $cq['cq3']['gap'] > 0 ? 'text-amber-600' : ($cq['cq3']['gap'] < 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                        {{ $cq['cq3']['gap'] > 0 ? '+' : '' }}{{ $cq['cq3']['gap'] }}%
                    </div>
                    <span class="text-[10px] text-slate-400">
                        {{ abs($cq['cq3']['gap']) <= 4 ? 'Aligned (±4% threshold)' : ($cq['cq3']['gap'] > 0 ? 'Self > Peer' : 'Peer > Self') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- QUESTION-BY-QUESTION COMPETENCY BREAKDOWN -->
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                            Competency Matrix
                        </span>
                        <h3 class="text-lg font-black text-slate-900 mt-1">Detailed Question Comparison</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Individual comparison across all assessment dimensions (Scale 1–10).
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-medium text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-indigo-600"></span> Self Rating
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-emerald-600"></span> Peer Avg Rating
                        </span>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3.5 px-6 w-12 text-center">#</th>
                            <th class="py-3.5 px-6">Competency Dimension</th>
                            <th class="py-3.5 px-6 text-center w-28">Self Rating</th>
                            <th class="py-3.5 px-6 text-center w-28">Peer Avg</th>
                            <th class="py-3.5 px-6 text-center w-28">Delta (Gap)</th>
                            <th class="py-3.5 px-6 w-44">Comparison Bar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($cq['questions_breakdown'] as $q)
                            @php
                                $selfScore = $q['self_score'];
                                $peerAvg = $q['peer_avg'];
                                $gap = $q['gap'];
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6 text-center font-bold text-slate-400">
                                    {{ $q['number'] }}
                                </td>
                                <td class="py-4 px-6 space-y-1">
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                        {{ $q['dimension'] }}
                                    </span>
                                    <div class="font-semibold text-slate-900 leading-snug">
                                        {{ $q['question_text'] }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($selfScore !== null)
                                        <span class="inline-flex items-center justify-center font-black text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg px-2.5 py-1 text-xs">
                                            {{ $selfScore }} / 10
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($peerAvg !== null)
                                        <span class="inline-flex items-center justify-center font-black text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-2.5 py-1 text-xs">
                                            {{ $peerAvg }} / 10
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($gap !== null)
                                        <span class="inline-flex items-center justify-center font-bold text-xs px-2 py-0.5 rounded-md
                                            {{ abs($gap) <= 0.5 ? 'bg-slate-100 text-slate-700' : ($gap > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200') }}">
                                            {{ $gap > 0 ? '+' : '' }}{{ $gap }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="space-y-1.5">
                                        <!-- Self Bar -->
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full bg-indigo-600" 
                                                 style="width: {{ $selfScore !== null ? ($selfScore * 10) : 0 }}%"></div>
                                        </div>
                                        <!-- Peer Bar -->
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full bg-emerald-500" 
                                                 style="width: {{ $peerAvg !== null ? ($peerAvg * 10) : 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-slate-400 text-xs">
                                    No questions registered for this survey.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
