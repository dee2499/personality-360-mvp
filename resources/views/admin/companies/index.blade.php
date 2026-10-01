<x-layouts.app>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                        B2B Enterprise
                    </span>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Companies & Organizations</h1>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-1">Manage client companies, invite employees, and create organizational 360 assessment cohorts</p>
            </div>
            <a href="{{ route('admin.companies.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-100 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add New Company</span>
            </a>
        </div>

        <!-- Companies Grid / Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($companies->isEmpty())
                <div class="py-12 px-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No companies created yet</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                        Add a client company to start inviting employees and running enterprise 360 personality evaluations.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.companies.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Create Company</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 uppercase font-semibold tracking-wider text-[10px]">
                                <th class="py-3 px-6">Company</th>
                                <th class="py-3 px-6">Contact Email</th>
                                <th class="py-3 px-6">Employees</th>
                                <th class="py-3 px-6">Assigned Surveys</th>
                                <th class="py-3 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($companies as $company)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-700 font-black text-sm flex items-center justify-center border border-indigo-100">
                                                {{ substr($company->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.companies.show', $company) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition text-sm">
                                                    {{ $company->name }}
                                                </a>
                                                @if($company->description)
                                                    <p class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">{{ $company->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-slate-600 font-medium">
                                        {{ $company->contact_email ?? '—' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                            <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i>
                                            {{ $company->users_count }} Employees
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            {{ $company->surveys_count }} Surveys
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.companies.show', $company) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-semibold text-xs text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                                <span>Manage & Invite</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
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
</x-layouts.app>
