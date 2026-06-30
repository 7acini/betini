<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import ClientPanel from './ClientPanel.vue';
import AppointmentPanel from './AppointmentPanel.vue';
import OrderPanel from './OrderPanel.vue';
import ProviderPanel from './ProviderPanel.vue';
import ProductPanel from './ProductPanel.vue';
import ServicePanel from './ServicePanel.vue';
import VehiclePanel from './VehiclePanel.vue';
import { apiFetch } from '../lib/http';
import { moneyFormatter } from '../lib/formatters';

const fallbackDashboard = {
    metrics: [],
    modules: [],
    ordersByStatus: {},
    recentOrders: [],
    user: {
        canManageRecords: false,
    },
};

const logoUrl = '/images/betini-logo.png';
const dashboard = ref(fallbackDashboard);
const isLoading = ref(true);
const loadError = ref(null);
const isSidebarCollapsed = ref(window.localStorage.getItem('betini-sidebar') === 'collapsed');
const activeModule = ref(window.location.hash.replace('#', '') || 'dashboard');
const isSettingsOpen = ref(false);

const modules = [
    { key: 'dashboard', label: 'Dashboard', icon: 'dashboard', eyebrow: 'Visao geral', title: 'Dashboard' },
    { key: 'clientes', label: 'Clientes', icon: 'person', eyebrow: 'Cadastros', title: 'Clientes da oficina', component: ClientPanel },
    { key: 'veiculos', label: 'Veiculos', icon: 'car', eyebrow: 'Cadastros', title: 'Veiculos atendidos', component: VehiclePanel },
    { key: 'fornecedores', label: 'Fornecedores', icon: 'truck', eyebrow: 'Suprimentos', title: 'Fornecedores e parceiros', component: ProviderPanel },
    { key: 'produtos', label: 'Produtos', icon: 'box', eyebrow: 'Estoque', title: 'Produtos e pecas', component: ProductPanel },
    { key: 'servicos', label: 'Servicos', icon: 'tool', eyebrow: 'Catalogo', title: 'Servicos da oficina', component: ServicePanel },
    { key: 'agendamentos', label: 'Agendamentos', icon: 'calendar', eyebrow: 'Agenda', title: 'Agendamentos da oficina', component: AppointmentPanel },
    { key: 'ordens', label: 'Ordens', icon: 'clipboard', eyebrow: 'Operacao', title: 'Ordens de servico', component: OrderPanel },
];

const statusRows = computed(() => Object.entries(dashboard.value.ordersByStatus ?? {}));
const activeMeta = computed(() => modules.find((module) => module.key === activeModule.value) ?? modules[0]);
const activeComponent = computed(() => activeMeta.value.component ?? null);

function setActiveModule(key) {
    activeModule.value = key;
    isSettingsOpen.value = false;
    window.history.replaceState(null, '', `#${key}`);
}

function toggleSidebar() {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
}

async function loadDashboard() {
    try {
        loadError.value = null;
        const response = await apiFetch('/api/workshop/dashboard');

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar o dashboard.');
        }

        dashboard.value = await response.json();
    } catch (error) {
        loadError.value = error.message;
    } finally {
        isLoading.value = false;
    }
}

async function logout() {
    await apiFetch('/logout', { method: 'POST' });
    window.location.href = '/login';
}

watch(isSidebarCollapsed, (value) => {
    window.localStorage.setItem('betini-sidebar', value ? 'collapsed' : 'expanded');
});

onMounted(loadDashboard);
</script>

<template>
    <main class="betini-app-shell" :class="{ 'is-sidebar-collapsed': isSidebarCollapsed }">
        <aside class="betini-sidebar">
            <a class="betini-sidebar__brand" href="#dashboard" aria-label="Betini Centro Automotivo" @click.prevent="setActiveModule('dashboard')">
                <span class="betini-sidebar__logo"><img :src="logoUrl" alt="Betini Centro Automotivo"></span>
            </a>

            <nav class="betini-sidebar__nav" aria-label="Modulos do Portal">
                <button
                    v-for="module in modules"
                    :key="module.key"
                    class="betini-nav-item"
                    :class="{ 'is-active': activeModule === module.key }"
                    type="button"
                    :title="module.label"
                    @click="setActiveModule(module.key)"
                >
                    <span class="betini-nav-icon" aria-hidden="true">
                        <svg v-if="module.icon === 'dashboard'" viewBox="0 0 24 24"><path d="M4 13h7V4H4v9Zm0 7h7v-5H4v5Zm9 0h7v-9h-7v9Zm0-16v5h7V4h-7Z" /></svg>
                        <svg v-else-if="module.icon === 'person'" viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0v1H5v-1Z" /></svg>
                        <svg v-else-if="module.icon === 'car'" viewBox="0 0 24 24"><path d="M5.4 10 7 5.8A3 3 0 0 1 9.8 4h4.4A3 3 0 0 1 17 5.8l1.6 4.2A3 3 0 0 1 21 13v4a1 1 0 0 1-1 1h-1a2 2 0 0 1-4 0H9a2 2 0 0 1-4 0H4a1 1 0 0 1-1-1v-4a3 3 0 0 1 2.4-3Zm2.2 0h8.8l-1-3a1 1 0 0 0-1-.7H9.6a1 1 0 0 0-1 .7l-1 3ZM7 15.5A1.5 1.5 0 1 0 7 12a1.5 1.5 0 0 0 0 3.5Zm10 0a1.5 1.5 0 1 0 0-3.5 1.5 1.5 0 0 0 0 3.5Z" /></svg>
                        <svg v-else-if="module.icon === 'truck'" viewBox="0 0 24 24"><path d="M3 5h11v9h1.2l1.7-4H21l2 3.5V18h-2a2 2 0 0 1-4 0H9a2 2 0 0 1-4 0H3V5Zm2 2v8.1A2 2 0 0 1 9 16h7.3l1.7-4h1.8l1.2 2.1V16h-1a2 2 0 0 1-4 0h-2V7H5Z" /></svg>
                        <svg v-else-if="module.icon === 'box'" viewBox="0 0 24 24"><path d="m12 2 9 4.5v11L12 22l-9-4.5v-11L12 2Zm0 2.2L6.2 7 12 9.8 17.8 7 12 4.2ZM5 8.7v7.6l6 3v-7.6l-6-3Zm8 10.6 6-3V8.7l-6 3v7.6Z" /></svg>
                        <svg v-else-if="module.icon === 'tool'" viewBox="0 0 24 24"><path d="M21 6.5a6.5 6.5 0 0 1-8.7 6.1l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9A6.5 6.5 0 0 1 17.5 1l-4 4 1.5 4 4 1.5 4-4Z" /></svg>
                        <svg v-else-if="module.icon === 'calendar'" viewBox="0 0 24 24"><path d="M7 2h2v3h6V2h2v3h3a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h3V2Zm13 8H4v10h16V10ZM4 8h16V7H4v1Zm3 5h3v3H7v-3Zm5 0h3v3h-3v-3Z" /></svg>
                        <svg v-else viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16l-7-3-7 3V5a2 2 0 0 1 2-2Zm2 4v2h6V7H9Zm0 4v2h6v-2H9Z" /></svg>
                    </span>
                    <strong>{{ module.label }}</strong>
                </button>
            </nav>

        </aside>

        <section class="betini-workspace">
            <header class="betini-topbar">
                <div class="betini-topbar__left">
                    <button class="betini-icon-button" type="button" :aria-label="isSidebarCollapsed ? 'Expandir menu' : 'Minimizar menu'" @click="toggleSidebar">
                        <span></span><span></span><span></span>
                    </button>
                    <div>
                        <p>{{ activeMeta.eyebrow }}</p>
                        <h1>{{ activeMeta.title }}</h1>
                    </div>
                </div>

                <div class="betini-topbar__right">
                    <div class="betini-settings-menu">
                        <button
                            class="betini-settings-button"
                            type="button"
                            aria-label="Configuracoes"
                            :aria-expanded="isSettingsOpen"
                            @click="isSettingsOpen = !isSettingsOpen"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19.4 13.5c.08-.49.1-.99.1-1.5s-.02-1.01-.1-1.5l2.08-1.58-2-3.46-2.46.99a7.95 7.95 0 0 0-2.6-1.5L14.05 2h-4.1l-.37 2.95a7.95 7.95 0 0 0-2.6 1.5l-2.46-.99-2 3.46L4.6 10.5c-.08.49-.1.99-.1 1.5s.02 1.01.1 1.5l-2.08 1.58 2 3.46 2.46-.99a7.95 7.95 0 0 0 2.6 1.5l.37 2.95h4.1l.37-2.95a7.95 7.95 0 0 0 2.6-1.5l2.46.99 2-3.46-2.08-1.58ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z" />
                            </svg>
                        </button>
                        <div v-if="isSettingsOpen" class="betini-settings-dropdown" role="menu">
                            <button type="button" role="menuitem" @click="setActiveModule('dashboard')">Dashboard</button>
                            <button type="button" role="menuitem" disabled>Configuracoes em breve</button>
                            <button class="is-danger" type="button" role="menuitem" @click="logout">Sair</button>
                        </div>
                    </div>
                </div>
            </header>

            <div v-if="loadError" class="betini-alert betini-alert--danger">
                {{ loadError }}
            </div>

            <section v-if="activeModule === 'dashboard'" class="betini-dashboard-view">
                <section class="betini-metric-grid">
                    <article v-for="metric in dashboard.metrics" :key="metric.label" class="betini-metric-card">
                        <span>{{ metric.label }}</span>
                        <strong>{{ metric.value }}</strong>
                        <small>{{ metric.trend }}</small>
                    </article>
                    <article v-if="isLoading" v-for="index in 4" :key="index" class="betini-metric-card is-loading" />
                </section>

                <section class="betini-dashboard-grid">
                    <article class="betini-card">
                        <div class="betini-card__head">
                            <div>
                                <p>Modulos</p>
                                <h3>Nucleo do Portal</h3>
                            </div>
                        </div>
                        <div class="betini-module-grid">
                            <button v-for="module in modules.filter((item) => item.key !== 'dashboard')" :key="module.key" type="button" @click="setActiveModule(module.key)">
                                <span class="betini-module-icon" aria-hidden="true">
                                    <svg v-if="module.icon === 'person'" viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0v1H5v-1Z" /></svg>
                                    <svg v-else-if="module.icon === 'car'" viewBox="0 0 24 24"><path d="M5.4 10 7 5.8A3 3 0 0 1 9.8 4h4.4A3 3 0 0 1 17 5.8l1.6 4.2A3 3 0 0 1 21 13v4a1 1 0 0 1-1 1h-1a2 2 0 0 1-4 0H9a2 2 0 0 1-4 0H4a1 1 0 0 1-1-1v-4a3 3 0 0 1 2.4-3Zm2.2 0h8.8l-1-3a1 1 0 0 0-1-.7H9.6a1 1 0 0 0-1 .7l-1 3ZM7 15.5A1.5 1.5 0 1 0 7 12a1.5 1.5 0 0 0 0 3.5Zm10 0a1.5 1.5 0 1 0 0-3.5 1.5 1.5 0 0 0 0 3.5Z" /></svg>
                                    <svg v-else-if="module.icon === 'truck'" viewBox="0 0 24 24"><path d="M3 5h11v9h1.2l1.7-4H21l2 3.5V18h-2a2 2 0 0 1-4 0H9a2 2 0 0 1-4 0H3V5Zm2 2v8.1A2 2 0 0 1 9 16h7.3l1.7-4h1.8l1.2 2.1V16h-1a2 2 0 0 1-4 0h-2V7H5Z" /></svg>
                                    <svg v-else-if="module.icon === 'box'" viewBox="0 0 24 24"><path d="m12 2 9 4.5v11L12 22l-9-4.5v-11L12 2Zm0 2.2L6.2 7 12 9.8 17.8 7 12 4.2ZM5 8.7v7.6l6 3v-7.6l-6-3Zm8 10.6 6-3V8.7l-6 3v7.6Z" /></svg>
                                    <svg v-else-if="module.icon === 'tool'" viewBox="0 0 24 24"><path d="M21 6.5a6.5 6.5 0 0 1-8.7 6.1l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9A6.5 6.5 0 0 1 17.5 1l-4 4 1.5 4 4 1.5 4-4Z" /></svg>
                                    <svg v-else-if="module.icon === 'calendar'" viewBox="0 0 24 24"><path d="M7 2h2v3h6V2h2v3h3a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h3V2Zm13 8H4v10h16V10ZM4 8h16V7H4v1Zm3 5h3v3H7v-3Zm5 0h3v3h-3v-3Z" /></svg>
                                    <svg v-else viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16l-7-3-7 3V5a2 2 0 0 1 2-2Zm2 4v2h6V7H9Zm0 4v2h6v-2H9Z" /></svg>
                                </span>
                                <strong>{{ module.label }}</strong>
                            </button>
                        </div>
                    </article>

                    <aside class="betini-card betini-card--dark">
                        <div class="betini-card__head">
                            <div>
                                <p>Status das OS</p>
                                <h3>Fila de trabalho</h3>
                            </div>
                        </div>
                        <div class="betini-status-list">
                            <div v-if="!statusRows.length" class="betini-empty">Nenhuma ordem cadastrada ainda.</div>
                            <div v-for="[status, total] in statusRows" :key="status">
                                <span>{{ status }}</span>
                                <strong>{{ total }}</strong>
                            </div>
                        </div>
                    </aside>

                    <article class="betini-card betini-card--wide">
                        <div class="betini-card__head">
                            <div>
                                <p>Ordens recentes</p>
                                <h3>Atendimento e vendas</h3>
                            </div>
                        </div>
                        <div class="betini-table-shell">
                            <table>
                                <thead>
                                    <tr>
                                        <th>OS</th>
                                        <th>Cliente</th>
                                        <th>Servico</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!dashboard.recentOrders.length">
                                        <td colspan="5">Nenhuma ordem cadastrada ainda.</td>
                                    </tr>
                                    <tr v-for="order in dashboard.recentOrders" :key="order.id">
                                        <td>#{{ order.id }}</td>
                                        <td>{{ order.client ?? 'Sem cliente' }}</td>
                                        <td>{{ order.service ?? 'Nao informado' }}</td>
                                        <td><span class="betini-chip">{{ order.status }}</span></td>
                                        <td>{{ moneyFormatter.format(order.total ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </section>
            </section>

            <section v-else class="betini-module-view">
                <component
                    :is="activeComponent"
                    :can-manage-records="dashboard.user.canManageRecords"
                    @changed="loadDashboard"
                />
            </section>
        </section>
    </main>
</template>
