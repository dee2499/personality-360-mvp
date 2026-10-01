<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">People & 360 Feedback Profiles</h1>
                <p class="text-xs text-slate-500 font-medium">Select any participant to examine their 360-degree score meter, combined rating, and individual peer assessments</p>
            </div>
            
            <!-- Survey Filter -->
            <form method="GET" action="{{ route('admin.people.index') }}" id="people-filter-form" class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 shrink-0">Filter by Survey:</label>
                
                <div class="relative min-w-[220px] sm:min-w-[280px]"
                     x-data="{
                         open: false,
                         searchQuery: '',
                         selectedSurveyTitle: '{{ $surveys->firstWhere('id', request('survey_id'))?->title ?? 'All Surveys' }}',
                         surveyTitles: @js(array_merge(['All Surveys'], $surveys->pluck('title')->values()->all())),
                         matches(name) {
                             if (!this.searchQuery.trim()) return true;
                             return name.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
                         },
                         selectSurvey(id, title) {
                             const sel = document.getElementById('survey_id');
                             if (sel) {
                                 sel.value = id;
                                 document.getElementById('people-filter-form').submit();
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
                                    @click="selectSurvey('', 'All Surveys')"
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
                                        @click="selectSurvey('{{ $survey->id }}', '{{ addslashes($survey->title) }}')"
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

                    <!-- Native select for accessibility, testing & standard form serialization -->
                    <select name="survey_id" id="survey_id" class="sr-only" onchange="this.form.submit()">
                        <option value="">All Surveys</option>
                        @foreach($surveys as $survey)
                            <option value="{{ $survey->id }}" {{ request('survey_id') == $survey->id ? 'selected' : '' }}>
                                {{ $survey->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Participants Directory Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($participants->isEmpty())
                <div class="py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No participants found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                        Enroll participants in surveys to see their 360-degree assessment progress.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                <th class="py-3 px-6">Person</th>
                                <th class="py-3 px-6">Email</th>
                                <th class="py-3 px-6">Assessments Received</th>
                                <th class="py-3 px-6">Completion</th>
                                <th class="py-3 px-6">Combined Score</th>
                                <th class="py-3 px-6">Category</th>
                                <th class="py-3 px-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($participants as $person)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100">
                                                {{ substr($person->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.people.show', $person) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition">
                                                    {{ $person->name }}
                                                </a>
                                                <div class="text-[10px] text-slate-400 font-medium">Participant</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-slate-600">
                                        {{ $person->email }}
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-800">
                                        <span class="text-indigo-700 font-bold">{{ $person->metrics['completed_count'] }}</span> 
                                        <span class="text-slate-400 font-normal">/ {{ $person->metrics['total_count'] }} completed</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2 max-w-[120px]">
                                            <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full {{ $person->metrics['completion_rate'] >= 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}" 
                                                     style="width: {{ $person->metrics['completion_rate'] }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-700">{{ $person->metrics['completion_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($person->metrics['completed_count'] > 0)
                                            <span class="font-black text-slate-900">{{ number_format($person->metrics['percentage'], 2) }}%</span>
                                            <span class="text-[11px] text-slate-400 font-medium ml-1">({{ $person->metrics['combined_score'] }}/{{ $person->metrics['combined_max_score'] }})</span>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Awaiting evaluations</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($person->metrics['completed_count'] > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $person->metrics['category_badge'] }}">
                                                <span>{{ $person->metrics['category_emoji'] }}</span>
                                                <span>{{ $person->metrics['category'] }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500">
                                                <span>⏳</span> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.people.show', $person) }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                                <span>View Profile</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </a>

                                            @if($person->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.people.destroy', $person) }}" 
                                                      onsubmit="return confirm('Are you sure you want to delete {{ $person->name }}? This will permanently remove their assessments and participation.')" 
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl font-semibold text-xs text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition"
                                                            title="Delete Person">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
