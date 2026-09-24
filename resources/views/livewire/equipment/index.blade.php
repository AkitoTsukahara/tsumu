<div class="min-h-dvh bg-stone-50">
    <header class="border-b border-stone-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="{{ route('today') }}" class="text-sm font-semibold tracking-[0.18em] text-emerald-700">TSUMU</a>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('today') }}"
                    class="min-h-11 rounded-xl px-4 py-3 text-sm font-medium text-stone-600 transition hover:bg-stone-100 hover:text-stone-950 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                >
                    Today
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="min-h-11 rounded-xl px-4 text-sm font-medium text-stone-600 transition hover:bg-stone-100 hover:text-stone-950 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                        ログアウト
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-16">
        <p class="text-sm font-semibold text-emerald-700">Settings</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">機材</h1>
        <p class="mt-3 max-w-xl text-base leading-7 text-stone-600">トレーニングで使う機材と、重量を変える刻み幅を確認できます。</p>

        @if (session('status'))
            <div role="status" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section class="mt-10 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-xl font-semibold text-stone-950">
                {{ $editingEquipmentId === null ? '機材を登録' : '機材を編集' }}
            </h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">
                {{ $editingEquipmentId === null ? '最初の機材として、レッグプレスと9kgの刻み幅を登録できます。' : '機材の名前、カテゴリ、重量単位、刻み幅を変更できます。' }}
            </p>

            <form wire:submit="{{ $editingEquipmentId === null ? 'save' : 'update' }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="equipment-name" class="text-sm font-medium text-stone-700">機材名</label>
                    <input
                        id="equipment-name"
                        name="name"
                        type="text"
                        wire:model="form.name"
                        autocomplete="off"
                        class="mt-2 min-h-12 w-full rounded-2xl border border-stone-300 bg-white px-4 text-base text-stone-950 outline-none transition placeholder:text-stone-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100"
                        placeholder="レッグプレス"
                    >
                    @error('form.name') <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="equipment-category" class="text-sm font-medium text-stone-700">カテゴリ</label>
                    <select
                        id="equipment-category"
                        name="category"
                        wire:model="form.category"
                        class="mt-2 min-h-12 w-full rounded-2xl border border-stone-300 bg-white px-4 text-base text-stone-950 outline-none transition focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="machine">マシン</option>
                        <option value="free_weight">フリーウェイト</option>
                        <option value="cardio">有酸素</option>
                        <option value="other">その他</option>
                    </select>
                    @error('form.category') <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="equipment-unit" class="text-sm font-medium text-stone-700">重量単位</label>
                    <select
                        id="equipment-unit"
                        name="weight_unit"
                        wire:model="form.weightUnit"
                        class="mt-2 min-h-12 w-full rounded-2xl border border-stone-300 bg-white px-4 text-base text-stone-950 outline-none transition focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="kg">kg</option>
                    </select>
                    @error('form.weightUnit') <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="equipment-increment" class="text-sm font-medium text-stone-700">重量の刻み幅</label>
                    <div class="mt-2 flex items-center gap-3">
                        <input
                            id="equipment-increment"
                            name="weight_increment"
                            type="number"
                            wire:model="form.weightIncrement"
                            inputmode="decimal"
                            min="0.01"
                            max="999.99"
                            step="0.01"
                            class="min-h-12 min-w-0 flex-1 rounded-2xl border border-stone-300 bg-white px-4 text-base text-stone-950 outline-none transition placeholder:text-stone-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100"
                            placeholder="9"
                        >
                        <span class="text-sm font-semibold text-stone-600">kg</span>
                    </div>
                    @error('form.weightIncrement') <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-3 sm:col-span-2 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-emerald-700 px-5 text-base font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:cursor-wait disabled:opacity-60 sm:w-auto"
                        wire:loading.attr="disabled"
                        wire:target="{{ $editingEquipmentId === null ? 'save' : 'update' }}"
                    >
                        <span wire:loading.remove wire:target="{{ $editingEquipmentId === null ? 'save' : 'update' }}">
                            {{ $editingEquipmentId === null ? '登録する' : '更新する' }}
                        </span>
                        <span wire:loading wire:target="{{ $editingEquipmentId === null ? 'save' : 'update' }}">
                            {{ $editingEquipmentId === null ? '登録中...' : '更新中...' }}
                        </span>
                    </button>

                    @if ($editingEquipmentId !== null)
                        <button
                            type="button"
                            wire:click="cancelEditing"
                            class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl border border-stone-300 bg-white px-5 text-base font-semibold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-stone-200 sm:w-auto"
                        >
                            キャンセル
                        </button>
                    @endif
                </div>
            </form>
        </section>

        @if ($equipmentItems->isEmpty())
            <section class="mt-10 rounded-3xl border border-dashed border-stone-300 bg-white p-8 text-center shadow-sm">
                <p class="text-lg font-semibold text-stone-900">機材はまだありません</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">機材を登録すると、ここに一覧で表示されます。</p>
            </section>
        @else
            <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                @foreach ($equipmentItems as $equipment)
                    <li wire:key="equipment-{{ $equipment->id }}" class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-stone-950">{{ $equipment->name }}</h2>
                                <p class="mt-1 text-sm text-stone-500">
                                    @switch($equipment->category)
                                        @case('machine') マシン @break
                                        @case('free_weight') フリーウェイト @break
                                        @case('cardio') 有酸素 @break
                                        @case('other') その他 @break
                                    @endswitch
                                </p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                {{ $equipment->weightUnit }}
                            </span>
                        </div>

                        <div class="mt-6 border-t border-stone-100 pt-4">
                            <p class="text-xs font-medium tracking-wide text-stone-500">重量の刻み幅</p>
                            <p class="mt-1 text-base font-semibold text-stone-900">{{ $equipment->weightIncrement }} {{ $equipment->weightUnit }}</p>
                        </div>

                        <button
                            type="button"
                            wire:click="edit('{{ $equipment->id }}')"
                            class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-stone-300 bg-white px-4 text-sm font-semibold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            {{ $equipment->name }}を編集
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </main>
</div>
