<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { apiFetch, jsonBody } from '../lib/http';
import { moneyFormatter } from '../lib/formatters';
import UiPagination from './UiPagination.vue';

const emit = defineEmits(['changed']);

defineProps({
    canManageRecords: {
        type: Boolean,
        default: false,
    },
});

const statuses = ['Aberto', 'Em andamento', 'Concluido', 'Cancelado'];
const emptyForm = {
    client_id: '',
    payment_method: '',
    status: 'Aberto',
    observation: '',
    services: [],
    items: [],
};

const orders = ref([]);
const clients = ref([]);
const services = ref([]);
const products = ref([]);
const pagination = ref(null);
const search = ref('');
const isLoading = ref(false);
const isSaving = ref(false);
const editingOrderId = ref(null);
const errors = ref({});
const message = ref(null);
const form = reactive({ ...emptyForm, services: [], items: [] });

const formTitle = computed(() => (editingOrderId.value ? 'Editar ordem' : 'Nova ordem'));
const servicePreview = computed(() => form.services.reduce((total, item) => total + Number(item.price || 0) * Number(item.quantity || 0), 0));
const itemsPreview = computed(() => form.items.reduce((total, item) => total + Number(item.price || 0) * Number(item.quantity || 0), 0));
const totalPreview = computed(() => servicePreview.value + itemsPreview.value);

function resetForm() {
    editingOrderId.value = null;
    Object.assign(form, { ...emptyForm, services: [], items: [] });
    errors.value = {};
}

function addService() {
    form.services.push({ service_id: '', quantity: 1, price: '' });
}

function removeService(index) {
    form.services.splice(index, 1);
}

function fillServicePrice(item) {
    const service = services.value.find((service) => service.id === Number(item.service_id));
    item.price = service?.base_price ?? '';
}

function addItem() {
    form.items.push({ product_id: '', quantity: 1, price: '' });
}

function removeItem(index) {
    form.items.splice(index, 1);
}

function fillProductPrice(item) {
    const product = products.value.find((product) => product.id === Number(item.product_id));
    item.price = product?.price ?? '';
}

function fillForm(order) {
    editingOrderId.value = order.id;
    Object.assign(form, {
        client_id: order.client_id ?? '',
        payment_method: order.payment_method ?? '',
        status: order.status ?? 'Aberto',
        observation: order.observation ?? '',
        services: (order.services?.length ? order.services : (order.service_id ? [{ service_id: order.service_id, quantity: 1, price: order.service_total }] : [])).map((item) => ({
            service_id: item.service_id,
            quantity: item.quantity ?? 1,
            price: item.price,
        })),
        items: (order.items ?? []).map((item) => ({
            product_id: item.product_id,
            quantity: item.quantity,
            price: item.price,
        })),
    });
    errors.value = {};
    message.value = null;
}

async function loadOptions() {
    const [clientsResponse, servicesResponse, productsResponse] = await Promise.all([
        apiFetch('/api/workshop/clients?per_page=100'),
        apiFetch('/api/workshop/services?per_page=100'),
        apiFetch('/api/workshop/products?per_page=100'),
    ]);

    if (!clientsResponse.ok || !servicesResponse.ok || !productsResponse.ok) {
        throw new Error('Nao foi possivel carregar os dados da ordem.');
    }

    clients.value = (await clientsResponse.json()).data;
    services.value = (await servicesResponse.json()).data;
    products.value = (await productsResponse.json()).data;
}

async function loadOrders(pageUrl = null) {
    isLoading.value = true;

    const url = pageUrl ?? `/api/workshop/orders?search=${encodeURIComponent(search.value)}`;

    try {
        const response = await apiFetch(url);

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar as ordens.');
        }

        const payload = await response.json();
        orders.value = payload.data;
        pagination.value = payload;
    } finally {
        isLoading.value = false;
    }
}

async function saveOrder() {
    isSaving.value = true;
    errors.value = {};
    message.value = null;

    const isEditing = Boolean(editingOrderId.value);
    const endpoint = isEditing ? `/api/workshop/orders/${editingOrderId.value}` : '/api/workshop/orders';

    try {
        const response = await apiFetch(endpoint, {
            method: isEditing ? 'PUT' : 'POST',
            body: jsonBody({
                ...form,
                client_id: Number(form.client_id),
                services: form.services
                    .filter((item) => item.service_id)
                    .map((item) => ({
                        service_id: Number(item.service_id),
                        quantity: Number(item.quantity),
                        price: item.price === '' ? null : Number(item.price),
                    })),
                items: form.items
                    .filter((item) => item.product_id)
                    .map((item) => ({
                        product_id: Number(item.product_id),
                        quantity: Number(item.quantity),
                        price: item.price === '' ? null : Number(item.price),
                    })),
            }),
        });

        if (response.status === 422) {
            const payload = await response.json();
            errors.value = payload.errors ?? {};
            return;
        }

        if (!response.ok) {
            throw new Error('Nao foi possivel salvar a ordem.');
        }

        message.value = isEditing ? 'Ordem atualizada com sucesso.' : 'Ordem aberta com sucesso.';
        resetForm();
        await loadOrders();
        emit('changed');
    } finally {
        isSaving.value = false;
    }
}

async function deleteOrder(order) {
    if (!window.confirm(`Remover OS #${order.id}?`)) {
        return;
    }

    const response = await apiFetch(`/api/workshop/orders/${order.id}`, {
        method: 'DELETE',
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel remover a ordem.');
    }

    message.value = 'Ordem removida com sucesso.';
    await loadOrders();
    emit('changed');
}

onMounted(async () => {
    await Promise.all([loadOptions(), loadOrders()]);
});
</script>

<template>
    <section id="ordens" class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Ordens de servico</p>
                <h3 class="text-2xl font-black">Abertura e acompanhamento de OS</h3>
            </div>
            <form class="flex gap-2" @submit.prevent="loadOrders()">
                <input
                    v-model="search"
                    class="min-w-0 rounded-2xl border border-black/10 bg-[#faf8f2] px-4 py-3 text-sm outline-none focus:border-red-600"
                    placeholder="Buscar por cliente, servico, status ou pagamento"
                    type="search"
                >
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Buscar</button>
            </form>
        </div>

        <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
            {{ message }}
        </div>

        <div class="mt-6 grid min-w-0 gap-6 xl:grid-cols-[minmax(0,460px)_minmax(0,1fr)]">
            <form class="min-w-0 rounded-3xl bg-[#fff0f0] p-5" @submit.prevent="saveOrder">
                <h4 class="text-xl font-black">{{ formTitle }}</h4>

                <div class="mt-5 grid gap-4">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Cliente</span>
                        <select v-model="form.client_id" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-red-600">
                            <option value="">Selecione um cliente</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }} - {{ client.cpf }}</option>
                        </select>
                        <small v-if="errors.client_id" class="mt-1 block text-red-700">{{ errors.client_id[0] }}</small>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Status</span>
                            <select v-model="form.status" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-red-600">
                                <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                            </select>
                            <small v-if="errors.status" class="mt-1 block text-red-700">{{ errors.status[0] }}</small>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Pagamento</span>
                            <input v-model="form.payment_method" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-red-600" placeholder="Pix, Cartao, Dinheiro" type="text">
                        </label>
                    </div>

                    <div class="min-w-0 rounded-3xl border border-black/10 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <strong>Servicos da OS</strong>
                            <button class="rounded-xl bg-slate-950 px-3 py-2 text-sm font-bold text-white" type="button" @click="addService">Adicionar servico</button>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div v-if="!form.services.length" class="text-sm text-slate-500">Nenhum servico adicionado.</div>
                            <div v-for="(item, index) in form.services" :key="index" class="grid min-w-0 gap-3 rounded-2xl bg-[#faf8f2] p-3">
                                <label class="block min-w-0">
                                    <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Servico</span>
                                    <select v-model="item.service_id" class="mt-1 w-full min-w-0 rounded-xl border border-black/10 px-3 py-2 outline-none" @change="fillServicePrice(item)">
                                        <option value="">Selecione um servico</option>
                                        <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                                    </select>
                                    <small v-if="errors[`services.${index}.service_id`]" class="mt-1 block text-red-700">{{ errors[`services.${index}.service_id`][0] }}</small>
                                </label>
                                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_44px]">
                                    <label class="block min-w-0">
                                        <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Quantidade</span>
                                        <input v-model="item.quantity" class="mt-1 w-full min-w-0 rounded-xl border border-black/10 px-3 py-2 outline-none" min="1" type="number">
                                        <small v-if="errors[`services.${index}.quantity`]" class="mt-1 block text-red-700">{{ errors[`services.${index}.quantity`][0] }}</small>
                                    </label>
                                    <label class="block min-w-0">
                                        <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Preco</span>
                                        <input v-model="item.price" class="mt-1 w-full min-w-0 rounded-xl border border-black/10 px-3 py-2 outline-none" min="0" step="0.01" type="number">
                                        <small v-if="errors[`services.${index}.price`]" class="mt-1 block text-red-700">{{ errors[`services.${index}.price`][0] }}</small>
                                    </label>
                                    <button class="mt-5 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" aria-label="Remover servico" @click="removeService(index)">X</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 rounded-3xl border border-black/10 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <strong>Produtos da OS</strong>
                            <button class="rounded-xl bg-slate-950 px-3 py-2 text-sm font-bold text-white" type="button" @click="addItem">Adicionar item</button>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div v-if="!form.items.length" class="text-sm text-slate-500">Nenhum produto adicionado.</div>
                            <div v-for="(item, index) in form.items" :key="index" class="grid min-w-0 gap-3 rounded-2xl bg-[#faf8f2] p-3">
                                <label class="block min-w-0">
                                    <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Produto</span>
                                    <select v-model="item.product_id" class="mt-1 w-full min-w-0 rounded-xl border border-black/10 px-3 py-2 outline-none" @change="fillProductPrice(item)">
                                        <option value="">Selecione um produto</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
                                    </select>
                                </label>
                                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_44px]">
                                    <label class="block min-w-0">
                                        <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Quantidade</span>
                                        <input v-model="item.quantity" class="mt-1 w-full min-w-0 rounded-xl border border-black/10 px-3 py-2 outline-none" min="1" type="number">
                                    </label>
                                    <label class="block min-w-0">
                                        <span class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Preco</span>
                                        <input v-model="item.price" class="mt-1 w-full min-w-0 rounded-xl border border-black/10 px-3 py-2 outline-none" min="0" step="0.01" type="number">
                                    </label>
                                    <button class="mt-5 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" aria-label="Remover item" @click="removeItem(index)">X</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Observacao</span>
                        <textarea v-model="form.observation" class="mt-2 min-h-24 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-red-600" />
                    </label>

                    <div class="grid gap-3 rounded-3xl bg-slate-950 p-4 text-sm font-bold text-white sm:grid-cols-3">
                        <span>Servico: {{ moneyFormatter.format(servicePreview) }}</span>
                        <span>Itens: {{ moneyFormatter.format(itemsPreview) }}</span>
                        <span>Total: {{ moneyFormatter.format(totalPreview) }}</span>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-2xl bg-red-700 px-5 py-3 text-sm font-black text-white disabled:opacity-60" :disabled="isSaving" type="submit">
                        {{ isSaving ? 'Salvando...' : 'Salvar ordem' }}
                    </button>
                    <button class="rounded-2xl border border-black/10 px-5 py-3 text-sm font-bold" type="button" @click="resetForm">Limpar</button>
                </div>
            </form>

            <div class="min-w-0 overflow-hidden rounded-3xl border border-black/10">
                <table class="w-full min-w-[920px] text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-5 py-4">OS</th>
                            <th class="px-5 py-4">Cliente</th>
                            <th class="px-5 py-4">Servico</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Total</th>
                            <th class="px-5 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="6">Carregando ordens...</td>
                        </tr>
                        <tr v-else-if="!orders.length">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="6">Nenhuma ordem aberta ainda.</td>
                        </tr>
                        <tr v-for="order in orders" v-else :key="order.id" class="border-t border-black/10">
                            <td class="px-5 py-4 font-black">#{{ order.id }}</td>
                            <td class="px-5 py-4">{{ order.client?.name ?? '-' }}</td>
                            <td class="px-5 py-4">{{ order.services?.length ? order.services.map((item) => item.service?.name).filter(Boolean).join(', ') : (order.service?.name ?? '-') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-red-50 px-3 py-1 font-bold text-red-800">{{ order.status }}</span></td>
                            <td class="px-5 py-4 text-right font-black">{{ moneyFormatter.format(order.total ?? 0) }}</td>
                            <td class="px-5 py-4 text-right">
                                <button class="rounded-xl bg-slate-100 px-3 py-2 font-bold" type="button" @click="fillForm(order)">Editar</button>
                                <button v-if="canManageRecords" class="ml-2 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" @click="deleteOrder(order)">Remover</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <UiPagination v-if="pagination" :pagination="pagination" @navigate="loadOrders" />
            </div>
        </div>
    </section>
</template>
