<x-layouts.app>
    <div class="max-w-3xl mx-auto space-y-6" x-data="{
        questions: {{ json_encode($defaultQuestions) }},
        addQuestion() {
            this.questions.push('');
        },
        removeQuestion(index) {
            if (this.questions.length > 1) {
                this.questions.splice(index, 1);
            }
        }
    }">
        <!-- Header -->
        <div>
            <a href="{{ route('admin.surveys.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Surveys</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Create Personality Survey</h1>
            <p class="text-xs text-slate-500 font-medium">Define survey details and formulate the 11 assessment questions</p>
        </div>

        <form method="POST" action="{{ route('admin.surveys.store') }}" class="space-y-6">
            @csrf

            <!-- Survey Basic Details Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-slate-900">1. Survey Details</h2>

                <div>
                    <label for="title" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Survey Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title', 'Personality Assessment ' . date('Y')) }}"
                           placeholder="e.g. Leadership & Personality 360 Feedback 2026"
                           class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 @error('title') border-rose-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Description / Instructions
                    </label>
                    <textarea name="description" id="description" rows="3"
                              placeholder="Provide guidance to respondents regarding this evaluation..."
                              class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">{{ old('description', 'Comprehensive 360-degree personality and self-assessment survey. Please rate yourself and each peer on a scale of 1 to 10.') }}</textarea>
                </div>
            </div>

            <!-- Questions Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">2. Assessment Questions</h2>
                        <p class="text-xs text-slate-500">Each question will be evaluated on a 1–10 rating scale (Total score max: Questions × 10)</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <span x-text="questions.length"></span> Questions
                    </span>
                </div>

                <div class="space-y-3 pt-2">
                    <template x-for="(question, index) in questions" :key="index">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-mono text-xs font-bold flex items-center justify-center shrink-0"
                                  x-text="index + 1"></span>
                            
                            <input type="text" 
                                   :name="'questions[' + index + ']'"
                                   x-model="questions[index]"
                                   required
                                   placeholder="Enter assessment question..."
                                   class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-xs sm:text-sm bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20">

                            <button type="button" @click="removeQuestion(index)" 
                                    class="p-2 text-slate-400 hover:text-rose-600 rounded-lg transition"
                                    title="Remove question">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="pt-2">
                    <button type="button" @click="addQuestion()" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Question</span>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.surveys.index') }}" 
                   class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                    Save & Proceed to Participants
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
