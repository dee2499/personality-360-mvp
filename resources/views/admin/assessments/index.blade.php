<x-layouts.app>
    <div class="space-y-6">
        <!-- Header & Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Assessments Master Matrix</h1>
                <p class="text-xs text-slate-500 font-medium">All generated 360-degree assessment pairings across active and past surveys</p>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('admin.assessments.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border-slate-300 py-2 pl-3 pr-8 focus:border-indigo-600 focus:ring-indigo-600/20">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>

                <select name="survey_id" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border-slate-300 py-2 pl-3 pr-8 focus:border-indigo-600 focus:ring-indigo-600/20">
                    <option value="">All Surveys</option>
                    @foreach($surveys as $survey)
                        <option value="{{ $survey->id }}" {{ request('survey_id') == $survey->id ? 'selected' : '' }}>
                            {{ $survey->title }}
                        </option>
                    @endforeach
                </select>

                @if(request('status') || request('survey_id'))
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
                                    <td class="py-3.5 px-6 text-slate-600 truncate max-w-[160px]">
                                        {{ $assessment->survey->title }}
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
