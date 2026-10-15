<x-layouts.guest>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-16 h-16 flex items-center justify-center">
                <img src="{{ asset('logo.png') }}" alt="ChangeQuo" class="w-16 h-16 object-contain">
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
                        Email Address or Username
                    </label>
                    <div class="mt-1.5">
                        <input id="email" 
                               name="email" 
                               type="text" 
                               autocomplete="username" 
                               required 
                               value="{{ old('email') }}"
                               placeholder="you@company.com or username"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('email') border-rose-500 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ showPassword: false }">
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Password
                    </label>
                    <div class="mt-1.5 relative">
                        <input id="password" 
                               name="password" 
                               :type="showPassword ? 'text' : 'password'"
                               type="password" 
                               autocomplete="current-password" 
                               required 
                               placeholder="••••••••"
                               class="block w-full rounded-xl border border-slate-300 pl-3.5 pr-11 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('password') border-rose-500 @enderror">
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition cursor-pointer focus:outline-hidden"
                                :title="showPassword ? 'Hide password' : 'Show password'"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'">
                            <!-- Eye icon (shown when password is hidden) -->
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Eye-slash icon (shown when password is visible) -->
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
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
