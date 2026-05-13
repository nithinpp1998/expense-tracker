<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Expense Tracker') }} — {{ $title ?? 'Dashboard' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-sans antialiased" x-data>

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="hidden md:flex w-64 flex-col bg-zinc-950 text-zinc-100 fixed inset-y-0 left-0 z-50">
        {{-- Logo --}}
        <div class="flex items-center gap-2 px-6 h-16 border-b border-zinc-800">
            <div class="flex items-center justify-center w-8 h-8 rounded-md bg-white text-zinc-950 font-bold text-sm">ET</div>
            <span class="font-semibold text-white text-sm">Expense Tracker</span>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5">
            @php
                $navItems = [
                    ['route' => 'dashboard',                'icon' => 'grid',  'label' => 'Dashboard'],
                    ['route' => 'expenses.index',           'icon' => 'list',  'label' => 'Expenses'],
                    ['route' => 'expenses.create',          'icon' => 'plus',  'label' => 'Add Expense'],
                    ['route' => 'categories.index',         'icon' => 'tag',   'label' => 'Categories', 'active_pattern' => 'categories.*'],
                    ['route' => 'reports.monthly-category', 'icon' => 'pie',   'label' => 'Monthly Report'],
                    ['route' => 'reports.monthly-average',  'icon' => 'bar',   'label' => 'Daily Average'],
                    ['route' => 'reports.lifetime',         'icon' => 'chart', 'label' => 'Lifetime Report'],
                ];
            @endphp
            @foreach ($navItems as $item)
                @php $active = request()->routeIs($item['active_pattern'] ?? $item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                          {{ $active
                              ? 'bg-zinc-800 text-white'
                              : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}">
                    @if ($item['icon'] === 'grid')
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    @elseif ($item['icon'] === 'list')
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    @elseif ($item['icon'] === 'plus')
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    @elseif ($item['icon'] === 'tag')
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    @elseif ($item['icon'] === 'pie')
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    @elseif ($item['icon'] === 'bar')
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    @else
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- User section --}}
        <div class="p-4 border-t border-zinc-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-zinc-700 flex items-center justify-center text-xs font-semibold text-zinc-300 shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-zinc-500 hover:text-zinc-200 transition-colors" title="Sign out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Mobile top bar --}}
    <div class="md:hidden fixed top-0 inset-x-0 z-40 h-14 bg-zinc-950 flex items-center justify-between px-4">
        <span class="font-semibold text-white text-sm">Expense Tracker</span>
        <a href="{{ route('expenses.create') }}"
           class="text-xs font-medium text-white bg-zinc-700 hover:bg-zinc-600 px-3 py-1.5 rounded-md transition-colors">
            + Add
        </a>
    </div>

    {{-- Main content --}}
    <main class="flex-1 md:ml-64 flex flex-col min-h-screen">
        {{-- Top header (desktop) --}}
        <header class="hidden md:flex items-center justify-between h-16 px-8 bg-white border-b border-border sticky top-0 z-30">
            <div>
                <h1 class="text-xl font-semibold text-foreground" style="letter-spacing:-0.02em;">{{ $title ?? 'Dashboard' }}</h1>
                @isset($subtitle)
                    <p style="font-size:13px; color:#6b7280; margin-top:1px;">{{ $subtitle }}</p>
                @endisset
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('expenses.create') }}"
                   class="inline-flex items-center gap-1.5 bg-primary text-primary-foreground text-sm font-medium px-4 py-2 rounded-md hover:bg-zinc-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Expense
                </a>
            </div>
        </header>

        {{-- Page content --}}
        <div class="flex-1 p-4 md:p-8 pt-18 md:pt-8">
            @if (session('success'))
                <div class="mb-6 flex items-center gap-3 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="alert">
                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 flex items-center gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>

</body>
</html>
