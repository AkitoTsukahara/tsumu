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
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">種目</h1>
        <p class="mt-3 max-w-xl text-base leading-7 text-stone-600">トレーニングで記録する種目と、対象部位・記録方式を確認できます。</p>

        <a
            href="{{ route('equipment.index') }}"
            class="mt-5 inline-flex min-h-11 items-center rounded-xl font-semibold text-emerald-700 hover:text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-100"
        >
            機材を管理
        </a>

        @if ($exerciseItems->isEmpty())
            <section class="mt-10 rounded-3xl border border-dashed border-stone-300 bg-white p-8 text-center shadow-sm">
                <p class="text-lg font-semibold text-stone-900">種目はまだありません</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">登録した種目が、ここに一覧で表示されます。</p>
            </section>
        @else
            <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                @foreach ($exerciseItems as $exercise)
                    <li wire:key="exercise-{{ $exercise->id }}" class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-stone-950">{{ $exercise->name }}</h2>
                                <p class="mt-1 text-sm text-stone-500">{{ $exercise->equipmentName ?? '機材なし' }}</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                @switch($exercise->recordingMethod)
                                    @case('weight_repetitions') 重量＋回数 @break
                                    @case('duration') 時間 @break
                                    @case('duration_distance') 時間＋距離 @break
                                    @case('speed_duration') 速度＋時間 @break
                                @endswitch
                            </span>
                        </div>

                        <div class="mt-6 border-t border-stone-100 pt-4">
                            <p class="text-xs font-medium tracking-wide text-stone-500">対象部位</p>
                            <p class="mt-1 text-base font-semibold text-stone-900">
                                <x-exercise.body-part-label :value="$exercise->primaryTarget" />

                                @if ($exercise->secondaryTarget !== null)
                                    <span class="font-normal text-stone-500">
                                        ／ <x-exercise.body-part-label :value="$exercise->secondaryTarget" />
                                    </span>
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </main>
</div>
