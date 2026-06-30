<script setup>
import { computed, onMounted, ref } from 'vue';
import { apiFetch, jsonBody } from '../lib/http';

const emit = defineEmits(['changed']);

const monthNames = [
    'Janeiro',
    'Fevereiro',
    'Marco',
    'Abril',
    'Maio',
    'Junho',
    'Julho',
    'Agosto',
    'Setembro',
    'Outubro',
    'Novembro',
    'Dezembro',
];

const weekDays = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];
const year = ref(new Date().getFullYear());
const appointments = ref([]);
const selectedMonth = ref(new Date().getMonth());
const selectedDate = ref(new Date().toISOString().slice(0, 10));
const isLoading = ref(false);
const message = ref(null);

const todayKey = new Date().toISOString().slice(0, 10);

const appointmentsByDate = computed(() => appointments.value.reduce((grouped, appointment) => {
    const key = appointment.scheduled_date;
    grouped[key] = grouped[key] ?? [];
    grouped[key].push(appointment);
    return grouped;
}, {}));

const selectedDayAppointments = computed(() => appointmentsByDate.value[selectedDate.value] ?? []);

const selectedMonthAppointments = computed(() => appointments.value.filter((appointment) => {
    const date = new Date(`${appointment.scheduled_date}T00:00:00`);
    return date.getMonth() === selectedMonth.value;
}));

function dayKey(monthIndex, day) {
    return `${year.value}-${String(monthIndex + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

function daysInMonth(monthIndex) {
    return new Date(year.value, monthIndex + 1, 0).getDate();
}

function monthOffset(monthIndex) {
    return new Date(year.value, monthIndex, 1).getDay();
}

function monthDays(monthIndex) {
    return Array.from({ length: daysInMonth(monthIndex) }, (_, index) => index + 1);
}

function appointmentClass(dateKey) {
    const dayAppointments = appointmentsByDate.value[dateKey] ?? [];

    if (dateKey < todayKey) {
        return 'is-past';
    }

    if (dayAppointments.some((appointment) => appointment.status === 'Convertido em OS')) {
        return 'is-converted';
    }

    if (dayAppointments.some((appointment) => appointment.status === 'Confirmado')) {
        return 'is-confirmed';
    }

    if (dayAppointments.length) {
        return 'is-booked';
    }

    return '';
}

function selectDay(monthIndex, day) {
    selectedMonth.value = monthIndex;
    selectedDate.value = dayKey(monthIndex, day);
}

async function loadAppointments() {
    isLoading.value = true;
    message.value = null;

    try {
        const response = await apiFetch(`/api/workshop/appointments?year=${year.value}`);

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar os agendamentos.');
        }

        const payload = await response.json();
        appointments.value = payload.appointments;
    } finally {
        isLoading.value = false;
    }
}

async function updateStatus(appointment, status) {
    const response = await apiFetch(`/api/workshop/appointments/${appointment.id}`, {
        method: 'PATCH',
        body: jsonBody({ status }),
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel atualizar o agendamento.');
    }

    message.value = 'Status atualizado.';
    await loadAppointments();
}

async function convertToOrder(appointment) {
    const response = await apiFetch(`/api/workshop/appointments/${appointment.id}/convert-to-order`, {
        method: 'POST',
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel gerar a ordem de servico.');
    }

    const payload = await response.json();
    message.value = `OS #${payload.order?.id ?? payload.order_id} gerada a partir do agendamento.`;
    await loadAppointments();
    emit('changed');
}

onMounted(loadAppointments);
</script>

<template>
    <section id="agendamentos" class="rounded-[2rem] border border-black/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-red-700">Agendamentos</p>
                <h3 class="text-2xl font-black">Calendario anual da oficina</h3>
            </div>

            <div class="flex items-center gap-2">
                <button class="rounded-xl border border-black/10 px-3 py-2 text-sm font-black" type="button" @click="year--; loadAppointments()">-</button>
                <strong class="min-w-20 text-center text-xl">{{ year }}</strong>
                <button class="rounded-xl border border-black/10 px-3 py-2 text-sm font-black" type="button" @click="year++; loadAppointments()">+</button>
            </div>
        </div>

        <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
            {{ message }}
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="betini-calendar-year" :class="{ 'is-loading': isLoading }">
                <article v-for="(month, monthIndex) in monthNames" :key="month" class="betini-calendar-month" :class="{ 'is-selected': selectedMonth === monthIndex }" @click="selectedMonth = monthIndex">
                    <header>
                        <strong>{{ month }}</strong>
                        <span>{{ selectedMonthAppointments.length && selectedMonth === monthIndex ? selectedMonthAppointments.length : (appointments.filter((appointment) => new Date(`${appointment.scheduled_date}T00:00:00`).getMonth() === monthIndex).length) }}</span>
                    </header>

                    <div class="betini-calendar-weekdays">
                        <span v-for="(day, index) in weekDays" :key="`${month}-${index}`">{{ day }}</span>
                    </div>

                    <div class="betini-calendar-days" :style="{ '--month-offset': monthOffset(monthIndex) }">
                        <button
                            v-for="day in monthDays(monthIndex)"
                            :key="day"
                            type="button"
                            :class="[appointmentClass(dayKey(monthIndex, day)), { 'is-selected': selectedDate === dayKey(monthIndex, day) }]"
                            @click.stop="selectDay(monthIndex, day)"
                        >
                            {{ day }}
                        </button>
                    </div>
                </article>
            </div>

            <aside class="betini-schedule-detail">
                <div>
                    <p>{{ monthNames[selectedMonth] }}</p>
                    <h4>{{ selectedDate.split('-').reverse().join('/') }}</h4>
                </div>

                <div class="betini-schedule-legend">
                    <span><i class="is-booked"></i>Novo</span>
                    <span><i class="is-confirmed"></i>Confirmado</span>
                    <span><i class="is-converted"></i>OS gerada</span>
                    <span><i class="is-past"></i>Passado</span>
                </div>

                <div class="betini-schedule-list">
                    <article v-if="!selectedDayAppointments.length" class="betini-schedule-empty">
                        Nenhum agendamento para este dia.
                    </article>

                    <article v-for="appointment in selectedDayAppointments" :key="appointment.id" class="betini-schedule-card">
                        <header>
                            <strong>{{ appointment.scheduled_time }}</strong>
                            <span>{{ appointment.status }}</span>
                        </header>
                        <h5>{{ appointment.lead_name }}</h5>
                        <p>{{ appointment.lead_phone }} - {{ appointment.lead_cpf }}</p>
                        <p>{{ appointment.vehicle_brand }} {{ appointment.vehicle_model }} - {{ appointment.vehicle_plate }}</p>
                        <small>{{ appointment.desired_service || 'Servico a definir' }}</small>

                        <div class="betini-schedule-actions">
                            <button v-if="appointment.status === 'Novo'" type="button" @click="updateStatus(appointment, 'Confirmado')">Confirmar</button>
                            <button v-if="!appointment.order_id && appointment.status !== 'Cancelado'" class="is-primary" type="button" @click="convertToOrder(appointment)">Gerar OS</button>
                            <button v-if="appointment.status !== 'Cancelado' && !appointment.order_id" class="is-danger" type="button" @click="updateStatus(appointment, 'Cancelado')">Cancelar</button>
                        </div>
                    </article>
                </div>
            </aside>
        </div>
    </section>
</template>
