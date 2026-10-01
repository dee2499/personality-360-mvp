<x-layouts.app>
    <div class="space-y-8">
        <!-- Back Navigation & Person Profile Header -->
        <div>
            <a href="{{ route('admin.people.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to People Directory</span>
            </a>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-indigo-100">
                        {{ substr($person->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $person->name }}</h1>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                360° Subject Profile
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                            <span class="flex items-center gap-1">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $person->email }}
                            </span>
                            <span>•</span>
                            <span class="font-medium text-slate-700">Role: {{ ucfirst($person->role) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <!-- Progress Pill -->
                    <div class="flex flex-col sm:items-end gap-1.5 bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-xl">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Assessment Progress</span>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-extrabold text-slate-900">
                                {{ $metrics['completed_count'] }} <span class="text-slate-400 text-sm font-normal">/ {{ $metrics['total_count'] }} Completed</span>
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-md {{ $metrics['completion_rate'] >= 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ $metrics['completion_rate'] }}%
                            </span>
                        </div>
                        <div class="w-48 bg-slate-200 rounded-full h-2 overflow-hidden mt-1">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $metrics['completion_rate'] }}%"></div>
                        </div>
                    </div>

                    @if($person->id !== auth()->id())
                        <div class="border-t sm:border-t-0 sm:border-l border-slate-200 pt-3 sm:pt-0 sm:pl-4">
                            <form method="POST" action="{{ route('admin.people.destroy', $person) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete employee {{ $person->name }}? All related assessments and data will be permanently removed.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-600"></i>
                                    <span>Delete Employee</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Assigned Surveys & Status (Completed & Pending) -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Assigned Surveys</h2>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">
                        {{ $userSurveys->count() }} Total
                    </span>
                    @if($pendingSurveys->isNotEmpty())
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ $pendingSurveys->count() }} Pending Survey(s)
                        </span>
                    @endif
                </div>

                <!-- Modern UI Survey Selection Box -->
                <div class="relative min-w-[260px] sm:min-w-[320px]"
                     x-data="{
                         open: false,
                         searchQuery: '',
                         surveyTitles: @js(array_merge(['All Surveys Combined'], $userSurveys->pluck('survey.title')->values()->all())),
                         matches(name) {
                             if (!this.searchQuery.trim()) return true;
                             return name.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
                         }
                     }"
                     @click.outside="open = false"
                     @keydown.escape.window="open = false">
                    
                    <!-- Select Trigger Button -->
                    <button type="button"
                            @click="open = !open; if (open) $nextTick(() => $refs.searchInput?.focus())"
                            class="w-full flex items-center justify-between gap-3 px-4 py-2.5 bg-slate-50 hover:bg-slate-100 rounded-2xl border border-slate-200 text-left transition shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 cursor-pointer">
                        <div class="flex items-center gap-2.5 truncate">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $selectedSurvey ? ($userSurveys->firstWhere('survey.id', $selectedSurvey->id)['is_completed'] ? 'bg-emerald-500' : 'bg-amber-500') : 'bg-indigo-600' }}"></span>
                            <div class="truncate">
                                <span class="text-xs font-bold text-slate-900 block truncate">
                                    {{ $selectedSurvey ? $selectedSurvey->title : 'All Surveys Combined' }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">
                                    {{ $selectedSurvey ? ($userSurveys->firstWhere('survey.id', $selectedSurvey->id)['is_completed'] ? 'Completed' : 'Pending Survey') : $userSurveys->count() . ' Surveys Active' }}
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

                        <!-- Options List -->
                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                            <!-- Option 1: All Surveys Combined -->
                            <a href="{{ route('admin.people.show', $person) }}"
                               x-show="matches('All Surveys Combined')"
                               class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-indigo-50/50 transition cursor-pointer {{ $selectedSurvey === null ? 'bg-indigo-50/70' : '' }}">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 {{ $selectedSurvey === null ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </div>
                                    <div class="truncate">
                                        <span class="text-xs font-bold text-slate-900 block truncate {{ $selectedSurvey === null ? 'text-indigo-950 font-black' : '' }}">
                                            All Surveys Combined
                                        </span>
                                        <span class="text-[10px] text-slate-400">
                                            Aggregated synthesis across {{ $userSurveys->count() }} surveys
                                        </span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 shrink-0">
                                    All
                                </span>
                            </a>

                            <!-- Individual Surveys -->
                            @foreach($userSurveys as $sData)
                                @php
                                    $sItem = $sData['survey'];
                                    $isSelected = $selectedSurvey && $selectedSurvey->id === $sItem->id;
                                    $sCompleted = $sData['is_completed'];
                                    $url = route('admin.people.show', [$person, 'survey_id' => $sItem->id]);
                                @endphp
                                <a href="{{ $url }}"
                                   x-show="matches('{{ addslashes($sItem->title) }}')"
                                   class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-indigo-50/50 transition cursor-pointer {{ $isSelected ? 'bg-indigo-50/70' : '' }}">
                                    <div class="flex items-center gap-2.5 truncate">
                                        <div class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 {{ $isSelected ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                        </div>
                                        <div class="truncate">
                                            <span class="text-xs font-bold text-slate-900 block truncate {{ $isSelected ? 'text-indigo-950 font-black' : '' }}">
                                                {{ $sItem->title }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ $sItem->company?->name ?? 'Company Survey' }}
                                            </span>
                                        </div>
                                    </div>

                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 {{ $sCompleted ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $sCompleted ? 'Completed' : 'Pending Survey' }}
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

                    <!-- Native select for accessibility & automation -->
                    <select class="sr-only" onchange="if (this.value) window.location.href = this.value">
                        <option value="{{ route('admin.people.show', $person) }}" {{ $selectedSurvey === null ? 'selected' : '' }}>
                            All Surveys Combined
                        </option>
                        @foreach($userSurveys as $sData)
                            @php
                                $sItem = $sData['survey'];
                                $isSelected = $selectedSurvey && $selectedSurvey->id === $sItem->id;
                            @endphp
                            <option value="{{ route('admin.people.show', [$person, 'survey_id' => $sItem->id]) }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $sItem->title }} ({{ $sData['is_completed'] ? 'Completed' : 'Pending Survey' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($userSurveys->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center text-xs text-slate-500">
                    No surveys assigned to {{ $person->name }} yet.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($userSurveys as $sData)
                        @php
                            $surveyItem = $sData['survey'];
                            $isSelected = $selectedSurvey && $selectedSurvey->id === $surveyItem->id;
                        @endphp
                        <div class="bg-white rounded-3xl border p-6 transition flex flex-col justify-between gap-5 {{ $isSelected ? 'border-indigo-600 ring-2 ring-indigo-500/20 shadow-md' : 'border-slate-200 hover:border-slate-300 shadow-xs' }}">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                        Survey #{{ $surveyItem->id }}
                                    </span>

                                    @if($sData['is_completed'])
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            Completed
                                        </span>
                                    @elseif($sData['is_pending'])
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                            Pending Survey
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600">
                                            In Progress
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-lg font-black text-slate-900 tracking-tight">
                                    {{ $surveyItem->title }}
                                </h3>

                                <!-- Survey Breakdown Details -->
                                <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Evaluations of {{ $person->name }}:</span>
                                        <span class="font-bold {{ $sData['received_pending'] > 0 ? 'text-amber-700' : 'text-slate-900' }}">
                                            {{ $sData['received_completed'] }} of {{ $sData['received_total'] }} received
                                            @if($sData['received_pending'] > 0)
                                                <span class="font-normal text-amber-600">({{ $sData['received_pending'] }} pending)</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Assessments Submitted by {{ $person->name }}:</span>
                                        <span class="font-bold {{ $sData['given_pending'] > 0 ? 'text-amber-700' : 'text-slate-900' }}">
                                            {{ $sData['given_completed'] }} of {{ $sData['given_total'] }} submitted
                                            @if($sData['given_pending'] > 0)
                                                <span class="font-normal text-amber-600">({{ $sData['given_pending'] }} pending)</span>
                                            @endif
                                        </span>
                                    </div>

                                    @if($sData['metrics']['completed_count'] > 0)
                                        <div class="flex items-center justify-between pt-2 border-t border-slate-200/60">
                                            <span class="text-slate-500">Survey 360 Score:</span>
                                            <span class="font-black text-slate-900 flex items-center gap-1.5">
                                                <span>{{ number_format($sData['metrics']['percentage'], 2) }}%</span>
                                                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ $sData['metrics']['category_badge'] }}">
                                                    {{ $sData['metrics']['category_emoji'] }} {{ $sData['metrics']['category'] }}
                                                </span>
                                            </span>
                                        </div>
                                    @else
                                        <div class="pt-2 border-t border-slate-200/60 text-slate-400 italic text-[11px]">
                                            Awaiting evaluations to calculate 360 score.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                @if($isSelected)
                                    <span class="text-xs font-bold text-indigo-600 flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-4 h-4"></i> Active Filter View
                                    </span>
                                    <a href="{{ route('admin.people.show', $person) }}" 
                                       class="text-xs font-semibold text-slate-500 hover:text-slate-800 underline">
                                        Reset to All Surveys
                                    </a>
                                @else
                                    <a href="{{ route('admin.people.show', [$person, 'survey_id' => $surveyItem->id]) }}" 
                                       class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white transition">
                                        <span>Inspect This Survey's 360 Results</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($selectedSurvey)
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between text-xs text-indigo-900">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-indigo-600"></i>
                    <span>Viewing 360 score & assessments for: <strong>{{ $selectedSurvey->title }}</strong></span>
                </div>
                <a href="{{ route('admin.people.show', $person) }}" class="font-bold text-indigo-600 hover:text-indigo-800 underline">
                    Reset to All Surveys Combined
                </a>
            </div>
        @endif

        <!-- Centerpiece: 3-Section Combined Result -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            
            <!-- Section 1: The Visual Meter Gauge -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between items-center text-center">
                <div class="w-full text-left">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Section 1</span>
                    <h2 class="text-base font-black text-slate-900 mt-0.5">360° Visual Gauge</h2>
                </div>

                <div class="w-full my-auto py-2">
                    <x-score-meter 
                        :percentage="$metrics['percentage']" 
                        :category="$metrics['category']" 
                        :only-gauge="true"
                    />
                </div>

                <div class="text-[11px] text-slate-400 font-medium">
                    Calibrated scale: 0% to 100%
                </div>
            </div>

            <!-- Section 2: Percentage Number & Score -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                        Section 2
                    </span>
                    <h2 class="text-base font-black text-slate-900 mt-1.5">Score & Percentage</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Overall evaluation rating for {{ $person->name }}</p>
                </div>

                <div class="my-auto py-4">
                    <!-- Big Bold Percentage Number -->
                    <div class="flex items-baseline gap-1">
                        <span class="text-5xl sm:text-6xl font-black tracking-tight text-slate-900 leading-none">
                            {{ number_format($metrics['percentage'], 2) }}
                        </span>
                        <span class="text-2xl font-bold text-slate-400">%</span>
                    </div>

                    <!-- Category Badge & Points -->
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-bold border shadow-xs {{ $metrics['category_badge'] }}">
                            <span class="text-base">{{ $metrics['category_emoji'] }}</span>
                            <span>{{ $metrics['category'] }}</span>
                        </span>

                        <span class="text-sm font-extrabold text-slate-800 bg-slate-100 px-3 py-1 rounded-xl">
                            {{ $metrics['combined_score'] }} <span class="text-slate-400 font-normal">/ {{ $metrics['combined_max_score'] }} pts</span>
                        </span>
                    </div>
                </div>

                <!-- Progress info -->
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-slate-600">Completion</span>
                        <span class="font-extrabold text-indigo-600">{{ $metrics['completed_count'] }} of {{ $metrics['total_count'] }} ({{ $metrics['completion_rate'] }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $metrics['completion_rate'] }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Rest (Formula Breakdown & Scale Reference) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between space-y-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Section 3</span>
                    <h2 class="text-base font-black text-slate-900 mt-0.5">Synthesis Breakdown</h2>
                </div>

                <!-- Formula Breakdown Box -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
                    <div class="flex items-center justify-between font-bold text-slate-700 mb-2 border-b border-slate-200 pb-1.5">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="calculator" class="w-3.5 h-3.5 text-indigo-600"></i>
                            Formula Breakdown
                        </span>
                        <span class="text-[10px] text-slate-400 font-normal">MVP Specification §6</span>
                    </div>

                    @if($metrics['completed_count'] > 0)
                        <div class="space-y-1 font-mono text-[11px] text-slate-600">
                            <div class="flex justify-between">
                                <span>Sum of Scores:</span>
                                <span class="font-bold text-slate-900">{{ $metrics['combined_score'] }} pts</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Max Possible:</span>
                                <span class="font-bold text-slate-900">{{ $metrics['combined_max_score'] }} pts</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-slate-200 text-slate-900 font-bold">
                                <span>Percentage:</span>
                                <span class="text-indigo-600">{{ $metrics['combined_score'] }}/{{ $metrics['combined_max_score'] }} × 100 = {{ number_format($metrics['percentage'], 2) }}%</span>
                            </div>
                        </div>
                    @else
                        <p class="text-slate-500 italic text-center py-1">
                            No completed assessments yet.
                        </p>
                    @endif
                </div>

                <!-- 5 Category Scale Reference -->
                <div class="pt-2 border-t border-slate-100">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Category Scale Guide</span>
                    <div class="grid grid-cols-5 gap-1 text-center">
                        <div class="flex flex-col items-center">
                            <span class="text-xs">🍏</span>
                            <span class="text-[10px] font-bold text-slate-700">Apple</span>
                            <span class="text-[8px] text-slate-400">0–20%</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="text-xs">🍊</span>
                            <span class="text-[10px] font-bold text-slate-700">Orange</span>
                            <span class="text-[8px] text-slate-400">>20–40%</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="text-xs">🍅</span>
                            <span class="text-[10px] font-bold text-slate-700">Tomato</span>
                            <span class="text-[8px] text-slate-400">>40–60%</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="text-xs">🍋</span>
                            <span class="text-[10px] font-bold text-slate-700">Lemon</span>
                            <span class="text-[8px] text-slate-400">>60–80%</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="text-xs">🥒</span>
                            <span class="text-[10px] font-bold text-slate-700">Cucum.</span>
                            <span class="text-[8px] text-slate-400">>80–100%</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 360° Dual Meters: Self-Assessed Meter vs Peer-Assessed Meter -->
        @php
            $selfM = $metrics['self_metrics'] ?? null;
            $peerM = $metrics['peer_metrics'] ?? null;
            $compM = $metrics['comparison'] ?? null;
        @endphp

        @if($selfM && $peerM)
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                <i data-lucide="gauge" class="w-4 h-4 text-indigo-600"></i>
                                360° Dual-Meter Comparison
                            </span>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight mt-1.5">
                            Self-Assessed Meter vs. Peer-Assessed Meter
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Compare {{ $person->name }}'s internal perception against feedback provided by colleagues.
                        </p>
                    </div>

                    @if($compM && $compM['has_both'])
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border text-xs font-bold {{ $compM['alignment_badge'] }}">
                            <i data-lucide="scale" class="w-4 h-4"></i>
                            <span>{{ $compM['alignment_label'] }} ({{ $compM['gap'] > 0 ? '+' : '' }}{{ number_format($compM['gap'], 2) }}%)</span>
                        </div>
                    @endif
                </div>

                <!-- Two Meters Side-by-Side -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                    <!-- Meter 1: Self Assessed Meter -->
                    <div class="bg-gradient-to-b from-indigo-50/40 to-white rounded-3xl border border-indigo-100 p-6 flex flex-col justify-between items-center text-center shadow-xs">
                        <div class="w-full text-left flex items-start justify-between gap-2 mb-2">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-100/80 px-2.5 py-0.5 rounded-md">
                                    Meter 1: Self Evaluation
                                </span>
                                <h4 class="text-base sm:text-lg font-black text-slate-900 mt-1.5">
                                    Personal Self-Assessment
                                </h4>
                                <p class="text-xs text-slate-400">
                                    {{ $person->name }}'s rating of own competencies
                                </p>
                            </div>

                            @if($selfM['is_completed'])
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

                        <!-- Gauge Needle -->
                        <div class="w-full my-auto py-3">
                            <x-score-meter 
                                :percentage="$selfM['percentage']" 
                                :category="$selfM['category']" 
                                :only-gauge="true"
                            />
                        </div>

                        <!-- Scores Box -->
                        <div class="w-full pt-4 border-t border-indigo-100/70 space-y-3">
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 leading-none">
                                    {{ number_format($selfM['percentage'], 2) }}
                                </span>
                                <span class="text-xl font-bold text-slate-400">%</span>
                            </div>

                            <div class="flex items-center justify-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-2xs {{ $selfM['category_badge'] }}">
                                    <span class="text-sm">{{ $selfM['category_emoji'] }}</span>
                                    <span>{{ $selfM['category'] }}</span>
                                </span>

                                <span class="text-xs font-extrabold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                    {{ $selfM['score'] }} / {{ $selfM['max_score'] }} pts
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
                                <h4 class="text-base sm:text-lg font-black text-slate-900 mt-1.5">
                                    Peer-Assessed Rating
                                </h4>
                                <p class="text-xs text-slate-400">
                                    Evaluated by {{ $peerM['completed_count'] }} colleagues
                                </p>
                            </div>

                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200 shrink-0">
                                {{ $peerM['completed_count'] }} of {{ $peerM['total_count'] }} Peers
                            </span>
                        </div>

                        <!-- Gauge Needle -->
                        <div class="w-full my-auto py-3">
                            <x-score-meter 
                                :percentage="$peerM['percentage']" 
                                :category="$peerM['category']" 
                                :only-gauge="true"
                            />
                        </div>

                        <!-- Scores Box -->
                        <div class="w-full pt-4 border-t border-teal-100/70 space-y-3">
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 leading-none">
                                    {{ number_format($peerM['percentage'], 2) }}
                                </span>
                                <span class="text-xl font-bold text-slate-400">%</span>
                            </div>

                            <div class="flex items-center justify-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border shadow-2xs {{ $peerM['category_badge'] }}">
                                    <span class="text-sm">{{ $peerM['category_emoji'] }}</span>
                                    <span>{{ $peerM['category'] }}</span>
                                </span>

                                <span class="text-xs font-extrabold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                    {{ $peerM['average_score'] }} / 110 avg pts
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($compM && $compM['has_both'])
                    <!-- Perception Gap Insight -->
                    <div class="p-4 rounded-2xl border flex items-center justify-between gap-4 {{ $compM['alignment_badge'] }}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-white/80 shadow-xs flex items-center justify-center shrink-0">
                                <i data-lucide="info" class="w-4 h-4 text-slate-700"></i>
                            </div>
                            <p class="text-xs text-slate-800 leading-relaxed">
                                {{ $compM['insight'] }}
                            </p>
                        </div>

                        <div class="shrink-0 flex items-center gap-3 text-xs font-mono bg-white/80 px-3 py-1.5 rounded-xl border border-black/5">
                            <span class="text-slate-500">Self: <strong>{{ number_format($selfM['percentage'], 1) }}%</strong></span>
                            <span class="text-slate-300">|</span>
                            <span class="text-slate-500">Peers: <strong>{{ number_format($peerM['percentage'], 1) }}%</strong></span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Section 17: Individual Assessment Cards -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Individual Assessment Breakdown</h3>
                    <p class="text-xs text-slate-500">
                        All evaluations received by <span class="font-semibold text-slate-700">{{ $person->name }}</span>. 
                        Click any card to inspect all 11 questions and detailed answer ratings.
                    </p>
                </div>
                <span class="text-xs font-semibold text-slate-400">
                    {{ $metrics['all_assessments']->count() }} Total Assigned
                </span>
            </div>

            @if($metrics['all_assessments']->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                    <p class="text-xs text-slate-500">No assessments assigned for this participant yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($metrics['all_assessments'] as $assessment)
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs hover:shadow-md hover:border-indigo-300 transition flex flex-col justify-between group">
                            <!-- Card Header -->
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md
                                        {{ $assessment->isSelfAssessment() ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $assessment->isSelfAssessment() ? 'Self Assessment' : 'Peer Assessment' }}
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

                                <!-- Assessor -> Subject Headline -->
                                <h4 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                                    <span>{{ $assessment->assessor->name }}</span>
                                    <span class="text-slate-300 font-normal">→</span>
                                    <span class="text-indigo-600">{{ $assessment->subject->name }}</span>
                                </h4>

                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">
                                    Survey: {{ $assessment->survey->title }}
                                </div>
                            </div>

                            <!-- Metrics / Score Block -->
                            <div class="my-4 pt-4 border-t border-slate-100">
                                @if($assessment->isCompleted())
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-slate-50 p-2 rounded-xl">
                                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Score</span>
                                            <span class="text-sm font-black text-slate-900">{{ $assessment->total_score }}/{{ $assessment->max_score }}</span>
                                        </div>
                                        <div class="bg-slate-50 p-2 rounded-xl">
                                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Percentage</span>
                                            <span class="text-sm font-black text-slate-900">{{ number_format($assessment->percentage, 2) }}%</span>
                                        </div>
                                        <div class="bg-slate-50 p-2 rounded-xl">
                                            <span class="block text-[10px] font-bold text-slate-400 uppercase">Category</span>
                                            <span class="text-xs font-bold text-indigo-700 truncate">
                                                {{ match($assessment->category) {
                                                    'Apple' => '🍏 Apple',
                                                    'Orange' => '🍊 Orange',
                                                    'Tomato' => '🍅 Tomato',
                                                    'Lemon' => '🍋 Lemon',
                                                    'Cucumber' => '🥒 Cucumber',
                                                    default => $assessment->category
                                                } }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-slate-50/75 rounded-xl p-3 text-center text-xs text-slate-400 italic">
                                        Assessment is currently {{ $assessment->status }}. Answers will appear here once submitted.
                                    </div>
                                @endif
                            </div>

                            <!-- View Action Button -->
                            <a href="{{ route('admin.assessments.show', $assessment) }}" 
                               class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white transition group-hover:bg-indigo-600 group-hover:text-white">
                                <span>View Assessment</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
