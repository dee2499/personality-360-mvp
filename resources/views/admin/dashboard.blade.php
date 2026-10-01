<x-layouts.app>
    <div class="space-y-6">
        <!-- Page Title & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Executive Dashboard</h1>
                <p class="text-xs text-slate-500 font-medium">Overview of surveys, participant evaluations, and 360-degree completion status</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.surveys.create') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Create Survey</span>
                </a>
                <a href="{{ route('admin.people.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>View People Profiles</span>
                </a>
            </div>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Surveys -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Surveys</span>
                    <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900">{{ $totalSurveys }}</div>
                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $activeSurveys }} active published</div>
                </div>
            </div>

            <!-- Total Participants -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Participants</span>
                    <div class="p-2 rounded-xl bg-sky-50 text-sky-600">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900">{{ $totalParticipants }}</div>
                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">Enrolled team members</div>
                </div>
            </div>

            <!-- Total Assessments Matrix -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Matrix (N × N)</span>
                    <div class="p-2 rounded-xl bg-purple-50 text-purple-600">
                        <i data-lucide="grid" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900">{{ $totalAssessments }}</div>
                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">Generated assignments</div>
                </div>
            </div>

            <!-- Completed Assessments -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Completed</span>
                    <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-emerald-600">{{ $completedAssessments }}</div>
                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $pendingAssessments }} pending submission</div>
                </div>
            </div>

            <!-- Completion Rate -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Completion</span>
                    <div class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <i data-lucide="percent" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900">{{ $overallCompletionRate }}%</div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                        <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $overallCompletionRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Assessments Section -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Recent Assessments</h2>
                    <p class="text-xs text-slate-400 font-medium">Evaluation assignments across all participant pairings</p>
                </div>
                <a href="{{ route('admin.assessments.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    <span>View all</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if($recentAssessments->isEmpty())
                <div class="py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="inbox" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No assessments created yet</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                        Create a survey and add participants to automatically generate the 360-degree evaluation matrix.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.surveys.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Create Survey</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                <th class="py-3 px-6">Assessor → Subject</th>
                                <th class="py-3 px-6">Survey</th>
                                <th class="py-3 px-6">Score</th>
                                <th class="py-3 px-6">Percentage</th>
                                <th class="py-3 px-6">Category</th>
                                <th class="py-3 px-6">Status</th>
                                <th class="py-3 px-6">Completed Date</th>
                                <th class="py-3 px-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentAssessments as $assessment)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 px-6 font-medium text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800">{{ $assessment->assessor->name }}</span>
                                            <span class="text-slate-300">→</span>
                                            <span class="font-bold text-indigo-700">{{ $assessment->subject->name }}</span>
                                            @if($assessment->isSelfAssessment())
                                                <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-semibold border border-indigo-100">Self</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6 text-slate-600 max-w-[160px] truncate">
                                        {{ $assessment->survey?->title ?? 'General Survey' }}
                                    </td>
                                    <td class="py-3.5 px-6 font-bold text-slate-800">
                                        @if($assessment->isCompleted())
                                            {{ $assessment->total_score }} / {{ $assessment->max_score }}
                                        @else
                                            <span class="text-slate-400 font-normal">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($assessment->isCompleted())
                                            <span class="font-extrabold text-slate-900">{{ number_format($assessment->percentage, 2) }}%</span>
                                        @else
                                            <span class="text-slate-400 font-normal">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($assessment->isCompleted() && $assessment->category)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold
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
                                    <td class="py-3.5 px-6 text-slate-500 text-[11px]">
                                        {{ $assessment->completed_at ? $assessment->completed_at->format('M d, Y H:i') : '—' }}
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        <a href="{{ route('admin.assessments.show', $assessment) }}" 
                                           class="inline-flex items-center gap-1 font-semibold text-indigo-600 hover:text-indigo-800">
                                            <span>Details</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </a>
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
