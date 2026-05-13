<x-app-layout>
    <x-slot name="title">Daily Average Report</x-slot>
    <x-slot name="subtitle">Average daily spending per month</x-slot>

    {{-- ── Period selector ─────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; margin-bottom:16px;">
        <form method="GET" action="{{ route('reports.monthly-average') }}">
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
                    <label style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">Month</label>
                    <select name="month" onchange="this.form.submit()"
                            style="height:36px; min-width:140px; background:#ffffff; border:1px solid #d1d5db; border-radius:7px; padding:0 28px 0 10px; font-size:13px; color:#111827; cursor:pointer; outline:none; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 9px center; background-size:13px;">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($month == $m)>{{ \Carbon\Carbon::create(null, $m)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        style="height:36px; padding:0 16px; background:#111827; color:#fff; font-size:13px; font-weight:500; border-radius:7px; border:none; cursor:pointer; transition:background 150ms; align-self:flex-end;"
                        onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                    View Report
                </button>
            </div>
        </form>
    </div>

    {{-- ── Stat cards ────────────────────────────────────────────────────── --}}
    @php $days = \Carbon\Carbon::create($year, $month)->daysInMonth; @endphp
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; max-width:640px;">

        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:28px 24px; text-align:center;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 10px;">Daily Average</p>
            <p style="font-size:36px; font-weight:700; color:#111827; margin:0 0 6px; letter-spacing:-0.03em; font-variant-numeric:tabular-nums;">₹{{ number_format($average, 2) }}</p>
            <p style="font-size:13px; color:#9ca3af; margin:0;">per day in {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</p>
        </div>

        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:28px 24px; text-align:center;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 10px;">Monthly Total</p>
            <p style="font-size:36px; font-weight:700; color:#111827; margin:0 0 6px; letter-spacing:-0.03em; font-variant-numeric:tabular-nums;">₹{{ number_format($average * $days, 2) }}</p>
            <p style="font-size:13px; color:#9ca3af; margin:0;">{{ $days }} days × ₹{{ number_format($average, 2) }}/day</p>
        </div>
    </div>

    {{-- ── Summary note ──────────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px; max-width:640px;">
        <p style="font-size:13px; color:#6b7280; line-height:1.6; margin:0;">
            In <strong style="color:#111827;">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</strong>,
            your average daily spending was
            <strong style="color:#111827;">₹{{ number_format($average, 2) }}</strong>.
            This is the total monthly spend divided by the number of days in the month ({{ $days }} days).
        </p>
        @if ($average === 0.0)
            <p style="font-size:13px; color:#9ca3af; margin:8px 0 0;">No expenses were recorded for this period.</p>
        @endif
    </div>
</x-app-layout>
