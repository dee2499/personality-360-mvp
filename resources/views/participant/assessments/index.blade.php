<x-layouts.app>
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Dashboard Header & Overall Completion Progress -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                    Participant Portal
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-2">
                    My 360° Assessment Dashboard
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                    View your self-assessment meter, peer-assessment meter, and inspect full competency matrices across your submitted 360 surveys.
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
                    <!-- Left: Label & Description -->
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

                    <!-- Right: Modern Select Box UI -->
                    <div class="relative min-w-[280px] sm:min-w-[340px]">
                        <!-- Custom Select Trigger Button -->
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

                        <!-- Floating Dropdown Menu -->
                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                             class="absolute right-0 left-0 mt-2 z-40 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden py-1">
                            
                            <!-- Search by Name Input -->
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

                            <!-- Survey Options List -->
                            <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                                @foreach($surveyGroups as $group)
                                    @php
                                        $s = $group['survey'];
                                        $isSelected = $selectedSurvey && $selectedSurvey->id === $s->id;
                                        $sCompleted = $group['isCompleted'];
                                        $url = route('participant.assessments.index', ['survey_id' => $s->id]);
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

                                <!-- Empty State when search matches nothing -->
                                <div x-show="!surveyTitles.some(t => matches(t))"
                                     x-cloak
                                     class="py-6 px-4 text-center">
                                    <p class="text-xs text-slate-500 font-medium">No surveys found matching "<span x-text="searchQuery" class="font-bold text-slate-700"></span>"</p>
                                    <button type="button" @click="searchQuery = ''; $refs.searchInput?.focus()" class="mt-1.5 text-[11px] font-bold text-indigo-600 hover:text-indigo-800">Clear search</button>
                                </div>
                            </div>
                        </div>

                        <!-- Native Select for accessibility & testing fallback -->
                        <select id="survey-select" 
                                class="sr-only"
                                onchange="if (this.value) window.location.href = this.value">
                            @foreach($surveyGroups as $group)
                                @php
                                    $s = $group['survey'];
                                    $isSelected = $selectedSurvey && $selectedSurvey->id === $s->id;
                                    $sCompleted = $group['isCompleted'];
                                    $url = route('participant.assessments.index', ['survey_id' => $s->id]);
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
                    $self = $selectedGroup['self'];
                    $peer = $selectedGroup['peer'];
                    $comparison = $selectedGroup['comparison'];
                    $cohort = $selectedGroup['cohortMembers'];
                @endphp

                <!-- Active Survey Master Card -->
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
                                {{ $survey->title }}
                            </h2>

                            @if($survey->description)
                                <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                                    {{ $survey->description }}
                                </p>
                            @endif
                        </div>

                        <!-- CTA Actions -->
                        <div class="shrink-0 flex flex-wrap items-center gap-2">
                            <a href="{{ route('participant.assessments.report', $survey) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
                                <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                                <span>Confidential CQ Report</span>
                            </a>

                            <a href="{{ route('participant.surveys.group-insights', $survey) }}" 
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
                                <span>Team Insights</span>
                            </a>

                            <a href="{{ route('participant.surveys.take', $survey) }}" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-100 transition">
                                <i data-lucide="{{ $isCompleted ? 'eye' : 'sparkles' }}" class="w-4 h-4"></i>
                                <span>{{ $isCompleted ? 'Review in 11-Question Wizard' : 'Take 11-Question Survey' }}</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                    <!-- TWO METERS SECTION (Self-Assessment Meter vs Peer-Assessment Meter) -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                    Dual-Meter Benchmark
                                </span>
                                <h3 class="text-lg font-black text-slate-900 mt-1">
                                    Self-Assessment Meter vs. Peer-Assessment Meter
                                </h3>
                            </div>
                            <span class="text-xs text-slate-400">Scale: 0% to 100%</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                            <!-- Meter 1: Self Assessed Meter -->
                            <div class="bg-gradient-to-b from-indigo-50/40 to-white rounded-3xl border border-indigo-100 p-6 flex flex-col justify-between items-center text-center shadow-xs">
                                <div class="w-full text-left flex items-start justify-between gap-2 mb-2">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-100/80 px-2.5 py-0.5 rounded-md">
                                            Meter 1: Self Evaluation
                                        </span>
                                        <h4 class="text-base font-black text-slate-900 mt-1.5">
                                            Personal Assessment
                                        </h4>
                                        <p class="text-xs text-slate-400">
                                            How you rated your own competencies in this survey
                                        </p>
                                    </div>

                                    @if($self['is_completed'])
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                            <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i>
                                            Rated
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                                            Pending
                                        </span>
                                    @endif
                                </div>

                                <!-- Visual Gauge Needle -->
                                <div class="w-full my-auto py-2">
                                    <x-score-meter 
                                        :percentage="$self['percentage']" 
                                        :category="$self['category']" 
                                        :only-gauge="true"
                                    />
                                </div>

                                <!-- Score Numbers Box -->
                                <div class="w-full pt-4 border-t border-indigo-100/70 space-y-3">
                                    <div class="flex items-baseline justify-center gap-1">
                                        <span class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 leading-none">
                                            {{ number_format($self['percentage'], 2) }}
                                        </span>
                                        <span class="text-xl font-bold text-slate-400">%</span>
                                    </div>

                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-2xs {{ $self['category_badge'] }}">
                                            <span class="text-sm">{{ $self['category_emoji'] }}</span>
                                            <span>{{ $self['category'] }}</span>
                                        </span>

                                        <span class="text-xs font-extrabold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                            {{ $self['score'] }} / {{ $self['max_score'] }} pts
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Meter 2: Peer Assessed Meter -->
                            <div class="bg-gradient-to-b from-teal-50/40 to-white rounded-3xl border border-teal-100 p-6 flex flex-col justify-between items-center text-center shadow-xs">
                                <div class="w-full text-left flex items-start justify-between gap-2 mb-2">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-teal-800 bg-teal-100/80 px-2.5 py-0.5 rounded-md">
                                            Meter 2: Peer Feedback
                                        </span>
                                        <h4 class="text-base font-black text-slate-900 mt-1.5">
                                            Peer-Assessed Rating
                                        </h4>
                                        <p class="text-xs text-slate-400">
                                            Average score evaluated by {{ $peer['completed_count'] }} colleagues
                                        </p>
                                    </div>

                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200 shrink-0">
                                        {{ $peer['completed_count'] }} of {{ $peer['total_count'] }} Peers
                                    </span>
                                </div>

                                <!-- Visual Gauge Needle -->
                                <div class="w-full my-auto py-2">
                                    <x-score-meter 
                                        :percentage="$peer['percentage']" 
                                        :category="$peer['category']" 
                                        :only-gauge="true"
                                    />
                                </div>

                                <!-- Score Numbers Box -->
                                <div class="w-full pt-4 border-t border-teal-100/70 space-y-3">
                                    <div class="flex items-baseline justify-center gap-1">
                                        <span class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 leading-none">
                                            {{ number_format($peer['percentage'], 2) }}
                                        </span>
                                        <span class="text-xl font-bold text-slate-400">%</span>
                                    </div>

                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-2xs {{ $peer['category_badge'] }}">
                                            <span class="text-sm">{{ $peer['category_emoji'] }}</span>
                                            <span>{{ $peer['category'] }}</span>
                                        </span>

                                        <span class="text-xs font-extrabold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                            {{ $peer['average_score'] }} / 110 avg pts
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 360° Alignment / Perception Gap Banner -->
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
                                        All Question Matrix Details Submitted
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
                                                        <span class="text-slate-400 italic text-[11px]">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 px-4 text-center">
                                                    @if($item['peer_avg'] !== null)
                                                        <span class="inline-flex items-center justify-center font-black text-xs px-2.5 py-1 rounded-xl bg-teal-50 text-teal-700 border border-teal-200">
                                                            {{ number_format($item['peer_avg'], 1) }} <span class="text-[10px] text-slate-400 font-normal">/ 10</span>
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400 italic text-[11px]">—</span>
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
                                    Cohort Evaluations Submitted for this Survey ({{ count($givenRatingsBreakdown) }} people):
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
</x-layouts.app>
