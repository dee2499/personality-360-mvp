<x-layouts.app>
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <a href="{{ route('admin.surveys.show', $survey) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Survey</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Survey Details</h1>
            <p class="text-xs text-slate-500 font-medium">Update survey details and assessment questions</p>
        </div>

        <form method="POST" action="{{ route('admin.surveys.update', $survey) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-slate-900">1. Survey Details</h2>

                <div>
                    <label for="title" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Survey Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title', $survey->title) }}"
                           class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Description / Instructions
                    </label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">{{ old('description', $survey->description) }}</textarea>
                </div>

                <div>
                    <label for="context" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Context
                    </label>
                    <textarea name="context" id="context" rows="4"
                              placeholder="Provide context on this survey before respondents begin..."
                              class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 @error('context') border-rose-500 @enderror">{{ old('context', $survey->context) }}</textarea>
                    <p class="mt-1 text-[11px] text-slate-400">This context will be shown to participants on their assessment start page before they begin.</p>
                    @error('context')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Questions Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">2. Assessment Questions</h2>
                        <p class="text-xs text-slate-500">Edit existing questions or update their wording</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $survey->questions->count() }} Questions
                    </span>
                </div>

                <div class="space-y-3 pt-2">
                    @foreach($survey->questions as $index => $q)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-mono text-xs font-bold flex items-center justify-center shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $q->id }}">
                            <input type="text" 
                                   name="questions[{{ $index }}][question_text]"
                                   value="{{ old("questions.{$index}.question_text", $q->question_text) }}"
                                   required
                                   placeholder="Enter assessment question..."
                                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-xs sm:text-sm bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.surveys.show', $survey) }}" 
                   class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
