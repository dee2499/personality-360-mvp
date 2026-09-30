<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">People & 360 Feedback Profiles</h1>
                <p class="text-xs text-slate-500 font-medium">Select any participant to examine their 360-degree score meter, combined rating, and individual peer assessments</p>
            </div>
            
            <!-- Survey Filter -->
            <form method="GET" action="{{ route('admin.people.index') }}" class="flex items-center gap-2">
                <label for="survey_id" class="text-xs font-semibold text-slate-600">Filter by Survey:</label>
                <select name="survey_id" id="survey_id" onchange="this.form.submit()" 
                        class="text-xs rounded-xl border-slate-300 py-2 pl-3 pr-8 focus:border-indigo-600 focus:ring-indigo-600/20">
                    <option value="">All Surveys</option>
                    @foreach($surveys as $survey)
                        <option value="{{ $survey->id }}" {{ request('survey_id') == $survey->id ? 'selected' : '' }}>
                            {{ $survey->title }}
                        </option>
                    @endforeach
                </select>
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
                                        <a href="{{ route('admin.people.show', $person) }}" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                            <span>View Profile</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
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
