<main class="relative flex min-h-dvh items-center justify-center overflow-hidden px-5 py-10 sm:px-8">
    <div aria-hidden="true" class="absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-emerald-100/80 to-transparent"></div>

    <section class="relative w-full max-w-md rounded-3xl border border-stone-200/80 bg-white p-6 shadow-xl shadow-stone-200/60 sm:p-9">
        <div class="mb-8">
            <p class="mb-3 text-sm font-semibold tracking-[0.18em] text-emerald-700">TSUMU</p>
            <h1 class="text-3xl font-bold tracking-tight text-stone-950">ログイン</h1>
            <p class="mt-3 text-sm leading-6 text-stone-600">前回の自分を基準に、今日も少しずつ積み重ねよう。</p>
        </div>

        <form wire:submit="login" class="space-y-5">
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-stone-800">メールアドレス</label>
                <input
                    wire:model="email"
                    id="email"
                    name="email"
                    type="email"
                    inputmode="email"
                    autocomplete="username"
                    autofocus
                    @class([
                        'min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-base shadow-sm outline-none transition focus:ring-4',
                        'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has('email'),
                        'border-stone-300 focus:border-emerald-600 focus:ring-emerald-100' => ! $errors->has('email'),
                    ])
                    @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                >
                @error('email')
                    <p id="email-error" role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-stone-800">パスワード</label>
                <input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    @class([
                        'min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-base shadow-sm outline-none transition focus:ring-4',
                        'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has('password'),
                        'border-stone-300 focus:border-emerald-600 focus:ring-emerald-100' => ! $errors->has('password'),
                    ])
                    @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                >
                @error('password')
                    <p id="password-error" role="alert" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="flex min-h-12 w-full items-center justify-center rounded-xl bg-stone-950 px-4 py-3 text-base font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-200 disabled:cursor-wait disabled:opacity-70"
                wire:loading.attr="disabled"
                wire:target="login"
            >
                <span wire:loading.remove wire:target="login">ログイン</span>
                <span wire:loading wire:target="login">確認しています...</span>
            </button>
        </form>
    </section>
</main>
