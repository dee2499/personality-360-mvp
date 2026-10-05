<x-layouts.guest>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                <i data-lucide="building-2" class="w-8 h-8"></i>
            </div>
        </div>

        <div class="mt-4 text-center">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                Employee Invitation
            </span>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                Welcome to {{ $user->company?->name ?? 'Change Quo' }}!
            </h2>
            <p class="mt-1 text-xs text-slate-500 font-medium">
                Hello <strong class="text-slate-700">{{ $user->name }}</strong>, set your password to activate your account.
            </p>
        </div>

        <div class="mt-8 bg-white py-8 px-6 shadow-sm rounded-2xl border border-slate-200 sm:px-10">
            <form method="POST" action="{{ route('invitation.accept', ['token' => $token]) }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Email Address
                    </label>
                    <div class="mt-1.5">
                        <input type="email" 
                               value="{{ $user->email }}" 
                               disabled 
                               class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Create Password
                    </label>
                    <div class="mt-1.5">
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               placeholder="Minimum 8 characters"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('password') border-rose-500 @enderror">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Confirm Password
                    </label>
                    <div class="mt-1.5">
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               type="password" 
                               required 
                               placeholder="Re-type your password"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full flex justify-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 shadow-sm shadow-indigo-100 transition cursor-pointer">
                        Activate Account & Get Started
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
