<x-layouts.app>
    <div class="max-w-3xl mx-auto space-y-6" 
         x-data="{
             name: '{{ old('name', $category->name) }}',
             min_percentage: '{{ old('min_percentage', $category->min_percentage) }}',
             max_percentage: '{{ old('max_percentage', $category->max_percentage) }}',
             emoji: '{{ old('emoji', $category->emoji) }}',
             color: '{{ old('color', $category->color) }}',
             description: '{{ old('description', $category->description) }}',
             setEmoji(em) {
                 this.emoji = em;
             },
             setColor(c) {
                 this.color = c;
             }
         }">
        
        <!-- Navigation -->
        <div>
            <a href="{{ route('admin.categories.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Categories</span>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Score Category</h1>
                    <p class="text-xs text-slate-500 mt-1">Update percentage bracket and metadata for "{{ $category->name }}"</p>
                </div>

                <span class="text-3xl p-2 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center w-12 h-12" x-text="emoji"></span>
            </div>

            <!-- Live Preview Card -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Live Preview</div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center w-12 h-12" x-text="emoji || '🎯'"></span>
                        <div>
                            <div class="text-base font-extrabold text-slate-900" x-text="name || 'Category Name'"></div>
                            <div class="text-xs text-slate-500">
                                Score range: <strong class="text-slate-800" x-text="(min_percentage || '0') + '% – ' + (max_percentage || '100') + '%'"></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Badge Preview -->
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition"
                              :style="'background-color: ' + color + '15; color: ' + color + '; border-color: ' + color + '40;'">
                            <span x-text="emoji"></span>
                            <span x-text="name || 'Preview'"></span>
                        </span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name & Emoji -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Category Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               x-model="name"
                               placeholder="e.g. Cucumber, Tomato, Orange, Apple, Master"
                               required
                               class="w-full text-sm rounded-xl border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20">
                    </div>

                    <div>
                        <label for="emoji" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Emoji Icon
                        </label>
                        <input type="text" 
                               name="emoji" 
                               id="emoji" 
                               x-model="emoji"
                               maxlength="16"
                               placeholder="e.g. 🥒"
                               class="w-full text-sm rounded-xl border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20 text-center text-lg">
                    </div>
                </div>

                <!-- Quick Emoji Picker -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        Quick Emoji Suggestions
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['🍏', '🍊', '🍅', '🍋', '🥒', '🥑', '🍓', '🍇', '🍑', '🍍', '🥕', '⭐', '🏆', '🚀', '🥇', '🎖️', '💎', '🎯'] as $em)
                            <button type="button" 
                                    @click="setEmoji('{{ $em }}')"
                                    class="w-9 h-9 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 flex items-center justify-center text-base transition cursor-pointer"
                                    :class="{ 'bg-indigo-50 border-indigo-500 ring-2 ring-indigo-500/20': emoji === '{{ $em }}' }">
                                {{ $em }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Min & Max Percentage -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="min_percentage" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Min Percentage (%) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               name="min_percentage" 
                               id="min_percentage" 
                               step="0.01" 
                               min="0" 
                               max="100"
                               x-model="min_percentage"
                               placeholder="e.g. 0.00 or 80.01"
                               required
                               class="w-full text-sm rounded-xl border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20">
                    </div>

                    <div>
                        <label for="max_percentage" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Max Percentage (%) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               name="max_percentage" 
                               id="max_percentage" 
                               step="0.01" 
                               min="0" 
                               max="100"
                               x-model="max_percentage"
                               placeholder="e.g. 20.00 or 100.00"
                               required
                               class="w-full text-sm rounded-xl border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20">
                    </div>
                </div>

                <!-- Theme Color -->
                <div>
                    <label for="color" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Theme Color (Hex)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color" 
                               x-model="color"
                               class="w-11 h-11 rounded-xl border border-slate-300 p-1 cursor-pointer bg-white">
                        <input type="text" 
                               name="color" 
                               id="color" 
                               x-model="color"
                               placeholder="#10B981"
                               class="w-40 text-sm font-mono rounded-xl border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20">
                        
                        <!-- Color Palette Pills -->
                        <div class="hidden sm:flex items-center gap-1.5 pl-2 border-l border-slate-200">
                            @foreach(['#10B981', '#F97316', '#EF4444', '#F59E0B', '#059669', '#3B82F6', '#8B5CF6', '#EC4899', '#6366F1'] as $c)
                                <button type="button" 
                                        @click="setColor('{{ $c }}')"
                                        style="background-color: {{ $c }}"
                                        class="w-7 h-7 rounded-lg border border-white shadow-2xs hover:scale-110 transition cursor-pointer"
                                        :class="{ 'ring-2 ring-indigo-500 ring-offset-2': color.toLowerCase() === '{{ strtolower($c) }}' }">
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Description (Optional)
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="2"
                              x-model="description"
                              placeholder="Brief description of this tier bracket..."
                              class="w-full text-sm rounded-xl border-slate-300 py-2.5 px-3.5 focus:border-indigo-600 focus:ring-indigo-600/20"></textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.categories.index') }}" 
                       class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition cursor-pointer">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
