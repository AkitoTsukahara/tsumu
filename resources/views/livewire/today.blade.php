<div class="min-h-dvh bg-stone-50">
    <header class="border-b border-stone-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4 sm:px-8">
            <p class="text-sm font-semibold tracking-[0.18em] text-emerald-700">TSUMU</p>

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
    </header>

    <main class="mx-auto max-w-5xl px-5 py-10 sm:px-8 sm:py-16">
        <p class="text-sm font-semibold text-emerald-700">Today</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">今日も積みましょう</h1>
        <p class="mt-3 max-w-xl text-base leading-7 text-stone-600">短いトレーニングでも大丈夫。前回の自分を基準に、一つずつ積み重ねます。</p>

        <section class="mt-10 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-stone-500">今日のトレーニング</p>
            <p class="mt-3 text-xl font-semibold text-stone-900">準備ができたら始めましょう。</p>
        </section>
    </main>
</div>
