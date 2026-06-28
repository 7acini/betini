<script setup>
import { computed, onMounted, ref } from 'vue';

const fallbackDashboard = {
    metrics: [],
    modules: [],
    ordersByStatus: {},
    recentOrders: [],
};

const dashboard = ref(fallbackDashboard);
const isLoading = ref(true);
const loadError = ref(null);

const formatter = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

const statusRows = computed(() => Object.entries(dashboard.value.ordersByStatus ?? {}));

onMounted(async () => {
    try {
        const response = await fetch('/api/workshop/dashboard', {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar o dashboard.');
        }

        dashboard.value = await response.json();
    } catch (error) {
        loadError.value = error.message;
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <main class="min-h-screen bg-[#f4f0e8] text-slate-950">
        <section class="grid min-h-screen lg:grid-cols-[280px_1fr]">
            <aside class="border-b border-black/10 bg-[#111827] p-6 text-white lg:border-b-0 lg:border-r">
                <div class="flex items-center gap-3">
                    <div class="grid size-11 place-items-center rounded-2xl bg-amber-400 font-black text-slate-950">B</div>
                    <div>
                        <p class="text-sm text-white/60">Betini ERP</p>
                        <h1 class="text-lg font-bold">Oficina moderna</h1>
                    </div>
                </div>

                <nav class="mt-10 space-y-2 text-sm">
                    <a class="block rounded-2xl bg-white px-4 py-3 font-semibold text-slate-950" href="#dashboard">Dashboard</a>
                    <a class="block rounded-2xl px-4 py-3 text-white/70 transition hover:bg-white/10 hover:text-white" href="#modulos">Modulos</a>
                    <a class="block rounded-2xl px-4 py-3 text-white/70 transition hover:bg-white/10 hover:text-white" href="#ordens">Ordens de servico</a>
                    <a class="block rounded-2xl px-4 py-3 text-white/70 transition hover:bg-white/10 hover:text-white" href="#relatorios">Relatorios</a>
                </nav>

                <div class="mt-10 rounded-3xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm font-semibold">Base migrada</p>
                    <p class="mt-2 text-sm leading-6 text-white/65">
                        Clientes, veiculos, fornecedores, produtos, servicos e pedidos foram remodelados a partir do legado.
                    </p>
                </div>
            </aside>

            <div class="p-5 sm:p-8 lg:p-10">
                <header id="dashboard" class="rounded-[2rem] bg-[#f97316] p-6 text-white shadow-xl shadow-orange-900/10 sm:p-8">
                    <div class="max-w-4xl">
                        <p class="font-semibold uppercase tracking-[0.25em] text-white/75">Painel operacional</p>
                        <h2 class="mt-4 text-4xl font-black tracking-tight sm:text-6xl">Controle sua oficina sem a cara do sistema antigo.</h2>
                        <p class="mt-4 max-w-2xl text-lg text-white/85">
                            Uma base Laravel atual, Vue no front e caminho aberto para evoluir cadastros, OS, estoque e financeiro.
                        </p>
                    </div>
                </header>

                <div v-if="loadError" class="mt-6 rounded-3xl border border-red-200 bg-red-50 p-5 text-red-800">
                    {{ loadError }}
                </div>

                <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="metric in dashboard.metrics"
                        :key="metric.label"
                        class="rounded-3xl border border-black/10 bg-white p-5 shadow-sm"
                    >
                        <p class="text-sm font-medium text-slate-500">{{ metric.label }}</p>
                        <div class="mt-4 flex items-end justify-between">
                            <strong class="text-4xl font-black">{{ metric.value }}</strong>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-700">{{ metric.trend }}</span>
                        </div>
                    </article>

                    <article v-if="isLoading" v-for="index in 4" :key="index" class="h-32 animate-pulse rounded-3xl bg-white/70" />
                </section>

                <section id="modulos" class="mt-8 grid gap-6 xl:grid-cols-[1fr_360px]">
                    <div class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-600">Modulos</p>
                                <h3 class="text-2xl font-black">Nucleo do ERP</h3>
                            </div>
                            <button class="rounded-full bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                                Novo cadastro
                            </button>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <article
                                v-for="module in dashboard.modules"
                                :key="module.name"
                                class="rounded-3xl border border-black/10 bg-[#faf8f2] p-5 transition hover:-translate-y-1 hover:shadow-lg"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="text-lg font-black">{{ module.name }}</h4>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ module.description }}</p>
                                    </div>
                                    <span class="rounded-2xl bg-white px-3 py-2 text-lg font-black">{{ module.count }}</span>
                                </div>
                            </article>
                        </div>
                    </div>

                    <aside class="rounded-[2rem] border border-black/10 bg-[#172554] p-6 text-white shadow-sm">
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-blue-200">Status das OS</p>
                        <h3 class="mt-2 text-2xl font-black">Fila de trabalho</h3>

                        <div class="mt-6 space-y-3">
                            <div v-if="!statusRows.length" class="rounded-3xl border border-white/10 bg-white/5 p-4 text-sm text-white/70">
                                Nenhuma ordem cadastrada ainda.
                            </div>
                            <div v-for="[status, total] in statusRows" :key="status" class="flex items-center justify-between rounded-3xl bg-white p-4 text-slate-950">
                                <span class="font-bold">{{ status }}</span>
                                <strong class="text-2xl">{{ total }}</strong>
                            </div>
                        </div>
                    </aside>
                </section>

                <section id="ordens" class="mt-8 rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-600">Ordens recentes</p>
                            <h3 class="text-2xl font-black">Atendimento e vendas</h3>
                        </div>
                        <span class="text-sm font-semibold text-slate-500">Integrado ao backend Laravel</span>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-3xl border border-black/10">
                        <table class="w-full min-w-[680px] text-left text-sm">
                            <thead class="bg-slate-950 text-white">
                                <tr>
                                    <th class="px-5 py-4">OS</th>
                                    <th class="px-5 py-4">Cliente</th>
                                    <th class="px-5 py-4">Servico</th>
                                    <th class="px-5 py-4">Status</th>
                                    <th class="px-5 py-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!dashboard.recentOrders.length">
                                    <td class="px-5 py-8 text-center text-slate-500" colspan="5">Nenhuma ordem cadastrada ainda.</td>
                                </tr>
                                <tr v-for="order in dashboard.recentOrders" :key="order.id" class="border-t border-black/10">
                                    <td class="px-5 py-4 font-bold">#{{ order.id }}</td>
                                    <td class="px-5 py-4">{{ order.client ?? 'Sem cliente' }}</td>
                                    <td class="px-5 py-4">{{ order.service ?? 'Nao informado' }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full bg-amber-100 px-3 py-1 font-bold text-amber-800">{{ order.status }}</span></td>
                                    <td class="px-5 py-4 text-right font-black">{{ formatter.format(order.total ?? 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </section>
    </main>
</template>
