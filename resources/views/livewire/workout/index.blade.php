<div class="min-h-dvh bg-stone-50">
    <header class="border-b border-stone-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4 sm:px-8">
            <a href="{{ route('today') }}" class="inline-flex min-h-11 items-center rounded-xl px-2 text-sm font-semibold tracking-[0.18em] text-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                TSUMU
            </a>

            <a href="{{ route('today') }}" class="inline-flex min-h-11 items-center rounded-xl px-4 text-sm font-medium text-stone-600 transition hover:bg-stone-100 hover:text-stone-950 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                Today
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-16">
        <p class="text-sm font-semibold text-emerald-700">Workout</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">トレーニング中</h1>
        <p class="mt-3 text-sm text-stone-500">{{ $startedAtLabel }} 開始</p>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        <section class="mt-10 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-xl font-semibold text-stone-900">種目を追加</h2>

            @if ($availableExerciseItems->isEmpty())
                <p class="mt-3 text-sm leading-6 text-stone-600">
                    追加できる種目がありません。
                    <a href="{{ route('exercise.index') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-100">設定から種目を登録できます。</a>
                </p>
            @else
                <form wire:submit="addExercise" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-start">
                    <div class="min-w-0 flex-1">
                        <label for="selected_exercise_id" class="sr-only">追加する種目</label>
                        <select
                            id="selected_exercise_id"
                            name="selected_exercise_id"
                            wire:model="selectedExerciseId"
                            class="min-h-12 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            <option value="">種目を選択</option>
                            @foreach ($availableExerciseItems as $exercise)
                                <option wire:key="available-exercise-{{ $exercise->id }}" value="{{ $exercise->id }}">{{ $exercise->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedExerciseId')
                            <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="addExercise"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-100 disabled:cursor-wait disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="addExercise">追加する</span>
                        <span wire:loading wire:target="addExercise">追加しています...</span>
                    </button>
                </form>
            @endif
        </section>

        @if ($exerciseItems->isEmpty())
            <section class="mt-6 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xl font-semibold text-stone-900">種目はまだありません</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">上の選択欄から、今日取り組む種目を追加しましょう。</p>
            </section>
        @else
            <ol class="mt-6 grid gap-4 sm:grid-cols-2">
                @foreach ($exerciseItems as $exercise)
                    <li wire:key="workout-exercise-{{ $exercise->id }}" class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold text-emerald-700">{{ $loop->iteration }}種目目</p>
                                <h2 class="mt-1 text-lg font-semibold text-stone-950">{{ $exercise->name }}</h2>
                                <p class="mt-1 text-sm text-stone-500">{{ $exercise->equipmentName ?? '機材なし' }}</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                <x-exercise.recording-method-label :value="$exercise->recordingMethod" />
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </main>
</div>
