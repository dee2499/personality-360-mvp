<x-layouts.app>
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Top Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ route('participant.assessments.index', ['survey_id' => $survey->id]) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Assessments</span>
            </a>

            <!-- Confidentiality Notice Tag -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-bold">
                <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i>
                <span>Team Public Report: Zero Names Exposed</span>
            </div>
        </div>

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
                    Team Group Insights & Action Plans
                </h1>

                <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
                    Survey: <span class="font-bold text-slate-800">{{ $survey->title }}</span> • Anonymous group-level analytics for collective introspection and continuous improvement.
                </p>
            </div>

            <!-- Quick Action: View My Confidential CQ Report -->
            <div class="shrink-0 flex items-center gap-2">
                <a href="{{ route('participant.assessments.report', $survey) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition shadow-xs">
                    <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                    <span>My Confidential CQ Report</span>
                </a>
            </div>
        </div>

        <!-- PSYCHOLOGICAL SAFETY BANNER -->
        <div class="bg-indigo-50/60 border border-indigo-200 p-4 sm:p-5 rounded-2xl flex items-start sm:items-center gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-indigo-950 uppercase tracking-wider">Collective Action Without Individual Attribution</h4>
                <p class="text-xs text-indigo-900/80 leading-relaxed">
                    {{ $insights['confidentiality_guarantee'] }} This report focuses on collective team behaviors, shared strengths, and collaborative growth opportunities.
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
                <!-- Group CQ 1 -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Cohort CQ 1 (Self)</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Self-Evaluation Average</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Cohort average self-ratings</p>
                        </div>
                        <div class="text-3xl font-black text-slate-900">
                            {{ $insights['average_cq1_self'] }}%
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $insights['average_cq1_self'] }}%"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100">
                        How team members assess their own change agility.
                    </p>
                </div>

                <!-- Group CQ 2 -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-600">Cohort CQ 2 (Others)</span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Peer Observation Average</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Colleague consensus rating</p>
                        </div>
                        <div class="text-3xl font-black text-slate-900">
                            {{ $insights['average_cq2_others'] }}%
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-emerald-600" style="width: {{ $insights['average_cq2_others'] }}%"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-4 pt-3 border-t border-slate-100">
                        Consensus view of colleagues' behavioral readiness.
                    </p>
                </div>

                <!-- Group CQ 3 -->
                <div class="bg-white rounded-3xl border border-indigo-200 p-6 shadow-xs flex flex-col justify-between bg-gradient-to-b from-indigo-50/20 to-white">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-indigo-700">Cohort CQ 3</span>
                            <span class="text-xl">{{ $insights['group_emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Calibrated Benchmark</h3>
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
                        Balanced organization benchmark for agility.
                    </p>
                </div>

                <!-- CQ Sync (Team Score) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-600">CQ Sync</span>
                            <span class="text-xl">{{ $insights['cq_sync']['emoji'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Helpfulness & Motivation</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Mutual support & synergy score</p>
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

        <!-- TWO COLUMNS: TEAM SUPERPOWERS vs WHAT TO SOLVE -->
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
                    <p class="text-xs text-slate-500 mt-0.5">Lowest-scoring competency dimensions requiring team collaboration.</p>
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
                    Team Action Plans
                </span>
                <h3 class="text-lg font-black text-slate-900 mt-1">Intent-Level Recommendations & Sprints</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tailored operational recommendations specifically mapped to the team's critical growth areas.
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

        <!-- LEADERSHIP SIGN-OFF STATUS (READ-ONLY FOR PARTICIPANTS) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        Governance
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-1">Leadership Sign-Off Status</h3>
                </div>

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

            @if($survey->sign_off_lead)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                    <div class="text-xs text-slate-700">
                        Formally signed off by: <span class="font-extrabold text-indigo-700">{{ $survey->sign_off_lead }}</span>
                        @if($survey->signed_off_at)
                            <span class="text-slate-400">• {{ $survey->signed_off_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                    @if($survey->sign_off_notes)
                        <p class="text-xs text-slate-600 italic bg-white p-3 rounded-xl border border-slate-200">
                            "{{ $survey->sign_off_notes }}"
                        </p>
                    @endif
                </div>
            @else
                <p class="text-xs text-slate-500">
                    Awaiting executive/sponsor review and formal sign-off on action plan commitments.
                </p>
            @endif
        </div>
    </div>
</x-layouts.app>
