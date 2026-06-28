<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { apiFetch, jsonBody } from '../lib/http';
import { moneyFormatter, onlyDigits } from '../lib/formatters';
import UiPagination from './UiPagination.vue';

const emit = defineEmits(['changed']);

const emptyForm = {
    provider_id: '',
    category: '',
    name: '',
    price: '',
    description: '',
    barcode: '',
};

const products = ref([]);
const providers = ref([]);
const pagination = ref(null);
const search = ref('');
const isLoading = ref(false);
const isSaving = ref(false);
const editingProductId = ref(null);
const errors = ref({});
const message = ref(null);
const form = reactive({ ...emptyForm });

const formTitle = computed(() => (editingProductId.value ? 'Editar produto' : 'Novo produto'));

function fillForm(product) {
    editingProductId.value = product.id;
    Object.assign(form, {
        provider_id: product.provider_id ?? '',
        category: product.category ?? '',
        name: product.name ?? '',
        price: product.price ?? '',
        description: product.description ?? '',
        barcode: product.barcode ?? '',
    });
    errors.value = {};
    message.value = null;
}

function resetForm() {
    editingProductId.value = null;
    Object.assign(form, emptyForm);
    errors.value = {};
}

async function loadProviders() {
    const response = await apiFetch('/api/workshop/providers?per_page=100');

    if (!response.ok) {
        throw new Error('Nao foi possivel carregar os fornecedores.');
    }

    const payload = await response.json();
    providers.value = payload.data;
}

async function loadProducts(pageUrl = null) {
    isLoading.value = true;

    const url = pageUrl ?? `/api/workshop/products?search=${encodeURIComponent(search.value)}`;

    try {
        const response = await apiFetch(url);

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar os produtos.');
        }

        const payload = await response.json();
        products.value = payload.data;
        pagination.value = payload;
    } finally {
        isLoading.value = false;
    }
}

async function saveProduct() {
    isSaving.value = true;
    errors.value = {};
    message.value = null;

    const isEditing = Boolean(editingProductId.value);
    const endpoint = isEditing ? `/api/workshop/products/${editingProductId.value}` : '/api/workshop/products';

    try {
        const response = await apiFetch(endpoint, {
            method: isEditing ? 'PUT' : 'POST',
            body: jsonBody({
                ...form,
                provider_id: form.provider_id === '' ? null : Number(form.provider_id),
                price: form.price === '' ? null : Number(form.price),
                barcode: onlyDigits(form.barcode),
            }),
        });

        if (response.status === 422) {
            const payload = await response.json();
            errors.value = payload.errors ?? {};
            return;
        }

        if (!response.ok) {
            throw new Error('Nao foi possivel salvar o produto.');
        }

        message.value = isEditing ? 'Produto atualizado com sucesso.' : 'Produto cadastrado com sucesso.';
        resetForm();
        await loadProducts();
        emit('changed');
    } finally {
        isSaving.value = false;
    }
}

async function deleteProduct(product) {
    if (!window.confirm(`Remover ${product.name}?`)) {
        return;
    }

    const response = await apiFetch(`/api/workshop/products/${product.id}`, {
        method: 'DELETE',
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel remover o produto.');
    }

    message.value = 'Produto removido com sucesso.';
    await loadProducts();
    emit('changed');
}

onMounted(async () => {
    await Promise.all([loadProviders(), loadProducts()]);
});
</script>

<template>
    <section id="produtos" class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-700">Produtos</p>
                <h3 class="text-2xl font-black">Pecas, insumos e estoque inicial</h3>
            </div>
            <form class="flex gap-2" @submit.prevent="loadProducts()">
                <input
                    v-model="search"
                    class="min-w-0 rounded-2xl border border-black/10 bg-[#faf8f2] px-4 py-3 text-sm outline-none focus:border-emerald-600"
                    placeholder="Buscar por produto, categoria, codigo ou fornecedor"
                    type="search"
                >
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Buscar</button>
            </form>
        </div>

        <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
            {{ message }}
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[420px_1fr]">
            <form class="rounded-3xl bg-[#edfff5] p-5" @submit.prevent="saveProduct">
                <h4 class="text-xl font-black">{{ formTitle }}</h4>

                <div class="mt-5 grid gap-4">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Fornecedor</span>
                        <select v-model="form.provider_id" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-emerald-600">
                            <option value="">Sem fornecedor vinculado</option>
                            <option v-for="provider in providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
                        </select>
                        <small v-if="errors.provider_id" class="mt-1 block text-red-700">{{ errors.provider_id[0] }}</small>
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Nome</span>
                        <input v-model="form.name" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-emerald-600" type="text">
                        <small v-if="errors.name" class="mt-1 block text-red-700">{{ errors.name[0] }}</small>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Categoria</span>
                            <input v-model="form.category" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-emerald-600" type="text">
                            <small v-if="errors.category" class="mt-1 block text-red-700">{{ errors.category[0] }}</small>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Preco</span>
                            <input v-model="form.price" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-emerald-600" min="0" step="0.01" type="number">
                            <small v-if="errors.price" class="mt-1 block text-red-700">{{ errors.price[0] }}</small>
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Codigo de barras</span>
                        <input v-model="form.barcode" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-emerald-600" type="text">
                        <small v-if="errors.barcode" class="mt-1 block text-red-700">{{ errors.barcode[0] }}</small>
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Descricao</span>
                        <textarea v-model="form.description" class="mt-2 min-h-24 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-emerald-600" />
                        <small v-if="errors.description" class="mt-1 block text-red-700">{{ errors.description[0] }}</small>
                    </label>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-black text-white disabled:opacity-60" :disabled="isSaving" type="submit">
                        {{ isSaving ? 'Salvando...' : 'Salvar produto' }}
                    </button>
                    <button class="rounded-2xl border border-black/10 px-5 py-3 text-sm font-bold" type="button" @click="resetForm">Limpar</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-black/10">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-5 py-4">Produto</th>
                            <th class="px-5 py-4">Categoria</th>
                            <th class="px-5 py-4">Fornecedor</th>
                            <th class="px-5 py-4 text-right">Preco</th>
                            <th class="px-5 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Carregando produtos...</td>
                        </tr>
                        <tr v-else-if="!products.length">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Nenhum produto cadastrado ainda.</td>
                        </tr>
                        <tr v-for="product in products" v-else :key="product.id" class="border-t border-black/10">
                            <td class="px-5 py-4">
                                <strong class="block font-black">{{ product.name }}</strong>
                                <span class="text-slate-500">{{ product.barcode ?? 'Sem codigo' }}</span>
                            </td>
                            <td class="px-5 py-4">{{ product.category ?? '-' }}</td>
                            <td class="px-5 py-4">{{ product.provider?.name ?? '-' }}</td>
                            <td class="px-5 py-4 text-right font-black">{{ moneyFormatter.format(product.price ?? 0) }}</td>
                            <td class="px-5 py-4 text-right">
                                <button class="rounded-xl bg-slate-100 px-3 py-2 font-bold" type="button" @click="fillForm(product)">Editar</button>
                                <button class="ml-2 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" @click="deleteProduct(product)">Remover</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <UiPagination v-if="pagination" :pagination="pagination" @navigate="loadProducts" />
            </div>
        </div>
    </section>
</template>
