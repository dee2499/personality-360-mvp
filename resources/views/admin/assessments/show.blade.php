<x-layouts.app>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Back Navigation -->
        <div>
            <a href="{{ route('admin.people.show', $assessment->subject) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to {{ $assessment->subject->name }}'s Profile</span>
            </a>
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
                        <span>{{ $assessment->assessor->name }}</span>
                        <span class="text-slate-300 font-normal">→</span>
                        <span class="text-indigo-600">{{ $assessment->subject->name }}</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Survey: <span class="font-semibold text-slate-700">{{ $assessment->survey->title }}</span>
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

            <!-- Score Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6">
                <!-- Score -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Score</span>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-900">{{ $assessment->total_score ?? '—' }}</span>
                        <span class="text-xs text-slate-500 font-medium">/ {{ $assessment->max_score ?? ($assessment->survey->questions->count() * 10) }}</span>
                    </div>
                </div>

                <!-- Percentage -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Percentage</span>
                    <div class="mt-1">
                        @if($assessment->percentage !== null)
                            <span class="text-3xl font-black text-slate-900">{{ number_format($assessment->percentage, 2) }}%</span>
                        @else
                            <span class="text-3xl font-black text-slate-400">—</span>
                        @endif
                    </div>
                </div>

                <!-- Category -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center text-center">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Category</span>
                    <div class="mt-1">
                        @if($assessment->category)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold {{ $categoryBadge }}">
                                <span>{{ $categoryEmoji }}</span>
                                <span>{{ $assessment->category }}</span>
                            </span>
                        @else
                            <span class="text-xs font-medium text-slate-400">Pending</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 18: Question by Question Breakdown -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Question Ratings</h2>
                    <p class="text-xs text-slate-500">Each answer is scored on an integer scale from 1 (lowest) to 10 (highest)</p>
                </div>
                <span class="text-xs font-bold text-slate-400">
                    {{ count($questionsWithAnswers) }} Questions
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($questionsWithAnswers as $index => $item)
                    @php
                        $qNum = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                        $score = $item['score'];
                        $pct = $score ? ($score / 10) * 100 : 0;
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

                            <!-- Rating Score Readout & Visualizer -->
                            <div class="flex flex-col sm:items-end gap-1.5 shrink-0 min-w-[200px]">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-lg font-black {{ $score ? 'text-slate-900' : 'text-slate-400' }}">
                                        {{ $score ?? '—' }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium">/ 10</span>
                                </div>

                                <!-- Visual Rating Scale Track (1-10) -->
                                <div class="w-full flex items-center gap-1">
                                    @for($i = 1; $i <= 10; $i++)
                                        <div class="h-2 flex-1 rounded-full transition-all
                                            {{ $score && $i <= $score ? 'bg-indigo-600' : 'bg-slate-100' }}"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Footer Summary -->
            <div class="p-6 sm:px-8 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs font-semibold text-slate-700">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    <span>Total Score: {{ $assessment->total_score ?? 0 }} / {{ $assessment->max_score ?? ($assessment->survey->questions->count() * 10) }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span>Percentage: <strong class="text-slate-900">{{ number_format($assessment->percentage ?? 0, 2) }}%</strong></span>
                    <span>Category: <strong class="text-indigo-600">{{ $assessment->category ?? 'Pending' }}</strong></span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
