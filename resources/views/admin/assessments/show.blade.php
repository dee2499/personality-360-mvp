<x-layouts.app>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Back Navigation -->
        <div>
            @if($assessment->subject)
                <a href="{{ route('admin.people.show', $assessment->subject) }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to {{ $assessment->subject->name }}'s Profile</span>
                </a>
            @else
                <a href="{{ route('admin.assessments.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Assessments</span>
                </a>
            @endif
        </div>

        <!-- Assessment Header Card -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-md
                        {{ $assessment->isSelfAssessment() ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-700' }}">
                        {{ $assessment->isSelfAssessment() ? 'Self Assessment' : 'Peer Assessment' }}
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 mt-2 flex items-center gap-2">
                        <span>{{ $assessment->assessor?->name ?? 'Unknown' }}</span>
                        <span class="text-slate-300 font-normal">→</span>
                        <span class="text-indigo-600">{{ $assessment->subject?->name ?? 'Unknown' }}</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Survey: <span class="font-semibold text-slate-700">{{ $assessment->survey?->title ?? 'General Survey' }}</span>
                        @if($assessment->completed_at)
                            • Submitted on {{ $assessment->completed_at->format('F d, Y \a\t H:i') }}
                        @endif
                    </p>
                </div>

                <!-- Status Badge -->
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold
                        {{ match($assessment->status) {
                            'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'in_progress' => 'bg-blue-50 text-blue-700 border border-blue-200',
                            default => 'bg-slate-100 text-slate-600'
                        } }}">
                        <span class="w-2 h-2 rounded-full {{ match($assessment->status) {
                            'completed' => 'bg-emerald-500',
                            'in_progress' => 'bg-blue-500',
                            default => 'bg-slate-400'
                        } }}"></span>
                        <span class="capitalize">{{ str_replace('_', ' ', $assessment->status) }}</span>
                    </span>
                </div>
            </div>

            <!-- Confidentiality Notice & Status Summary -->
            <div class="mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-amber-900">Confidential Evaluation</h3>
                    <p class="text-[11px] text-amber-700 leading-relaxed">
                        To preserve the privacy and objectivity of the 360-degree feedback process, individual question ratings and exact numeric scores are strictly confidential and masked from all administrators and managers.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section 18: Question Breakdown (Protected / Anonymous) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Evaluation Questions</h2>
                    <p class="text-xs text-slate-500">Evaluation survey question roster</p>
                </div>
                <span class="text-xs font-bold text-slate-400">
                    {{ count($questionsWithAnswers) }} Questions
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($questionsWithAnswers as $index => $item)
                    @php
                        $qNum = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    @endphp
                    <div class="p-6 sm:px-8 hover:bg-slate-50/50 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <!-- Question Title & Index -->
                            <div class="flex items-start gap-4">
                                <span class="font-mono text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">
                                    {{ $qNum }}
                                </span>
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        {{ $item['question']->question_text }}
                                    </h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Rating scale: 1 (Needs development) to 10 (Exceptional)</p>
                                </div>
                            </div>

                            <!-- Rating Score Masked Readout -->
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Score Confidential</span>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Footer Summary -->
            <div class="p-6 sm:px-8 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs font-semibold text-slate-700">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                    <span>Evaluation Status: <strong class="capitalize text-slate-900">{{ str_replace('_', ' ', $assessment->status) }}</strong></span>
                </div>
                <div class="flex items-center gap-2 text-slate-500">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    <span>Aggregated results are only available via anonymized competency and cohort reports.</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
