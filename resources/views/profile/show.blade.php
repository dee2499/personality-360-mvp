<x-layouts.app>
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Account & Security</h1>
            <p class="text-xs text-slate-500 mt-1">Manage your employee profile details and update your password</p>
        </div>

        <!-- Profile Information Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-indigo-100">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        @if($user->company)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $user->company->name }}
                            </span>
                        @endif
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Role: {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 text-xs">
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <span class="block text-slate-400 font-medium">Email Address</span>
                    <span class="font-bold text-slate-800 text-sm mt-0.5 block truncate">{{ $user->email }}</span>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <span class="block text-slate-400 font-medium">Organization / Company</span>
                    <span class="font-bold text-slate-800 text-sm mt-0.5 block truncate">{{ $user->company?->name ?? 'System Organization' }}</span>
                </div>
            </div>
        </div>

        <!-- Update Password Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Change Password</h3>
                <p class="text-xs text-slate-500 mt-0.5">Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Current Password
                    </label>
                    <div class="mt-1.5">
                        <input id="current_password" 
                               name="current_password" 
                               type="password" 
                               required 
                               placeholder="Enter your current password"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('current_password') border-rose-500 @enderror">
                    </div>
                    @error('current_password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        New Password
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
                        Confirm New Password
                    </label>
                    <div class="mt-1.5">
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               type="password" 
                               required 
                               placeholder="Confirm your new password"
                               class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 py-2.5 px-6 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
