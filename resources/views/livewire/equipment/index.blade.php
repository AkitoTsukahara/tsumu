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

        @if ($equipmentItems === [])
            <section class="mt-10 rounded-3xl border border-dashed border-stone-300 bg-white p-8 text-center shadow-sm">
                <p class="text-lg font-semibold text-stone-900">機材はまだありません</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">機材を登録すると、ここに一覧で表示されます。</p>
            </section>
        @else
            <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                @foreach ($equipmentItems as $equipment)
                    <li wire:key="equipment-{{ $equipment['id'] }}" class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-stone-950">{{ $equipment['name'] }}</h2>
                                <p class="mt-1 text-sm text-stone-500">{{ $equipment['category'] }}</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                {{ $equipment['weightUnit'] }}
                            </span>
                        </div>

                        <div class="mt-6 border-t border-stone-100 pt-4">
                            <p class="text-xs font-medium tracking-wide text-stone-500">重量の刻み幅</p>
                            <p class="mt-1 text-base font-semibold text-stone-900">{{ $equipment['weightIncrement'] }} {{ $equipment['weightUnit'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </main>
</div>
