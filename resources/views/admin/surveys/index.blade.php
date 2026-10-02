<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Survey Management</h1>
                <p class="text-xs text-slate-500 font-medium">Create surveys, define 360 personality questions, assign participant cohorts, and publish</p>
            </div>
            <a href="{{ route('admin.surveys.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Create New Survey</span>
            </a>
        </div>

        <!-- Search & Company Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Filter Form with Search Input -->
            <form method="GET" action="{{ route('admin.surveys.index') }}" class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" 
                           name="company" 
                           value="{{ $companyFilter }}" 
                           placeholder="Filter surveys by company name..."
                           class="w-full text-xs pl-9 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-indigo-600 focus:outline-hidden focus:ring-1 focus:ring-indigo-600 transition placeholder-slate-400 font-medium">
                    @if($companyFilter)
                        <a href="{{ route('admin.surveys.index') }}" 
                           class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5" 
                           title="Clear company filter">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition cursor-pointer">
                        Filter
                    </button>
                    @if($companyFilter || $companyId)
                        <a href="{{ route('admin.surveys.index') }}" 
                           class="px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Company Quick Selector Dropdown -->
            <div class="relative shrink-0"
                 x-data="{
                     open: false,
                     search: '',
                     companies: @js($companies->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'count' => $c->surveys_count])),
                     filteredCompanies() {
                         if (!this.search.trim()) return this.companies;
                         return this.companies.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase().trim()));
                     }
                 }"
                 @click.outside="open = false"
                 @keydown.escape.window="open = false">
                
                <button type="button" 
                        @click="open = !open; if(open) $nextTick(() => $refs.compSearch?.focus())"
                        class="flex items-center justify-between gap-3 px-4 py-2.5 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 transition cursor-pointer min-w-[220px]">
                    <div class="flex items-center gap-2 truncate">
                        <i data-lucide="building-2" class="w-4 h-4 text-indigo-500 shrink-0"></i>
                        <span class="truncate">
                            {{ $selectedCompany?->name ?? ($companyFilter ? 'Company: ' . $companyFilter : 'All Companies') }}
                        </span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transition" :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-72 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden z-30">
                    
                    <div class="p-2 border-b border-slate-100 bg-slate-50/70">
                        <input type="text"
                               x-ref="compSearch"
                               x-model="search"
                               placeholder="Search companies..."
                               class="w-full text-xs px-3 py-1.5 bg-white border border-slate-200 rounded-lg focus:border-indigo-600 focus:outline-hidden text-slate-800">
                    </div>

                    <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                        <a href="{{ route('admin.surveys.index') }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 hover:bg-indigo-50/50 text-xs transition {{ empty($companyFilter) && empty($companyId) ? 'bg-indigo-50 font-bold text-indigo-900' : 'text-slate-700' }}">
                            <span>All Companies</span>
                            <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-600 font-bold">
                                {{ $totalSurveysCount }}
                            </span>
                        </a>

                        <template x-for="comp in filteredCompanies()" :key="comp.id">
                            <a :href="'{{ route('admin.surveys.index') }}?company=' + encodeURIComponent(comp.name)"
                               class="flex items-center justify-between px-3.5 py-2.5 hover:bg-indigo-50/50 text-xs transition"
                               :class="{ 'bg-indigo-50 font-bold text-indigo-900': '{{ strtolower($companyFilter ?? '') }}' === comp.name.toLowerCase() }">
                                <span class="truncate pr-2" x-text="comp.name"></span>
                                <span class="text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-0.5 rounded-full font-bold shrink-0" x-text="comp.count + ' surveys'"></span>
                            </a>
                        </template>

                        @if($unassignedSurveysCount > 0)
                            <a href="{{ route('admin.surveys.index', ['company' => 'none']) }}"
                               class="flex items-center justify-between px-3.5 py-2.5 hover:bg-indigo-50/50 text-xs transition {{ strtolower($companyFilter ?? '') === 'none' ? 'bg-indigo-50 font-bold text-indigo-900' : 'text-slate-500 italic' }}">
                                <span>No Company / Unassigned</span>
                                <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500 font-bold">
                                    {{ $unassignedSurveysCount }}
                                </span>
                            </a>
                        @endif

                        <div x-show="filteredCompanies().length === 0" class="py-4 text-center text-xs text-slate-400">
                            No matching companies
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($companyFilter || $companyId)
            <div class="p-3.5 bg-indigo-50 border border-indigo-200 rounded-xl flex items-center justify-between text-xs text-indigo-950">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-indigo-600"></i>
                    <span>Filtering surveys for company: <strong>{{ $selectedCompany?->name ?? $companyFilter }}</strong> ({{ $surveys->total() }} survey{{ $surveys->total() === 1 ? '' : 's' }} found)</span>
                </div>
                <a href="{{ route('admin.surveys.index') }}" class="font-bold text-indigo-700 hover:text-indigo-900 underline flex items-center gap-1">
                    <span>Clear filter</span>
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        @endif

        <!-- Surveys Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($surveys->isEmpty())
                <div class="py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    @if($companyFilter || $companyId)
                        <h3 class="text-sm font-bold text-slate-800">No surveys found for company "{{ $selectedCompany?->name ?? $companyFilter }}"</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                            There are currently no 360-degree personality assessment surveys matching this company name.
                        </p>
                        <div class="mt-4 flex items-center justify-center gap-3">
                            <a href="{{ route('admin.surveys.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                <span>Clear Filter</span>
                            </a>
                            <a href="{{ route('admin.surveys.create', ['company_id' => $selectedCompany?->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Create Survey</span>
                            </a>
                        </div>
                    @else
                        <h3 class="text-sm font-bold text-slate-800">No surveys configured</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                            Get started by creating your first 360-degree personality assessment survey.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('admin.surveys.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Create Survey</span>
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                <th class="py-3 px-6">Survey Title & Company</th>
                                <th class="py-3 px-6">Status</th>
                                <th class="py-3 px-6">Questions</th>
                                <th class="py-3 px-6">Participants</th>
                                <th class="py-3 px-6">Assessments (N × N)</th>
                                <th class="py-3 px-6">Completion</th>
                                <th class="py-3 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($surveys as $survey)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-6 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            @if($survey->company)
                                                <a href="{{ route('admin.surveys.index', ['company' => $survey->company->name]) }}" 
                                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2 py-0.5 rounded-md border border-indigo-200 transition" 
                                                   title="Filter by {{ $survey->company->name }}">
                                                    <i data-lucide="building-2" class="w-3 h-3 text-indigo-500"></i>
                                                    <span>{{ $survey->company->name }}</span>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200">
                                                    <i data-lucide="building-2" class="w-3 h-3 text-slate-300"></i>
                                                    <span>No Company</span>
                                                </span>
                                            @endif
                                        </div>
                                        <a href="{{ route('admin.surveys.show', $survey) }}" class="hover:text-indigo-600 transition font-bold text-sm block">
                                            {{ $survey->title }}
                                        </a>
                                        @if($survey->description)
                                            <p class="text-[11px] text-slate-400 font-normal truncate max-w-xs mt-0.5">{{ $survey->description }}</p>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold
                                            {{ match($survey->status) {
                                                'published' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                'closed' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                                default => 'bg-amber-50 text-amber-700 border border-amber-200'
                                            } }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ match($survey->status) {
                                                'published' => 'bg-emerald-500',
                                                'closed' => 'bg-slate-400',
                                                default => 'bg-amber-500'
                                            } }}"></span>
                                            <span class="capitalize">{{ $survey->status }}</span>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-slate-700 font-medium">
                                        <span class="font-bold text-slate-900">{{ $survey->questions_count }}</span> questions
                                    </td>
                                    <td class="py-4 px-6 text-slate-700 font-medium">
                                        <span class="font-bold text-slate-900">{{ $survey->participants_count }}</span> members
                                    </td>
                                    <td class="py-4 px-6 text-slate-700 font-medium">
                                        <span class="font-bold text-indigo-700">{{ $survey->assessments_count }}</span> pairings
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2 max-w-[120px]">
                                            <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full bg-indigo-600" 
                                                     style="width: {{ $survey->completionPercentage() }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-700">{{ $survey->completionPercentage() }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.surveys.show', $survey) }}" 
                                               class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Manage Survey">
                                                <i data-lucide="settings" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('admin.surveys.edit', $survey) }}" 
                                               class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit Survey">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.surveys.destroy', $survey) }}" 
                                                  data-confirm="true"
                                                  data-confirm-title="Delete Survey"
                                                  data-confirm-message="Are you sure you want to delete this survey ({{ e($survey->title) }})? All questions and generated assessments will be permanently removed."
                                                  data-confirm-btn="Delete Survey"
                                                  data-confirm-type="danger"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Delete Survey">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($surveys->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $surveys->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.app>
