<x-app-layout>
    <x-slot name="title">Edit Expense</x-slot>
    <x-slot name="subtitle">Update expense details</x-slot>

    <div class="max-w-lg">
        <div class="card">
            <div class="px-6 py-5 border-b border-border">
                <h2 class="font-semibold text-foreground" style="font-size:15px;">Expense Details</h2>
            </div>
            <form method="POST" action="{{ route('expenses.update', $expense) }}" class="p-6 space-y-5">
                @csrf
                @method('PATCH')

                <div class="space-y-1.5">
                    <label for="description" class="font-medium text-zinc-700" style="font-size:13px;">Description</label>
                    <input id="description" type="text" name="description"
                           value="{{ old('description', $expense->description) }}" required maxlength="500"
                           class="input w-full @error('description') border-red-400 @enderror">
                    @error('description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="amount" class="font-medium text-zinc-700" style="font-size:13px;">Amount</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-muted-foreground text-sm pointer-events-none">$</span>
                            <input id="amount" type="number" name="amount"
                                   value="{{ old('amount', $expense->amount) }}" required
                                   step="0.01" min="0.01" max="99999999.99"
                                   class="input w-full pl-7 @error('amount') border-red-400 @enderror">
                        </div>
                        @error('amount') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="occurred_at" class="font-medium text-zinc-700" style="font-size:13px;">Date</label>
                        <input id="occurred_at" type="date" name="occurred_at"
                               value="{{ old('occurred_at', $expense->occurred_at->format('Y-m-d')) }}" required
                               max="{{ now()->format('Y-m-d') }}"
                               class="input w-full @error('occurred_at') border-red-400 @enderror">
                        @error('occurred_at') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="category_id" class="font-medium text-zinc-700" style="font-size:13px;">Category</label>
                    <select id="category_id" name="category_id" required
                            class="select w-full @error('category_id') border-red-400 @enderror">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                    @selected(old('category_id', $expense->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-primary text-primary-foreground font-medium py-2.5 px-6 rounded-md text-sm hover:bg-zinc-800 transition-colors">
                        Update expense
                    </button>
                    <a href="{{ route('expenses.index') }}"
                       class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
