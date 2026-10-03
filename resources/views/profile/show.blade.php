<x-layouts.app>
    <div class="max-w-5xl mx-auto space-y-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">User Profile</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Manage your personal profile information and account credentials.
            </p>
        </div>

        <!-- Profile Information Card -->
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

        <!-- 360° Survey Performance Meters (Normalised, Self, Peer) -->
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        360° Assessment Performance
                    </span>
                    <h3 class="text-xl font-black text-slate-900 mt-1">
                        Three Score Meters (Normalised, Self, Peer)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Your calibrated benchmark alongside personal and observer consensus ratings.
                    </p>
                </div>
            </div>

            @if($surveys->isEmpty())
                <div class="bg-white p-8 rounded-3xl border border-slate-200 text-center space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="gauge" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">No assessment scores available yet</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Once you participate in a 360 survey, your three performance score meters (Normalised, Self, and Peer) will appear here.
                    </p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($surveys as $s)
                        @php
                            $survey = $s['survey'];
                            $norm = $s['normalised'];
                            $self = $s['self'];
                            $peer = $s['peer'];
                            $comp = $s['comparison'];
                        @endphp
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                            <!-- Survey Card Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-black text-slate-900">{{ $survey->title }}</span>
                                        @if($survey->company)
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                {{ $survey->company->name }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($survey->description)
                                        <p class="text-xs text-slate-500 mt-1 max-w-xl">{{ $survey->description }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('participant.assessments.report', $survey) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition shadow-2xs">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                        <span>Full CQ Report</span>
                                    </a>
                                </div>
                            </div>

                            <!-- THREE METERS GRID (Normalised, Self, Peer) -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- 1. Normalised Meter -->
                                <div class="bg-gradient-to-b from-indigo-50/40 via-white to-white rounded-2xl border-2 border-indigo-200 p-5 flex flex-col justify-between items-center text-center space-y-4 shadow-2xs relative">
                                    <div class="w-full flex items-center justify-between">
                                        <div class="text-left">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-100/70 px-2 py-0.5 rounded-md">
                                                Meter 1: Normalised
                                            </span>
                                            <h4 class="text-sm font-black text-slate-900 mt-1">Normalised Score</h4>
                                            <p class="text-[10px] text-slate-400">40% Self + 60% Peer Consensus</p>
                                        </div>
                                        <span class="text-xl">{{ $norm['emoji'] }}</span>
                                    </div>

                                    <!-- Gauge -->
                                    <div class="w-full max-w-[200px] my-auto py-2">
                                        <x-score-meter 
                                            :percentage="$norm['percentage']" 
                                            :category="$norm['category']" 
                                            :only-gauge="true"
                                        />
                                    </div>

                                    <!-- Score Numbers Box -->
                                    <div class="w-full pt-3 border-t border-indigo-100 space-y-2">
                                        <div class="flex items-baseline justify-center gap-1">
                                            <span class="text-3xl sm:text-4xl font-black tracking-tight text-indigo-950 leading-none">
                                                {{ number_format($norm['percentage'], 2) }}
                                            </span>
                                            <span class="text-lg font-bold text-slate-400">%</span>
                                        </div>

                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $norm['badge'] }}">
                                                <span>{{ $norm['category'] }}</span>
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                                Gap: {{ $norm['gap'] > 0 ? '+' : '' }}{{ $norm['gap'] }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Self Meter -->
                                <div class="bg-gradient-to-b from-slate-50/80 via-white to-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between items-center text-center space-y-4 shadow-2xs">
                                    <div class="w-full flex items-center justify-between">
                                        <div class="text-left">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                                Meter 2: Self
                                            </span>
                                            <h4 class="text-sm font-black text-slate-900 mt-1">Self Score</h4>
                                            <p class="text-[10px] text-slate-400">Personal Self-Assessment</p>
                                        </div>
                                        <span class="text-xl">{{ $self['emoji'] }}</span>
                                    </div>

                                    <!-- Gauge -->
                                    <div class="w-full max-w-[200px] my-auto py-2">
                                        <x-score-meter 
                                            :percentage="$self['percentage']" 
                                            :category="$self['category']" 
                                            :only-gauge="true"
                                        />
                                    </div>

                                    <!-- Score Numbers Box -->
                                    <div class="w-full pt-3 border-t border-slate-100 space-y-2">
                                        <div class="flex items-baseline justify-center gap-1">
                                            <span class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 leading-none">
                                                {{ number_format($self['percentage'], 2) }}
                                            </span>
                                            <span class="text-lg font-bold text-slate-400">%</span>
                                        </div>

                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $self['badge'] }}">
                                                <span>{{ $self['category'] }}</span>
                                            </span>
                                            @if($self['is_completed'])
                                                <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                                    {{ $self['score'] }} / {{ $self['max_score'] }} pts
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Peer Meter -->
                                <div class="bg-gradient-to-b from-emerald-50/30 via-white to-white rounded-2xl border border-emerald-200/80 p-5 flex flex-col justify-between items-center text-center space-y-4 shadow-2xs">
                                    <div class="w-full flex items-center justify-between">
                                        <div class="text-left">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                                Meter 3: Peer
                                            </span>
                                            <h4 class="text-sm font-black text-slate-900 mt-1">Peer Score</h4>
                                            <p class="text-[10px] text-slate-400">Observer Consensus Rating</p>
                                        </div>
                                        <span class="text-xl">{{ $peer['emoji'] }}</span>
                                    </div>

                                    <!-- Gauge -->
                                    <div class="w-full max-w-[200px] my-auto py-2">
                                        <x-score-meter 
                                            :percentage="$peer['percentage']" 
                                            :category="$peer['category']" 
                                            :only-gauge="true"
                                        />
                                    </div>

                                    <!-- Score Numbers Box -->
                                    <div class="w-full pt-3 border-t border-emerald-100 space-y-2">
                                        <div class="flex items-baseline justify-center gap-1">
                                            <span class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 leading-none">
                                                {{ number_format($peer['percentage'], 2) }}
                                            </span>
                                            <span class="text-lg font-bold text-slate-400">%</span>
                                        </div>

                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $peer['badge'] }}">
                                                <span>{{ $peer['category'] }}</span>
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-600 bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                                {{ $peer['completed_count'] }} of {{ $peer['total_count'] }} Peers
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alignment Diagnosis Summary -->
                            @if($norm['alignment_status'])
                                <div class="p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $norm['alignment_badge'] }}">
                                    <div class="flex items-start sm:items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-white/80 shadow-2xs flex items-center justify-center shrink-0">
                                            <i data-lucide="scale" class="w-4 h-4 text-slate-700"></i>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-xs text-slate-900 block">
                                                Alignment Status: {{ $norm['alignment_status'] }}
                                            </span>
                                            <p class="text-xs mt-0.5 text-slate-700 leading-relaxed">
                                                {{ $norm['alignment_insight'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex items-center gap-3 text-xs bg-white/70 px-3 py-1.5 rounded-xl border border-black/5">
                                        <div class="text-center">
                                            <span class="text-[9px] text-slate-400 uppercase font-sans block">Self</span>
                                            <span class="font-bold text-slate-900">{{ number_format($self['percentage'], 1) }}%</span>
                                        </div>
                                        <span class="text-slate-300 font-sans">vs</span>
                                        <div class="text-center">
                                            <span class="text-[9px] text-slate-400 uppercase font-sans block">Peers</span>
                                            <span class="font-bold text-slate-900">{{ number_format($peer['percentage'], 1) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
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
