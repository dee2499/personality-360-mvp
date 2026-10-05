<x-layouts.app>
    @if(request()->query('view') !== 'matrix')
        <!-- ========================================================================= -->
        <!-- HOME PAGE (UNCOMPLETED SURVEY): ONLY TAKE READINESS SCORE SECTION         -->
        <!-- NO DASHBOARD HEADER, NO PROGRESS BAR, NO SURVEY SELECTION SECTION         -->
        <!-- ========================================================================= -->
        @if($selectedSurvey)
            @php
                $survey = $selectedSurvey;
            @endphp
            <div class="max-w-4xl mx-auto space-y-8 py-4 pb-12">
                <!-- Take Readiness Score Container Card -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-xs space-y-8 relative overflow-hidden">
                    <!-- Top decorative gradient line -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 via-indigo-600 to-purple-600"></div>

                    <!-- Action Required Hero CTA -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-indigo-50/90 via-white to-purple-50/60 border-2 border-indigo-200 flex flex-col md:flex-row md:items-center md:justify-between gap-6 shadow-sm">
                        <div class="space-y-1.5">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-100/80 px-2.5 py-0.5 rounded-md">
                                <i data-lucide="sparkles" class="w-3 h-3 text-indigo-600"></i>
                                Action Required
                            </span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                Ready to calculate your Change Quotient?
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 max-w-lg leading-relaxed">
                                Provide your ratings to unlock your personalized CQ leadership profile, self-perception matrix, and observer consensus ratings.
                            </p>
                        </div>

                        <div class="shrink-0">
                            <a href="{{ route('participant.surveys.take', $survey) }}" 
                               class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl text-sm font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-200 hover:shadow-indigo-300 transition-all duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                                <i data-lucide="sparkles" class="w-5 h-5 text-indigo-200"></i>
                                <span>Take Readiness Score</span>
                                <i data-lucide="arrow-right" class="w-5 h-5 text-indigo-200"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Survey Details Container -->
                    <div class="space-y-6 pt-2">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($survey->company)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-500"></i>
                                        {{ $survey->company->name }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-200">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    100% Confidential
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-200">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    360° Multi-Rater
                                </span>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                                {{ $survey->title }}
                            </h2>

                            @if($survey->description)
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-3xl">
                                    {{ $survey->description }}
                                </p>
                            @endif
                        </div>

                        <!-- Survey Specs Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1">
                                <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold">
                                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                                    <span>Survey Scope</span>
                                </div>
                                <div class="text-lg font-black text-slate-900">
                                    {{ $survey->questions->count() }} Questions
                                </div>
                                <p class="text-[11px] text-slate-400">11 CQ + 3 Team Sync</p>
                            </div>

                            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1">
                                <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold">
                                    <i data-lucide="sliders" class="w-4 h-4"></i>
                                    <span>Rating Format</span>
                                </div>
                                <div class="text-lg font-black text-slate-900">
                                    Scale 1 to 10
                                </div>
                                <p class="text-[11px] text-slate-400">Interactive quick sliders</p>
                            </div>

                            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1">
                                <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold">
                                    <i data-lucide="hourglass" class="w-4 h-4"></i>
                                    <span>Estimated Time</span>
                                </div>
                                <div class="text-lg font-black text-slate-900">
                                    ~5 – 8 Minutes
                                </div>
                                <p class="text-[11px] text-slate-400">Quick question-first flow</p>
                            </div>

                            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1">
                                <div class="flex items-center gap-2 text-indigo-600 text-xs font-bold">
                                    <i data-lucide="award" class="w-4 h-4"></i>
                                    <span>Unlocked Report</span>
                                </div>
                                <div class="text-lg font-black text-slate-900">
                                    Full CQ Report
                                </div>
                                <p class="text-[11px] text-slate-400">Score dial, rings & matrix</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center space-y-2 max-w-xl mx-auto my-12">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">No active surveys assigned</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    When an administrator assigns you to a company survey cohort, it will appear here.
                </p>
            </div>
        @endif
    @else
        <!-- ========================================================================= -->
        <!-- MY ASSESSMENTS PAGE (view=matrix): ALL THE MATRIX (WITH NO SCORE IF PENDING) -->
        <!-- ========================================================================= -->
        <div class="max-w-5xl mx-auto space-y-8 pb-12">
            <!-- Header for My Assessments -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                        Participant Portal
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-2">
                        My Assessments
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                        Inspect your 360° competency question breakdown, self vs. peer consensus scores, and cohort evaluations.
                    </p>
                </div>

                <!-- Progress Meter -->
                <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-100 flex flex-col min-w-[240px]">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-slate-700">All Surveys Completion</span>
                        <span class="font-extrabold text-indigo-600">{{ $completedCount }} / {{ $totalAssigned }} ({{ $completionRate }}%)</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-700" 
                             style="width: {{ $completionRate }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 mt-2">
                        <span>{{ $surveyGroups->count() }} Survey(s) Assigned</span>
                        <span>{{ $totalAssigned - $completedCount }} evaluation(s) remaining</span>
                    </div>
                </div>
            </div>

            @if($surveyGroups->isEmpty())
                <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">No active surveys assigned</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        When an administrator assigns you to a company survey cohort, it will appear here.
                    </p>
                </div>
            @else
                <!-- Modern UI Survey Select Box -->
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-xs"
                     x-data="{
                         open: false,
                         selectedId: '{{ $selectedSurvey?->id }}',
                         searchQuery: '',
                         surveyTitles: @js($surveyGroups->pluck('survey.title')->values()->all()),
                         matches(name) {
                             if (!this.searchQuery.trim()) return true;
                             return name.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
                         }
                     }"
                     @click.outside="open = false"
                     @keydown.escape.window="open = false">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-indigo-50 text-indigo-600">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </span>
                                <label for="survey-select" class="text-sm font-black text-slate-900 tracking-tight">
                                    Select Survey to View Matrix
                                </label>
                            </div>
                            <p class="text-xs text-slate-500 pl-9">
                                Choose from your assigned 360 surveys to switch evaluation meters and matrix details.
                            </p>
                        </div>

                        <!-- Right: Select Box UI -->
                        <div class="relative min-w-[280px] sm:min-w-[340px]">
                            <button type="button"
                                    @click="open = !open; if (open) $nextTick(() => $refs.searchInput?.focus())"
                                    class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-slate-50 hover:bg-slate-100/80 rounded-2xl border border-slate-200 text-left transition shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 cursor-pointer">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $selectedGroup && $selectedGroup['isCompleted'] ? 'bg-emerald-500' : 'bg-amber-500' }} shrink-0"></span>
                                    <div class="truncate">
                                        <span class="text-xs font-bold text-slate-900 block truncate">
                                            {{ $selectedSurvey ? $selectedSurvey->title : 'Select a survey...' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            {{ $selectedGroup && $selectedGroup['isCompleted'] ? 'Submitted' : 'Pending Survey' }}
                                        </span>
                                    </div>
                                </div>
                                <i data-lucide="chevron-down" 
                                   class="w-4 h-4 text-slate-400 transition-transform duration-200 shrink-0"
                                   :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                                 class="absolute right-0 left-0 mt-2 z-40 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden py-1">
                                
                                <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                                    <div class="relative">
                                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <input type="text"
                                               x-ref="searchInput"
                                               x-model="searchQuery"
                                               placeholder="Search surveys by name..."
                                               @keydown.escape.stop="if (searchQuery) { searchQuery = '' } else { open = false }"
                                               class="w-full text-xs pl-8 pr-7 py-2 bg-white border border-slate-200 rounded-xl focus:border-indigo-600 focus:outline-hidden focus:ring-1 focus:ring-indigo-600 transition placeholder-slate-400 font-medium">
                                        <button type="button"
                                                x-show="searchQuery.length > 0"
                                                x-cloak
                                                @click="searchQuery = ''; $refs.searchInput?.focus()"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                                    @foreach($surveyGroups as $group)
                                        @php
                                            $s = $group['survey'];
                                            $isSelected = $selectedSurvey && $selectedSurvey->id === $s->id;
                                            $sCompleted = $group['isCompleted'];
                                            $url = route('participant.assessments.index', ['survey_id' => $s->id, 'view' => 'matrix']);
                                        @endphp
                                        <a href="{{ $url }}"
                                           x-show="matches('{{ addslashes($s->title) }}')"
                                           class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-indigo-50/50 transition cursor-pointer {{ $isSelected ? 'bg-indigo-50/70' : '' }}">
                                            <div class="flex items-center gap-3 truncate">
                                                <div class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 {{ $isSelected ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                                    <i data-lucide="check" class="w-3 h-3"></i>
                                                </div>
                                                <div class="truncate">
                                                    <span class="text-xs font-bold text-slate-900 block truncate {{ $isSelected ? 'text-indigo-950 font-black' : '' }}">
                                                        {{ $s->title }}
                                                    </span>
                                                    <span class="text-[10px] text-slate-400">
                                                        {{ $s->company?->name ?? 'Company Survey' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full shrink-0 {{ $sCompleted ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                {{ $sCompleted ? 'Submitted' : 'Pending Survey' }}
                                            </span>
                                        </a>
                                    @endforeach

                                    <div x-show="!surveyTitles.some(t => matches(t))"
                                         x-cloak
                                         class="py-6 px-4 text-center">
                                        <p class="text-xs text-slate-500 font-medium">No surveys found matching "<span x-text="searchQuery" class="font-bold text-slate-700"></span>"</p>
                                        <button type="button" @click="searchQuery = ''; $refs.searchInput?.focus()" class="mt-1.5 text-[11px] font-bold text-indigo-600 hover:text-indigo-800">Clear search</button>
                                    </div>
                                </div>
                            </div>

                            <select id="survey-select" 
                                    class="sr-only"
                                    onchange="if (this.value) window.location.href = this.value">
                                @foreach($surveyGroups as $group)
                                    @php
                                        $s = $group['survey'];
                                        $isSelected = $selectedSurvey && $selectedSurvey->id === $s->id;
                                        $sCompleted = $group['isCompleted'];
                                        $url = route('participant.assessments.index', ['survey_id' => $s->id, 'view' => 'matrix']);
                                    @endphp
                                    <option value="{{ $url }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $s->title }} ({{ $sCompleted ? 'Submitted' : 'Pending Survey' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if($selectedGroup)
                    @php
                        $survey = $selectedGroup['survey'];
                        $isCompleted = $selectedGroup['isCompleted'];
                        $norm = $selectedGroup['normalised'] ?? ($selectedGroup['cq']['cq3'] ?? null);
                        $self = $selectedGroup['self'];
                        $peer = $selectedGroup['peer'];
                        $comparison = $selectedGroup['comparison'];
                        $cohort = $selectedGroup['cohortMembers'];
                    @endphp

                    <!-- Active Survey Master Card (Matrix View) -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-8">
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
                                    {{ $survey->title }} — Detailed Competency Matrix
                                </h2>

                                @if($survey->description)
                                    <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                                        {{ $survey->description }}
                                    </p>
                                @endif
                            </div>

                            <!-- CTA Actions -->
                            <div class="shrink-0 flex flex-wrap items-center gap-2">
                                @if($isCompleted)
                                    <a href="{{ route('participant.assessments.report', $survey) }}" 
                                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
                                        <i data-lucide="file-bar-chart-2" class="w-4 h-4 text-indigo-600"></i>
                                        <span>Confidential CQ Report</span>
                                    </a>
                                @endif

                                <a href="{{ route('participant.surveys.group-insights', $survey) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                    <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
                                    <span>Team Insights</span>
                                </a>

                                <a href="{{ route('participant.surveys.take', $survey) }}" 
                                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-100 transition">
                                    <i data-lucide="{{ $isCompleted ? 'eye' : 'sparkles' }}" class="w-4 h-4"></i>
                                    <span>{{ $isCompleted ? 'Review in 11-Question Wizard' : 'Take Readiness Score' }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>

                        @if(! $isCompleted)
                            <!-- Friendly Notification for Uncompleted Matrix View -->
                            <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/80 border border-amber-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                        <i data-lucide="clock" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-amber-900">Assessment Pending — No Scores Calculated Yet</h4>
                                        <p class="text-[11px] text-amber-700">All competency questions and cohort members are listed below. Take the assessment to generate and view calibrated scores.</p>
                                    </div>
                                </div>
                                <a href="{{ route('participant.surveys.take', $survey) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-xs shrink-0">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    <span>Take Readiness Score</span>
                                </a>
                            </div>
                        @endif

                        <!-- THREE SCORE METERS (Normalised, Self, Peer) -->
                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                        360° Assessment Performance
                                    </span>
                                    <h3 class="text-xl font-black text-slate-900 mt-1">
                                        Three Score Meters (Normalised, Self, Peer)
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Your calibrated benchmark alongside personal and observer consensus ratings.
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400 font-medium">Scale: 0% to 100%</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                                <!-- Meter 1: Normalised Meter -->
                                <div class="bg-gradient-to-b from-indigo-50/40 via-white to-white rounded-3xl border-2 border-indigo-200 p-5 flex flex-col justify-between items-center text-center space-y-4 shadow-2xs relative">
                                    <div class="w-full flex items-center justify-between">
                                        <div class="text-left">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-100/70 px-2 py-0.5 rounded-md">
                                                Meter 1: Normalised
                                            </span>
                                            <h4 class="text-base font-black text-slate-900 mt-1.5">Normalised Score</h4>
                                            <p class="text-[10px] text-slate-400">Moderated: (Self + Peer Average) / 2</p>
                                        </div>
                                        <span class="text-2xl">{{ $norm['emoji'] ?? '🎯' }}</span>
                                    </div>

                                    <!-- Gauge -->
                                    <div class="w-full max-w-[200px] my-auto py-2">
                                        <x-score-meter 
                                            :percentage="$isCompleted ? ($norm['percentage'] ?? 0) : 0" 
                                            :category="$isCompleted ? ($norm['category'] ?? 'Supporter') : 'Pending'" 
                                            :only-gauge="true"
                                        />
                                    </div>

                                    <!-- Score Numbers Box -->
                                    <div class="w-full pt-4 border-t border-indigo-100/70 space-y-2">
                                        <div class="flex items-baseline justify-center gap-1">
                                            @if($isCompleted)
                                                <span class="text-3xl sm:text-4xl font-black tracking-tight text-indigo-950 leading-none">
                                                    {{ number_format($norm['percentage'] ?? 0, 2) }}
                                                </span>
                                                <span class="text-lg font-bold text-slate-400">%</span>
                                            @else
                                                <span class="text-2xl font-black tracking-tight text-slate-400 leading-none">
                                                    No score
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            @if($isCompleted)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-2xs {{ $norm['badge'] ?? 'bg-indigo-50 text-indigo-700 border-indigo-200' }}">
                                                    <span>{{ $norm['category'] ?? 'Supporter' }}</span>
                                                </span>
                                                @if(isset($norm['gap']))
                                                    <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                                        Gap: {{ $norm['gap'] > 0 ? '+' : '' }}{{ $norm['gap'] }}%
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-2xs bg-slate-100 text-slate-500 border-slate-200">
                                                    <span>Pending Assessment</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Meter 2: Self Meter -->
                                <div class="bg-gradient-to-b from-slate-50/80 via-white to-white rounded-3xl border border-slate-200 p-5 flex flex-col justify-between items-center text-center space-y-4 shadow-2xs">
                                    <div class="w-full flex items-center justify-between">
                                        <div class="text-left">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                                Meter 2: Self
                                            </span>
                                            <h4 class="text-base font-black text-slate-900 mt-1.5">Self Score</h4>
                                            <p class="text-[10px] text-slate-400">Meter 1: Self Evaluation</p>
                                        </div>
                                        <span class="text-2xl">{{ $self['category_emoji'] ?? $self['emoji'] ?? '👤' }}</span>
                                    </div>

                                    <!-- Gauge -->
                                    <div class="w-full max-w-[200px] my-auto py-2">
                                        <x-score-meter 
                                            :percentage="$self['is_completed'] ? $self['percentage'] : 0" 
                                            :category="$self['is_completed'] ? $self['category'] : 'Pending'" 
                                            :only-gauge="true"
                                        />
                                    </div>

                                    <!-- Score Numbers Box -->
                                    <div class="w-full pt-4 border-t border-slate-100 space-y-2">
                                        <div class="flex items-baseline justify-center gap-1">
                                            @if($self['is_completed'])
                                                <span class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 leading-none">
                                                    {{ number_format($self['percentage'], 2) }}
                                                </span>
                                                <span class="text-lg font-bold text-slate-400">%</span>
                                            @else
                                                <span class="text-2xl font-black tracking-tight text-slate-400 leading-none">
                                                    No score
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            @if($self['is_completed'])
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-2xs {{ $self['category_badge'] ?? $self['badge'] }}">
                                                    <span>{{ $self['category'] }}</span>
                                                </span>
                                                @if(isset($self['score']))
                                                    <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                                        {{ $self['score'] }} / {{ $self['max_score'] }} pts
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-2xs bg-slate-100 text-slate-500 border-slate-200">
                                                    <span>Pending Self Rating</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Meter 3: Peer Meter -->
                                <div class="bg-gradient-to-b from-emerald-50/30 via-white to-white rounded-2xl border border-emerald-200/80 p-5 flex flex-col justify-between items-center text-center space-y-4 shadow-2xs">
                                    <div class="w-full flex items-center justify-between">
                                        <div class="text-left">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                                Meter 3: Peer
                                            </span>
                                            <h4 class="text-base font-black text-slate-900 mt-1.5">Peer Score</h4>
                                            <p class="text-[10px] text-slate-400">Meter 2: Peer Feedback</p>
                                        </div>
                                        <span class="text-2xl">{{ $peer['category_emoji'] ?? $peer['emoji'] ?? '👥' }}</span>
                                    </div>

                                    <!-- Gauge -->
                                    <div class="w-full max-w-[200px] my-auto py-2">
                                        <x-score-meter 
                                            :percentage="(($peer['completed_count'] ?? 0) > 0) ? $peer['percentage'] : 0" 
                                            :category="(($peer['completed_count'] ?? 0) > 0) ? $peer['category'] : 'Pending'" 
                                            :only-gauge="true"
                                        />
                                    </div>

                                    <!-- Score Numbers Box -->
                                    <div class="w-full pt-4 border-t border-emerald-100 space-y-2">
                                        <div class="flex items-baseline justify-center gap-1">
                                            @if(($peer['completed_count'] ?? 0) > 0)
                                                <span class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 leading-none">
                                                    {{ number_format($peer['percentage'], 2) }}
                                                </span>
                                                <span class="text-lg font-bold text-slate-400">%</span>
                                            @else
                                                <span class="text-2xl font-black tracking-tight text-slate-400 leading-none">
                                                    No score
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            @if(($peer['completed_count'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-2xs {{ $peer['category_badge'] ?? $peer['badge'] }}">
                                                    <span>{{ $peer['category'] }}</span>
                                                </span>
                                                <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                                    {{ $peer['completed_count'] }} of {{ $peer['total_count'] }} Peers
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border shadow-2xs bg-slate-100 text-slate-500 border-slate-200">
                                                    <span>0 of {{ $peer['total_count'] }} Peers</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 360° Alignment / Perception Gap Banner -->
                            @if($isCompleted)
                                <div class="p-4 sm:p-5 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $comparison['alignment_badge'] }}">
                                    <div class="flex items-start sm:items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-white/80 shadow-xs flex items-center justify-center shrink-0">
                                            <i data-lucide="scale" class="w-5 h-5 text-slate-700"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-extrabold text-sm text-slate-900">
                                                    Perception Analysis: {{ $comparison['alignment_label'] }}
                                                </span>
                                                @if($comparison['has_both'])
                                                    <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-white/90 text-slate-700 shadow-2xs">
                                                        Gap: {{ $comparison['gap'] > 0 ? '+' : '' }}{{ number_format($comparison['gap'], 2) }}%
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs mt-0.5 text-slate-700 leading-relaxed">
                                                {{ $comparison['insight'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center gap-4 text-xs font-mono bg-white/80 px-3.5 py-2 rounded-xl border border-black/5">
                                        <div class="text-center">
                                            <span class="text-[10px] text-slate-400 uppercase font-sans block">Self</span>
                                            <span class="font-bold text-slate-900">{{ number_format($self['percentage'], 1) }}%</span>
                                        </div>
                                        <span class="text-slate-300 font-sans">vs</span>
                                        <div class="text-center">
                                            <span class="text-[10px] text-slate-400 uppercase font-sans block">Peers</span>
                                            <span class="font-bold text-slate-900">{{ number_format($peer['percentage'], 1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-white shadow-xs flex items-center justify-center shrink-0 border border-slate-200">
                                            <i data-lucide="scale" class="w-5 h-5 text-slate-400"></i>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-sm text-slate-800">
                                                Perception Analysis: Awaiting Assessment Submission
                                            </span>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                Submit your ratings to calculate your personal vs. peer perception gap.
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-mono font-bold text-slate-400 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shrink-0">
                                        No score yet
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- ALL DETAIL OF THE SURVEY SUBMITTED: Competency Dimension Matrix -->
                        @if(!empty($questionsBreakdown))
                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                            Detailed Matrix
                                        </span>
                                        <h3 class="text-lg font-black text-slate-900 mt-1">
                                            All Question Matrix Details
                                        </h3>
                                        <p class="text-xs text-slate-500">
                                            Breakdown of each competency question comparing your self-rating (1–10) vs peer average rating (1–10).
                                        </p>
                                    </div>
                                </div>

                                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                            <tr>
                                                <th class="py-3 px-4 w-12 text-center">#</th>
                                                <th class="py-3 px-4">Behavioral Competency Question</th>
                                                <th class="py-3 px-4 text-center w-28">Your Self Rating</th>
                                                <th class="py-3 px-4 text-center w-28">Peers Avg Rating</th>
                                                <th class="py-3 px-4 w-40 text-center">Comparison Bar</th>
                                                <th class="py-3 px-4 text-center w-24">Alignment Gap</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($questionsBreakdown as $item)
                                                <tr class="hover:bg-slate-50/60 transition">
                                                    <td class="py-3.5 px-4 font-mono font-bold text-center text-slate-400">
                                                        Q{{ $item['question']->sort_order }}
                                                    </td>
                                                    <td class="py-3.5 px-4 font-medium text-slate-900">
                                                        {{ $item['question']->question_text }}
                                                    </td>
                                                    <td class="py-3.5 px-4 text-center">
                                                        @if($item['self_score'] !== null)
                                                            <span class="inline-flex items-center justify-center font-black text-xs px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                                {{ $item['self_score'] }} <span class="text-[10px] text-slate-400 font-normal">/ 10</span>
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400 italic text-[11px]">No score</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3.5 px-4 text-center">
                                                        @if($item['peer_avg'] !== null)
                                                            <span class="inline-flex items-center justify-center font-black text-xs px-2.5 py-1 rounded-xl bg-teal-50 text-teal-700 border border-teal-200">
                                                                {{ number_format($item['peer_avg'], 1) }} <span class="text-[10px] text-slate-400 font-normal">/ 10</span>
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400 italic text-[11px]">No score</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3.5 px-4">
                                                        <div class="space-y-1">
                                                            <!-- Self bar (indigo) -->
                                                            <div class="flex items-center gap-1.5 text-[9px] text-slate-400 font-mono">
                                                                <span class="w-7">Self:</span>
                                                                <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                                    <div class="bg-indigo-600 h-1.5 rounded-full" 
                                                                         style="width: {{ ($item['self_score'] ?? 0) * 10 }}%"></div>
                                                                </div>
                                                            </div>
                                                            <!-- Peer bar (teal) -->
                                                            <div class="flex items-center gap-1.5 text-[9px] text-slate-400 font-mono">
                                                                <span class="w-7">Peer:</span>
                                                                <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                                    <div class="bg-teal-500 h-1.5 rounded-full" 
                                                                         style="width: {{ ($item['peer_avg'] ?? 0) * 10 }}%"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-xs">
                                                        @if($item['gap'] !== null)
                                                            <span class="{{ $item['gap'] > 0.5 ? 'text-amber-600' : ($item['gap'] < -0.5 ? 'text-blue-600' : 'text-emerald-600') }}">
                                                                {{ $item['gap'] > 0 ? '+' : '' }}{{ number_format($item['gap'], 1) }}
                                                            </span>
                                                        @else
                                                            <span class="text-slate-300">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- COHORT RATINGS MATRIX (Ratings Submitted for Team Members in this Survey) -->
                        @if(!empty($givenRatingsBreakdown))
                            <div class="space-y-3 pt-4 border-t border-slate-100">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                                        Cohort Evaluations for this Survey ({{ count($givenRatingsBreakdown) }} people):
                                    </span>
                                    <span class="text-slate-400 text-[11px]">
                                        Row 1 is You (Self), followed by all colleagues
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($givenRatingsBreakdown as $row)
                                        <div class="p-3.5 rounded-2xl border flex items-center justify-between gap-3 {{ $row['is_self'] ? 'bg-indigo-50/60 border-indigo-200 shadow-xs' : 'bg-slate-50/60 border-slate-200' }}">
                                            <div class="flex items-center gap-2.5 truncate">
                                                <div class="w-8 h-8 rounded-xl font-black text-xs flex items-center justify-center shrink-0 {{ $row['is_self'] ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-200 text-slate-700' }}">
                                                    {{ substr($row['member']->name, 0, 1) }}
                                                </div>
                                                <div class="truncate">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-xs font-bold text-slate-900 truncate">
                                                            {{ $row['is_self'] ? 'You (' . $row['member']->name . ')' : $row['member']->name }}
                                                        </span>
                                                    </div>
                                                    <span class="text-[10px] text-slate-400 truncate block">{{ $row['member']->email }}</span>
                                                </div>
                                            </div>

                                            <div class="shrink-0 text-right">
                                                @if($row['is_completed'])
                                                    <span class="text-[11px] font-extrabold text-slate-900 block font-mono">
                                                        {{ $row['total_score'] }} / 110
                                                    </span>
                                                    <span class="text-[9px] font-bold text-emerald-600 block">
                                                        {{ number_format($row['percentage'], 1) }}%
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $row['is_self'] ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                                        {{ $row['is_self'] ? '1st (Self)' : 'Pending' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    @endif
</x-layouts.app>
