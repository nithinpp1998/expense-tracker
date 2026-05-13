<x-guest-layout>
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900 mb-1">Create an account</h1>
        <p class="text-sm text-zinc-500 mb-8">Start tracking your expenses today</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="name" class="text-sm font-medium text-zinc-700">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="input w-full @error('name') border-red-400 @enderror"
                   placeholder="John Doe">
            @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="email" class="text-sm font-medium text-zinc-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="input w-full @error('email') border-red-400 @enderror"
                   placeholder="you@example.com">
            @error('email') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="password" class="text-sm font-medium text-zinc-700">Password</label>
            <input id="password" type="password" name="password" required
                   class="input w-full @error('password') border-red-400 @enderror"
                   placeholder="Min. 12 characters">
            @error('password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="password_confirmation" class="text-sm font-medium text-zinc-700">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="input w-full"
                   placeholder="Re-enter password">
        </div>

        <button type="submit"
                class="w-full bg-primary text-primary-foreground font-medium py-2.5 px-4 rounded-md text-sm hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
            Create account
        </button>

        <p class="text-center text-sm text-zinc-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-zinc-900 hover:underline">Sign in</a>
        </p>
    </form>
</x-guest-layout>
