<x-app-layout>
    <x-slot name="title">Add Category</x-slot>
    <x-slot name="subtitle">Create a new expense category</x-slot>

    <div class="max-w-lg">
        <div class="card">
            <div class="px-6 py-5 border-b border-border">
                <h2 class="text-sm font-semibold text-foreground">Category Details</h2>
            </div>
            <form method="POST" action="{{ route('categories.store') }}" class="p-6 space-y-5">
                @csrf

                {{-- Name --}}
                <div class="space-y-1.5">
                    <label for="name" class="text-sm font-medium text-zinc-700">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                           maxlength="100" autofocus
                           class="input w-full @error('name') border-red-400 @enderror"
                           placeholder="e.g. Groceries">
                    @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Color --}}
                <div class="space-y-1.5">
                    <label for="color" class="text-sm font-medium text-zinc-700">Color</label>
                    <div class="flex items-center gap-2">
                        <input id="color" type="color" name="color"
                               value="{{ old('color', '#6b7280') }}"
                               class="h-10 w-12 rounded-md border border-input cursor-pointer p-0.5 @error('color') border-red-400 @enderror">
                        <input type="text" id="color_hex" value="{{ old('color', '#6b7280') }}"
                               class="input flex-1 font-mono text-sm"
                               placeholder="#6b7280" maxlength="7"
                    </div>
                    @error('color') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-zinc-700">Status</label>
                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative" x-data="{ on: {{ old('is_active', '1') === '1' ? 'true' : 'false' }} }">
                            <input type="hidden" name="is_active" :value="on ? '1' : '0'">
                            <button type="button"
                                    @click="on = !on"
                                    :class="on ? 'bg-zinc-900' : 'bg-zinc-200'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                <span :class="on ? 'translate-x-6' : 'translate-x-1'"
                                      class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </div>
                        <span class="text-sm text-zinc-700">Active</span>
                    </label>
                    <p class="text-xs text-muted-foreground">Inactive categories won't appear in expense forms.</p>
                </div>

<div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-primary text-primary-foreground font-medium py-2.5 px-6 rounded-md text-sm hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        Save category
                    </button>
                    <a href="{{ route('categories.index') }}"
                       class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('color').addEventListener('input', e => {
            document.getElementById('color_hex').value = e.target.value;
        });
        document.getElementById('color_hex').addEventListener('input', e => {
            if (/^#[0-9a-fA-F]{6}$/.test(e.target.value)) {
                document.getElementById('color').value = e.target.value;
            }
        });
    </script>
</x-app-layout>
