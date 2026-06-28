<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const emit = defineEmits(['changed']);

const emptyForm = {
    name: '',
    cnpj: '',
    phone: '',
    postal_code: '',
    address: '',
    address_number: '',
    complement: '',
    city: '',
    state: '',
    website_url: '',
};

const providers = ref([]);
const pagination = ref(null);
const search = ref('');
const isLoading = ref(false);
const isSaving = ref(false);
const editingProviderId = ref(null);
const errors = ref({});
const message = ref(null);
const form = reactive({ ...emptyForm });

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const formTitle = computed(() => (editingProviderId.value ? 'Editar fornecedor' : 'Novo fornecedor'));

function onlyDigits(value) {
    return String(value ?? '').replace(/\D+/g, '');
}

function fillForm(provider) {
    editingProviderId.value = provider.id;
    Object.assign(form, {
        name: provider.name ?? '',
        cnpj: provider.cnpj ?? '',
        phone: provider.phone ?? '',
        postal_code: provider.postal_code ?? '',
        address: provider.address ?? '',
        address_number: provider.address_number ?? '',
        complement: provider.complement ?? '',
        city: provider.city ?? '',
        state: provider.state ?? '',
        website_url: provider.website_url ?? '',
    });
    errors.value = {};
    message.value = null;
}

function resetForm() {
    editingProviderId.value = null;
    Object.assign(form, emptyForm);
    errors.value = {};
}

async function loadProviders(pageUrl = null) {
    isLoading.value = true;

    const url = pageUrl ?? `/api/workshop/providers?search=${encodeURIComponent(search.value)}`;

    try {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar os fornecedores.');
        }

        const payload = await response.json();
        providers.value = payload.data;
        pagination.value = payload;
    } finally {
        isLoading.value = false;
    }
}

async function saveProvider() {
    isSaving.value = true;
    errors.value = {};
    message.value = null;

    const isEditing = Boolean(editingProviderId.value);
    const endpoint = isEditing ? `/api/workshop/providers/${editingProviderId.value}` : '/api/workshop/providers';

    try {
        const response = await fetch(endpoint, {
            method: isEditing ? 'PUT' : 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                ...form,
                cnpj: onlyDigits(form.cnpj),
                phone: onlyDigits(form.phone),
                postal_code: onlyDigits(form.postal_code),
            }),
        });

        if (response.status === 422) {
            const payload = await response.json();
            errors.value = payload.errors ?? {};
            return;
        }

        if (!response.ok) {
            throw new Error('Nao foi possivel salvar o fornecedor.');
        }

        message.value = isEditing ? 'Fornecedor atualizado com sucesso.' : 'Fornecedor cadastrado com sucesso.';
        resetForm();
        await loadProviders();
        emit('changed');
    } finally {
        isSaving.value = false;
    }
}

async function deleteProvider(provider) {
    if (!window.confirm(`Remover ${provider.name}?`)) {
        return;
    }

    const response = await fetch(`/api/workshop/providers/${provider.id}`, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel remover o fornecedor.');
    }

    message.value = 'Fornecedor removido com sucesso.';
    await loadProviders();
    emit('changed');
}

onMounted(() => loadProviders());
</script>

<template>
    <section id="fornecedores" class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-purple-700">Fornecedores</p>
                <h3 class="text-2xl font-black">Parceiros de pecas e insumos</h3>
            </div>
            <form class="flex gap-2" @submit.prevent="loadProviders()">
                <input
                    v-model="search"
                    class="min-w-0 rounded-2xl border border-black/10 bg-[#faf8f2] px-4 py-3 text-sm outline-none focus:border-purple-600"
                    placeholder="Buscar por nome, CNPJ ou telefone"
                    type="search"
                >
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Buscar</button>
            </form>
        </div>

        <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
            {{ message }}
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[420px_1fr]">
            <form class="rounded-3xl bg-[#f5f0ff] p-5" @submit.prevent="saveProvider">
                <h4 class="text-xl font-black">{{ formTitle }}</h4>

                <div class="mt-5 grid gap-4">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Razao/Nome</span>
                        <input v-model="form.name" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-purple-600" type="text">
                        <small v-if="errors.name" class="mt-1 block text-red-700">{{ errors.name[0] }}</small>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">CNPJ</span>
                            <input v-model="form.cnpj" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-purple-600" maxlength="18" type="text">
                            <small v-if="errors.cnpj" class="mt-1 block text-red-700">{{ errors.cnpj[0] }}</small>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Telefone</span>
                            <input v-model="form.phone" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-purple-600" type="text">
                            <small v-if="errors.phone" class="mt-1 block text-red-700">{{ errors.phone[0] }}</small>
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Site</span>
                        <input v-model="form.website_url" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-purple-600" placeholder="https://" type="url">
                        <small v-if="errors.website_url" class="mt-1 block text-red-700">{{ errors.website_url[0] }}</small>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-[1fr_96px]">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Cidade</span>
                            <input v-model="form.city" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-purple-600" type="text">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">UF</span>
                            <input v-model="form.state" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 uppercase outline-none focus:border-purple-600" maxlength="2" type="text">
                            <small v-if="errors.state" class="mt-1 block text-red-700">{{ errors.state[0] }}</small>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-[1fr_110px]">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Endereco</span>
                            <input v-model="form.address" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-purple-600" type="text">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Numero</span>
                            <input v-model="form.address_number" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-purple-600" type="text">
                        </label>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-2xl bg-purple-700 px-5 py-3 text-sm font-black text-white disabled:opacity-60" :disabled="isSaving" type="submit">
                        {{ isSaving ? 'Salvando...' : 'Salvar fornecedor' }}
                    </button>
                    <button class="rounded-2xl border border-black/10 px-5 py-3 text-sm font-bold" type="button" @click="resetForm">Limpar</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-black/10">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-5 py-4">Fornecedor</th>
                            <th class="px-5 py-4">CNPJ</th>
                            <th class="px-5 py-4">Contato</th>
                            <th class="px-5 py-4">Cidade</th>
                            <th class="px-5 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Carregando fornecedores...</td>
                        </tr>
                        <tr v-else-if="!providers.length">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Nenhum fornecedor cadastrado ainda.</td>
                        </tr>
                        <tr v-for="provider in providers" v-else :key="provider.id" class="border-t border-black/10">
                            <td class="px-5 py-4">
                                <strong class="block font-black">{{ provider.name }}</strong>
                                <a v-if="provider.website_url" class="text-purple-700 underline" :href="provider.website_url" rel="noreferrer" target="_blank">Site</a>
                            </td>
                            <td class="px-5 py-4">{{ provider.cnpj }}</td>
                            <td class="px-5 py-4">{{ provider.phone ?? '-' }}</td>
                            <td class="px-5 py-4">{{ provider.city ?? '-' }}{{ provider.state ? `/${provider.state}` : '' }}</td>
                            <td class="px-5 py-4 text-right">
                                <button class="rounded-xl bg-slate-100 px-3 py-2 font-bold" type="button" @click="fillForm(provider)">Editar</button>
                                <button class="ml-2 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" @click="deleteProvider(provider)">Remover</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="pagination" class="flex items-center justify-between border-t border-black/10 bg-[#faf8f2] px-5 py-4 text-sm">
                    <span>Pagina {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                    <div class="flex gap-2">
                        <button class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40" :disabled="!pagination.prev_page_url" type="button" @click="loadProviders(pagination.prev_page_url)">Anterior</button>
                        <button class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40" :disabled="!pagination.next_page_url" type="button" @click="loadProviders(pagination.next_page_url)">Proxima</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
