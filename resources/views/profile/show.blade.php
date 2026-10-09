<x-layouts.app>
    <div class="max-w-5xl mx-auto space-y-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">User Profile</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Manage your personal profile information and account credentials.
            </p>
        </div>

        <!-- Profile Overview Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-indigo-100 shrink-0">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $user->name }}</h2>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            @if($user->company)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $user->company->name }}
                                </span>
                            @endif
                            <span class="text-xs text-slate-400 font-medium">
                                Joined {{ $user->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-slate-100 text-xs">
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <span class="block text-slate-400 font-medium">Username</span>
                    <span class="font-bold text-slate-800 text-sm mt-0.5 block truncate">{{ $user->username ?: '— (None)' }}</span>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <span class="block text-slate-400 font-medium">Email Address</span>
                    <span class="font-bold text-slate-800 text-sm mt-0.5 block truncate">{{ $user->email ?: '— (Not set)' }}</span>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <span class="block text-slate-400 font-medium">Designation</span>
                    <span class="font-bold text-slate-800 text-sm mt-0.5 block truncate">{{ $user->designation ?: '— (Not set)' }}</span>
                </div>
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <span class="block text-slate-400 font-medium">Department / Division</span>
                    <span class="font-bold text-slate-800 text-sm mt-0.5 block truncate">
                        {{ $user->department ?: '—' }} {{ $user->division ? "({$user->division})" : '' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Personal Details Form Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Personal Information</h3>
                <p class="text-xs text-slate-500 mt-0.5">Update your personal details, email address, and organizational information.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            First Name <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1.5">
                            <input id="first_name" 
                                   name="first_name" 
                                   type="text" 
                                   required 
                                   value="{{ old('first_name', $user->first_name) }}"
                                   placeholder="First Name"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('first_name') border-rose-500 @enderror">
                        </div>
                        @error('first_name')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Last Name
                        </label>
                        <div class="mt-1.5">
                            <input id="last_name" 
                                   name="last_name" 
                                   type="text" 
                                   value="{{ old('last_name', $user->last_name) }}"
                                   placeholder="Last Name"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('last_name') border-rose-500 @enderror">
                        </div>
                        @error('last_name')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                Username
                            </label>
                            <span id="username_badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
                        </div>
                        <div class="mt-1.5 relative">
                            <input id="username" 
                                   name="username"
                                   type="text" 
                                   value="{{ old('username', $user->username) }}" 
                                   placeholder="e.g. harish@falcon"
                                   autocomplete="username"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('username') border-rose-500 @enderror">
                            <div id="username_spinner" class="absolute right-3.5 top-3 hidden">
                                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('username')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                        <p id="username_hint" class="mt-1.5 text-[11px] text-slate-400">
                            You can sign in using this username. Must be unique.
                        </p>
                    </div>

                    <div>
                        <label for="profile_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Email Address
                        </label>
                        <div class="mt-1.5">
                            <input id="profile_email" 
                                   name="email"
                                   type="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   placeholder="your.email@company.com"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('email') border-rose-500 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-[11px] text-slate-400">You can add or update your email address for account notifications.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Organization / Role <span class="text-slate-400 font-normal lowercase">(read-only)</span>
                    </label>
                    <div class="mt-1.5">
                        <input type="text" 
                               value="{{ $user->company?->name ?? 'System Organization' }} ({{ ucfirst($user->role) }})" 
                               disabled 
                               readonly
                               class="block w-full rounded-xl border border-slate-200 bg-slate-100/80 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed select-none">
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-400">Assigned by organization administration.</p>
                </div>

                <!-- Professional & Organizational Details (Optional) -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2">
                    <div>
                        <label for="designation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Designation <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <div class="mt-1.5">
                            <input id="designation" 
                                   name="designation"
                                   type="text" 
                                   value="{{ old('designation', $user->designation) }}" 
                                   placeholder="e.g. CFO, AGM"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('designation') border-rose-500 @enderror">
                        </div>
                        @error('designation')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="department" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Department <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <div class="mt-1.5">
                            <input id="department" 
                                   name="department"
                                   type="text" 
                                   value="{{ old('department', $user->department) }}" 
                                   placeholder="e.g. Finance, Operations"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('department') border-rose-500 @enderror">
                        </div>
                        @error('department')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="division" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Division <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <div class="mt-1.5">
                            <input id="division" 
                                   name="division"
                                   type="text" 
                                   value="{{ old('division', $user->division) }}" 
                                   placeholder="e.g. Marine, Feed"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('division') border-rose-500 @enderror">
                        </div>
                        @error('division')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="age" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Age <span class="text-slate-400 font-normal lowercase">(optional)</span>
                        </label>
                        <div class="mt-1.5">
                            <input id="age" 
                                   name="age"
                                   type="number" 
                                   min="16"
                                   max="100"
                                   value="{{ old('age', $user->age) }}" 
                                   placeholder="e.g. 45"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition @error('age') border-rose-500 @enderror">
                        </div>
                        @error('age')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button id="save_profile_btn"
                            type="submit" 
                            class="inline-flex items-center justify-center gap-2 py-2.5 px-6 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
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
                            class="inline-flex items-center justify-center gap-2 py-2.5 px-6 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition cursor-pointer">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const usernameInput = document.getElementById('username');
            const badge = document.getElementById('username_badge');
            const hint = document.getElementById('username_hint');
            const spinner = document.getElementById('username_spinner');
            const saveBtn = document.getElementById('save_profile_btn');
            const currentUsername = @json($user->username ?? '');

            let timeout = null;

            if (usernameInput) {
                usernameInput.addEventListener('input', function () {
                    const val = usernameInput.value.trim();

                    clearTimeout(timeout);

                    if (val === '' || val.toLowerCase() === currentUsername.toLowerCase()) {
                        badge.classList.add('hidden');
                        badge.textContent = '';
                        spinner.classList.add('hidden');
                        hint.textContent = 'You can sign in using this username. Must be unique.';
                        hint.className = 'mt-1.5 text-[11px] text-slate-400';
                        usernameInput.classList.remove('border-rose-500', 'border-emerald-500');
                        if (saveBtn) saveBtn.disabled = false;
                        return;
                    }

                    spinner.classList.remove('hidden');
                    badge.classList.add('hidden');

                    timeout = setTimeout(async function () {
                        try {
                            const response = await fetch(`{{ route('profile.check-username') }}?username=` + encodeURIComponent(val), {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const data = await response.json();
                            spinner.classList.add('hidden');
                            badge.classList.remove('hidden');

                            if (data.available) {
                                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700';
                                badge.textContent = 'Available';
                                hint.textContent = '✓ This username is available!';
                                hint.className = 'mt-1.5 text-[11px] text-emerald-600 font-medium';
                                usernameInput.classList.remove('border-rose-500');
                                usernameInput.classList.add('border-emerald-500');
                                if (saveBtn) saveBtn.disabled = false;
                            } else {
                                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700';
                                badge.textContent = 'Already Taken';
                                hint.textContent = '✕ This username is already in use. Please choose another.';
                                hint.className = 'mt-1.5 text-[11px] text-rose-600 font-medium';
                                usernameInput.classList.remove('border-emerald-500');
                                usernameInput.classList.add('border-rose-500');
                                if (saveBtn) saveBtn.disabled = true;
                            }
                        } catch (e) {
                            spinner.classList.add('hidden');
                        }
                    }, 300);
                });
            }
        });
    </script>
</x-layouts.app>
