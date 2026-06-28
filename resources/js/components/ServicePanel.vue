<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const emit = defineEmits(['changed']);

const emptyForm = {
    name: '',
    description: '',
    base_price: '',
};

const services = ref([]);
const pagination = ref(null);
const search = ref('');
const isLoading = ref(false);
const isSaving = ref(false);
const editingServiceId = ref(null);
const errors = ref({});
const message = ref(null);
const form = reactive({ ...emptyForm });

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const moneyFormatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

const formTitle = computed(() => (editingServiceId.value ? 'Editar servico' : 'Novo servico'));

function fillForm(service) {
    editingServiceId.value = service.id;
    Object.assign(form, {
        name: service.name ?? '',
        description: service.description ?? '',
        base_price: service.base_price ?? '',
    });
    errors.value = {};
    message.value = null;
}

function resetForm() {
    editingServiceId.value = null;
    Object.assign(form, emptyForm);
    errors.value = {};
}

async function loadServices(pageUrl = null) {
    isLoading.value = true;

    const url = pageUrl ?? `/api/workshop/services?search=${encodeURIComponent(search.value)}`;

    try {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar os servicos.');
        }

        const payload = await response.json();
        services.value = payload.data;
        pagination.value = payload;
    } finally {
        isLoading.value = false;
    }
}

async function saveService() {
    isSaving.value = true;
    errors.value = {};
    message.value = null;

    const isEditing = Boolean(editingServiceId.value);
    const endpoint = isEditing ? `/api/workshop/services/${editingServiceId.value}` : '/api/workshop/services';

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
                base_price: form.base_price === '' ? null : Number(form.base_price),
            }),
        });

        if (response.status === 422) {
            const payload = await response.json();
            errors.value = payload.errors ?? {};
            return;
        }

        if (!response.ok) {
            throw new Error('Nao foi possivel salvar o servico.');
        }

        message.value = isEditing ? 'Servico atualizado com sucesso.' : 'Servico cadastrado com sucesso.';
        resetForm();
        await loadServices();
        emit('changed');
    } finally {
        isSaving.value = false;
    }
}

async function deleteService(service) {
    if (!window.confirm(`Remover ${service.name}?`)) {
        return;
    }

    const response = await fetch(`/api/workshop/services/${service.id}`, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel remover o servico.');
    }

    message.value = 'Servico removido com sucesso.';
    await loadServices();
    emit('changed');
}

onMounted(() => loadServices());
</script>

<template>
    <section id="servicos" class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">Servicos</p>
                <h3 class="text-2xl font-black">Catalogo tecnico da oficina</h3>
            </div>
            <form class="flex gap-2" @submit.prevent="loadServices()">
                <input
                    v-model="search"
                    class="min-w-0 rounded-2xl border border-black/10 bg-[#faf8f2] px-4 py-3 text-sm outline-none focus:border-amber-600"
                    placeholder="Buscar por nome ou descricao"
                    type="search"
                >
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Buscar</button>
            </form>
        </div>

        <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
            {{ message }}
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[420px_1fr]">
            <form class="rounded-3xl bg-[#fff8e8] p-5" @submit.prevent="saveService">
                <h4 class="text-xl font-black">{{ formTitle }}</h4>

                <div class="mt-5 grid gap-4">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Nome</span>
                        <input v-model="form.name" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-amber-600" type="text">
                        <small v-if="errors.name" class="mt-1 block text-red-700">{{ errors.name[0] }}</small>
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Preco base</span>
                        <input v-model="form.base_price" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-amber-600" min="0" step="0.01" type="number">
                        <small v-if="errors.base_price" class="mt-1 block text-red-700">{{ errors.base_price[0] }}</small>
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Descricao</span>
                        <textarea v-model="form.description" class="mt-2 min-h-32 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-amber-600" />
                        <small v-if="errors.description" class="mt-1 block text-red-700">{{ errors.description[0] }}</small>
                    </label>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-2xl bg-amber-700 px-5 py-3 text-sm font-black text-white disabled:opacity-60" :disabled="isSaving" type="submit">
                        {{ isSaving ? 'Salvando...' : 'Salvar servico' }}
                    </button>
                    <button class="rounded-2xl border border-black/10 px-5 py-3 text-sm font-bold" type="button" @click="resetForm">Limpar</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-black/10">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-5 py-4">Servico</th>
                            <th class="px-5 py-4">Descricao</th>
                            <th class="px-5 py-4 text-right">Preco base</th>
                            <th class="px-5 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="4">Carregando servicos...</td>
                        </tr>
                        <tr v-else-if="!services.length">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="4">Nenhum servico cadastrado ainda.</td>
                        </tr>
                        <tr v-for="service in services" v-else :key="service.id" class="border-t border-black/10">
                            <td class="px-5 py-4 font-black">{{ service.name }}</td>
                            <td class="max-w-sm px-5 py-4 text-slate-600">{{ service.description ?? '-' }}</td>
                            <td class="px-5 py-4 text-right font-black">{{ moneyFormatter.format(service.base_price ?? 0) }}</td>
                            <td class="px-5 py-4 text-right">
                                <button class="rounded-xl bg-slate-100 px-3 py-2 font-bold" type="button" @click="fillForm(service)">Editar</button>
                                <button class="ml-2 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" @click="deleteService(service)">Remover</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="pagination" class="flex items-center justify-between border-t border-black/10 bg-[#faf8f2] px-5 py-4 text-sm">
                    <span>Pagina {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                    <div class="flex gap-2">
                        <button class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40" :disabled="!pagination.prev_page_url" type="button" @click="loadServices(pagination.prev_page_url)">Anterior</button>
                        <button class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40" :disabled="!pagination.next_page_url" type="button" @click="loadServices(pagination.next_page_url)">Proxima</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
