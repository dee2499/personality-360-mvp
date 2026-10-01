<x-layouts.app>
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Dashboard Header & Completion Progress -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                    Participant Portal
                </span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2">My 360 Feedback Surveys</h1>
                <p class="text-xs text-slate-500 mt-1 max-w-lg">
                    Each survey guides you through 11 questions. On each question, you will rate yourself first, followed by all colleagues in your cohort.
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
                    {{ $totalAssigned - $completedCount }} evaluation(s) remaining
                </span>
            </div>
        </div>

        @if($surveyGroups->isEmpty())
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">No active surveys assigned</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    When an administrator assigns you to a company survey cohort, it will appear here.
                </p>
            </div>
        @else
            <!-- Surveys List -->
            <div class="space-y-6">
                @foreach($surveyGroups as $group)
                    @php
                        $survey = $group['survey'];
                        $isCompleted = $group['isCompleted'];
                        $cohort = $group['cohortMembers'];
                    @endphp

                    <div class="bg-white rounded-3xl border {{ $isCompleted ? 'border-emerald-200 bg-emerald-50/10' : 'border-slate-200' }} p-6 sm:p-8 shadow-xs space-y-6">
                        <!-- Survey Header -->
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-6 border-b border-slate-100">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($survey->company)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <i data-lucide="building-2" class="w-3 h-3 text-indigo-500"></i>
                                            {{ $survey->company->name }}
                                        </span>
                                    @endif

                                    @if($isCompleted)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            Completed & Submitted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                            Pending Survey
                                        </span>
                                    @endif

                                    <span class="text-xs text-slate-400 font-medium">
                                        {{ $survey->questions->count() }} Questions • Scale 1 to 10
                                    </span>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                    {{ $survey->title }}
                                </h2>

                                @if($survey->description)
                                    <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                                        {{ $survey->description }}
                                    </p>
                                @endif
                            </div>

                            <!-- Survey Action CTA -->
                            <div class="shrink-0 flex sm:flex-col sm:items-end justify-between items-center gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('profile.show') }}" 
                                       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition border border-indigo-200">
                                        <i data-lucide="gauge" class="w-4 h-4 text-indigo-600"></i>
                                        <span>View 360 Meters in Profile</span>
                                    </a>
                                    <a href="{{ route('participant.surveys.take', $survey) }}" 
                                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white {{ $isCompleted ? 'bg-slate-800 hover:bg-slate-900' : 'bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-200' }} transition">
                                        <i data-lucide="{{ $isCompleted ? 'eye' : 'sparkles' }}" class="w-4 h-4"></i>
                                        <span>{{ $isCompleted ? 'Review 11-Question Survey' : 'Open 11-Question Survey' }}</span>
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </a>
                                </div>

                                <span class="text-[11px] font-bold {{ $isCompleted ? 'text-emerald-700' : 'text-slate-400' }}">
                                    {{ $group['completedCount'] }} of {{ $group['totalCount'] }} ratings submitted
                                </span>
                            </div>
                        </div>

                        <!-- Cohort Team Members Preview -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                                    Cohort Team Members ({{ $cohort->count() }} people to rate across 11 questions):
                                </span>
                                <span class="text-slate-400 text-[11px]">You (Self) is rated first on each question</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($cohort as $member)
                                    @php
                                        $isSelf = $member->id === auth()->id();
                                    @endphp
                                    <div class="p-3.5 rounded-2xl border flex items-center justify-between gap-3 {{ $isSelf ? 'bg-indigo-50/60 border-indigo-200 shadow-xs' : 'bg-slate-50/60 border-slate-100' }}">
                                        <div class="flex items-center gap-2.5 truncate">
                                            <div class="w-8 h-8 rounded-xl font-black text-xs flex items-center justify-center shrink-0 {{ $isSelf ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-200 text-slate-700' }}">
                                                {{ substr($member->name, 0, 1) }}
                                            </div>
                                            <div class="truncate">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-slate-900 truncate">
                                                        {{ $isSelf ? 'You (' . $member->name . ')' : $member->name }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] text-slate-400 truncate block">{{ $member->email }}</span>
                                            </div>
                                        </div>

                                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 {{ $isSelf ? 'bg-indigo-600 text-white' : 'bg-slate-200/70 text-slate-600' }}">
                                            {{ $isSelf ? '1st (Self)' : 'Peer' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Instructions / How It Works Strip -->
                        <div class="p-4 rounded-2xl bg-indigo-50/40 border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-950">
                            <div class="flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-indigo-600 shrink-0"></i>
                                <span>
                                    <strong>How it works:</strong> Click "Open 11-Question Survey" to see Question 1. Rate yourself and all team members on a 1–10 scale, then advance to Question 2 through 11.
                                </span>
                            </div>

                            <a href="{{ route('participant.surveys.take', $survey) }}" 
                               class="font-bold text-indigo-600 hover:text-indigo-800 underline shrink-0">
                                Launch Wizard &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
