<x-app-layout>
    <x-slot name="title">Expenses</x-slot>
    <x-slot name="subtitle">Manage and filter your expenses</x-slot>

    {{-- ── Filter Bar ──────────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; margin-bottom:16px;">
        <form method="GET" action="{{ route('expenses.index') }}" id="filter-form">
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">

                {{-- Search --}}
                <div style="flex:1; min-width:160px; display:flex; flex-direction:column; gap:5px;">
                    <label style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Description..."
                           style="background:#ffffff; border:1px solid #d1d5db; color:#111827; font-size:13px; padding:7px 11px; border-radius:7px; outline:none; transition:border-color 150ms; box-sizing:border-box; height:36px;"
                           onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                {{-- Category --}}
                <div style="width:168px; display:flex; flex-direction:column; gap:5px;">
                    <label style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">Category</label>
                    <select name="category_id"
                            style="background:#ffffff; border:1px solid #d1d5db; color:#111827; font-size:13px; padding:7px 11px; border-radius:7px; outline:none; height:36px; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='none' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 9px center; padding-right:28px; cursor:pointer;">
                        <option value="">All categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') === $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- From --}}
                <div style="width:144px; display:flex; flex-direction:column; gap:5px;">
                    <label style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           style="background:#ffffff; border:1px solid #d1d5db; color:#111827; font-size:13px; padding:7px 11px; border-radius:7px; outline:none; height:36px; box-sizing:border-box;"
                           onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                {{-- To --}}
                <div style="width:144px; display:flex; flex-direction:column; gap:5px;">
                    <label style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           style="background:#ffffff; border:1px solid #d1d5db; color:#111827; font-size:13px; padding:7px 11px; border-radius:7px; outline:none; height:36px; box-sizing:border-box;"
                           onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                {{-- Per page (hidden, persisted through filters) --}}
                @if (request()->has('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif

                {{-- Actions --}}
                <div style="display:flex; gap:8px; align-items:flex-end;">
                    <button type="submit"
                            style="height:36px; padding:0 16px; background:#111827; color:#fff; font-size:13px; font-weight:500; border-radius:7px; border:none; cursor:pointer; transition:background 150ms; white-space:nowrap;"
                            onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                        Apply
                    </button>
                    @if (request()->hasAny(['search','category_id','from','to']))
                        <a href="{{ route('expenses.index') }}"
                           style="height:36px; padding:0 14px; display:inline-flex; align-items:center; background:#ffffff; border:1px solid #d1d5db; color:#374151; font-size:13px; font-weight:500; border-radius:7px; text-decoration:none; transition:background 150ms; white-space:nowrap;"
                           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#ffffff'">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── Table Card ─────────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">

        {{-- Card header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #e5e7eb;">
            <div>
                <h2 style="font-size:15px; font-weight:600; color:#111827; margin:0;">All Expenses</h2>
                <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">{{ $expenses->total() }} total records</p>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                {{-- Export CSV (carries current filters) --}}
                <a href="{{ route('expenses.export', request()->only(['search','category_id','from','to'])) }}"
                   style="display:inline-flex; align-items:center; gap:6px; background:#ffffff; border:1px solid #d1d5db; color:#374151; font-size:12px; font-weight:500; padding:7px 14px; border-radius:7px; text-decoration:none; transition:background 150ms;"
                   onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('expenses.create') }}"
                   style="display:inline-flex; align-items:center; gap:6px; background:#111827; color:#fff; font-size:12px; font-weight:500; padding:7px 14px; border-radius:7px; text-decoration:none; transition:background 150ms;"
                   onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Expense
                </a>
            </div>
        </div>

        @if ($expenses->isEmpty())
            <div style="padding:64px 24px; text-align:center;">
                <svg style="width:40px; height:40px; color:#d1d5db; margin:0 auto 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p style="font-size:14px; font-weight:500; color:#111827; margin:0 0 4px;">No expenses found</p>
                <p style="font-size:13px; color:#6b7280; margin:0;">
                    @if (request()->hasAny(['search','category_id','from','to']))
                        Try adjusting your filters.
                    @else
                        <a href="{{ route('expenses.create') }}" style="color:#111827; font-weight:500; text-decoration:underline;">Add your first expense</a>
                    @endif
                </p>
            </div>
        @else

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:580px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <th style="text-align:left; padding:10px 12px 10px 20px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Description</th>
                        <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Category</th>
                        <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Date</th>
                        <th style="text-align:right; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Amount</th>
                        <th style="width:44px; padding:10px 20px 10px 8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $expense)
                    <tr style="border-bottom:1px solid #f3f4f6; transition:background 120ms;"
                        onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''"
                        x-data="{ menuOpen: false }">

                        {{-- Description --}}
                        <td style="padding:13px 12px 13px 20px; vertical-align:middle; max-width:280px;">
                            <span style="font-size:14px; font-weight:600; color:#111827; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ $expense->description }}
                            </span>
                        </td>

                        {{-- Category --}}
                        <td style="padding:13px 12px; vertical-align:middle;">
                            @if ($expense->category)
                                <span style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500; padding:3px 9px; border-radius:20px;"
                                      style2="background-color:{{ $expense->category->color }}18; color:{{ $expense->category->color }}; border:1px solid {{ $expense->category->color }}30;">
                                    <span style="width:7px; height:7px; border-radius:50%; flex-shrink:0; display:inline-block; background:{{ $expense->category->color }};"></span>
                                    <span style="color:#374151;">{{ $expense->category->name }}</span>
                                </span>
                            @else
                                <span style="font-size:12px; color:#9ca3af;">—</span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td style="padding:13px 12px; vertical-align:middle; white-space:nowrap;">
                            <span style="font-size:12px; color:#6b7280; font-family:ui-monospace,monospace; background:#f3f4f6; padding:2px 8px; border-radius:5px; border:1px solid #e5e7eb;">
                                {{ $expense->occurred_at->format('M j, Y') }}
                            </span>
                        </td>

                        {{-- Amount --}}
                        <td style="padding:13px 12px; vertical-align:middle; text-align:right; white-space:nowrap;">
                            <span style="font-size:15px; font-weight:700; color:#111827; font-variant-numeric:tabular-nums;">
                                ${{ number_format((float)$expense->amount, 2) }}
                            </span>
                        </td>

                        {{-- Actions dropdown --}}
                        <td style="padding:13px 20px 13px 8px; vertical-align:middle; text-align:right; position:relative;">
                            <div style="position:relative; display:inline-block;">
                                <button type="button" @click="menuOpen = !menuOpen" @click.outside="menuOpen = false"
                                        style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:none; background:transparent; color:#9ca3af; cursor:pointer; transition:all 120ms;"
                                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151'"
                                        onmouseout="this.style.background='transparent'; this.style.color='#9ca3af'">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>

                                <div x-show="menuOpen"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     style="display:none; position:absolute; right:0; top:calc(100% + 4px); z-index:40; min-width:140px; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; padding:4px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">

                                    <a href="{{ route('expenses.edit', $expense) }}"
                                       style="display:flex; align-items:center; gap:8px; padding:7px 10px; border-radius:5px; font-size:13px; color:#374151; text-decoration:none; transition:background 100ms;"
                                       onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>

                                    <div style="height:1px; background:#f3f4f6; margin:4px 0;"></div>

                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" style="margin:0;"
                                          x-data x-on:submit.prevent="if(confirm('Delete this expense?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="display:flex; align-items:center; gap:8px; width:100%; padding:7px 10px; border-radius:5px; font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer; text-align:left; transition:background 100ms;"
                                                onmouseover="this.style.background='rgba(239,68,68,0.06)'" onmouseout="this.style.background='transparent'">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Pagination Bar ──────────────────────────────────────────── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-top:1px solid #e5e7eb; flex-wrap:wrap; gap:10px;">

            {{-- Left: record range --}}
            <span style="font-size:12px; color:#6b7280; white-space:nowrap;">
                Showing {{ $expenses->firstItem() }}–{{ $expenses->lastItem() }} of {{ $expenses->total() }} results
            </span>

            {{-- Right: per page + page info + nav --}}
            <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">

                {{-- Rows per page --}}
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:12px; color:#6b7280; white-space:nowrap;">Rows per page</span>
                    <form method="GET" action="{{ route('expenses.index') }}">
                        @foreach (['search','category_id','from','to'] as $param)
                            @if (request()->has($param))
                                <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                            @endif
                        @endforeach
                        <select name="per_page" onchange="this.form.submit()"
                                style="background:#ffffff; border:1px solid #d1d5db; color:#374151; font-size:12px; padding:4px 24px 4px 8px; border-radius:6px; cursor:pointer; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='none' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 7px center; outline:none;">
                            @foreach ([10, 15, 25, 50] as $n)
                                <option value="{{ $n }}" {{ request()->input('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Page info --}}
                <span style="font-size:12px; color:#6b7280; white-space:nowrap;">
                    Page {{ $expenses->currentPage() }} of {{ $expenses->lastPage() }}
                </span>

                {{-- Navigation --}}
                <div style="display:flex; align-items:center; gap:4px;">
                    @if ($expenses->onFirstPage())
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">«</button>
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">‹</button>
                    @else
                        <a href="{{ $expenses->url(1) }}" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:background 120ms;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">«</a>
                        <a href="{{ $expenses->previousPageUrl() }}" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:background 120ms;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">‹</a>
                    @endif

                    @if ($expenses->hasMorePages())
                        <a href="{{ $expenses->nextPageUrl() }}" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:background 120ms;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">›</a>
                        <a href="{{ $expenses->url($expenses->lastPage()) }}" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:background 120ms;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">»</a>
                    @else
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">›</button>
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">»</button>
                    @endif
                </div>
            </div>
        </div>

        @endif
    </div>
</x-app-layout>
