@extends('layouts.app')
@section('page_title', 'Verification & Approval')
@section('page_description', 'Review, approve, or reject student registrations')
@section('content')

{{-- Reject Modal --}}
<div id="reject-modal-overlay"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-white mb-3">Reject Candidates</h3>
        <p class="text-xs text-slate-400 mb-4">Please provide remarks explaining the reason for rejection. This will be
            visible to the school admins.</p>
        <textarea id="reject-remarks" rows="3" placeholder="Enter rejection reason..."
            class="w-full bg-slate-950/50 border border-slate-700 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded-xl text-slate-100 placeholder-slate-600 focus:outline-none text-sm p-3 mb-4"></textarea>
        <div class="flex justify-end gap-3">
            <button type="button" id="btn-cancel-reject"
                class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-all cursor-pointer">
                Cancel
            </button>
            <button type="button" id="btn-confirm-reject"
                class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-rose-600/10 cursor-pointer">
                Reject Selected
            </button>
        </div>
    </div>
</div>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <p class="text-sm text-slate-400 mt-0.5">Review, approve, or reject student registrations</p>
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, reg no…"
        class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-64">
    <select name="status"
        class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
        <option value="">All Status</option>
        @foreach(['Submitted', 'Under Review', 'Approved', 'Rejected', 'Hall Ticket Issued'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
    </select>
    <select name="school_id"
        class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
        <option value="">All Schools</option>
        @foreach($schools as $school)
            <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>{{ $school->name }}</option>
        @endforeach
    </select>
    <select name="class_id"
        class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
        <option value="">All Classes</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
        @endforeach
    </select>
    <select name="category_id"
        class="bg-slate-800/50 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500">
        <option value="">All Categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <button type="submit"
        class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all cursor-pointer">Filter</button>
    @if(request()->hasAny(['search', 'status', 'school_id', 'class_id', 'category_id']))
        <a href="{{ route('admin.verification.index') }}"
            class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">Clear</a>
    @endif
</form>

@php
    $statusColors = [
        'Submitted'          => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
        'Under Review'       => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'Approved'           => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'Rejected'           => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
        'Hall Ticket Issued' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    ];
@endphp

{{-- Hidden Bulk Submit Form --}}
<form id="bulk-form" method="POST" action="{{ route('admin.verification.bulk-verify') }}">
    @csrf
    <input type="hidden" name="action" id="bulk-action-hidden">
    <input type="hidden" name="remarks" id="bulk-remarks-hidden">
    <div id="hidden-student-ids"></div>
</form>

{{-- Table Card --}}
<div class="bg-slate-900/60 border border-slate-800/60 rounded-2xl overflow-hidden mb-8">

    {{-- Bulk Actions Toolbar --}}
    <div class="px-6 py-4 border-b border-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-950/20">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bulk Actions:</span>
            <button type="button" id="btn-approve" disabled
                class="bulk-action-btn px-4 py-2 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-emerald-600/10">
                ✓ Approve Selected
            </button>
            <button type="button" id="btn-review" disabled
                class="bulk-action-btn px-4 py-2 bg-amber-600 hover:bg-amber-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-amber-600/10">
                ⟳ Mark Under Review
            </button>
            <button type="button" id="btn-reject" disabled
                class="bulk-action-btn px-4 py-2 bg-rose-600 hover:bg-rose-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl transition-all shadow-md shadow-rose-600/10">
                ✕ Reject Selected
            </button>
        </div>
        <div class="text-xs font-medium text-slate-400">
            Selected: <span id="selected-count" class="font-bold text-indigo-400">0</span> candidates
        </div>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800/60">
                <th class="px-6 py-4 text-center w-12">
                    <input type="checkbox" id="select-all-cb"
                           class="rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                </th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Student</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">School</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Class</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Category</th>
                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Examination</th>
                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60">
            @forelse($students as $student)
                <tr class="hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4 text-center">
                        <input type="checkbox"
                               class="student-cb rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                               value="{{ $student->id }}">
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-semibold text-slate-200">{{ $student->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $student->registration_number ?? 'No Reg. #' }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs hidden md:table-cell">{{ $student->school->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-400 text-xs hidden lg:table-cell">{{ $student->class->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-400 text-xs hidden lg:table-cell">{{ $student->category->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-400 text-xs hidden lg:table-cell">{{ $student->examination->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$student->status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20' }}">
                            {{ $student->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.verification.show', $student) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-400 bg-indigo-600/10 hover:bg-indigo-600/20 transition-all">
                                Review
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1"
                            stroke="currentColor" class="w-12 h-12 mx-auto mb-3 text-slate-700">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        No registrations found for verification.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($students->hasPages())
        <div class="px-6 py-4 border-t border-slate-800/60">{{ $students->withQueryString()->links() }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script @nonce>
document.addEventListener('DOMContentLoaded', function () {

    // ── Helpers ───────────────────────────────────────────────────
    function getCheckedIds() {
        return Array.from(document.querySelectorAll('.student-cb:checked')).map(cb => cb.value);
    }

    function updateSelection() {
        const ids      = getCheckedIds();
        const count    = ids.length;
        const allCbs   = document.querySelectorAll('.student-cb');
        const masterCb = document.getElementById('select-all-cb');

        document.getElementById('selected-count').textContent = count;

        document.querySelectorAll('.bulk-action-btn').forEach(btn => {
            btn.disabled = (count === 0);
        });

        if (allCbs.length > 0) {
            masterCb.indeterminate = count > 0 && count < allCbs.length;
            masterCb.checked       = count > 0 && count === allCbs.length;
        }
    }

    function submitBulkAction(action) {
        const ids = getCheckedIds();
        if (ids.length === 0) { alert('Please select at least one student.'); return; }

        document.getElementById('bulk-action-hidden').value = action;

        if (action === 'reject') {
            const remarks = document.getElementById('reject-remarks').value.trim();
            if (!remarks) { alert('Please provide a reason for rejection.'); return; }
            document.getElementById('bulk-remarks-hidden').value = remarks;
        }

        // Populate student IDs into the hidden form
        const container = document.getElementById('hidden-student-ids');
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'student_ids[]';
            input.value = id;
            container.appendChild(input);
        });

        closeRejectModal();
        document.getElementById('bulk-form').submit();
    }

    function openRejectModal() {
        if (getCheckedIds().length === 0) { alert('Please select at least one student.'); return; }
        document.getElementById('reject-remarks').value = '';
        document.getElementById('reject-modal-overlay').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('reject-modal-overlay').classList.add('hidden');
    }

    // ── Event listeners (CSP-safe, no inline handlers) ────────────

    // Master "Select All" checkbox
    document.getElementById('select-all-cb').addEventListener('change', function () {
        document.querySelectorAll('.student-cb').forEach(cb => { cb.checked = this.checked; });
        updateSelection();
    });

    // Individual row checkboxes
    document.querySelectorAll('.student-cb').forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });

    // Bulk action buttons
    document.getElementById('btn-approve').addEventListener('click', function () {
        submitBulkAction('approve');
    });
    document.getElementById('btn-review').addEventListener('click', function () {
        submitBulkAction('review');
    });
    document.getElementById('btn-reject').addEventListener('click', openRejectModal);

    // Reject modal buttons
    document.getElementById('btn-cancel-reject').addEventListener('click', closeRejectModal);
    document.getElementById('btn-confirm-reject').addEventListener('click', function () {
        submitBulkAction('reject');
    });

    // Close modal on backdrop click
    document.getElementById('reject-modal-overlay').addEventListener('click', function (e) {
        if (e.target === this) closeRejectModal();
    });

});
</script>
@endpush
