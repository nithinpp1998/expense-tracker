<x-app-layout>
    <x-slot name="title">Edit Category</x-slot>
    <x-slot name="subtitle">Update category details</x-slot>

    <div class="max-w-lg">
        <div class="card">
            <div class="px-6 py-5 border-b border-border">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-foreground" style="font-size:15px;">Category Details</h2>
                    @if ($category->is_system)
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">
                            System category
                        </span>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('categories.update', $category) }}" class="p-6 space-y-5">
                @csrf
                @method('PATCH')

                {{-- Name --}}
                <div class="space-y-1.5">
                    <label for="name" class="font-medium text-zinc-700" style="font-size:13px;">Name</label>
                    <input id="name" type="text" name="name"
                           value="{{ old('name', $category->name) }}" required
                           maxlength="100" autofocus
                           class="input w-full @error('name') border-red-400 @enderror"
                           placeholder="e.g. Groceries">
                    @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Color --}}
                <div class="space-y-1.5">
                    <label for="color" class="font-medium text-zinc-700" style="font-size:13px;">Color</label>
                    <div class="flex items-center gap-2">
                        <input id="color" type="color" name="color"
                               value="{{ old('color', $category->color) }}"
                               class="h-10 w-12 rounded-md border border-input cursor-pointer p-0.5 @error('color') border-red-400 @enderror">
                        <input type="text" id="color_hex"
                               value="{{ old('color', $category->color) }}"
                               class="input flex-1 font-mono text-sm"
                               placeholder="#6b7280" maxlength="7"
                    </div>
                    @error('color') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div class="space-y-1.5">
                    <label class="font-medium text-zinc-700" style="font-size:13px;">Status</label>
                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                        @php $isActive = old('is_active', $category->is_active ? '1' : '0') === '1'; @endphp
                        <div class="relative" x-data="{ on: {{ $isActive ? 'true' : 'false' }} }">
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
                        Update category
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
