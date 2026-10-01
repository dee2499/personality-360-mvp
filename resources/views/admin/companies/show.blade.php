<x-layouts.app>
    <div class="space-y-8" x-data="{
        showInviteModal: false,
        copied: false,
        copyLink(link) {
            navigator.clipboard.writeText(link);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
    }">
        <!-- Top Back Nav & Company Header -->
        <div>
            <a href="{{ route('admin.companies.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition mb-3">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Companies</span>
            </a>

            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-md shadow-indigo-100">
                        {{ substr($company->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $company->name }}</h1>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200">
                                Organization
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                            @if($company->contact_email)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $company->contact_email }}
                                </span>
                                <span>•</span>
                            @endif
                            <span>{{ $company->users->count() }} Employees</span>
                            <span>•</span>
                            <span>{{ $company->surveys->count() }} Surveys</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.companies.edit', $company) }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        <span>Edit Company</span>
                    </a>
                    <button type="button" @click="showInviteModal = true"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Add / Invite Employee</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Invitation Link Flash Alert -->
        @if(session('invitation_link'))
            <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-emerald-900 flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        Invitation link generated for {{ session('invited_employee') }}!
                    </span>
                    <button type="button" @click="copyLink('{{ session('invitation_link') }}')"
                            class="font-bold text-emerald-800 bg-white border border-emerald-300 px-3 py-1 rounded-lg hover:bg-emerald-100 transition flex items-center gap-1">
                        <i data-lucide="copy" class="w-3 h-3"></i>
                        <span x-text="copied ? 'Copied!' : 'Copy Activation Link'">Copy Activation Link</span>
                    </button>
                </div>
                <p class="text-emerald-700">
                    An email was dispatched. For immediate local testing, you can open this direct activation link:
                </p>
                <div class="p-2.5 bg-white/80 rounded-xl border border-emerald-200 font-mono text-[11px] text-emerald-900 break-all select-all">
                    {{ session('invitation_link') }}
                </div>
            </div>
        @endif

        <!-- 2 Columns Grid: Employees & Company Surveys -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left 2 Cols: Employees List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Employees of {{ $company->name }}</h2>
                        <p class="text-xs text-slate-500">All registered and invited team members eligible for survey cohorts</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                        {{ $company->users->count() }} Total
                    </span>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
                    @if($company->users->isEmpty())
                        <div class="py-12 px-6 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="user-plus" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">No employees added yet</h3>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                                Click "Add / Invite Employee" above to send account setup links to this company's team members.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                        <th class="py-3 px-6">Employee</th>
                                        <th class="py-3 px-6">Email</th>
                                        <th class="py-3 px-6">Account Status</th>
                                        <th class="py-3 px-6 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($company->users as $employee)
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="py-4 px-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100">
                                                        {{ substr($employee->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('admin.people.show', $employee) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition">
                                                            {{ $employee->name }}
                                                        </a>
                                                        <div class="text-[10px] text-slate-400 font-medium capitalize">{{ $employee->role }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6 text-slate-600">
                                                {{ $employee->email }}
                                            </td>
                                            <td class="py-4 px-6">
                                                @if($employee->isInvited())
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                                        <i data-lucide="clock" class="w-3 h-3 text-amber-500"></i>
                                                        Invitation Pending
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                                                        Active
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($employee->isInvited())
                                                        <button type="button" 
                                                                @click="copyLink('{{ route('invitation.show', ['token' => $employee->invitation_token]) }}')"
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                                            <span>Link</span>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('admin.people.show', $employee) }}" 
                                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-semibold text-xs text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                                        <span>360 Profile</span>
                                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                    </a>
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

            <!-- Right 1 Col: Company Surveys -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Surveys</h2>
                        <p class="text-xs text-slate-500">Cohorts for {{ $company->name }}</p>
                    </div>
                    <a href="{{ route('admin.surveys.create', ['company_id' => $company->id]) }}" 
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>New Survey</span>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($company->surveys as $survey)
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between gap-3 hover:border-indigo-300 transition">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Survey #{{ $survey->id }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full capitalize
                                        {{ $survey->isPublished() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $survey->status }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-extrabold text-slate-900">
                                    <a href="{{ route('admin.surveys.show', $survey) }}" class="hover:text-indigo-600 transition">
                                        {{ $survey->title }}
                                    </a>
                                </h3>
                                <div class="flex items-center gap-3 text-[11px] text-slate-500 mt-2">
                                    <span>{{ $survey->participants_count }} Participants</span>
                                    <span>•</span>
                                    <span>{{ $survey->assessments_count }} Evaluations</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">Completion: {{ $survey->completionPercentage() }}%</span>
                                <a href="{{ route('admin.surveys.show', $survey) }}" class="font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                    <span>Manage</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center text-xs text-slate-500">
                            No surveys created for this company yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Add / Invite Employee Modal -->
        <div x-show="showInviteModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-5 border border-slate-100"
                 @click.away="showInviteModal = false">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                            {{ $company->name }}
                        </span>
                        <h3 class="text-lg font-black text-slate-900 mt-1">Invite Employee</h3>
                    </div>
                    <button type="button" @click="showInviteModal = false" class="text-slate-400 hover:text-slate-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.companies.invite', $company) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="invite_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Employee Full Name <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input id="invite_name" 
                                   name="name" 
                                   type="text" 
                                   required 
                                   placeholder="e.g. John Doe"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                        </div>
                    </div>

                    <div>
                        <label for="invite_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Work Email Address <span class="text-rose-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input id="invite_email" 
                                   name="email" 
                                   type="email" 
                                   required 
                                   placeholder="e.g. jdoe@company.com"
                                   class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                        </div>
                    </div>

                    <div>
                        <label for="invite_role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Role
                        </label>
                        <div class="mt-1">
                            <select id="invite_role" name="role" 
                                    class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-600/20 transition">
                                <option value="participant">Participant (Employee)</option>
                                <option value="admin">Company Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="showInviteModal = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Send Invitation</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
