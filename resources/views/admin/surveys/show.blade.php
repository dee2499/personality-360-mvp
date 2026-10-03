<x-layouts.app>
    <div class="space-y-8" x-data="{ tab: 'participants' }">
        <!-- Back Navigation & Top Bar -->
        <div>
            <a href="{{ route('admin.surveys.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Surveys</span>
            </a>

            <!-- Survey Overview Card -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2">
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
                        @if($survey->published_at)
                            <span class="text-xs text-slate-400">• Published {{ $survey->published_at->format('M d, Y') }}</span>
                        @endif

                        @if($survey->sign_off_status && $survey->sign_off_status !== 'pending')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold
                                {{ $survey->sign_off_status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                <i data-lucide="check-square" class="w-3 h-3"></i>
                                <span>Sign-Off: {{ ucwords(str_replace('_', ' ', $survey->sign_off_status)) }}</span>
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-2">{{ $survey->title }}</h1>
                    @if($survey->description)
                        <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">{{ $survey->description }}</p>
                    @endif
                </div>

                <!-- Action Controls (Publish / Unpublish / Edit / Insights) -->
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <a href="{{ route('admin.surveys.group-insights', $survey) }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition cursor-pointer">
                        <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                        <span>Group Insights & Sign-Off</span>
                    </a>

                    <a href="{{ route('admin.surveys.edit', $survey) }}" 
                       class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition">
                        Edit Details
                    </a>

                    @if($survey->isDraft())
                        <form method="POST" action="{{ route('admin.surveys.publish', $survey) }}" 
                              data-confirm="true"
                              data-confirm-title="Publish Survey & Generate Assessments"
                              data-confirm-message="Publishing this survey will activate it and generate all 360-degree assessment pairings for all participants. Proceed?"
                              data-confirm-btn="Publish & Generate"
                              data-confirm-type="primary"
                              class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-100 transition cursor-pointer">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                <span>Publish & Generate Assessments</span>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.surveys.unpublish', $survey) }}" 
                              data-confirm="true"
                              data-confirm-title="Set Survey to Draft"
                              data-confirm-message="Setting this survey to draft will pause participant evaluations. Proceed?"
                              data-confirm-btn="Set to Draft"
                              data-confirm-type="warning"
                              class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition cursor-pointer">
                                <i data-lucide="pause-circle" class="w-3.5 h-3.5"></i>
                                <span>Set to Draft</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Questions</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $survey->questions->count() }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Rating scale 1–10 (Max: {{ $survey->questions->count() * 10 }})</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Participants</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $survey->participants->count() }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Assigned cohort members</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Assessments Matrix</span>
                <div class="text-2xl font-black text-indigo-700 mt-1">{{ $totalAssessmentsCount }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $survey->participants->count() }} × {{ $survey->participants->count() }} N×N pairings</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Completed</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $completedAssessmentsCount }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ $survey->completionPercentage() }}% completion rate</div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-slate-200 flex items-center gap-6">
            <button type="button" @click="tab = 'participants'"
                    :class="tab === 'participants' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Participants ({{ $survey->participants->count() }})</span>
            </button>
            <button type="button" @click="tab = 'questions'"
                    :class="tab === 'questions' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span>Questions ({{ $survey->questions->count() }})</span>
            </button>
            <button type="button" @click="tab = 'matrix'"
                    :class="tab === 'matrix' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="grid" class="w-4 h-4"></i>
                <span>Assessments Matrix ({{ $totalAssessmentsCount }})</span>
            </button>
        </div>

        <!-- Tab 1: Participants -->
        <div x-show="tab === 'participants'" class="space-y-6">
            <!-- Add Participants Panel -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Enroll Participants</h3>
                <p class="text-xs text-slate-500">
                    Assign existing team members or register a new participant. When the survey is published, 
                    every participant will evaluate themselves and every other member (N × N assessments).
                </p>

                <form method="POST" action="{{ route('admin.surveys.participants.store', $survey) }}" class="space-y-4">
                    @csrf

                    <!-- Select from existing participants -->
                    @php
                        $assignedIds = $survey->participants->pluck('id')->toArray();
                        $unassignedParticipants = $allParticipants->whereNotIn('id', $assignedIds);
                    @endphp

                    @if($unassignedParticipants->isNotEmpty())
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Existing Members:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                @foreach($unassignedParticipants as $unassigned)
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition">
                                        <input type="checkbox" name="user_ids[]" value="{{ $unassigned->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        <div class="truncate">
                                            <span class="block text-xs font-bold text-slate-800">{{ $unassigned->name }}</span>
                                            <span class="block text-[10px] text-slate-400 truncate">{{ $unassigned->email }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Or create a new participant -->
                    <div class="pt-3 border-t border-slate-100">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Or Register & Add New Member:</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" name="new_name" placeholder="Full Name (e.g. Alice)" 
                                   class="rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">
                            <input type="email" name="new_email" placeholder="Email Address (e.g. alice@example.com)" 
                                   class="rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition cursor-pointer">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Save & Update Cohort</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Enrolled Participants Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Enrolled Participants ({{ $survey->participants->count() }})</h3>
                </div>

                @if($survey->participants->isEmpty())
                    <div class="p-8 text-center text-xs text-slate-500">
                        No participants assigned yet. Use the form above to add members.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                    <th class="py-3 px-6">Name</th>
                                    <th class="py-3 px-6">Email</th>
                                    <th class="py-3 px-6">Assessments Given</th>
                                    <th class="py-3 px-6">Assessments Received</th>
                                    <th class="py-3 px-6 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($survey->participants as $participant)
                                    @php
                                        $givenCount = $survey->assessments->where('assessor_id', $participant->id)->where('status', 'completed')->count();
                                        $receivedCount = $survey->assessments->where('subject_id', $participant->id)->where('status', 'completed')->count();
                                        $expectedCount = $survey->participants->count();
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3.5 px-6 font-bold text-slate-900">
                                            <a href="{{ route('admin.people.show', $participant) }}" class="hover:text-indigo-600">
                                                {{ $participant->name }}
                                            </a>
                                        </td>
                                        <td class="py-3.5 px-6 text-slate-600">{{ $participant->email }}</td>
                                        <td class="py-3.5 px-6 font-medium text-slate-800">
                                            <span class="text-indigo-600 font-bold">{{ $givenCount }}</span> / {{ $expectedCount }} completed
                                        </td>
                                        <td class="py-3.5 px-6 font-medium text-slate-800">
                                            <span class="text-emerald-600 font-bold">{{ $receivedCount }}</span> / {{ $expectedCount }} completed
                                        </td>
                                        <td class="py-3.5 px-6 text-right">
                                            <form method="POST" action="{{ route('admin.surveys.participants.destroy', [$survey, $participant]) }}"
                                                  data-confirm="true"
                                                  data-confirm-title="Remove Participant"
                                                  data-confirm-message="Are you sure you want to remove participant {{ $participant->name }} from this survey? Their evaluation pairings will be permanently deleted."
                                                  data-confirm-btn="Remove Participant"
                                                  data-confirm-type="danger"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-medium cursor-pointer">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tab 2: Questions -->
        <div x-show="tab === 'questions'" class="space-y-6">
            <!-- Add Question Form -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900">Add New Question</h3>
                <form method="POST" action="{{ route('admin.surveys.questions.store', $survey) }}" class="flex gap-3">
                    @csrf
                    <input type="text" name="question_text" required placeholder="e.g. How effectively does this person communicate under pressure?"
                           class="flex-1 rounded-xl border border-slate-300 px-3.5 py-2 text-xs sm:text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shrink-0 cursor-pointer">
                        Add Question
                    </button>
                </form>
            </div>

            <!-- Questions List -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs divide-y divide-slate-100">
                @foreach($survey->questions as $index => $question)
                    <div class="p-4 sm:px-6 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-mono text-xs font-bold flex items-center justify-center shrink-0">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-sm font-medium text-slate-800">{{ $question->question_text }}</span>
                        </div>

                        <form method="POST" action="{{ route('admin.surveys.questions.destroy', [$survey, $question]) }}"
                              data-confirm="true"
                              data-confirm-title="Delete Question"
                              data-confirm-message="Are you sure you want to delete this question? Any submitted ratings for this question will be removed."
                              data-confirm-btn="Delete Question"
                              data-confirm-type="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition cursor-pointer" title="Delete question">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Tab 3: N x N Assessments Matrix -->
        <div x-show="tab === 'matrix'" class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">N × N Assessment Matrix</h3>
                        <p class="text-xs text-slate-500">Every assessor evaluates themselves and all peers</p>
                    </div>
                </div>

                @if($survey->assessments->isEmpty())
                    <div class="p-8 text-center text-xs text-slate-500">
                        Assessments have not been generated yet. Ensure you have participants and click <strong>Publish & Generate Assessments</strong>.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                    <th class="py-3 px-6">Assessor</th>
                                    <th class="py-3 px-6">Subject</th>
                                    <th class="py-3 px-6">Type</th>
                                    <th class="py-3 px-6">Status</th>
                                    <th class="py-3 px-6">Score</th>
                                    <th class="py-3 px-6">Category</th>
                                    <th class="py-3 px-6 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($survey->assessments as $assessment)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3.5 px-6 font-bold text-slate-900">{{ $assessment->assessor->name }}</td>
                                        <td class="py-3.5 px-6 font-bold text-indigo-700">{{ $assessment->subject->name }}</td>
                                        <td class="py-3.5 px-6">
                                            @if($assessment->isSelfAssessment())
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">Self</span>
                                            @else
                                                <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600">Peer</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-6">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                                {{ match($assessment->status) {
                                                    'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                    'in_progress' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                    default => 'bg-slate-100 text-slate-600'
                                                } }}">
                                                <span class="capitalize">{{ str_replace('_', ' ', $assessment->status) }}</span>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-6 font-semibold text-slate-800">
                                            {{ $assessment->total_score ? $assessment->total_score . ' / ' . $assessment->max_score : '—' }}
                                        </td>
                                        <td class="py-3.5 px-6">
                                            {{ $assessment->category ?? '—' }}
                                        </td>
                                        <td class="py-3.5 px-6 text-right">
                                            <a href="{{ route('admin.assessments.show', $assessment) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                                View
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
    </div>
</x-layouts.app>
