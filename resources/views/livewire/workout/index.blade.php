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

        <section class="mt-10 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xl font-semibold text-stone-900">種目はまだありません</p>
            <p class="mt-2 max-w-xl text-sm leading-6 text-stone-600">次のステップで、登録済みの種目をこのトレーニングへ追加できるようにします。</p>
        </section>
    </main>
</div>
