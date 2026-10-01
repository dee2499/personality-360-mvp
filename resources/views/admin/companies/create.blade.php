<x-layouts.app>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <a href="{{ route('admin.companies.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Companies</span>
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Add New Company</h1>
            <p class="text-xs text-slate-500 mt-1">Register a client company to start managing employees and organizational cohorts.</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
            <form method="POST" action="{{ route('admin.companies.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Company / Organization Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="mt-1.5">
                        <input id="name" 
                               name="name" 
                               type="text" 
                               required 
                               value="{{ old('name') }}"
                               placeholder="e.g. Acme Technologies Inc."
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('name') border-rose-500 @enderror">
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="contact_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Primary Contact Email
                    </label>
                    <div class="mt-1.5">
                        <input id="contact_email" 
                               name="contact_email" 
                               type="email" 
                               value="{{ old('contact_email') }}"
                               placeholder="e.g. hr@acme.com"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('contact_email') border-rose-500 @enderror">
                    </div>
                    @error('contact_email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Description / Notes
                    </label>
                    <div class="mt-1.5">
                        <textarea id="description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Optional notes regarding the department or organizational division..."
                                  class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('description') border-rose-500 @enderror">{{ old('description') }}</textarea>
                    </div>
                    @error('description')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a href="{{ route('admin.companies.index') }}" 
                       class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Create Company</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
