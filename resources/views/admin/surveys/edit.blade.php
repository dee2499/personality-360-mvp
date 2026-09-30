<x-layouts.app>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <a href="{{ route('admin.surveys.show', $survey) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Survey</span>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Survey Details</h1>
        </div>

        <form method="POST" action="{{ route('admin.surveys.update', $survey) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
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
