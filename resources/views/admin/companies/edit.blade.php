<x-layouts.app>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <a href="{{ route('admin.companies.show', $company) }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to {{ $company->name }}</span>
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Company</h1>
            <p class="text-xs text-slate-500 mt-1">Update organization details and primary contact information.</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
            <form method="POST" action="{{ route('admin.companies.update', $company) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Company Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="mt-1.5">
                        <input id="name" 
                               name="name" 
                               type="text" 
                               required 
                               value="{{ old('name', $company->name) }}"
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
                               value="{{ old('contact_email', $company->contact_email) }}"
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
                                  class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('description') border-rose-500 @enderror">{{ old('description', $company->description) }}</textarea>
                    </div>
                    @error('description')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 flex items-center justify-between border-t border-slate-100">
                    <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" onsubmit="return confirm('Are you sure you want to delete this company?');">
                        <!-- separate delete action -->
                    </form>
                    <a href="{{ route('admin.companies.show', $company) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
