<x-app-layout>
    <x-slot name="title">Monthly Category Report</x-slot>
    <x-slot name="subtitle">Spending breakdown by category</x-slot>

    {{-- ── Period selector ─────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; margin-bottom:16px;">
        <form method="GET" action="{{ route('reports.monthly-category') }}">
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

    @if ($data->isEmpty())
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:48px 24px; text-align:center;">
            <p style="font-size:13px; color:#9ca3af; margin:0;">No expenses recorded for {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}.</p>
        </div>
    @else
        @php $total = $data->sum(fn($r) => (float)$r->total); @endphp

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

            {{-- Donut chart card --}}
            <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
                <h2 style="font-size:14px; font-weight:600; color:#111827; margin:0 0 2px;">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</h2>
                <p style="font-size:26px; font-weight:700; color:#111827; margin:0 0 16px; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">₹{{ number_format($total, 2) }}</p>
                <div style="position:relative; height:256px; display:flex; align-items:center; justify-content:center;">
                    <canvas id="reportChart"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new Chart(document.getElementById('reportChart'), {
                            type: 'doughnut',
                            data: {
                                labels: {!! json_encode($data->pluck('category.name')) !!},
                                datasets: [{
                                    data: {!! json_encode($data->pluck('total')->map(fn($v) => round((float)$v, 2))) !!},
                                    backgroundColor: {!! json_encode($data->pluck('category.color')) !!},
                                    borderWidth: 2, borderColor: '#fff',
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } } },
                                cutout: '62%',
                            }
                        });
                    });
                </script>
            </div>

            {{-- Breakdown table card --}}
            <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:15px; font-weight:600; color:#111827; margin:0;">Category Breakdown</h2>
                    <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">{{ $data->count() }} categories</p>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                                <th style="text-align:left; padding:10px 12px 10px 20px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Category</th>
                                <th style="text-align:right; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Total</th>
                                <th style="text-align:right; padding:10px 20px 10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data->sortByDesc('total') as $row)
                                @php $share = $total > 0 ? ((float)$row->total / $total) * 100 : 0; @endphp
                                <tr style="border-bottom:1px solid #f3f4f6; transition:background 120ms;"
                                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                                    <td style="padding:13px 12px 13px 20px; vertical-align:middle;">
                                        <span style="display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:#111827;">
                                            <span style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:{{ $row->category?->color }};"></span>
                                            {{ $row->category?->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td style="padding:13px 12px; vertical-align:middle; text-align:right; font-size:15px; font-weight:700; color:#111827; white-space:nowrap; font-variant-numeric:tabular-nums;">
                                        ₹{{ number_format((float)$row->total, 2) }}
                                    </td>
                                    <td style="padding:13px 20px 13px 12px; vertical-align:middle; text-align:right;">
                                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                                            <div style="width:52px; height:5px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                                                <div style="height:100%; border-radius:99px; background:{{ $row->category?->color }}; width:{{ $share }}%;"></div>
                                            </div>
                                            <span style="font-size:12px; color:#6b7280; width:36px; text-align:right;">{{ number_format($share, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 20px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:13px; font-weight:600; color:#6b7280;">Total</span>
                    <span style="font-size:15px; font-weight:700; color:#111827; font-variant-numeric:tabular-nums;">₹{{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
