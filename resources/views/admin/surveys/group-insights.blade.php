<x-layouts.app>
    <div class="space-y-8">
        <!-- Top Back Link & Header -->
        <div>
            <a href="{{ route('admin.surveys.show', $survey) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Survey Overview</span>
            </a>

            <!-- Overview Card -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($survey->company)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i data-lucide="building-2" class="w-3 h-3 text-indigo-500"></i>
                                {{ $survey->company->name }}
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                            Cohort Size: {{ $insights['cohort_size'] }} Members
                        </span>

                        <!-- Sign Off Status Badge -->
                        @php
                            $signOffStatus = $insights['sign_off']['status'] ?? 'pending';
                        @endphp
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full
                            {{ match($signOffStatus) {
                                'approved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                'needs_review' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                default => 'bg-slate-100 text-slate-600 border border-slate-200'
                            } }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ match($signOffStatus) {
                                'approved' => 'bg-emerald-500',
                                'needs_review' => 'bg-amber-500',
                                default => 'bg-slate-400'
                            } }}"></span>
                            Sign-Off: {{ ucwords(str_replace('_', ' ', $signOffStatus)) }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Group Analytics, Insights & Action Plan Sign-Off
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
                        Survey: <span class="font-bold text-slate-800">{{ $survey->title }}</span> • Anonymous cohort analytics to identify collective superpowers and solve critical growth gaps.
                    </p>
                </div>

                <!-- Fast Jump to Sign-off -->
                <div class="shrink-0">
                    <a href="#sign-off-panel" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-100 transition">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                        <span>Jump to Leadership Sign-Off</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- CONFIDENTIALITY & PSYCHOLOGICAL SAFETY GUARANTEE -->
        <div class="bg-indigo-50/60 border border-indigo-200 p-4 sm:p-5 rounded-2xl flex items-start sm:items-center gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Zero Individual Names Guarantee</h4>
                <p class="text-xs text-indigo-900/80 leading-relaxed">
                    {{ $insights['confidentiality_guarantee'] }} Individual evaluations remain completely private to protect candor and psychological safety.
                </p>
            </div>
        </div>

        <!-- 4 CQ GROUP AGGREGATE CARDS -->
        <div>
            <div class="mb-4">
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                    Cohort Change Quotients
                </span>
                <h2 class="text-lg font-black text-slate-900 mt-1">Aggregated CQ & Team Sync Indices</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Group CQ 1 (Self Avg) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Cohort CQ 1 (Self)</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Self-Evaluation Average</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Average self-ratings across cohort</p>
                        </div>
                        <div class="text-3xl font-black text-slate-900">
                            {{ $insights['average_cq1_self'] }}%
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $insights['average_cq1_self'] }}%"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100">
                        How team members assess their own change readiness.
                    </p>
                </div>

                <!-- Group CQ 2 (Others Avg) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-600">Cohort CQ 2 (Others)</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Peer Observation Average</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Average colleague ratings</p>
                        </div>
                        <div class="text-3xl font-black text-slate-900">
                            {{ $insights['average_cq2_others'] }}%
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-emerald-600" style="width: {{ $insights['average_cq2_others'] }}%"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100">
                        Consensus perspective on team members' behavioral agility.
                    </p>
                </div>

                <!-- Group CQ 3 (Normalised Avg) -->
                <div class="bg-white rounded-3xl border border-indigo-200 p-6 shadow-xs flex flex-col justify-between bg-gradient-to-b from-indigo-50/20 to-white">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-indigo-700">Cohort CQ 3</span>
                            <span class="text-xl">{{ $insights['group_emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Calibrated Team Benchmark</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">40% Self + 60% Observer consensus</p>
                        </div>
                        <div class="text-3xl font-black text-indigo-900">
                            {{ $insights['average_cq3_normalised'] }}%
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-indigo-700" style="width: {{ $insights['average_cq3_normalised'] }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] pt-1">
                            <span class="text-slate-500 font-medium">Category:</span>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $insights['group_badge'] }}">
                                {{ $insights['group_category'] }}
                            </span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100">
                        Weighted organizational baseline for Change Quotient agility.
                    </p>
                </div>

                <!-- CQ Sync (Team Score) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-600">CQ Sync Score</span>
                            <span class="text-xl">{{ $insights['cq_sync']['emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Mutual Helpfulness & Motivation</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Interpersonal support index</p>
                        </div>
                        <div class="text-3xl font-black text-purple-900">
                            {{ $insights['cq_sync']['percentage'] }}%
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-purple-600" style="width: {{ $insights['cq_sync']['percentage'] }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] pt-1">
                            <span class="text-slate-500 font-medium">Synergy:</span>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $insights['cq_sync']['badge'] }}">
                                {{ $insights['cq_sync']['category'] }}
                            </span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100">
                        {{ $insights['cq_sync']['insight'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- TWO COLUMNS: TEAM SUPERPOWERS vs WHAT TO SOLVE (CRITICAL GROWTH GAPS) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Team Superpowers -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                        Strengths
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">Top 3 Team Superpowers</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Highest consensus competency scores across the entire cohort.</p>
                </div>

                <div class="space-y-4">
                    @forelse($insights['top_strengths'] as $index => $item)
                        <div class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 flex items-start gap-4">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-xs shrink-0">
                                #{{ $index + 1 }}
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">
                                        {{ $item['dimension'] }}
                                    </span>
                                    <span class="text-sm font-black text-emerald-800">
                                        {{ $item['overall_percentage'] }}%
                                    </span>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 leading-snug">
                                    {{ $item['question_text'] }}
                                </p>
                                <div class="flex items-center gap-3 text-[10px] text-slate-500 pt-1">
                                    <span>Self Avg: <strong>{{ $item['self_avg'] }}/10</strong></span>
                                    <span>•</span>
                                    <span>Peer Avg: <strong>{{ $item['peer_avg'] }}/10</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">No responses available yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- "What to Solve" - Critical Growth Gaps -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">
                        Targeted Growth
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">What to Solve: Top 3 Critical Gaps</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Lowest-scoring competency dimensions requiring collective intervention.</p>
                </div>

                <div class="space-y-4">
                    @forelse($insights['critical_gaps'] as $index => $item)
                        <div class="p-4 rounded-2xl bg-amber-50/40 border border-amber-200/80 flex items-start gap-4">
                            <div class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center font-black text-xs shrink-0">
                                #{{ $index + 1 }}
                            </div>
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">
                                        {{ $item['dimension'] }}
                                    </span>
                                    <span class="text-sm font-black text-amber-800">
                                        {{ $item['overall_percentage'] }}%
                                    </span>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 leading-snug">
                                    {{ $item['question_text'] }}
                                </p>
                                <div class="flex items-center gap-3 text-[10px] text-slate-500 pt-1">
                                    <span>Self Avg: <strong>{{ $item['self_avg'] }}/10</strong></span>
                                    <span>•</span>
                                    <span>Peer Avg: <strong>{{ $item['peer_avg'] }}/10</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">No responses available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- INTENT-LEVEL ACTION PLAN RECOMMENDATIONS -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                    Targeted Interventions
                </span>
                <h3 class="text-lg font-black text-slate-900 mt-1">Intent-Level Action Plan Recommendations</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tailored operational recommendations specifically mapped to the cohort's critical growth gaps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($insights['recommendations'] as $rec)
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">
                                Focus: {{ $rec['dimension'] }}
                            </span>
                            <h4 class="text-sm font-bold text-slate-900 leading-snug">
                                {{ $rec['title'] }}
                            </h4>
                            <div class="pt-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Strategic Intent</span>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                    {{ $rec['intent'] }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Recommended Sprint Ritual</span>
                            <p class="text-xs text-slate-800 font-medium mt-1 leading-relaxed">
                                {{ $rec['action'] }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-6 text-slate-400 text-xs">
                        Recommendations will unlock once assessment responses are recorded.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- FULL ANONYMOUS COMPETENCY QUESTION ANALYSIS TABLE -->
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                    Distribution Matrix
                </span>
                <h3 class="text-lg font-black text-slate-900 mt-1">Full Anonymous Competency Breakdown</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Cohort-wide comparison across all 11 competency items with zero individual attribution.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3.5 px-6">Competency Dimension</th>
                            <th class="py-3.5 px-6">Survey Prompt</th>
                            <th class="py-3.5 px-6 text-center w-28">Overall Score</th>
                            <th class="py-3.5 px-6 text-center w-28">Self Avg</th>
                            <th class="py-3.5 px-6 text-center w-28">Peer Avg</th>
                            <th class="py-3.5 px-6 text-center w-28">Perception Gap</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($insights['questions_data'] as $q)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-6 font-bold text-slate-800">
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                        {{ $q['dimension'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-700 font-medium">
                                    {{ $q['question_text'] }}
                                </td>
                                <td class="py-4 px-6 text-center font-black text-slate-900">
                                    {{ $q['overall_percentage'] }}%
                                </td>
                                <td class="py-4 px-6 text-center text-slate-600 font-semibold">
                                    {{ $q['self_avg'] }} / 10
                                </td>
                                <td class="py-4 px-6 text-center text-slate-600 font-semibold">
                                    {{ $q['peer_avg'] }} / 10
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center justify-center font-bold text-xs px-2 py-0.5 rounded-md
                                        {{ abs($q['gap']) <= 0.5 ? 'bg-slate-100 text-slate-700' : ($q['gap'] > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200') }}">
                                        {{ $q['gap'] > 0 ? '+' : '' }}{{ $q['gap'] }}
                                    </span>
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

        <!-- FORMAL LEADERSHIP SIGN-OFF PANEL (Requirement 5) -->
        <div id="sign-off-panel" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        Governance & Commitments
                    </span>
                    <h3 class="text-xl font-black text-slate-900 mt-1">Leadership Sign-Off Panel</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Formal administrative review and sign-off on the team action plans and growth commitments.
                    </p>
                </div>

                <!-- Current Status Indicator -->
                <div class="shrink-0 flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400">Current Sign-Off Status:</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold
                        {{ match($signOffStatus) {
                            'approved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'needs_review' => 'bg-amber-50 text-amber-700 border border-amber-200',
                            default => 'bg-slate-100 text-slate-600 border border-slate-200'
                        } }}">
                        <i data-lucide="{{ match($signOffStatus) {
                            'approved' => 'check-circle-2',
                            'needs_review' => 'alert-circle',
                            default => 'clock'
                        } }}" class="w-4 h-4"></i>
                        <span>{{ ucwords(str_replace('_', ' ', $signOffStatus)) }}</span>
                    </span>
                </div>
            </div>

            <!-- Existing Sign-off Log if already recorded -->
            @if($survey->sign_off_lead)
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-800">Signed Off By:</span>
                            <span class="font-extrabold text-indigo-700 text-xs">{{ $survey->sign_off_lead }}</span>
                        </div>
                        @if($survey->signed_off_at)
                            <div class="text-[11px] text-slate-400">
                                Date Recorded: {{ $survey->signed_off_at->format('M d, Y • h:i A') }}
                            </div>
                        @endif
                        @if($survey->sign_off_notes)
                            <div class="text-xs text-slate-600 mt-2 bg-white p-3 rounded-xl border border-slate-200 italic">
                                "{{ $survey->sign_off_notes }}"
                            </div>
                        @endif
                    </div>

                    <span class="text-[11px] text-slate-400 font-medium shrink-0">
                        You can update the sign-off details below at any time.
                    </span>
                </div>
            @endif

            <!-- Sign-Off Form -->
            <form method="POST" action="{{ route('admin.surveys.sign-off', $survey) }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="sign_off_lead" class="block text-xs font-bold text-slate-700 mb-1">
                            Sign-Off Lead / Executive Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="sign_off_lead" 
                               name="sign_off_lead" 
                               required
                               value="{{ old('sign_off_lead', $survey->sign_off_lead ?? auth()->user()->name) }}"
                               placeholder="e.g. Director of People, Cohort Sponsor"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition">
                        @error('sign_off_lead')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sign_off_status" class="block text-xs font-bold text-slate-700 mb-1">
                            Review Decision Status <span class="text-rose-500">*</span>
                        </label>
                        <select id="sign_off_status" 
                                name="sign_off_status" 
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition bg-white">
                            <option value="approved" {{ old('sign_off_status', $survey->sign_off_status) === 'approved' ? 'selected' : '' }}>
                                Approved (Action Plans Adopted)
                            </option>
                            <option value="needs_review" {{ old('sign_off_status', $survey->sign_off_status) === 'needs_review' ? 'selected' : '' }}>
                                Needs Review (Adjustments Required)
                            </option>
                            <option value="pending" {{ old('sign_off_status', $survey->sign_off_status) === 'pending' ? 'selected' : '' }}>
                                Pending Decision
                            </option>
                        </select>
                        @error('sign_off_status')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="sign_off_notes" class="block text-xs font-bold text-slate-700 mb-1">
                        Executive Commitments & Action Notes
                    </label>
                    <textarea id="sign_off_notes" 
                              name="sign_off_notes" 
                              rows="3"
                              placeholder="Document commitments, scheduled team rituals, or agreed milestones..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition">{{ old('sign_off_notes', $survey->sign_off_notes) }}</textarea>
                    @error('sign_off_notes')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-100 transition cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Confirm & Save Leadership Sign-Off</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
