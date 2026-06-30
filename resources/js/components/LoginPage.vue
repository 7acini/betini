<script setup>
const loginData = window.betiniLogin ?? {};

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const actionUrl = loginData.action ?? '/login';
const oldEmail = loginData.old?.email ?? '';
const errors = loginData.errors ?? {};
const logoUrl = '/images/betini-logo.png';
</script>

<template>
    <main class="betini-login-shell">
        <section class="betini-login-brand" aria-label="Betini Centro Automotivo">
            <div class="betini-login-brand__logo">
                <img :src="logoUrl" alt="Betini Centro Automotivo">
            </div>
            <div>
                <p>Portal da oficina</p>
                <h1>Controle operacional com a identidade da Betini.</h1>
                <span>Clientes, veiculos, produtos, servicos e ordens em uma rotina mais organizada para o time.</span>
            </div>
        </section>

        <section class="betini-login-card">
            <div class="betini-login-card__head">
                <div>
                    <p>Acesso seguro</p>
                    <h2>Entrar no Portal</h2>
                </div>
            </div>

            <form class="betini-login-form" method="POST" :action="actionUrl">
                <input type="hidden" name="_token" :value="csrfToken">

                <label>
                    <span>E-mail</span>
                    <input
                        name="email"
                        type="email"
                        :value="oldEmail"
                        autofocus
                        required
                    >
                    <small v-if="errors.email">{{ errors.email }}</small>
                </label>

                <label>
                    <span>Senha</span>
                    <input
                        name="password"
                        type="password"
                        required
                    >
                    <small v-if="errors.password">{{ errors.password }}</small>
                </label>

                <label class="betini-login-check">
                    <input name="remember" type="checkbox" value="1">
                    Manter conectado
                </label>

                <button type="submit">
                    Entrar
                </button>
            </form>
        </section>
    </main>
</template>
