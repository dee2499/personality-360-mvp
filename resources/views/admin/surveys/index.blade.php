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

        <!-- Surveys Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($surveys->isEmpty())
                <div class="py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
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
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                <th class="py-3 px-6">Survey Title</th>
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
                                        <a href="{{ route('admin.surveys.show', $survey) }}" class="hover:text-indigo-600 transition font-bold text-sm">
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
