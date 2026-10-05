<x-layouts.guest>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-16 h-16 flex items-center justify-center">
                <svg class="w-16 h-16" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M48 48 C38 20, 10 15, 8 32 C6 48, 30 50, 48 50 Z" fill="#7C3AED" opacity="0.95"/>
                    <path d="M48 52 C32 54, 16 66, 20 80 C24 92, 42 78, 48 56 Z" fill="#9333EA" opacity="0.85"/>
                    <path d="M52 48 C62 20, 90 15, 92 32 C94 48, 70 50, 52 50 Z" fill="#7C3AED" opacity="0.95"/>
                    <path d="M52 52 C68 54, 84 66, 80 80 C76 92, 58 78, 52 56 Z" fill="#9333EA" opacity="0.85"/>
                    <ellipse cx="50" cy="50" rx="3" ry="22" fill="#581C87"/>
                    <circle cx="50" cy="24" r="3.5" fill="#581C87"/>
                </svg>
            </div>
        </div>
        <div class="text-center mt-3">
            <h2 class="text-3xl font-black tracking-tight text-purple-950 leading-none">
                change<span class="text-purple-600">quo</span>
            </h2>
            <p class="mt-1.5 text-xs font-bold text-purple-700 tracking-tight">
                Unlocking Possibilities
            </p>
        </div>

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
