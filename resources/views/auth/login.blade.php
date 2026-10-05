<x-layouts.guest>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                <i data-lucide="compass" class="w-8 h-8"></i>
            </div>
        </div>
        <h2 class="mt-4 text-center text-2xl font-black tracking-tight text-slate-900">
            Personality 360 Assessment
        </h2>
        <p class="mt-1 text-center text-xs text-slate-500 font-medium">
            Sign in to access assessments, profiles, and enterprise feedback
        </p>

        <div class="mt-8 bg-white py-8 px-6 shadow-sm rounded-2xl border border-slate-200 sm:px-10">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Email Address
                    </label>
                    <div class="mt-1.5">
                        <input id="email" 
                               name="email" 
                               type="email" 
                               autocomplete="email" 
                               required 
                               value="{{ old('email') }}"
                               placeholder="you@company.com"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('email') border-rose-500 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Password
                    </label>
                    <div class="mt-1.5">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               autocomplete="current-password" 
                               required 
                               placeholder="••••••••"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('password') border-rose-500 @enderror">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Remember me</span>
                    </label>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 shadow-sm shadow-indigo-100 transition cursor-pointer">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
