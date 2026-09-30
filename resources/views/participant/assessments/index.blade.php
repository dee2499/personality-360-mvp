<x-layouts.app>
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Dashboard Header & Completion Progress -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                    Participant Portal
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2">My 360 Assessments</h1>
                <p class="text-xs text-slate-500 mt-1 max-w-lg">
                    Complete your self-reflection and provide peer evaluations for everyone in your team cohort.
                </p>
            </div>

            <!-- Progress Meter -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col min-w-[240px]">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-700">Completion Status</span>
                    <span class="font-extrabold text-indigo-600">{{ $completedCount }} / {{ $totalAssigned }} ({{ $completionRate }}%)</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-700" 
                         style="width: {{ $completionRate }}%"></div>
                </div>
                <span class="text-[10px] text-slate-400 mt-2 text-right">
                    {{ $totalAssigned - $completedCount }} assessment(s) remaining
                </span>
            </div>
        </div>

        @if($totalAssigned === 0)
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">No active assessments assigned</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    When an administrator publishes a survey for your group, your assignments will appear here.
                </p>
            </div>
        @else
            <!-- Self-Assessment Section (Section 14) -->
            @if($selfAssessment)
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">1. Self Evaluation</h2>
                        <span class="text-[11px] text-slate-400 font-medium">Evaluate your own performance</span>
                    </div>

                    <div class="bg-white p-6 rounded-3xl border-2 border-indigo-100 shadow-sm hover:border-indigo-300 transition flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 relative overflow-hidden group">
                        <div class="absolute -top-10 -right-10 w-28 h-28 bg-indigo-50 rounded-full opacity-50 pointer-events-none group-hover:scale-125 transition"></div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-100">
                                <i data-lucide="user-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900">Self Assessment</h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Assessing Yourself
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    Survey: {{ $selfAssessment->survey->title }} • {{ $selfAssessment->survey->questions->count() }} Questions
                                </div>
                                
                                @if($selfAssessment->isCompleted())
                                    <div class="mt-2 flex items-center gap-3 text-xs">
                                        <span class="font-bold text-slate-800">Score: {{ $selfAssessment->total_score }} / {{ $selfAssessment->max_score }}</span>
                                        <span>•</span>
                                        <span class="font-extrabold text-indigo-600">{{ number_format($selfAssessment->percentage, 2) }}%</span>
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                            {{ match($selfAssessment->category) {
                                                'Apple' => '🍏 Apple',
                                                'Orange' => '🍊 Orange',
                                                'Tomato' => '🍅 Tomato',
                                                'Lemon' => '🍋 Lemon',
                                                'Cucumber' => '🥒 Cucumber',
                                                default => $selfAssessment->category
                                            } }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full
                                {{ match($selfAssessment->status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                    'in_progress' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                    default => 'bg-slate-100 text-slate-600'
                                } }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ match($selfAssessment->status) {
                                    'completed' => 'bg-emerald-500',
                                    'in_progress' => 'bg-blue-500',
                                    default => 'bg-slate-400'
                                } }}"></span>
                                <span class="capitalize">{{ str_replace('_', ' ', $selfAssessment->status) }}</span>
                            </span>

                            <a href="{{ route('participant.assessments.show', $selfAssessment) }}" 
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                                <span>{{ $selfAssessment->isCompleted() ? 'View Answers' : ($selfAssessment->isInProgress() ? 'Continue' : 'Start Assessment') }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Peer Assessments Section -->
            <div class="space-y-4 pt-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-400">2. Peer Evaluations</h2>
                    <span class="text-[11px] text-slate-400 font-medium">Confidential ratings for your colleagues</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($peerAssessments as $assessment)
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs hover:border-indigo-300 hover:shadow-md transition flex flex-col justify-between gap-4">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        Peer Evaluation
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                        {{ match($assessment->status) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                            'in_progress' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                            default => 'bg-slate-100 text-slate-600'
                                        } }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ match($assessment->status) {
                                            'completed' => 'bg-emerald-500',
                                            'in_progress' => 'bg-blue-500',
                                            default => 'bg-slate-400'
                                        } }}"></span>
                                        <span class="capitalize">{{ str_replace('_', ' ', $assessment->status) }}</span>
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-sm border border-indigo-100">
                                        {{ substr($assessment->subject->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">
                                            Assessment of {{ $assessment->subject->name }}
                                        </h3>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            {{ $assessment->survey->questions->count() }} Questions • Scale 1 to 10
                                        </p>
                                    </div>
                                </div>

                                @if($assessment->isCompleted())
                                    <div class="mt-4 p-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                                        <span class="text-slate-500">Your Score Awarded:</span>
                                        <span class="font-bold text-slate-900">{{ $assessment->total_score }} / {{ $assessment->max_score }} ({{ number_format($assessment->percentage, 1) }}%)</span>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-2">
                                <a href="{{ route('participant.assessments.show', $assessment) }}" 
                                   class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl text-xs font-semibold
                                        {{ $assessment->isCompleted() 
                                            ? 'text-slate-700 bg-slate-100 hover:bg-slate-200' 
                                            : 'text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100' }} transition">
                                    <span>{{ $assessment->isCompleted() ? 'View Submitted Answers' : ($assessment->isInProgress() ? 'Continue Assessment' : 'Start Assessment') }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
