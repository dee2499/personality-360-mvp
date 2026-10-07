<x-layouts.app>
    <div class="space-y-8" x-data="{
        showInviteModal: {{ $errors->any() ? 'true' : 'false' }},
        copied: false,
        copyLink(link) {
            navigator.clipboard.writeText(link);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
    }">
        <!-- Top Back Nav & Company Header -->
        <div>
            <a href="{{ route('admin.companies.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Companies</span>
            </a>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-indigo-100 shrink-0">
                        {{ substr($company->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $company->name }}</h1>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Organization Profile
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                            @if($company->contact_email)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $company->contact_email }}
                                </span>
                                <span>•</span>
                            @endif
                            <span class="font-medium text-slate-700">{{ $company->employees->count() }} Employees</span>
                            <span>•</span>
                            <span class="font-medium text-slate-700">{{ $company->surveys->count() }} Surveys</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <!-- Overall Company Evaluation Progress Pill -->
                    <div class="flex flex-col sm:items-end gap-1.5 bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-xl">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Evaluation Benchmark</span>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-extrabold text-slate-900">
                                {{ number_format($metrics['average_percentage'], 1) }}%
                                <span class="text-slate-400 text-sm font-normal">({{ $metrics['completed_assessments'] }}/{{ $metrics['total_assessments'] }})</span>
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-md {{ $metrics['completion_rate'] >= 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ $metrics['completion_rate'] }}% Done
                            </span>
                        </div>
                        <div class="w-48 bg-slate-200 rounded-full h-2 overflow-hidden mt-1">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $metrics['completion_rate'] }}%"></div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="border-t sm:border-t-0 sm:border-l border-slate-200 pt-3 sm:pt-0 sm:pl-4 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.companies.edit', $company) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            <span>Edit Company</span>
                        </a>
                        <button type="button" @click="showInviteModal = true"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs shadow-indigo-100 transition cursor-pointer">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Add Employee</span>
                        </button>
                        <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" 
                              data-confirm="true"
                              data-confirm-title="Delete Company"
                              data-confirm-message="Are you sure you want to delete '{{ $company->name }}'? All associated employees, surveys, and assessments will be permanently removed."
                              data-confirm-btn="Delete Company"
                              data-confirm-type="danger"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                                    title="Delete Company">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invitation Link Flash Alert -->
        @if(session('invitation_link'))
            <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-emerald-900 flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        Invitation link generated for {{ session('invited_employee') }}!
                    </span>
                    <button type="button" @click="copyLink('{{ session('invitation_link') }}')"
                            class="font-bold text-emerald-800 bg-white border border-emerald-300 px-3 py-1 rounded-lg hover:bg-emerald-100 transition flex items-center gap-1">
                        <i data-lucide="copy" class="w-3 h-3"></i>
                        <span x-text="copied ? 'Copied!' : 'Copy Activation Link'">Copy Activation Link</span>
                    </button>
                </div>
                <p class="text-emerald-700">
                    An email was dispatched. For immediate local testing, you can open this direct activation link:
                </p>
                <div class="p-2.5 bg-white/80 rounded-xl border border-emerald-200 font-mono text-[11px] text-emerald-900 break-all select-all">
                    {{ session('invitation_link') }}
                </div>
            </div>
        @endif

        @if($selectedSurvey)
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between text-xs text-indigo-900">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-indigo-600"></i>
                    <span>Viewing 360 benchmark score & evaluations for survey: <strong>{{ $selectedSurvey->title }}</strong></span>
                </div>
                <a href="{{ route('admin.companies.show', $company) }}" class="font-bold text-indigo-600 hover:text-indigo-800 underline">
                    Reset to All Surveys Combined
                </a>
            </div>
        @endif

        <!-- Team CQ Sync & Cohort Intelligence Hub -->
        @if($targetSurvey && $groupInsights)
            @php
                $hubSync = $groupInsights['cq_sync'] ?? [];
                $hubSyncScore = (float) ($hubSync['score'] ?? $groupInsights['team_cq_sync_score'] ?? 0.0);
                $hubMaturity = $hubSync['maturity_level'] ?? 'Aligned';
                $hubBadge = $hubSync['badge'] ?? 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                $hubDims = $hubSync['dimensions'] ?? [];
                $hubSee = (float) ($hubDims['see_together']['score'] ?? 0.0);
                $hubAgree = (float) ($hubDims['agree_together']['score'] ?? 0.0);
                $hubAct = (float) ($hubDims['act_together']['score'] ?? 0.0);
                $hubGap = (float) ($hubSync['gap_to_benchmark'] ?? ($hubSyncScore - 8.0));
                
                // Needle angle for mini gauge (piecewise mapping)
                if ($hubSyncScore <= 0) {
                    $hubNeedleAngle = -90;
                } elseif ($hubSyncScore <= 2.0) {
                    $hubNeedleAngle = -90 + (max(0, $hubSyncScore - 1.0) / 1.0) * 36;
                } elseif ($hubSyncScore <= 4.0) {
                    $hubNeedleAngle = -54 + (($hubSyncScore - 2.0) / 2.0) * 36;
                } elseif ($hubSyncScore <= 6.0) {
                    $hubNeedleAngle = -18 + (($hubSyncScore - 4.0) / 2.0) * 36;
                } elseif ($hubSyncScore <= 8.0) {
                    $hubNeedleAngle = 18 + (($hubSyncScore - 6.0) / 2.0) * 36;
                } else {
                    $hubNeedleAngle = 54 + (min(2.0, $hubSyncScore - 8.0) / 2.0) * 36;
                }
                $hubNeedleAngle = round(max(-90, min(90, $hubNeedleAngle)), 2);
            @endphp

            <div class="bg-gradient-to-br from-white via-purple-50/20 to-indigo-50/30 rounded-3xl border border-purple-200/80 shadow-sm p-6 sm:p-8">
                <!-- Section Header & Buttons Ribbon -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-xl bg-purple-100 text-purple-700 border border-purple-200">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                            </span>
                            <span class="text-xs font-black uppercase tracking-wider text-purple-700">Team Intelligence</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-purple-100 text-purple-800">
                                {{ $targetSurvey->title }}
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight mt-1.5">
                            Team CQ Sync & Cohort Intelligence
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Alignment to see, agree, and act on change together across {{ $groupInsights['cohort_size'] }} assessed team members.
                        </p>
                    </div>

                    <!-- All Team Action Buttons Requested by User -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Button 1: Team Report -->
                        <a href="{{ route('admin.companies.team-sync', ['company' => $company, 'survey_id' => $targetSurvey->id]) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-md shadow-purple-200 transition cursor-pointer">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Team Report</span>
                        </a>

                        <!-- Button 2: Team CQ Report -->
                        <a href="{{ route('admin.surveys.group-insights', ['survey' => $targetSurvey, 'tab' => 'summary']) }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-2xs transition">
                            <i data-lucide="compass" class="w-3.5 h-3.5 text-indigo-600"></i>
                            <span>Team CQ Report</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Company 360° Benchmark Bar & Survey Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                        <i data-lucide="gauge" class="w-4 h-4"></i>
                    </span>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Company 360° Benchmark</h2>
                </div>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">
                    {{ $surveys->count() }} Surveys
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $metrics['category_badge'] }}">
                    <span>{{ $metrics['category_emoji'] }}</span>
                    <span>{{ $metrics['category'] }}</span>
                </span>
            </div>

            <!-- Modern UI Survey Selection Box -->
            @if($surveys->isNotEmpty())
                <div class="relative w-full sm:w-80 shrink-0"
                     x-data="{
                         open: false,
                         searchQuery: '',
                         selectedSurveyTitle: '{{ $selectedSurvey ? $selectedSurvey->title : 'All Surveys (Company-wide)' }}',
                         surveyTitles: @js(array_merge(['All Surveys (Company-wide)'], $surveys->pluck('title')->values()->all())),
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
                            <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $selectedSurvey ? 'bg-indigo-600' : 'bg-emerald-500' }}"></span>
                            <div class="truncate">
                                <span class="text-xs font-bold text-slate-900 block truncate" x-text="selectedSurveyTitle"></span>
                                <span class="text-[10px] text-slate-400 font-medium">
                                    {{ $selectedSurvey ? 'Individual Survey Benchmark' : 'All ' . $surveys->count() . ' Surveys Combined' }}
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
                            <a href="{{ route('admin.companies.show', $company) }}"
                               x-show="matches('All Surveys (Company-wide)')"
                               class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-indigo-50/50 transition cursor-pointer {{ $selectedSurvey === null ? 'bg-indigo-50/70' : '' }}">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 {{ $selectedSurvey === null ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </div>
                                    <div class="truncate">
                                        <span class="text-xs font-bold text-slate-900 block truncate {{ $selectedSurvey === null ? 'text-indigo-950 font-black' : '' }}">
                                            All Surveys (Company-wide)
                                        </span>
                                        <span class="text-[10px] text-slate-400">
                                            Company-wide synthesis across all surveys
                                        </span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 shrink-0">
                                    All
                                </span>
                            </a>

                            <!-- Individual Surveys -->
                            @foreach($surveys as $sItem)
                                @php
                                    $isSelected = $selectedSurvey && $selectedSurvey->id === $sItem->id;
                                @endphp
                                <a href="{{ route('admin.companies.show', ['company' => $company, 'survey_id' => $sItem->id]) }}"
                                   x-show="matches('{{ addslashes($sItem->title) }}')"
                                   class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-indigo-50/50 transition cursor-pointer {{ $isSelected ? 'bg-indigo-50/70' : '' }}">
                                    <div class="flex items-center gap-2.5 truncate">
                                        <div class="w-5 h-5 rounded-lg flex items-center justify-center shrink-0 {{ $isSelected ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-transparent' }}">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                        </div>
                                        <div class="truncate">
                                            <span class="text-xs font-bold text-slate-800 block truncate {{ $isSelected ? 'text-indigo-950 font-black' : '' }}">
                                                {{ $sItem->title }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ $sItem->assessments_count }} evaluations • {{ $sItem->completionPercentage() }}% complete
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full capitalize shrink-0 {{ $sItem->isPublished() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $sItem->status }}
                                    </span>
                                </a>
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

                    <!-- Native select for accessibility & automation -->
                    <select class="sr-only" onchange="if (this.value) window.location.href = this.value">
                        <option value="{{ route('admin.companies.show', $company) }}" {{ $selectedSurvey === null ? 'selected' : '' }}>
                            All Surveys (Company-wide)
                        </option>
                        @foreach($surveys as $sItem)
                            @php
                                $isSelected = $selectedSurvey && $selectedSurvey->id === $sItem->id;
                            @endphp
                            <option value="{{ route('admin.companies.show', ['company' => $company, 'survey_id' => $sItem->id]) }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $sItem->title }} ({{ $sItem->completionPercentage() }}% complete)
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- Centerpiece: 3-Column Benchmark Result -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            
            <!-- Col 1: 360° Visual Gauge Meter -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between items-center text-center">
                <div class="w-full text-left">
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-100">
                        Overall 360° Evaluation Score
                    </span>
                    <h3 class="text-base font-black text-slate-900 mt-1.5">Company Visual Gauge</h3>
                    <p class="text-xs text-slate-400">Weighted average across all evaluations</p>
                </div>

                <div class="w-full my-auto py-2">
                    <x-score-meter 
                        :percentage="$metrics['average_percentage']" 
                        :category="$metrics['category']" 
                        :only-gauge="true"
                    />
                </div>

                <div class="w-full pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-medium">Evaluations:</span>
                    <span class="font-bold text-slate-800">
                        {{ $metrics['completed_assessments'] }} / {{ $metrics['total_assessments'] }} Completed
                    </span>
                </div>
            </div>

            <!-- Col 2: Company Performance Breakdown -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Benchmark Tier</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border {{ $metrics['category_badge'] }}">
                            <span>{{ $metrics['category_emoji'] }}</span>
                            <span>{{ $metrics['category'] }}</span>
                        </span>
                    </div>

                    <div class="py-4 space-y-4">
                        <div>
                            <span class="text-xs text-slate-400 font-medium block">Average Company Percentage</span>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                <span class="text-3xl sm:text-4xl font-black text-slate-900">{{ number_format($metrics['average_percentage'], 2) }}%</span>
                                <span class="text-xs font-bold text-slate-400">overall score</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs font-bold text-slate-600">
                                <span>Completion Progress</span>
                                <span class="text-indigo-600">{{ $metrics['completion_rate'] }}%</span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 rounded-full transition-all duration-500"
                                     style="width: {{ $metrics['completion_rate'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] text-slate-400">
                                <span>{{ $metrics['completed_assessments'] }} Completed</span>
                                <span>{{ $metrics['pending_assessments'] }} Pending</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Total Accumulated Points:</span>
                    <span class="font-extrabold text-slate-900">
                        {{ number_format($metrics['total_score']) }} <span class="text-slate-400 font-normal">/ {{ number_format($metrics['max_score']) }} pts</span>
                    </span>
                </div>
            </div>

            <!-- Col 3: Self vs Peer Perception Benchmark -->
            <div class="bg-gradient-to-b from-indigo-50/30 to-white rounded-3xl border border-indigo-100 p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-indigo-100/60">
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-100/80 px-2 py-0.5 rounded-md">
                            360° Perception Analysis
                        </span>
                        @if($metrics['completed_assessments'] > 0)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $metrics['perception_gap'] >= 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-teal-50 text-teal-800 border border-teal-200' }}">
                                Gap: {{ $metrics['perception_gap'] > 0 ? '+' : '' }}{{ number_format($metrics['perception_gap'], 2) }}%
                            </span>
                        @endif
                    </div>

                    <div class="py-4 space-y-4">
                        <!-- Self Rating -->
                        <div class="p-3 rounded-2xl bg-white border border-indigo-100 flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Self-Assessed Average</span>
                                    <span class="text-[10px] text-slate-400">{{ $metrics['self_completed_count'] }} employee self-evaluations</span>
                                </div>
                            </div>
                            <span class="text-lg font-black text-indigo-700">{{ number_format($metrics['self_average_percentage'], 2) }}%</span>
                        </div>

                        <!-- Peer Rating -->
                        <div class="p-3 rounded-2xl bg-white border border-teal-100 flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 font-bold flex items-center justify-center text-xs border border-teal-100">
                                    <i data-lucide="users" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Peer-Assessed Average</span>
                                    <span class="text-[10px] text-slate-400">{{ $metrics['peer_completed_count'] }} peer reviews from colleagues</span>
                                </div>
                            </div>
                            <span class="text-lg font-black text-teal-700">{{ number_format($metrics['peer_average_percentage'], 2) }}%</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-indigo-100/60 text-[11px] text-slate-500">
                    @if($metrics['completed_assessments'] === 0)
                        <span>Complete cohort assessments to view company-wide perception analysis.</span>
                    @elseif($metrics['perception_gap'] > 5)
                        <span class="text-amber-800 font-medium">⚠️ Company-wide self-ratings are {{ number_format($metrics['perception_gap'], 1) }}% higher than colleague reviews.</span>
                    @elseif($metrics['perception_gap'] < -5)
                        <span class="text-teal-800 font-medium">✨ Employees are modest: peer reviews rate the team {{ number_format(abs($metrics['perception_gap']), 1) }}% higher than self-ratings.</span>
                    @else
                        <span class="text-emerald-800 font-medium">🎯 High self-awareness: self-ratings and peer feedback are strongly aligned (within {{ number_format(abs($metrics['perception_gap']), 1) }}%).</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- Employees of Company (Full Width) -->
        <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Employees of {{ $company->name }}</h2>
                        <p class="text-xs text-slate-500">All registered and invited team members eligible for survey cohorts</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                        {{ $company->employees->count() }} Total
                    </span>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
                    @if($company->employees->isEmpty())
                        <div class="py-12 px-6 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="user-plus" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">No employees added yet</h3>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                                Click "Add / Invite Employee" above to send account setup links to this company's team members.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                        <th class="py-3 px-6">Employee</th>
                                        <th class="py-3 px-6">Email</th>
                                        <th class="py-3 px-6">Role</th>
                                        <th class="py-3 px-6">Account Status</th>
                                        <th class="py-3 px-6 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($company->employees as $employee)
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100">
                                                        {{ substr($employee->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('admin.people.show', $employee) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition">
                                                            {{ $employee->name }}
                                                        </a>
                                                        <div class="text-[10px] text-slate-400 font-medium">Joined {{ $employee->created_at->format('M d, Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-slate-600">
                                                {{ $employee->email }}
                                            </td>
                                            <td class="py-4 px-6">
                                                @if($employee->isManager())
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                                                        <i data-lucide="shield-check" class="w-3 h-3 text-purple-600"></i>
                                                        Company Manager
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                                        <i data-lucide="user" class="w-3 h-3 text-slate-500"></i>
                                                        Participant
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6">
                                                @if($employee->isInvited())
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                                        <i data-lucide="clock" class="w-3 h-3 text-amber-500"></i>
                                                        Invitation Pending
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                                                        Active
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if(auth()->user()->isAdmin() && $employee->id !== auth()->id())
                                                        <form method="POST" action="{{ route('admin.companies.employees.role', [$company, $employee]) }}" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="role" value="{{ $employee->isManager() ? 'participant' : 'manager' }}">
                                                            <button type="submit" 
                                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl font-semibold text-xs {{ $employee->isManager() ? 'text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200' : 'text-purple-700 hover:text-purple-800 bg-purple-50 hover:bg-purple-100 border border-purple-200' }} transition cursor-pointer"
                                                                    title="{{ $employee->isManager() ? 'Revoke manager rights (make participant)' : 'Promote to Company Manager' }}">
                                                                <i data-lucide="{{ $employee->isManager() ? 'user-minus' : 'shield' }}" class="w-3.5 h-3.5"></i>
                                                                <span>{{ $employee->isManager() ? 'Make Participant' : 'Make Manager' }}</span>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($employee->isInvited())
                                                        <button type="button" 
                                                                @click="copyLink('{{ route('invitation.show', ['token' => $employee->invitation_token]) }}')"
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                                            <span>Link</span>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('admin.people.show', $employee) }}" 
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-semibold text-xs text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                                        <span>360 Profile</span>
                                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                    </a>

                                                    @if($employee->id !== auth()->id())
                                                        <form method="POST" action="{{ route('admin.companies.employees.destroy', [$company, $employee]) }}" 
                                                              data-confirm="true"
                                                              data-confirm-title="Delete Employee"
                                                              data-confirm-message="Are you sure you want to delete employee {{ $employee->name }}? This will permanently remove their assessments and participation."
                                                              data-confirm-btn="Delete Employee"
                                                              data-confirm-type="danger"
                                                              class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl font-semibold text-xs text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                                                                    title="Delete Employee">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                                <span>Delete</span>
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

        <!-- Add / Invite Employee Modal -->
        <div x-show="showInviteModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-5 border border-slate-100"
                 @click.away="showInviteModal = false">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                            {{ $company->name }}
                        </span>
                        <h3 class="text-lg font-black text-slate-900 mt-1">Invite Employee</h3>
                    </div>
                    <button type="button" @click="showInviteModal = false" class="text-slate-400 hover:text-slate-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.companies.invite', $company) }}" class="space-y-4">
                    @csrf

                    @if($errors->any())
                        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
                            <span class="font-bold flex items-center gap-1.5 text-rose-900">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                                Could not invite employee:
                            </span>
                            <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-700 pl-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label for="invite_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Employee Full Name <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input id="invite_name" 
                                   name="name" 
                                   type="text" 
                                   required 
                                   value="{{ old('name') }}"
                                   placeholder="e.g. John Doe"
                                   class="block w-full rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' }} px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                        </div>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="invite_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Work Email Address <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input id="invite_email" 
                                   name="email" 
                                   type="email" 
                                   required 
                                   value="{{ old('email') }}"
                                   placeholder="e.g. jdoe@company.com"
                                   class="block w-full rounded-xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' }} px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div>
                            <label for="invite_role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                Role / Permission Level <span class="text-rose-500">*</span>
                            </label>
                            <div class="mt-1">
                                <select id="invite_role" 
                                        name="role" 
                                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 bg-white focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                                    <option value="participant" {{ old('role') === 'participant' ? 'selected' : '' }}>Participant (Survey & Assessment Access)</option>
                                    <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Company Manager (Full Company Console & Survey Management)</option>
                                </select>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400">Managers have access to view and manage all data, surveys, and reports for this company.</p>
                        </div>
                    @else
                        <input type="hidden" name="role" value="participant">
                    @endif

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="showInviteModal = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Send Invitation</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
