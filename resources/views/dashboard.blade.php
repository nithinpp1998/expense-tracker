<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="subtitle">
        @if ($fromStr === now()->startOfMonth()->toDateString() && $toStr === now()->toDateString())
            {{ now()->format('F Y') }} overview
        @else
            {{ \Carbon\Carbon::parse($fromStr)->format('M j, Y') }}
            @if ($fromStr !== $toStr)
                — {{ \Carbon\Carbon::parse($toStr)->format('M j, Y') }}
            @endif
        @endif
    </x-slot>

    {{-- ── Date-range filter ───────────────────────────────────────────── --}}
    @php
        $today  = now();
        $lastMo = $today->copy()->subMonthNoOverflow();
        $presets = [
            'This Month' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'Last Month'  => [$lastMo->copy()->startOfMonth()->toDateString(), $lastMo->copy()->endOfMonth()->toDateString()],
            'Last 30 d'   => [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
            'Last 90 d'   => [$today->copy()->subDays(89)->toDateString(), $today->toDateString()],
            'This Year'   => [$today->copy()->startOfYear()->toDateString(), $today->toDateString()],
        ];
    @endphp

    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 20px; margin-bottom:20px;">
        <form method="GET" action="{{ route('dashboard') }}">
            <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">

                {{-- From --}}
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label for="dash-from" style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">From</label>
                    <input id="dash-from" type="date" name="from" value="{{ $fromStr }}"
                           max="{{ now()->toDateString() }}"
                           style="height:34px; padding:0 10px; background:#fff; border:1px solid #d1d5db; color:#111827; font-size:13px; border-radius:7px; outline:none; box-sizing:border-box;"
                           onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                {{-- To --}}
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label for="dash-to" style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.04em; text-transform:uppercase;">To</label>
                    <input id="dash-to" type="date" name="to" value="{{ $toStr }}"
                           max="{{ now()->toDateString() }}"
                           style="height:34px; padding:0 10px; background:#fff; border:1px solid #d1d5db; color:#111827; font-size:13px; border-radius:7px; outline:none; box-sizing:border-box;"
                           onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#d1d5db'">
                </div>

                {{-- Apply --}}
                <button type="submit"
                        style="height:34px; padding:0 16px; background:#111827; color:#fff; font-size:13px; font-weight:500; border-radius:7px; border:none; cursor:pointer; transition:background 150ms; white-space:nowrap; align-self:flex-end;"
                        onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                    Apply
                </button>

                {{-- Separator --}}
                <div style="width:1px; height:34px; background:#e5e7eb; align-self:flex-end; margin:0 2px;"></div>

                {{-- Preset chips --}}
                <div style="display:flex; gap:6px; align-items:flex-end; flex-wrap:wrap;">
                    @foreach ($presets as $label => [$pFrom, $pTo])
                        @php $active = ($fromStr === $pFrom && $toStr === $pTo); @endphp
                        <a href="{{ route('dashboard', ['from' => $pFrom, 'to' => $pTo]) }}"
                           style="height:34px; padding:0 13px; display:inline-flex; align-items:center; border-radius:7px; font-size:12px; font-weight:500; text-decoration:none; white-space:nowrap; transition:all 120ms;
                                  border:1px solid {{ $active ? '#111827' : '#e5e7eb' }};
                                  background:{{ $active ? '#111827' : '#ffffff' }};
                                  color:{{ $active ? '#ffffff' : '#374151' }};"
                           @unless($active)
                               onmouseover="this.style.background='#f3f4f6'"
                               onmouseout="this.style.background='#ffffff'"
                           @endunless>
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

            </div>
        </form>
    </div>

    {{-- ── Stats row ────────────────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">

        {{-- Period total --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 6px;">
                @if ($days === 1) Today @elseif ($days <= 31) This Period @else Total Spend @endif
            </p>
            <p style="font-size:30px; font-weight:700; color:#111827; margin:0 0 4px; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">
                ₹{{ number_format($monthTotal, 2) }}
            </p>
            <p style="font-size:13px; color:#9ca3af; margin:0;">
                {{ $monthly->count() }} {{ Str::plural('category', $monthly->count()) }}
            </p>
        </div>

        {{-- Daily average --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 6px;">Daily Average</p>
            <p style="font-size:30px; font-weight:700; color:#111827; margin:0 0 4px; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">
                ₹{{ number_format($average, 2) }}
            </p>
            <p style="font-size:13px; color:#9ca3af; margin:0;">
                over {{ $days }} {{ Str::plural('day', $days) }}
            </p>
        </div>

        {{-- All time --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 6px;">All Time</p>
            <p style="font-size:30px; font-weight:700; color:#111827; margin:0 0 4px; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">
                ₹{{ number_format($lifetime->sum(fn($r) => (float)$r->total), 2) }}
            </p>
            <p style="font-size:13px; color:#9ca3af; margin:0;">{{ $lifetime->count() }} {{ Str::plural('category', $lifetime->count()) }}</p>
        </div>
    </div>

    {{-- ── Chart + Top categories ────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">

        {{-- Donut chart --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <h2 style="font-size:14px; font-weight:600; color:#111827; margin:0 0 16px;">
                Spending by Category
                <span style="font-size:12px; font-weight:400; color:#9ca3af; margin-left:6px;">
                    @if ($fromStr === $toStr)
                        {{ \Carbon\Carbon::parse($fromStr)->format('M j') }}
                    @else
                        {{ \Carbon\Carbon::parse($fromStr)->format('M j') }} – {{ \Carbon\Carbon::parse($toStr)->format('M j') }}
                    @endif
                </span>
            </h2>
            @if ($monthly->isEmpty())
                <div style="display:flex; align-items:center; justify-content:center; height:192px; font-size:13px; color:#9ca3af;">
                    No expenses for this period.
                </div>
            @else
                <div style="position:relative; display:flex; align-items:center; justify-content:center; height:192px;">
                    <canvas id="categoryChart" style="max-height:192px;"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new Chart(document.getElementById('categoryChart').getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: {!! json_encode($monthly->pluck('category.name')) !!},
                                datasets: [{
                                    data: {!! json_encode($monthly->pluck('total')->map(fn($v) => round((float)$v, 2))) !!},
                                    backgroundColor: {!! json_encode($monthly->pluck('category.color')) !!},
                                    borderWidth: 2, borderColor: '#fff',
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } },
                                cutout: '65%',
                            }
                        });
                    });
                </script>
            @endif
        </div>

        {{-- Top categories --}}
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
            <h2 style="font-size:14px; font-weight:600; color:#111827; margin:0 0 16px;">Top Categories</h2>
            @if ($monthly->isEmpty())
                <p style="font-size:13px; color:#9ca3af;">No data for this period.</p>
            @else
                @php $topCats = $monthly->sortByDesc('total')->take(5); $catMax = (float)$topCats->first()?->total ?: 1; @endphp
                <div style="display:flex; flex-direction:column; gap:14px;">
                    @foreach ($topCats as $row)
                        @php $pct = (float)$row->total / $catMax * 100; @endphp
                        <div>
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:5px;">
                                <span style="display:flex; align-items:center; gap:7px; font-size:12px; font-weight:500; color:#111827;">
                                    <span style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:{{ $row->category?->color ?? '#71717a' }};"></span>
                                    {{ $row->category?->name ?? 'Unknown' }}
                                </span>
                                <span style="font-size:13px; font-weight:600; color:#374151; font-variant-numeric:tabular-nums;">₹{{ number_format((float)$row->total, 2) }}</span>
                            </div>
                            <div style="height:5px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                                <div style="height:100%; border-radius:99px; background:{{ $row->category?->color ?? '#71717a' }}; width:{{ $pct }}%; transition:width 500ms;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Recent expenses table ─────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">

        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #e5e7eb;">
            <div>
                <h2 style="font-size:15px; font-weight:600; color:#111827; margin:0;">Recent Expenses</h2>
                <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">Latest activity</p>
            </div>
            <a href="{{ route('expenses.index') }}"
               style="font-size:12px; color:#6b7280; text-decoration:none; transition:color 150ms;"
               onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">
                View all →
            </a>
        </div>

        @if ($recent->isEmpty())
            <div style="padding:48px 24px; text-align:center;">
                <p style="font-size:13px; color:#9ca3af; margin:0 0 12px;">No expenses yet.</p>
                <a href="{{ route('expenses.create') }}"
                   style="display:inline-flex; align-items:center; gap:6px; background:#111827; color:#fff; font-size:12px; font-weight:500; padding:8px 16px; border-radius:7px; text-decoration:none; transition:background 150ms;"
                   onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                    Add your first expense
                </a>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:500px;">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                            <th style="text-align:left; padding:10px 12px 10px 20px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Description</th>
                            <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Category</th>
                            <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Date</th>
                            <th style="text-align:right; padding:10px 20px 10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent as $expense)
                        <tr style="border-bottom:1px solid #f3f4f6; transition:background 120ms;"
                            onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                            <td style="padding:13px 12px 13px 20px; vertical-align:middle; max-width:220px;">
                                <span style="font-size:14px; font-weight:600; color:#111827; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    {{ $expense->description }}
                                </span>
                            </td>
                            <td style="padding:13px 12px; vertical-align:middle;">
                                @if ($expense->category)
                                    <span style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:500;">
                                        <span style="width:7px; height:7px; border-radius:50%; flex-shrink:0; display:inline-block; background:{{ $expense->category->color }};"></span>
                                        <span style="color:#374151;">{{ $expense->category->name }}</span>
                                    </span>
                                @endif
                            </td>
                            <td style="padding:13px 12px; vertical-align:middle; white-space:nowrap;">
                                <span style="font-size:12px; color:#6b7280; font-family:ui-monospace,monospace; background:#f3f4f6; padding:2px 8px; border-radius:5px; border:1px solid #e5e7eb;">
                                    {{ $expense->occurred_at->format('M j, Y') }}
                                </span>
                            </td>
                            <td style="padding:13px 20px 13px 12px; vertical-align:middle; text-align:right; white-space:nowrap;">
                                <span style="font-size:15px; font-weight:700; color:#111827; font-variant-numeric:tabular-nums;">₹{{ number_format((float)$expense->amount, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
