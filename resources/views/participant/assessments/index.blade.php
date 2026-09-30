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
            <!-- Loop through each assigned Survey -->
            <div class="space-y-10">
                @foreach($surveyGroups as $groupIndex => $group)
                    <div class="space-y-6 bg-white/50 p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
                        <!-- Survey Header with Pending / Completed Status -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-200">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                                        Survey #{{ $group['survey']->id }}
                                    </span>
                                    @if($group['isCompleted'])
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            Completed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                            Pending Survey ({{ $group['totalCount'] - $group['completedCount'] }} remaining)
                                        </span>
                                    @endif
                                </div>
                                <h2 class="text-xl font-black text-slate-900 tracking-tight mt-1">
                                    {{ $group['survey']->title }}
                                </h2>
                            </div>

                            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl">
                                <span>Progress:</span>
                                <span class="{{ $group['isCompleted'] ? 'text-emerald-700 font-black' : 'text-amber-700 font-black' }}">
                                    {{ $group['completedCount'] }} of {{ $group['totalCount'] }} Submitted
                                </span>
                            </div>
                        </div>

                        <!-- Self-Assessment for this Survey -->
                        @if($group['selfAssessment'])
                            @php $self = $group['selfAssessment']; @endphp
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Self Evaluation</h3>
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
                                                <h4 class="text-base font-bold text-slate-900">Self Assessment</h4>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    Assessing Yourself
                                                </span>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">
                                                {{ $self->survey->questions->count() }} Questions
                                            </div>
                                            
                                            @if($self->isCompleted())
                                                <div class="mt-2 flex items-center gap-3 text-xs">
                                                    <span class="font-bold text-slate-800">Score: {{ $self->total_score }} / {{ $self->max_score }}</span>
                                                    <span>•</span>
                                                    <span class="font-extrabold text-indigo-600">{{ number_format($self->percentage, 2) }}%</span>
                                                    <span>•</span>
                                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                                        {{ match($self->category) {
                                                            'Apple' => '🍏 Apple',
                                                            'Orange' => '🍊 Orange',
                                                            'Tomato' => '🍅 Tomato',
                                                            'Lemon' => '🍋 Lemon',
                                                            'Cucumber' => '🥒 Cucumber',
                                                            default => $self->category
                                                        } }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full
                                            {{ match($self->status) {
                                                'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                'in_progress' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                default => 'bg-slate-100 text-slate-600'
                                            } }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ match($self->status) {
                                                'completed' => 'bg-emerald-500',
                                                'in_progress' => 'bg-blue-500',
                                                default => 'bg-slate-400'
                                            } }}"></span>
                                            <span class="capitalize">{{ str_replace('_', ' ', $self->status) }}</span>
                                        </span>

                                        <a href="{{ route('participant.assessments.show', $self) }}" 
                                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                                            <span>{{ $self->isCompleted() ? 'View Answers' : ($self->isInProgress() ? 'Continue' : 'Start Assessment') }}</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Peer Evaluations for this Survey -->
                        @if($group['peerAssessments']->isNotEmpty())
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Peer Evaluations</h3>
                                    <span class="text-[11px] text-slate-400 font-medium">Confidential ratings for your colleagues</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($group['peerAssessments'] as $assessment)
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
                                                        <h4 class="text-base font-bold text-slate-900">
                                                            Assessment of {{ $assessment->subject->name }}
                                                        </h4>
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
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
