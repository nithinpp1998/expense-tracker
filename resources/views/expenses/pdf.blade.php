<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Expenses Export</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            background: #ffffff;
            padding: 32px 36px;
        }

        /* ── Header ───────────────────────────────────────────────────── */
        .header {
            display: block;
            border-bottom: 2px solid #111827;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header-top {
            display: block;
            margin-bottom: 8px;
        }
        .app-name {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            letter-spacing: -0.02em;
        }
        .report-title {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-top: 2px;
        }
        .meta-row {
            font-size: 10px;
            color: #6b7280;
            margin-top: 6px;
        }

        /* ── Filter summary ───────────────────────────────────────────── */
        .filters {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 9px 14px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #6b7280;
        }
        .filters strong {
            color: #374151;
        }

        /* ── Table ────────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
        }
        th {
            text-align: left;
            padding: 8px 10px;
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        th.right { text-align: right; }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }
        tbody tr:nth-child(even) {
            background: #fafafa;
        }
        td {
            padding: 8px 10px;
            font-size: 11px;
            color: #111827;
            vertical-align: middle;
        }
        td.right {
            text-align: right;
        }
        td.muted {
            color: #6b7280;
        }

        /* Category pill ─────────────────────────────────────────────── */
        .cat-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 500;
        }

        /* Amount ────────────────────────────────────────────────────── */
        .amount {
            font-weight: 700;
            font-size: 12px;
        }

        /* ── Totals row ───────────────────────────────────────────────── */
        .totals-row td {
            border-top: 2px solid #111827;
            padding-top: 10px;
            font-weight: 700;
            font-size: 12px;
        }

        /* ── Footer ───────────────────────────────────────────────────── */
        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-top">
            <div class="app-name">Expense Tracker</div>
            <div class="report-title">Expenses Export</div>
        </div>
        <div class="meta-row">
            Generated on {{ now()->format('F j, Y \a\t g:i A') }}
            &nbsp;·&nbsp; {{ $user->name }}
            &nbsp;·&nbsp; {{ $expenses->count() }} record(s)
        </div>
    </div>

    {{-- ── Active filters summary ───────────────────────────────────────── --}}
    @if ($filters['search'] || $filters['category'] || $filters['from'] || $filters['to'])
        <div class="filters">
            <strong>Filters applied:</strong>
            @if ($filters['search'])
                &nbsp; Search: "{{ $filters['search'] }}"
            @endif
            @if ($filters['category'])
                &nbsp; Category: {{ $filters['category'] }}
            @endif
            @if ($filters['from'] || $filters['to'])
                &nbsp; Date: {{ $filters['from'] ?? '—' }} → {{ $filters['to'] ?? '—' }}
            @endif
        </div>
    @endif

    {{-- ── Expense table ────────────────────────────────────────────────── --}}
    @if ($expenses->isEmpty())
        <p style="text-align:center; padding:40px 0; color:#9ca3af; font-size:12px;">
            No expenses match the selected filters.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:18%;">Date</th>
                    <th style="width:42%;">Description</th>
                    <th style="width:22%;">Category</th>
                    <th class="right" style="width:18%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                <tr>
                    <td class="muted" style="font-family:monospace; font-size:10px;">
                        {{ $expense->occurred_at->format('M j, Y') }}
                    </td>
                    <td>{{ $expense->description }}</td>
                    <td>
                        @if ($expense->category)
                            <span class="cat-pill"
                                  style="background-color: {{ $expense->category->color }}22;
                                         color: {{ $expense->category->color }};
                                         border: 1px solid {{ $expense->category->color }}44;">
                                {{ $expense->category->name }}
                            </span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="right amount">
                        ${{ number_format((float) $expense->amount, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="3" style="color:#374151; font-size:11px;">Total ({{ $expenses->count() }} expenses)</td>
                    <td class="right" style="color:#111827; font-size:13px;">
                        ${{ number_format($expenses->sum(fn ($e) => (float) $e->amount), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ── Footer ───────────────────────────────────────────────────────── --}}
    <div class="footer">
        Expense Tracker &nbsp;·&nbsp; Confidential &nbsp;·&nbsp; {{ now()->format('Y') }}
    </div>

</body>
</html>
