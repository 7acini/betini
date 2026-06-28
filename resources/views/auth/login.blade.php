<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | Betini ERP Oficina</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-[#111827] text-slate-950">
        <main class="grid min-h-screen place-items-center p-6">
            <section class="w-full max-w-md rounded-[2rem] bg-[#f4f0e8] p-8 shadow-2xl">
                <div class="flex items-center gap-3">
                    <div class="grid size-12 place-items-center rounded-2xl bg-amber-400 text-xl font-black">B</div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">Betini ERP</p>
                        <h1 class="text-2xl font-black">Acessar oficina</h1>
                    </div>
                </div>

                <form class="mt-8 space-y-5" method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">E-mail</span>
                        <input
                            class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-600"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autofocus
                            required
                        >
                        @error('email')
                            <small class="mt-2 block font-bold text-red-700">{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Senha</span>
                        <input
                            class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-600"
                            name="password"
                            type="password"
                            required
                        >
                        @error('password')
                            <small class="mt-2 block font-bold text-red-700">{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input class="rounded border-black/20" name="remember" type="checkbox" value="1">
                        Manter conectado
                    </label>

                    <button class="w-full rounded-2xl bg-orange-600 px-5 py-3 font-black text-white transition hover:bg-orange-700" type="submit">
                        Entrar
                    </button>
                </form>
            </section>
        </main>
    </body>
</html>
