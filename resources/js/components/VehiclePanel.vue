<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { apiFetch, jsonBody } from '../lib/http';
import UiPagination from './UiPagination.vue';

const emit = defineEmits(['changed']);

const emptyForm = {
    client_id: '',
    model: '',
    brand: '',
    plate: '',
    year: '',
    current_km: '',
    color: '',
    fuel_type: '',
};

const vehicles = ref([]);
const clients = ref([]);
const pagination = ref(null);
const search = ref('');
const isLoading = ref(false);
const isSaving = ref(false);
const editingVehicleId = ref(null);
const errors = ref({});
const message = ref(null);
const form = reactive({ ...emptyForm });

const formTitle = computed(() => (editingVehicleId.value ? 'Editar veiculo' : 'Novo veiculo'));

function normalizePlate(value) {
    return String(value ?? '').replace(/[^a-zA-Z0-9]+/g, '').toUpperCase();
}

function fillForm(vehicle) {
    editingVehicleId.value = vehicle.id;
    Object.assign(form, {
        client_id: vehicle.client_id ?? '',
        model: vehicle.model ?? '',
        brand: vehicle.brand ?? '',
        plate: vehicle.plate ?? '',
        year: vehicle.year ?? '',
        current_km: vehicle.current_km ?? '',
        color: vehicle.color ?? '',
        fuel_type: vehicle.fuel_type ?? '',
    });
    errors.value = {};
    message.value = null;
}

function resetForm() {
    editingVehicleId.value = null;
    Object.assign(form, emptyForm);
    errors.value = {};
}

async function loadClients() {
    const response = await apiFetch('/api/workshop/clients?per_page=100');

    if (!response.ok) {
        throw new Error('Nao foi possivel carregar os clientes.');
    }

    const payload = await response.json();
    clients.value = payload.data;
}

async function loadVehicles(pageUrl = null) {
    isLoading.value = true;

    const url = pageUrl ?? `/api/workshop/vehicles?search=${encodeURIComponent(search.value)}`;

    try {
        const response = await apiFetch(url);

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar os veiculos.');
        }

        const payload = await response.json();
        vehicles.value = payload.data;
        pagination.value = payload;
    } finally {
        isLoading.value = false;
    }
}

async function saveVehicle() {
    isSaving.value = true;
    errors.value = {};
    message.value = null;

    const isEditing = Boolean(editingVehicleId.value);
    const endpoint = isEditing ? `/api/workshop/vehicles/${editingVehicleId.value}` : '/api/workshop/vehicles';

    try {
        const response = await apiFetch(endpoint, {
            method: isEditing ? 'PUT' : 'POST',
            body: jsonBody({
                ...form,
                plate: normalizePlate(form.plate),
                current_km: form.current_km === '' ? null : Number(form.current_km),
            }),
        });

        if (response.status === 422) {
            const payload = await response.json();
            errors.value = payload.errors ?? {};
            return;
        }

        if (!response.ok) {
            throw new Error('Nao foi possivel salvar o veiculo.');
        }

        message.value = isEditing ? 'Veiculo atualizado com sucesso.' : 'Veiculo cadastrado com sucesso.';
        resetForm();
        await loadVehicles();
        emit('changed');
    } finally {
        isSaving.value = false;
    }
}

async function deleteVehicle(vehicle) {
    if (!window.confirm(`Remover ${vehicle.brand} ${vehicle.model}?`)) {
        return;
    }

    const response = await apiFetch(`/api/workshop/vehicles/${vehicle.id}`, {
        method: 'DELETE',
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel remover o veiculo.');
    }

    message.value = 'Veiculo removido com sucesso.';
    await loadVehicles();
    emit('changed');
}

onMounted(async () => {
    await Promise.all([loadClients(), loadVehicles()]);
});
</script>

<template>
    <section id="veiculos" class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-blue-700">Veiculos</p>
                <h3 class="text-2xl font-black">Frota vinculada aos clientes</h3>
            </div>
            <form class="flex gap-2" @submit.prevent="loadVehicles()">
                <input
                    v-model="search"
                    class="min-w-0 rounded-2xl border border-black/10 bg-[#faf8f2] px-4 py-3 text-sm outline-none focus:border-blue-600"
                    placeholder="Buscar por modelo, marca, placa ou cliente"
                    type="search"
                >
                <button class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Buscar</button>
            </form>
        </div>

        <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
            {{ message }}
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[420px_1fr]">
            <form class="rounded-3xl bg-[#eef4ff] p-5" @submit.prevent="saveVehicle">
                <h4 class="text-xl font-black">{{ formTitle }}</h4>

                <div class="mt-5 grid gap-4">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Cliente</span>
                        <select v-model="form.client_id" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-blue-600">
                            <option value="">Selecione um cliente</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }} - {{ client.cpf }}</option>
                        </select>
                        <small v-if="errors.client_id" class="mt-1 block text-red-700">{{ errors.client_id[0] }}</small>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Marca</span>
                            <input v-model="form.brand" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-blue-600" type="text">
                            <small v-if="errors.brand" class="mt-1 block text-red-700">{{ errors.brand[0] }}</small>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Modelo</span>
                            <input v-model="form.model" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-blue-600" type="text">
                            <small v-if="errors.model" class="mt-1 block text-red-700">{{ errors.model[0] }}</small>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Placa</span>
                            <input v-model="form.plate" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 uppercase outline-none focus:border-blue-600" maxlength="8" type="text">
                            <small v-if="errors.plate" class="mt-1 block text-red-700">{{ errors.plate[0] }}</small>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Ano</span>
                            <input v-model="form.year" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-blue-600" maxlength="4" type="text">
                            <small v-if="errors.year" class="mt-1 block text-red-700">{{ errors.year[0] }}</small>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">KM atual</span>
                            <input v-model="form.current_km" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-blue-600" min="0" type="number">
                            <small v-if="errors.current_km" class="mt-1 block text-red-700">{{ errors.current_km[0] }}</small>
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Combustivel</span>
                            <input v-model="form.fuel_type" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-blue-600" type="text">
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Cor</span>
                        <input v-model="form.color" class="mt-2 w-full rounded-2xl border border-black/10 px-4 py-3 outline-none focus:border-blue-600" type="text">
                    </label>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white disabled:opacity-60" :disabled="isSaving" type="submit">
                        {{ isSaving ? 'Salvando...' : 'Salvar veiculo' }}
                    </button>
                    <button class="rounded-2xl border border-black/10 px-5 py-3 text-sm font-bold" type="button" @click="resetForm">Limpar</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-3xl border border-black/10">
                <table class="w-full min-w-[840px] text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-5 py-4">Veiculo</th>
                            <th class="px-5 py-4">Placa</th>
                            <th class="px-5 py-4">Cliente</th>
                            <th class="px-5 py-4">Ano/KM</th>
                            <th class="px-5 py-4 text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="isLoading">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Carregando veiculos...</td>
                        </tr>
                        <tr v-else-if="!vehicles.length">
                            <td class="px-5 py-8 text-center text-slate-500" colspan="5">Nenhum veiculo cadastrado ainda.</td>
                        </tr>
                        <tr v-for="vehicle in vehicles" v-else :key="vehicle.id" class="border-t border-black/10">
                            <td class="px-5 py-4">
                                <strong class="block font-black">{{ vehicle.brand }} {{ vehicle.model }}</strong>
                                <span class="text-slate-500">{{ vehicle.color ?? '-' }} / {{ vehicle.fuel_type ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 font-black">{{ vehicle.plate }}</td>
                            <td class="px-5 py-4">{{ vehicle.client?.name ?? '-' }}</td>
                            <td class="px-5 py-4">{{ vehicle.year ?? '-' }} / {{ vehicle.current_km ?? 0 }} km</td>
                            <td class="px-5 py-4 text-right">
                                <button class="rounded-xl bg-slate-100 px-3 py-2 font-bold" type="button" @click="fillForm(vehicle)">Editar</button>
                                <button class="ml-2 rounded-xl bg-red-50 px-3 py-2 font-bold text-red-700" type="button" @click="deleteVehicle(vehicle)">Remover</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <UiPagination v-if="pagination" :pagination="pagination" @navigate="loadVehicles" />
            </div>
        </div>
    </section>
</template>
