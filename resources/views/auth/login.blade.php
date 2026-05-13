<x-guest-layout>
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 mb-1">Welcome back</h1>
        <p class="text-sm text-zinc-500 mb-8">Sign in to your account to continue</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="email" class="text-sm font-medium text-zinc-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="input w-full @error('email') border-red-400 @enderror"
                   placeholder="you@example.com">
            @error('email')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-medium text-zinc-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-zinc-500 hover:text-zinc-900 transition-colors">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password" required
                   class="input w-full @error('password') border-red-400 @enderror"
                   placeholder="••••••••••••">
            @error('password')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 w-4 h-4">
            <label for="remember_me" class="text-sm text-zinc-600">Remember me</label>
        </div>

        <button type="submit"
                class="w-full bg-primary text-primary-foreground font-medium py-2.5 px-4 rounded-md text-sm hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
            Sign in
        </button>

        @if (Route::has('register'))
            <p class="text-center text-sm text-zinc-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-zinc-900 hover:underline">Sign up</a>
            </p>
        @endif
    </form>
</x-guest-layout>
