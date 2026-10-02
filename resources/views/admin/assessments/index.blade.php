<x-layouts.app>
    <div class="space-y-6">
        <!-- Header & Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Assessments Master Matrix</h1>
                <p class="text-xs text-slate-500 font-medium">All generated 360-degree assessment pairings across active and past surveys</p>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.assessments.index') }}" id="assessments-filter-form" class="flex flex-wrap items-center gap-2">
                <select name="status" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border-slate-300 py-2 pl-3 pr-8 focus:border-indigo-600 focus:ring-indigo-600/20">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>

                <!-- Searchable Company Select Box -->
                <div class="relative min-w-[180px] sm:min-w-[220px]"
                     x-data="{
                         open: false,
                         searchQuery: '',
                         selectedCompanyName: '{{ $selectedCompany?->name ?? 'All Companies' }}',
                         companyNames: @js(array_merge(['All Companies'], $companies->pluck('name')->values()->all())),
                         matches(name) {
                             if (!this.searchQuery.trim()) return true;
                             return name.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
                         },
                         selectCompany(id) {
                             const sel = document.getElementById('assessments_company_id');
                             if (sel) {
                                 sel.value = id;
                                 document.getElementById('assessments-filter-form').submit();
                             }
                         }
                     }"
                     @click.outside="open = false"
                     @keydown.escape.window="open = false">
                    
                    <!-- Select Trigger Button -->
                    <button type="button"
                            @click="open = !open; if (open) $nextTick(() => $refs.compSearchInput?.focus())"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 bg-white hover:bg-slate-50 rounded-xl border border-slate-300 text-left text-xs transition shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 cursor-pointer">
                        <div class="flex items-center gap-1.5 truncate">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                            <span class="font-medium text-slate-900 truncate" x-text="selectedCompanyName"></span>
                        </div>
                        <i data-lucide="chevron-down" 
                           class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 shrink-0"
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
                         class="absolute right-0 left-0 sm:left-auto sm:right-0 sm:w-72 mt-1.5 z-40 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden py-1">
                        
                        <!-- Search by Name Input -->
                        <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                            <div class="relative">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text"
                                       x-ref="compSearchInput"
                                       x-model="searchQuery"
                                       placeholder="Search companies..."
                                       @keydown.escape.stop="if (searchQuery) { searchQuery = '' } else { open = false }"
                                       class="w-full text-xs pl-8 pr-7 py-1.5 bg-white border border-slate-200 rounded-xl focus:border-indigo-600 focus:outline-hidden focus:ring-1 focus:ring-indigo-600 transition placeholder-slate-400 font-medium">
                                <button type="button"
                                        x-show="searchQuery.length > 0"
                                        x-cloak
                                        @click="searchQuery = ''; $refs.compSearchInput?.focus()"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Options List -->
                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                            <!-- All Companies Option -->
                            <button type="button"
                                    x-show="matches('All Companies')"
                                    @click="selectCompany('')"
                                    class="w-full flex items-center justify-between gap-3 px-3.5 py-2 hover:bg-indigo-50/50 transition cursor-pointer text-left {{ !request('company_id') && !request('company') ? 'bg-indigo-50/70' : '' }}">
                                <div class="flex items-center gap-2 truncate">
                                    <div class="w-4 h-4 rounded-md flex items-center justify-center shrink-0 {{ !request('company_id') && !request('company') ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                        <i data-lucide="check" class="w-2.5 h-2.5"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800 truncate {{ !request('company_id') && !request('company') ? 'text-indigo-950 font-bold' : '' }}">
                                        All Companies
                                    </span>
                                </div>
                            </button>

                            <!-- Individual Companies -->
                            @foreach($companies as $comp)
                                @php
                                    $isSelected = (request('company_id') == $comp->id) || (request('company') == $comp->name);
                                @endphp
                                <button type="button"
                                        x-show="matches('{{ addslashes($comp->name) }}')"
                                        @click="selectCompany('{{ $comp->id }}')"
                                        class="w-full flex items-center justify-between gap-3 px-3.5 py-2 hover:bg-indigo-50/50 transition cursor-pointer text-left {{ $isSelected ? 'bg-indigo-50/70' : '' }}">
                                    <div class="flex items-center gap-2 truncate">
                                        <div class="w-4 h-4 rounded-md flex items-center justify-center shrink-0 {{ $isSelected ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                            <i data-lucide="check" class="w-2.5 h-2.5"></i>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-800 truncate {{ $isSelected ? 'text-indigo-950 font-bold' : '' }}">
                                            {{ $comp->name }}
                                        </span>
                                    </div>
                                </button>
                            @endforeach

                            <!-- Empty State when search matches nothing -->
                            <div x-show="!companyNames.some(t => matches(t))"
                                 x-cloak
                                 class="py-6 px-4 text-center">
                                <p class="text-xs text-slate-500 font-medium">No companies found matching "<span x-text="searchQuery" class="font-bold text-slate-700"></span>"</p>
                                <button type="button" @click="searchQuery = ''; $refs.compSearchInput?.focus()" class="mt-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800">Clear search</button>
                            </div>
                        </div>
                    </div>

                    <!-- Native select for accessibility & form serialization -->
                    <select name="company_id" id="assessments_company_id" class="sr-only" onchange="this.form.submit()">
                        <option value="">All Companies</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ (request('company_id') == $comp->id || request('company') == $comp->name) ? 'selected' : '' }}>
                                {{ $comp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Searchable Survey Select Box -->
                <div class="relative min-w-[200px] sm:min-w-[260px]"
                     x-data="{
                         open: false,
                         searchQuery: '',
                         selectedSurveyTitle: '{{ $surveys->firstWhere('id', request('survey_id'))?->title ?? 'All Surveys' }}',
                         surveyTitles: @js(array_merge(['All Surveys'], $surveys->pluck('title')->values()->all())),
                         matches(name) {
                             if (!this.searchQuery.trim()) return true;
                             return name.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
                         },
                         selectSurvey(id) {
                             const sel = document.getElementById('assessments_survey_id');
                             if (sel) {
                                 sel.value = id;
                                 document.getElementById('assessments-filter-form').submit();
                             }
                         }
                     }"
                     @click.outside="open = false"
                     @keydown.escape.window="open = false">
                    
                    <!-- Select Trigger Button -->
                    <button type="button"
                            @click="open = !open; if (open) $nextTick(() => $refs.searchInput?.focus())"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 bg-white hover:bg-slate-50 rounded-xl border border-slate-300 text-left text-xs transition shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 cursor-pointer">
                        <span class="font-medium text-slate-900 truncate" x-text="selectedSurveyTitle"></span>
                        <i data-lucide="chevron-down" 
                           class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 shrink-0"
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
                         class="absolute right-0 left-0 sm:left-auto sm:right-0 sm:w-80 mt-1.5 z-40 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden py-1">
                        
                        <!-- Search by Name Input -->
                        <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                            <div class="relative">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <input type="text"
                                       x-ref="searchInput"
                                       x-model="searchQuery"
                                       placeholder="Search surveys by name..."
                                       @keydown.escape.stop="if (searchQuery) { searchQuery = '' } else { open = false }"
                                       class="w-full text-xs pl-8 pr-7 py-1.5 bg-white border border-slate-200 rounded-xl focus:border-indigo-600 focus:outline-hidden focus:ring-1 focus:ring-indigo-600 transition placeholder-slate-400 font-medium">
                                <button type="button"
                                        x-show="searchQuery.length > 0"
                                        x-cloak
                                        @click="searchQuery = ''; $refs.searchInput?.focus()"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Options List -->
                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                            <!-- All Surveys Option -->
                            <button type="button"
                                    x-show="matches('All Surveys')"
                                    @click="selectSurvey('')"
                                    class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 hover:bg-indigo-50/50 transition cursor-pointer text-left {{ !request('survey_id') ? 'bg-indigo-50/70' : '' }}">
                                <div class="flex items-center gap-2 truncate">
                                    <div class="w-4 h-4 rounded-md flex items-center justify-center shrink-0 {{ !request('survey_id') ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                        <i data-lucide="check" class="w-2.5 h-2.5"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800 truncate {{ !request('survey_id') ? 'text-indigo-950 font-bold' : '' }}">
                                        All Surveys
                                    </span>
                                </div>
                            </button>

                            <!-- Individual Surveys -->
                            @foreach($surveys as $survey)
                                @php
                                    $isSelected = request('survey_id') == $survey->id;
                                @endphp
                                <button type="button"
                                        x-show="matches('{{ addslashes($survey->title) }}')"
                                        @click="selectSurvey('{{ $survey->id }}')"
                                        class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 hover:bg-indigo-50/50 transition cursor-pointer text-left {{ $isSelected ? 'bg-indigo-50/70' : '' }}">
                                    <div class="flex items-center gap-2 truncate">
                                        <div class="w-4 h-4 rounded-md flex items-center justify-center shrink-0 {{ $isSelected ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                            <i data-lucide="check" class="w-2.5 h-2.5"></i>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-800 truncate {{ $isSelected ? 'text-indigo-950 font-bold' : '' }}">
                                            {{ $survey->title }}
                                        </span>
                                    </div>
                                </button>
                            @endforeach

                            <!-- Empty State when search matches nothing -->
                            <div x-show="!surveyTitles.some(t => matches(t))"
                                 x-cloak
                                 class="py-6 px-4 text-center">
                                <p class="text-xs text-slate-500 font-medium">No surveys found matching "<span x-text="searchQuery" class="font-bold text-slate-700"></span>"</p>
                                <button type="button" @click="searchQuery = ''; $refs.searchInput?.focus()" class="mt-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800">Clear search</button>
                            </div>
                        </div>
                    </div>

                    <!-- Native select for accessibility & form serialization -->
                    <select name="survey_id" id="assessments_survey_id" class="sr-only" onchange="this.form.submit()">
                        <option value="">All Surveys</option>
                        @foreach($surveys as $survey)
                            <option value="{{ $survey->id }}" {{ request('survey_id') == $survey->id ? 'selected' : '' }}>
                                {{ $survey->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(request('status') || request('survey_id') || request('company_id') || request('company'))
                    <a href="{{ route('admin.assessments.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800 p-2">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Assessments Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($assessments->isEmpty())
                <div class="py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="file-question" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No assessments match the filters</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                        Try changing the status or survey filter.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                <th class="py-3 px-6">Assessor</th>
                                <th class="py-3 px-6">Subject</th>
                                <th class="py-3 px-6">Type</th>
                                <th class="py-3 px-6">Survey</th>
                                <th class="py-3 px-6">Score</th>
                                <th class="py-3 px-6">Percentage</th>
                                <th class="py-3 px-6">Category</th>
                                <th class="py-3 px-6">Status</th>
                                <th class="py-3 px-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($assessments as $assessment)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 px-6 font-semibold text-slate-900">
                                        {{ $assessment->assessor->name }}
                                    </td>
                                    <td class="py-3.5 px-6 font-bold text-indigo-700">
                                        <a href="{{ route('admin.people.show', $assessment->subject) }}" class="hover:underline">
                                            {{ $assessment->subject->name }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($assessment->isSelfAssessment())
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Self
                                            </span>
                                        @else
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                                Peer
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-slate-600 max-w-[180px]">
                                        <div class="truncate font-semibold text-slate-900" title="{{ $assessment->survey?->title }}">
                                            {{ $assessment->survey?->title ?? 'General Survey' }}
                                        </div>
                                        @if($assessment->survey?->company)
                                            <a href="{{ route('admin.assessments.index', array_filter(['company_id' => $assessment->survey->company->id, 'status' => request('status')])) }}" 
                                               class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-1.5 py-0.5 rounded border border-indigo-100 mt-0.5 transition"
                                               title="Filter assessments by {{ $assessment->survey->company->name }}">
                                                <i data-lucide="building-2" class="w-3 h-3 text-indigo-500"></i>
                                                <span>{{ $assessment->survey->company->name }}</span>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 font-bold text-slate-800">
                                        @if($assessment->isCompleted())
                                            {{ $assessment->total_score }} / {{ $assessment->max_score }}
                                        @else
                                            <span class="text-slate-400 font-normal">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 font-extrabold text-slate-900">
                                        @if($assessment->isCompleted())
                                            {{ number_format($assessment->percentage, 2) }}%
                                        @else
                                            <span class="text-slate-400 font-normal">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($assessment->isCompleted() && $assessment->category)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                                                {{ match($assessment->category) {
                                                    'Apple' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                    'Orange' => 'bg-orange-50 text-orange-700 border border-orange-200',
                                                    'Tomato' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                                    'Lemon' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                    'Cucumber' => 'bg-teal-50 text-teal-700 border border-teal-200',
                                                    default => 'bg-slate-100 text-slate-700'
                                                } }}">
                                                <span>{{ match($assessment->category) {
                                                    'Apple' => '🍏',
                                                    'Orange' => '🍊',
                                                    'Tomato' => '🍅',
                                                    'Lemon' => '🍋',
                                                    'Cucumber' => '🥒',
                                                    default => '🎯'
                                                } }}</span>
                                                <span>{{ $assessment->category }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[11px]">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            {{ match($assessment->status) {
                                                'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                                'in_progress' => 'bg-blue-50 text-blue-700 border border-blue-100',
                                                default => 'bg-slate-100 text-slate-600 border border-slate-200'
                                            } }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ match($assessment->status) {
                                                'completed' => 'bg-emerald-500',
                                                'in_progress' => 'bg-blue-500',
                                                default => 'bg-slate-400'
                                            } }}"></span>
                                            <span class="capitalize">{{ str_replace('_', ' ', $assessment->status) }}</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        <a href="{{ route('admin.assessments.show', $assessment) }}" 
                                           class="inline-flex items-center gap-1 font-semibold text-indigo-600 hover:text-indigo-800">
                                            <span>View Details</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($assessments->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $assessments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.app>
