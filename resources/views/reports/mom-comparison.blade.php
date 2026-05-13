<x-app-layout>
    <x-slot name="title">vs. Last Month</x-slot>
    <x-slot name="subtitle">
        {{ \Carbon\Carbon::create($prevYear, $prevMonth)->format('F Y') }}
        vs.
        {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
    </x-slot>

    {{-- ── Period selector ─────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; margin-bottom:16px;">
        <form method="GET" action="{{ route('reports.mom-comparison') }}">
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <label style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">Year</label>
                    <select name="year" onchange="this.form.submit()"
                            style="height:36px; min-width:90px; background:#ffffff; border:1px solid #d1d5db; border-radius:7px; padding:0 28px 0 10px; font-size:13px; color:#111827; cursor:pointer; outline:none; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 9px center; background-size:13px;">
                        @foreach (range(now()->year, now()->year - 3) as $y)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <label style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">Month (current)</label>
                    <select name="month" onchange="this.form.submit()"
                            style="height:36px; min-width:140px; background:#ffffff; border:1px solid #d1d5db; border-radius:7px; padding:0 28px 0 10px; font-size:13px; color:#111827; cursor:pointer; outline:none; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 9px center; background-size:13px;">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($month == $m)>{{ \Carbon\Carbon::create(null, $m)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        style="height:36px; padding:0 16px; background:#111827; color:#fff; font-size:13px; font-weight:500; border-radius:7px; border:none; cursor:pointer; transition:background 150ms;"
                        onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                    View
                </button>
            </div>
        </form>
    </div>

    {{-- ── Summary cards ────────────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:16px;">

        {{-- Last month --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 6px;">
                {{ \Carbon\Carbon::create($prevYear, $prevMonth)->format('F Y') }}
            </p>
            <p style="font-size:28px; font-weight:700; color:#111827; margin:0; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">
                ${{ number_format($lastMonthTotal, 2) }}
            </p>
        </div>

        {{-- This month --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 6px;">
                {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            </p>
            <p style="font-size:28px; font-weight:700; color:#111827; margin:0; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">
                ${{ number_format($thisMonthTotal, 2) }}
            </p>
        </div>

        {{-- Overall change --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 6px;">
                Overall Change
            </p>
            @if ($overallChange === null)
                <p style="font-size:28px; font-weight:700; color:#6b7280; margin:0;">—</p>
                <p style="font-size:12px; color:#9ca3af; margin:4px 0 0;">No prior-month data</p>
            @else
                @php
                    $isUp  = $overallChange > 0.5;
                    $isDown = $overallChange < -0.5;
                    $changeColor = $isUp ? '#dc2626' : ($isDown ? '#16a34a' : '#6b7280');
                    $changeBg    = $isUp ? 'rgba(220,38,38,0.07)' : ($isDown ? 'rgba(22,163,74,0.07)' : '#f3f4f6');
                @endphp
                <div style="display:flex; align-items:center; gap:10px;">
                    <p style="font-size:28px; font-weight:700; color:{{ $changeColor }}; margin:0; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">
                        {{ $overallChange > 0 ? '+' : '' }}{{ number_format($overallChange, 1) }}%
                    </p>
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:50%; background:{{ $changeBg }};">
                        @if ($isUp)
                            <svg width="16" height="16" fill="none" stroke="{{ $changeColor }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                        @elseif ($isDown)
                            <svg width="16" height="16" fill="none" stroke="{{ $changeColor }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        @else
                            <svg width="16" height="16" fill="none" stroke="{{ $changeColor }}" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                        @endif
                    </span>
                </div>
                <p style="font-size:12px; color:#9ca3af; margin:4px 0 0;">
                    vs. {{ \Carbon\Carbon::create($prevYear, $prevMonth)->format('F Y') }}
                </p>
            @endif
        </div>
    </div>

    {{-- ── Per-category breakdown ───────────────────────────────────────── --}}
    @if ($data->isEmpty())
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:48px 24px; text-align:center;">
            <p style="font-size:13px; color:#9ca3af; margin:0;">
                No expenses found for either
                {{ \Carbon\Carbon::create($prevYear, $prevMonth)->format('F Y') }} or
                {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}.
            </p>
        </div>
    @else
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">

            <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb;">
                <h2 style="font-size:15px; font-weight:600; color:#111827; margin:0;">Category Comparison</h2>
                <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">{{ $data->count() }} {{ Str::plural('category', $data->count()) }}</p>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:600px;">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                            <th style="text-align:left; padding:10px 12px 10px 20px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Category</th>
                            <th style="text-align:right; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">
                                {{ \Carbon\Carbon::create($prevYear, $prevMonth)->format('M Y') }}
                            </th>
                            <th style="text-align:right; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">
                                {{ \Carbon\Carbon::create($year, $month)->format('M Y') }}
                            </th>
                            <th style="text-align:right; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Change ($)</th>
                            <th style="text-align:right; padding:10px 20px 10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Change (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $row)
                            @php
                                $diff   = $row->this_month - $row->last_month;
                                $isUp   = $row->direction === 'up';
                                $isDown = $row->direction === 'down';
                                $isNew  = $row->direction === 'new';
                                $pctColor = $isUp ? '#dc2626' : ($isDown ? '#16a34a' : '#6b7280');
                                $pctBg    = $isUp ? 'rgba(220,38,38,0.07)' : ($isDown ? 'rgba(22,163,74,0.07)' : '#f3f4f6');
                            @endphp
                            <tr style="border-bottom:1px solid #f3f4f6; transition:background 120ms;"
                                onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">

                                {{-- Category --}}
                                <td style="padding:13px 12px 13px 20px; vertical-align:middle;">
                                    <span style="display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:#111827;">
                                        <span style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:{{ $row->category?->color ?? '#71717a' }};"></span>
                                        {{ $row->category?->name ?? 'Unknown' }}
                                    </span>
                                </td>

                                {{-- Last month --}}
                                <td style="padding:13px 12px; text-align:right; vertical-align:middle; font-size:14px; font-weight:500; color:{{ $row->last_month > 0 ? '#374151' : '#d1d5db' }}; font-variant-numeric:tabular-nums; white-space:nowrap;">
                                    {{ $row->last_month > 0 ? '$'.number_format($row->last_month, 2) : '—' }}
                                </td>

                                {{-- This month --}}
                                <td style="padding:13px 12px; text-align:right; vertical-align:middle; font-size:15px; font-weight:700; color:{{ $row->this_month > 0 ? '#111827' : '#d1d5db' }}; font-variant-numeric:tabular-nums; white-space:nowrap;">
                                    {{ $row->this_month > 0 ? '$'.number_format($row->this_month, 2) : '—' }}
                                </td>

                                {{-- Change $ --}}
                                <td style="padding:13px 12px; text-align:right; vertical-align:middle; white-space:nowrap;">
                                    @if ($row->last_month > 0 || $row->this_month > 0)
                                        <span style="font-size:13px; font-weight:500; color:{{ $diff > 0 ? '#dc2626' : ($diff < 0 ? '#16a34a' : '#6b7280') }}; font-variant-numeric:tabular-nums;">
                                            {{ $diff >= 0 ? '+' : '' }}${{ number_format(abs($diff), 2) }}
                                        </span>
                                    @else
                                        <span style="color:#d1d5db;">—</span>
                                    @endif
                                </td>

                                {{-- Change % + arrow --}}
                                <td style="padding:13px 20px 13px 12px; text-align:right; vertical-align:middle; white-space:nowrap;">
                                    @if ($isNew)
                                        <span style="display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:600; padding:3px 9px; border-radius:20px; background:rgba(37,99,235,0.08); color:#2563eb; border:1px solid rgba(37,99,235,0.2);">
                                            New
                                        </span>
                                    @elseif ($row->change_pct !== null)
                                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; padding:3px 9px; border-radius:20px; background:{{ $pctBg }}; color:{{ $pctColor }};">
                                            @if ($isUp)
                                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                            @elseif ($isDown)
                                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                            @else
                                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                            @endif
                                            {{ number_format(abs($row->change_pct), 1) }}%
                                        </span>
                                    @else
                                        <span style="font-size:12px; color:#d1d5db;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                            <td style="padding:12px 12px 12px 20px; font-size:13px; font-weight:600; color:#374151;">Total</td>
                            <td style="padding:12px; text-align:right; font-size:14px; font-weight:600; color:#374151; font-variant-numeric:tabular-nums;">${{ number_format($lastMonthTotal, 2) }}</td>
                            <td style="padding:12px; text-align:right; font-size:15px; font-weight:700; color:#111827; font-variant-numeric:tabular-nums;">${{ number_format($thisMonthTotal, 2) }}</td>
                            <td style="padding:12px; text-align:right; font-size:13px; font-weight:600; color:{{ ($thisMonthTotal - $lastMonthTotal) > 0 ? '#dc2626' : (($thisMonthTotal - $lastMonthTotal) < 0 ? '#16a34a' : '#6b7280') }}; font-variant-numeric:tabular-nums;">
                                @php $totalDiff = $thisMonthTotal - $lastMonthTotal; @endphp
                                {{ $totalDiff >= 0 ? '+' : '' }}${{ number_format(abs($totalDiff), 2) }}
                            </td>
                            <td style="padding:12px 20px 12px 12px; text-align:right;">
                                @if ($overallChange !== null)
                                    <span style="font-size:12px; font-weight:700; color:{{ $overallChange > 0.5 ? '#dc2626' : ($overallChange < -0.5 ? '#16a34a' : '#6b7280') }}; font-variant-numeric:tabular-nums;">
                                        {{ $overallChange > 0 ? '+' : '' }}{{ number_format($overallChange, 1) }}%
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
