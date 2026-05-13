<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Expense Tracker') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-50 font-sans antialiased">

<div class="min-h-screen flex">
    {{-- Left panel (decorative, desktop only) --}}
    <div class="hidden lg:flex lg:w-1/2 bg-zinc-950 flex-col justify-between p-12">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-md bg-white text-zinc-950 flex items-center justify-center font-bold text-sm">ET</div>
            <span class="text-white font-semibold text-sm">Expense Tracker</span>
        </div>
        <div>
            <blockquote class="text-zinc-300 text-xl font-light leading-relaxed mb-6">
                "Take control of your finances with clear, simple expense tracking and insightful reports."
            </blockquote>
            <div class="flex gap-6 text-zinc-500 text-sm">
                <div>
                    <div class="text-2xl font-bold text-white mb-1">12</div>
                    <div>categories</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white mb-1">3</div>
                    <div>report types</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white mb-1">REST</div>
                    <div>API</div>
                </div>
            </div>
        </div>
        <p class="text-zinc-600 text-xs">© {{ date('Y') }} Expense Tracker. Built with Laravel.</p>
    </div>

    {{-- Right panel (form) --}}
    <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center lg:text-left">
                <div class="lg:hidden flex items-center justify-center gap-2 mb-6">
                    <div class="w-8 h-8 rounded-md bg-zinc-950 text-white flex items-center justify-center font-bold text-sm">ET</div>
                    <span class="font-semibold text-zinc-900 text-sm">Expense Tracker</span>
                </div>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>

</body>
</html>
