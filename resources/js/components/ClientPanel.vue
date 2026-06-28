<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const emit = defineEmits(['changed']);

const emptyForm = {
    name: '',
    cpf: '',
    phone: '',
    postal_code: '',
    address: '',
    address_number: '',
    complement: '',
    city: '',
    state: '',
};

const clients = ref([]);
const pagination = ref(null);
const search = ref('');
const isLoading = ref(false);
const isSaving = ref(false);
const editingClientId = ref(null);
const errors = ref({});
const message = ref(null);
const form = reactive({ ...emptyForm });

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const formTitle = computed(() => (editingClientId.value ? 'Editar cliente' : 'Novo cliente'));

function onlyDigits(value) {
    return String(value ?? '').replace(/\D+/g, '');
}

function fillForm(client) {
    editingClientId.value = client.id;
    Object.assign(form, {
        name: client.name ?? '',
        cpf: client.cpf ?? '',
        phone: client.phone ?? '',
        postal_code: client.postal_code ?? '',
        address: client.address ?? '',
        address_number: client.address_number ?? '',
        complement: client.complement ?? '',
        city: client.city ?? '',
        state: client.state ?? '',
    });
    errors.value = {};
    message.value = null;
}

function resetForm() {
    editingClientId.value = null;
    Object.assign(form, emptyForm);
    errors.value = {};
}

async function loadClients(pageUrl = null) {
    isLoading.value = true;

    const url = pageUrl ?? `/api/workshop/clients?search=${encodeURIComponent(search.value)}`;

    try {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar os clientes.');
        }

        const payload = await response.json();
        clients.value = payload.data;
        pagination.value = payload;
    } finally {
        isLoading.value = false;
    }
}

async function saveClient() {
    isSaving.value = true;
    errors.value = {};
    message.value = null;

    const isEditing = Boolean(editingClientId.value);
    const endpoint = isEditing ? `/api/workshop/clients/${editingClientId.value}` : '/api/workshop/clients';

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
                cpf: onlyDigits(form.cpf),
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
            throw new Error('Nao foi possivel salvar o cliente.');
        }

        message.value = isEditing ? 'Cliente atualizado com sucesso.' : 'Cliente cadastrado com sucesso.';
        resetForm();
        await loadClients();
        emit('changed');
    } finally {
        isSaving.value = false;
    }
}

async function deleteClient(client) {
    if (!window.confirm(`Remover ${client.name}?`)) {
        return;
    }

    const response = await fetch(`/api/workshop/clients/${client.id}`, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel remover o cliente.');
    }

    message.value = 'Cliente removido com sucesso.';
    await loadClients();
    emit('changed');
}

onMounted(() => loadClients());
</script>

<template>
    <section id="clientes" class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-600">Clientes</p>
                <h3 class="text-2xl font-black">Cadastro central da oficina</h3>
            </div>
            <form class="flex gap-2" @submit.prevent="loadClients()">
                <input
                    v-model="search"
                    class="min-w-0 rounded-2xl border border-black/10 bg-[#faf8f2] px-4 py-3 text-sm outline-none focus:border-orange-500"
                    placeholder="Buscar por nome, CPF ou telefone"
                    type="search"
                >
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Buscar</button>
            </form>
        </div>

        <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
            {{ message }}
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[420px_1fr]">
            <form class="rounded-3xl bg-[#faf8f2] p-5" @submit.prevent="saveClient">
                <h4 class="text-xl font-black">{{ formTitle }}</h4>

                <div class="mt-5 grid gap-4">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Nome</span>
                        <input v-model="form.name" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-500" type="text">
                        <small v-if="errors.name" class="mt-1 block text-red-700">{{ errors.name[0] }}</small>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">CPF</span>
                            <input v-model="form.cpf" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-500" maxlength="14" type="text">
                            <small v-if="errors.cpf" class="mt-1 block text-red-700">{{ errors.cpf[0] }}</small>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Telefone</span>
                            <input v-model="form.phone" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-500" type="text">
                            <small v-if="errors.phone" class="mt-1 block text-red-700">{{ errors.phone[0] }}</small>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-[1fr_96px]">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Cidade</span>
                            <input v-model="form.city" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-500" type="text">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">UF</span>
                            <input v-model="form.state" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 uppercase outline-none focus:border-orange-500" maxlength="2" type="text">
                            <small v-if="errors.state" class="mt-1 block text-red-700">{{ errors.state[0] }}</small>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-[1fr_110px]">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Endereco</span>
                            <input v-model="form.address" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-500" type="text">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Numero</span>
                            <input v-model="form.address_number" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-orange-500" type="text">
                        </label>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white disabled:opacity-60" :disabled="isSaving" type="submit">
                        {{ isSaving ? 'Salvando...' : 'Salvar cliente' }}
                    </button>
                    <button class="rounded-2xl border border-black/10 px-5 py-3 text-sm font-bold" type="button" @click="resetForm">Limpar</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-black/10">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-5 py-4">Cliente</th>
                            <th class="px-5 py-4">CPF</th>
                            <th class="px-5 py-4">Telefone</th>
                            <th class="px-5 py-4">Cidade</th>
                            <th class="px-5 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Carregando clientes...</td>
                        </tr>
                        <tr v-else-if="!clients.length">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Nenhum cliente cadastrado ainda.</td>
                        </tr>
                        <tr v-for="client in clients" v-else :key="client.id" class="border-t border-black/10">
                            <td class="px-5 py-4 font-black">{{ client.name }}</td>
                            <td class="px-5 py-4">{{ client.cpf }}</td>
                            <td class="px-5 py-4">{{ client.phone ?? '-' }}</td>
                            <td class="px-5 py-4">{{ client.city ?? '-' }}{{ client.state ? `/${client.state}` : '' }}</td>
                            <td class="px-5 py-4 text-right">
                                <button class="rounded-xl bg-slate-100 px-3 py-2 font-bold" type="button" @click="fillForm(client)">Editar</button>
                                <button class="ml-2 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" @click="deleteClient(client)">Remover</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="pagination" class="flex items-center justify-between border-t border-black/10 bg-[#faf8f2] px-5 py-4 text-sm">
                    <span>Pagina {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                    <div class="flex gap-2">
                        <button class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40" :disabled="!pagination.prev_page_url" type="button" @click="loadClients(pagination.prev_page_url)">Anterior</button>
                        <button class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40" :disabled="!pagination.next_page_url" type="button" @click="loadClients(pagination.next_page_url)">Proxima</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
