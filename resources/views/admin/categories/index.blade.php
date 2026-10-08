<x-layouts.app>
    <div class="space-y-6">
        <!-- Header & Top Actions -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Score Categories & Rating Tiers</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Configure percentage brackets and fruit/vegetable performance names (e.g., Apple, Orange, Tomato, Lemon, Cucumber)</p>
            </div>

            <div class="flex items-center gap-2 shrink-0 flex-nowrap">
                <!-- Reset Defaults -->
                <form method="POST" action="{{ route('admin.categories.reset-defaults') }}" class="inline-flex m-0">
                    @csrf
                    <button type="submit" 
                            data-confirm="true"
                            data-confirm-title="Reset to System Defaults?"
                            data-confirm-message="This will reset all categories to the standard 5 tiers: Apple (0-20%), Orange (>20-40%), Tomato (>40-60%), Lemon (>60-80%), and Cucumber (>80-100%)."
                            data-confirm-btn="Reset Categories"
                            data-confirm-type="warning"
                            class="inline-flex items-center whitespace-nowrap gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition shadow-2xs cursor-pointer">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Reset Defaults</span>
                    </button>
                </form>

                <!-- Recalculate -->
                <form method="POST" action="{{ route('admin.categories.recalculate') }}" class="inline-flex m-0">
                    @csrf
                    <button type="submit" 
                            data-confirm="true"
                            data-confirm-title="Recalculate Assessment Categories?"
                            data-confirm-message="This will re-evaluate all completed assessment percentages against the current category brackets."
                            data-confirm-btn="Recalculate All"
                            data-confirm-type="primary"
                            class="inline-flex items-center whitespace-nowrap gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition shadow-2xs cursor-pointer">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span>Recalculate Scores</span>
                    </button>
                </form>

                <!-- Create Category -->
                <a href="{{ route('admin.categories.create') }}" 
                   class="inline-flex items-center whitespace-nowrap gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Add Category</span>
                </a>
            </div>
        </div>

        <!-- Visual Category Spectrum Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                    <i data-lucide="activity" class="w-4 h-4 text-indigo-600"></i>
                    <span>Current Score Spectrum (0% – 100%)</span>
                </span>
                <span class="text-slate-400 font-medium">{{ $categories->count() }} Tiers Configured</span>
            </div>

            <!-- Segmented Progress Bar -->
            <div class="w-full h-4 bg-slate-100 rounded-full overflow-hidden flex shadow-inner">
                @foreach($categories as $category)
                    @php
                        $width = max(2, $category->max_percentage - $category->min_percentage);
                    @endphp
                    <div style="width: {{ $width }}%; background-color: {{ $category->color }};" 
                         title="{{ $category->emoji }} {{ $category->name }}: {{ $category->range_label }}"
                         class="h-full transition hover:opacity-85 relative group cursor-pointer border-r border-white/30 last:border-0">
                    </div>
                @endforeach
            </div>

            <!-- Spectrum Labels -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-[11px] text-slate-500 font-semibold">
                @foreach($categories as $category)
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $category->color }}"></span>
                        <span class="text-slate-800 font-bold">{{ $category->emoji }} {{ $category->name }}</span>
                        <span class="text-slate-400 text-[10px]">({{ $category->range_label }})</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($categories->isEmpty())
                <div class="py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="tag" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No score categories found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                        Get started by adding your first score category or click Reset Defaults.
                    </p>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('admin.categories.reset-defaults') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                <span>Reset to Default 5 Tiers</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                <th class="py-3.5 px-6">Tier Name</th>
                                <th class="py-3.5 px-6">Percentage Range</th>
                                <th class="py-3.5 px-6">Theme Color</th>
                                <th class="py-3.5 px-6">Badge Preview</th>
                                <th class="py-3.5 px-6">Description</th>
                                <th class="py-3.5 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($categories as $category)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <!-- Tier Name & Emoji -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl shrink-0 p-1.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-center w-10 h-10">{{ $category->emoji }}</span>
                                            <div>
                                                <div class="font-extrabold text-slate-900 text-sm">{{ $category->name }}</div>
                                                <span class="text-[10px] text-slate-400">Order: {{ $loop->iteration }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Range -->
                                    <td class="py-4 px-6">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-bold text-xs border border-slate-200">
                                            <span>{{ number_format($category->min_percentage, 2) }}%</span>
                                            <span class="text-slate-400">→</span>
                                            <span>{{ number_format($category->max_percentage, 2) }}%</span>
                                        </div>
                                    </td>

                                    <!-- Theme Color -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-lg border border-slate-300 shadow-2xs shrink-0" style="background-color: {{ $category->color }}"></span>
                                            <code class="text-slate-600 text-[11px] font-mono">{{ $category->color }}</code>
                                        </div>
                                    </td>

                                    <!-- Badge Preview -->
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border"
                                              style="background-color: {{ $category->color }}15; color: {{ $category->color }}; border-color: {{ $category->color }}40;">
                                            <span>{{ $category->emoji }}</span>
                                            <span>{{ $category->name }}</span>
                                        </span>
                                    </td>

                                    <!-- Description -->
                                    <td class="py-4 px-6 text-slate-500 text-xs max-w-xs truncate">
                                        {{ $category->description ?: '—' }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Edit -->
                                            <a href="{{ route('admin.categories.edit', $category) }}" 
                                               class="p-2 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition"
                                               title="Edit Category">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>

                                            <!-- Delete -->
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        data-confirm="true"
                                                        data-confirm-title="Delete Category '{{ $category->name }}'?"
                                                        data-confirm-message="Are you sure you want to delete this category tier? Assessments scoring in this range will map to the closest remaining category."
                                                        data-confirm-btn="Delete Category"
                                                        data-confirm-type="danger"
                                                        class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                                        title="Delete Category">
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
            @endif
        </div>
    </div>
</x-layouts.app>
