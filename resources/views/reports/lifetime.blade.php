<x-app-layout>
    <x-slot name="title">Lifetime Report</x-slot>
    <x-slot name="subtitle">All-time spending totals by category</x-slot>

    @if ($data->isEmpty())
        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:48px 24px; text-align:center;">
            <p style="font-size:13px; color:#9ca3af; margin:0 0 12px;">No expenses recorded yet.</p>
            <a href="{{ route('expenses.create') }}"
               style="display:inline-flex; align-items:center; gap:6px; background:#111827; color:#fff; font-size:12px; font-weight:500; padding:8px 16px; border-radius:7px; text-decoration:none; transition:background 150ms;"
               onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                Add your first expense
            </a>
        </div>
    @else
        @php $total = $data->sum(fn($r) => (float)$r->total); @endphp

        <div style="display:grid; grid-template-columns:1fr 2fr; gap:16px;">

            {{-- Summary + chart card --}}
            <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px;">
                <p style="font-size:11px; font-weight:600; color:#6b7280; letter-spacing:0.06em; text-transform:uppercase; margin:0 0 6px;">All-Time Total</p>
                <p style="font-size:30px; font-weight:700; color:#111827; margin:0 0 2px; letter-spacing:-0.02em; font-variant-numeric:tabular-nums;">₹{{ number_format($total, 2) }}</p>
                <p style="font-size:13px; color:#9ca3af; margin:0 0 20px;">across {{ $data->count() }} categories</p>

                <div style="position:relative; height:192px; display:flex; align-items:center; justify-content:center;">
                    <canvas id="lifetimeChart"></canvas>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new Chart(document.getElementById('lifetimeChart'), {
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
                                plugins: { legend: { display: false } },
                                cutout: '60%',
                            }
                        });
                    });
                </script>
            </div>

            {{-- Breakdown table card --}}
            <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb;">
                    <h2 style="font-size:15px; font-weight:600; color:#111827; margin:0;">Category Breakdown</h2>
                    <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">{{ $data->count() }} categories · all time</p>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                                <th style="text-align:left; padding:10px 12px 10px 20px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Category</th>
                                <th style="text-align:right; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Total Spent</th>
                                <th style="text-align:right; padding:10px 20px 10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data->sortByDesc('total') as $row)
                                @php $share = $total > 0 ? ((float)$row->total / $total) * 100 : 0; @endphp
                                <tr style="border-bottom:1px solid #f3f4f6; transition:background 120ms;"
                                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                                    <td style="padding:13px 12px 13px 20px; vertical-align:middle;">
                                        <span style="display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:#111827;">
                                            <span style="width:9px; height:9px; border-radius:50%; flex-shrink:0; background:{{ $row->category?->color }};"></span>
                                            {{ $row->category?->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td style="padding:13px 12px; vertical-align:middle; text-align:right; font-size:15px; font-weight:700; color:#111827; white-space:nowrap; font-variant-numeric:tabular-nums;">
                                        ₹{{ number_format((float)$row->total, 2) }}
                                    </td>
                                    <td style="padding:13px 20px 13px 12px; vertical-align:middle; text-align:right;">
                                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                                            <div style="width:64px; height:5px; background:#f3f4f6; border-radius:99px; overflow:hidden;">
                                                <div style="height:100%; border-radius:99px; background:{{ $row->category?->color }}; width:{{ $share }}%;"></div>
                                            </div>
                                            <span style="font-size:12px; color:#6b7280; width:38px; text-align:right;">{{ number_format($share, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:12px 20px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:13px; font-weight:600; color:#6b7280;">Grand Total</span>
                    <span style="font-size:15px; font-weight:700; color:#111827; font-variant-numeric:tabular-nums;">₹{{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
