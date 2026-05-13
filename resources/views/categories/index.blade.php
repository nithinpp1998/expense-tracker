<x-app-layout>
    <x-slot name="title">Categories</x-slot>
    <x-slot name="subtitle">Manage expense categories</x-slot>

    {{-- ── Table Card ─────────────────────────────────────────────────── --}}
    <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">

        {{-- Card header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #e5e7eb;">
            <div>
                <h2 style="font-size:15px; font-weight:600; color:#111827; margin:0;">All Categories</h2>
                <p style="font-size:13px; color:#6b7280; margin:2px 0 0;">{{ $categories->total() }} total</p>
            </div>
            <button type="button" onclick="openCatModal('create')"
                    style="display:inline-flex; align-items:center; gap:6px; background:#111827; color:#fff; font-size:12px; font-weight:500; padding:7px 14px; border-radius:7px; border:none; cursor:pointer; transition:background 150ms;"
                    onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Category
            </button>
        </div>

        @if ($categories->isEmpty())
            <div style="padding:64px 24px; text-align:center;">
                <svg style="width:40px; height:40px; color:#d1d5db; margin:0 auto 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p style="font-size:14px; font-weight:500; color:#111827; margin:0 0 4px;">No categories yet</p>
                <button type="button" onclick="openCatModal('create')"
                        style="font-size:12px; color:#6b7280; background:none; border:none; cursor:pointer; text-decoration:underline;">
                    Create your first category
                </button>
            </div>
        @else

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; min-width:640px;">
                <thead>
                    <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <th style="width:28px; padding:10px 8px 10px 16px;">
                            <input type="checkbox" id="select-all"
                                   style="width:14px; height:14px; accent-color:#111827; cursor:pointer; border-radius:3px;"
                                   onchange="toggleAllRows(this)">
                        </th>
                        <th style="width:28px; padding:10px 0;"></th>
                        <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em; white-space:nowrap;">Category</th>
                        <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Slug</th>
                        <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Status</th>
                        <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Type</th>
                        <th style="text-align:left; padding:10px 12px; font-size:12px; font-weight:600; color:#6b7280; letter-spacing:0.02em;">Color</th>
                        <th style="width:44px; padding:10px 16px 10px 8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                    <tr class="cat-row"
                        style="border-bottom:1px solid #f3f4f6; transition:background 120ms;"
                        onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">

                        {{-- Checkbox --}}
                        <td style="padding:13px 8px 13px 16px; vertical-align:middle;">
                            <input type="checkbox" class="row-check"
                                   style="width:14px; height:14px; accent-color:#111827; cursor:pointer; border-radius:3px;">
                        </td>

                        {{-- Drag handle --}}
                        <td style="padding:13px 4px; vertical-align:middle; cursor:grab;">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="#d1d5db">
                                <circle cx="5" cy="4" r="1.2"/><circle cx="5" cy="8" r="1.2"/><circle cx="5" cy="12" r="1.2"/>
                                <circle cx="11" cy="4" r="1.2"/><circle cx="11" cy="8" r="1.2"/><circle cx="11" cy="12" r="1.2"/>
                            </svg>
                        </td>

                        {{-- Category name + color dot --}}
                        <td style="padding:13px 12px; vertical-align:middle;">
                            <div style="display:flex; align-items:center; gap:9px;">
                                <span style="width:9px; height:9px; border-radius:50%; flex-shrink:0; display:inline-block; background:{{ $category->color }};"></span>
                                <span style="font-size:14px; font-weight:600; color:#111827; white-space:nowrap;">{{ $category->name }}</span>
                            </div>
                        </td>

                        {{-- Slug --}}
                        <td style="padding:13px 12px; vertical-align:middle;">
                            <span style="font-size:12px; color:#6b7280; font-family:ui-monospace,monospace; background:#f3f4f6; padding:2px 8px; border-radius:5px; border:1px solid #e5e7eb;">{{ $category->slug }}</span>
                        </td>

                        {{-- Status --}}
                        <td style="padding:13px 12px; vertical-align:middle;">
                            @if ($category->is_active)
                                <span style="display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:500; padding:3px 9px; border-radius:20px; background:rgba(34,197,94,0.1); color:#16a34a; border:1px solid rgba(34,197,94,0.2);">
                                    <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                                        <circle cx="6" cy="6" r="5" fill="#22c55e"/>
                                        <path d="M3.5 6l1.8 1.8 3-3.5" stroke="#fff" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Active
                                </span>
                            @else
                                <span style="display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:500; padding:3px 9px; border-radius:20px; background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb;">
                                    <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                                        <circle cx="6" cy="6" r="5" stroke="#9ca3af" stroke-width="1.2"/>
                                        <line x1="4.5" y1="6" x2="7.5" y2="6" stroke="#9ca3af" stroke-width="1.2" stroke-linecap="round"/>
                                    </svg>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Type --}}
                        <td style="padding:13px 12px; vertical-align:middle;">
                            @if ($category->is_system)
                                <span style="font-size:11.5px; font-weight:500; padding:3px 9px; border-radius:20px; background:rgba(59,130,246,0.08); color:#2563eb; border:1px solid rgba(59,130,246,0.2);">System</span>
                            @else
                                <span style="font-size:11.5px; font-weight:500; padding:3px 9px; border-radius:20px; background:rgba(168,85,247,0.08); color:#9333ea; border:1px solid rgba(168,85,247,0.2);">Custom</span>
                            @endif
                        </td>

                        {{-- Color swatch --}}
                        <td style="padding:13px 12px; vertical-align:middle;">
                            <span style="display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; font-family:ui-monospace,monospace;">
                                <span style="width:16px; height:16px; border-radius:4px; display:inline-block; border:1px solid rgba(0,0,0,0.08); flex-shrink:0; background:{{ $category->color }};"></span>
                                {{ $category->color }}
                            </span>
                        </td>

                        {{-- Actions dropdown --}}
                        <td style="padding:13px 16px 13px 8px; vertical-align:middle; text-align:right; position:relative;">
                            <div style="position:relative; display:inline-block;">
                                <button type="button"
                                        onclick="toggleRowMenu(event,'cat-{{ $category->id }}')"
                                        style="width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:none; background:transparent; color:#9ca3af; cursor:pointer; transition:all 120ms;"
                                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151'"
                                        onmouseout="this.style.background='transparent'; this.style.color='#9ca3af'">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>

                                <div id="cat-{{ $category->id }}"
                                     class="row-menu"
                                     style="display:none; position:absolute; right:0; top:calc(100% + 4px); z-index:40; min-width:148px; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; padding:4px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">

                                    {{-- Edit — opens modal --}}
                                    <button type="button"
                                            onclick="openCatModal('edit', {{ $category->id }}, @json($category->name), @json($category->color), {{ $category->is_active ? 'true' : 'false' }}, {{ $category->is_system ? 'true' : 'false' }}, '{{ route('categories.update', $category) }}')"
                                            style="display:flex; align-items:center; gap:8px; width:100%; padding:7px 10px; border-radius:5px; font-size:13px; color:#374151; background:transparent; border:none; cursor:pointer; text-align:left; transition:background 100ms;"
                                            onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>

                                    {{-- Activate / Deactivate --}}
                                    <form method="POST" action="{{ route('categories.toggle', $category) }}" style="margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                onclick="return confirm('{{ $category->is_active ? 'Deactivate' : 'Activate' }} this category?')"
                                                style="display:flex; align-items:center; gap:8px; width:100%; padding:7px 10px; border-radius:5px; font-size:13px; color:{{ $category->is_active ? '#d97706' : '#16a34a' }}; background:transparent; border:none; cursor:pointer; text-align:left; transition:background 100ms;"
                                                onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                                            @if ($category->is_active)
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                Deactivate
                                            @else
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Activate
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete — custom + not in use --}}
                                    @if (!$category->is_system && $category->expenses_count === 0)
                                        <div style="height:1px; background:#f3f4f6; margin:4px 0;"></div>
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Delete &quot;{{ $category->name }}&quot;? This cannot be undone.')"
                                                    style="display:flex; align-items:center; gap:8px; width:100%; padding:7px 10px; border-radius:5px; font-size:13px; color:#dc2626; background:transparent; border:none; cursor:pointer; text-align:left; transition:background 100ms;"
                                                    onmouseover="this.style.background='rgba(239,68,68,0.06)'" onmouseout="this.style.background='transparent'">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Delete
                                            </button>
                                        </form>

                                    {{-- Delete — custom but in use: disabled --}}
                                    @elseif (!$category->is_system && $category->expenses_count > 0)
                                        <div style="height:1px; background:#f3f4f6; margin:4px 0;"></div>
                                        <button type="button" disabled
                                                title="Used by {{ $category->expenses_count }} expense(s) — deactivate instead"
                                                style="display:flex; align-items:center; gap:8px; width:100%; padding:7px 10px; border-radius:5px; font-size:13px; color:#d1d5db; background:transparent; border:none; cursor:not-allowed; text-align:left;">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Pagination Bar ──────────────────────────────────────────── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-top:1px solid #e5e7eb; flex-wrap:wrap; gap:10px;">

            {{-- Left: selection count --}}
            <span id="selection-count" style="font-size:12px; color:#6b7280; white-space:nowrap;">
                0 of {{ $categories->total() }} row(s) selected.
            </span>

            {{-- Center + Right --}}
            <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">

                {{-- Rows per page --}}
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:12px; color:#6b7280; white-space:nowrap;">Rows per page</span>
                    <form method="GET" action="{{ route('categories.index') }}" id="per-page-form">
                        <select name="per_page" onchange="this.form.submit()"
                                style="background:#ffffff; border:1px solid #d1d5db; color:#374151; font-size:12px; padding:4px 24px 4px 8px; border-radius:6px; cursor:pointer; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='none' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 7px center; outline:none;">
                            @foreach ([5, 10, 20, 50] as $n)
                                <option value="{{ $n }}" {{ request()->input('per_page', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Page info --}}
                <span style="font-size:12px; color:#6b7280; white-space:nowrap;">
                    Page {{ $categories->currentPage() }} of {{ $categories->lastPage() }}
                </span>

                {{-- Navigation buttons --}}
                <div style="display:flex; align-items:center; gap:4px;">
                    @if ($categories->onFirstPage())
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">«</button>
                    @else
                        <a href="{{ $categories->url(1) . (request()->has('per_page') ? '&per_page='.request()->input('per_page') : '') }}"
                           style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:all 120ms;"
                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">«</a>
                    @endif

                    @if ($categories->onFirstPage())
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">‹</button>
                    @else
                        <a href="{{ $categories->previousPageUrl() . (request()->has('per_page') ? '&per_page='.request()->input('per_page') : '') }}"
                           style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:all 120ms;"
                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">‹</a>
                    @endif

                    @if ($categories->hasMorePages())
                        <a href="{{ $categories->nextPageUrl() . (request()->has('per_page') ? '&per_page='.request()->input('per_page') : '') }}"
                           style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:all 120ms;"
                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">›</a>
                    @else
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">›</button>
                    @endif

                    @if ($categories->hasMorePages())
                        <a href="{{ $categories->url($categories->lastPage()) . (request()->has('per_page') ? '&per_page='.request()->input('per_page') : '') }}"
                           style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:13px; transition:all 120ms;"
                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#ffffff'">»</a>
                    @else
                        <button disabled style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb; color:#d1d5db; cursor:not-allowed; font-size:13px;">»</button>
                    @endif
                </div>
            </div>
        </div>

        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         Shared Create / Edit Category Modal
         mode = 'create' | 'edit'  (set by openCatModal())
    ════════════════════════════════════════════════════════════════════ --}}
    <div id="cat-modal"
         style="display:none; position:fixed; inset:0; z-index:50; align-items:center; justify-content:center; padding:1rem;"
         aria-modal="true" role="dialog">

        <div id="cat-modal-backdrop" onclick="closeCatModal()"
             style="position:absolute; inset:0; background:rgba(0,0,0,0.4); opacity:0; transition:opacity 220ms ease;"></div>

        <div id="cat-modal-panel"
             style="position:relative; width:100%; max-width:448px; background:#ffffff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.14); border:1px solid #e5e7eb; overflow:hidden; opacity:0; transform:scale(0.96) translateY(10px); transition:opacity 220ms ease, transform 220ms ease;">

            {{-- ── Header ─────────────────────────────────────────────── --}}
            <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 20px; border-bottom:1px solid #e5e7eb;">
                <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                    <div>
                        <h2 id="modal-title" style="font-size:15px; font-weight:600; color:#111827; margin:0;">New Category</h2>
                        <p id="modal-subtitle" style="font-size:12px; color:#6b7280; margin:3px 0 0;">Create a new expense category</p>
                    </div>
                    <span id="modal-system-badge"
                          style="display:none; font-size:11px; font-weight:500; padding:2px 9px; border-radius:20px; background:rgba(59,130,246,0.08); color:#2563eb; border:1px solid rgba(59,130,246,0.2); white-space:nowrap;">
                        System
                    </span>
                </div>
                <button type="button" onclick="closeCatModal()"
                        style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:none; background:transparent; color:#9ca3af; cursor:pointer; transition:all 120ms; flex-shrink:0;"
                        onmouseover="this.style.background='#f3f4f6'; this.style.color='#374151'" onmouseout="this.style.background='transparent'; this.style.color='#9ca3af'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- ── Form ───────────────────────────────────────────────── --}}
            <form id="cat-form" method="POST" action="{{ route('categories.store') }}"
                  style="padding:20px; display:flex; flex-direction:column; gap:18px;">
                @csrf
                {{-- Method spoofing placeholder — set to PATCH for edit, removed for create --}}
                <span id="method-field"></span>
                {{-- Hidden field to identify edit target (used to reopen modal on validation error) --}}
                <input type="hidden" name="edit_category_id" id="modal-edit-id" value="">

                {{-- Name --}}
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <label for="modal_name" style="font-size:13px; font-weight:500; color:#374151;">Name</label>
                    <input id="modal_name" type="text" name="name"
                           value="{{ old('name') }}" required maxlength="100"
                           placeholder="e.g. Groceries"
                           style="background:#ffffff; border:1px solid {{ $errors->has('name') ? '#f87171' : '#d1d5db' }}; color:#111827; font-size:13px; padding:9px 12px; border-radius:7px; outline:none; transition:border-color 150ms; width:100%; box-sizing:border-box;"
                           onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='{{ $errors->has('name') ? '#f87171' : '#d1d5db' }}'">
                    @error('name')
                        <p style="font-size:12px; color:#dc2626; margin:0;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Color --}}
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <label for="modal_color" style="font-size:13px; font-weight:500; color:#374151;">Color</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input id="modal_color" type="color" name="color"
                               value="{{ old('color', '#6b7280') }}"
                               style="width:42px; height:38px; border-radius:7px; border:1px solid #d1d5db; cursor:pointer; padding:3px; background:#ffffff;">
                        <input type="text" id="modal_color_hex"
                               value="{{ old('color', '#6b7280') }}"
                               placeholder="#6b7280" maxlength="7"
                               style="flex:1; background:#ffffff; border:1px solid #d1d5db; color:#111827; font-size:13px; padding:9px 12px; border-radius:7px; outline:none; font-family:ui-monospace,monospace; box-sizing:border-box; transition:border-color 150ms;"
                               onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    @error('color')
                        <p style="font-size:12px; color:#dc2626; margin:0;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status toggle --}}
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <label style="font-size:13px; font-weight:500; color:#374151;">Status</label>
                    <label style="display:inline-flex; align-items:center; gap:10px; cursor:pointer; user-select:none;">
                        <input type="hidden" name="is_active" id="modal_is_active" value="{{ old('is_active', '1') }}">
                        <button type="button" id="modal_toggle_btn" onclick="toggleModalActive()"
                                style="position:relative; width:42px; height:22px; border-radius:11px; border:none; cursor:pointer; transition:background 200ms; flex-shrink:0; background:{{ old('is_active', '1') === '1' ? '#111827' : '#d1d5db' }};">
                            <span id="modal_toggle_thumb"
                                  style="position:absolute; top:2px; left:0; width:18px; height:18px; border-radius:50%; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.2); transition:transform 200ms; display:block; transform:{{ old('is_active', '1') === '1' ? 'translateX(20px)' : 'translateX(2px)' }};"></span>
                        </button>
                        <span style="font-size:13px; color:#374151;">Active</span>
                    </label>
                    <p style="font-size:12px; color:#9ca3af; margin:0;">Inactive categories won't appear in expense forms.</p>
                </div>

                {{-- Footer --}}
                <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:4px;">
                    <button type="button" onclick="closeCatModal()"
                            style="font-size:13px; font-weight:500; color:#6b7280; background:transparent; border:none; cursor:pointer; padding:9px 16px; border-radius:7px; transition:color 150ms;"
                            onmouseover="this.style.color='#111827'" onmouseout="this.style.color='#6b7280'">
                        Cancel
                    </button>
                    <button id="modal-submit-btn" type="submit"
                            style="font-size:13px; font-weight:500; background:#111827; color:#fff; padding:9px 20px; border-radius:7px; border:none; cursor:pointer; transition:background 150ms;"
                            onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">
                        Save category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ── Modal references ───────────────────────────────────────────
        var _modal    = document.getElementById('cat-modal');
        var _backdrop = document.getElementById('cat-modal-backdrop');
        var _panel    = document.getElementById('cat-modal-panel');

        // ── Open modal ─────────────────────────────────────────────────
        // mode: 'create' | 'edit'
        // For edit, pass: id, name, color, isActive (bool), isSystem (bool), updateUrl
        function openCatModal(mode, id, name, color, isActive, isSystem, updateUrl) {
            var form    = document.getElementById('cat-form');
            var title   = document.getElementById('modal-title');
            var subtitle = document.getElementById('modal-subtitle');
            var badge   = document.getElementById('modal-system-badge');
            var editId  = document.getElementById('modal-edit-id');
            var method  = document.getElementById('method-field');
            var submitBtn = document.getElementById('modal-submit-btn');

            if (mode === 'edit') {
                // Titles
                title.textContent   = 'Edit Category';
                subtitle.textContent = 'Update category details';
                submitBtn.textContent = 'Update category';

                // System badge
                badge.style.display = isSystem ? 'inline-flex' : 'none';

                // Form action → PATCH /categories/{id}
                form.action = updateUrl;
                method.innerHTML = '<input type="hidden" name="_method" value="PATCH">';
                editId.value = id;

                // Populate fields
                document.getElementById('modal_name').value = name;
                document.getElementById('modal_color').value = color;
                document.getElementById('modal_color_hex').value = color;

                // Toggle state
                var active = isActive ? '1' : '0';
                document.getElementById('modal_is_active').value = active;
                document.getElementById('modal_toggle_btn').style.background = isActive ? '#111827' : '#d1d5db';
                document.getElementById('modal_toggle_thumb').style.transform = isActive ? 'translateX(20px)' : 'translateX(2px)';

            } else {
                // Create mode — reset to defaults
                title.textContent   = 'New Category';
                subtitle.textContent = 'Create a new expense category';
                submitBtn.textContent = 'Save category';
                badge.style.display  = 'none';

                form.action = '{{ route('categories.store') }}';
                method.innerHTML = '';
                editId.value = '';

                // Clear fields (preserve old() for validation failure reopens)
                @if (!old('edit_category_id'))
                    document.getElementById('modal_name').value = '{{ old('name', '') }}';
                    document.getElementById('modal_color').value = '{{ old('color', '#6b7280') }}';
                    document.getElementById('modal_color_hex').value = '{{ old('color', '#6b7280') }}';
                    var isOn = '{{ old('is_active', '1') }}' === '1';
                    document.getElementById('modal_is_active').value = isOn ? '1' : '0';
                    document.getElementById('modal_toggle_btn').style.background  = isOn ? '#111827' : '#d1d5db';
                    document.getElementById('modal_toggle_thumb').style.transform = isOn ? 'translateX(20px)' : 'translateX(2px)';
                @endif
            }

            // Animate in
            _modal.style.display = 'flex';
            void _panel.offsetHeight;
            _backdrop.style.opacity = '1';
            _panel.style.opacity    = '1';
            _panel.style.transform  = 'scale(1) translateY(0)';
            document.addEventListener('keydown', _escHandler);

            // Focus name field
            setTimeout(function () { document.getElementById('modal_name').focus(); }, 230);
        }

        function closeCatModal() {
            _backdrop.style.opacity = '0';
            _panel.style.opacity    = '0';
            _panel.style.transform  = 'scale(0.96) translateY(10px)';
            setTimeout(function () { _modal.style.display = 'none'; }, 220);
            document.removeEventListener('keydown', _escHandler);
        }

        function _escHandler(e) { if (e.key === 'Escape') closeCatModal(); }

        // ── Auto-reopen after validation failure ───────────────────────
        @if (old('edit_category_id'))
            {{-- Edit form failed validation → reopen edit modal --}}
            openCatModal(
                'edit',
                {{ (int) old('edit_category_id') }},
                @json(old('name', '')),
                @json(old('color', '#6b7280')),
                '{{ old('is_active', '1') }}' === '1',
                '{{ old('is_system', '0') }}' === '1',
                '{{ route('categories.update', (int) old('edit_category_id')) }}'
            );
        @elseif ($errors->any())
            {{-- Create form failed validation → reopen create modal --}}
            openCatModal('create');
        @endif

        // ── Active toggle ──────────────────────────────────────────────
        function toggleModalActive() {
            var input = document.getElementById('modal_is_active');
            var btn   = document.getElementById('modal_toggle_btn');
            var thumb = document.getElementById('modal_toggle_thumb');
            var isOn  = input.value === '1';
            input.value           = isOn ? '0' : '1';
            btn.style.background  = isOn ? '#d1d5db' : '#111827';
            thumb.style.transform = isOn ? 'translateX(2px)' : 'translateX(20px)';
        }

        // ── Color picker sync ──────────────────────────────────────────
        document.getElementById('modal_color').addEventListener('input', function (e) {
            document.getElementById('modal_color_hex').value = e.target.value;
        });
        document.getElementById('modal_color_hex').addEventListener('input', function (e) {
            if (/^#[0-9a-fA-F]{6}$/.test(e.target.value)) {
                document.getElementById('modal_color').value = e.target.value;
            }
        });

        // ── Checkbox selection counter ─────────────────────────────────
        function toggleAllRows(master) {
            document.querySelectorAll('.row-check').forEach(function (cb) { cb.checked = master.checked; });
            updateSelectionCount();
        }

        document.querySelectorAll('.row-check').forEach(function (cb) {
            cb.addEventListener('change', updateSelectionCount);
        });

        function updateSelectionCount() {
            var total   = {{ $categories->total() }};
            var checked = document.querySelectorAll('.row-check:checked').length;
            document.getElementById('selection-count').textContent =
                checked + ' of ' + total + ' row(s) selected.';
            var master = document.getElementById('select-all');
            if (master) {
                var all = document.querySelectorAll('.row-check').length;
                master.indeterminate = checked > 0 && checked < all;
                master.checked       = checked === all && all > 0;
            }
        }

        // ── Row action dropdowns ───────────────────────────────────────
        function toggleRowMenu(e, id) {
            e.stopPropagation();
            var menu   = document.getElementById(id);
            var isOpen = menu.style.display !== 'none';
            document.querySelectorAll('.row-menu').forEach(function (m) { m.style.display = 'none'; });
            if (!isOpen) { menu.style.display = 'block'; }
        }

        document.addEventListener('click', function () {
            document.querySelectorAll('.row-menu').forEach(function (m) { m.style.display = 'none'; });
        });
    </script>
</x-app-layout>
