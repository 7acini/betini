import { createApp } from 'vue';
import LoginPage from './components/LoginPage.vue';
import WorkshopDashboard from './components/WorkshopDashboard.vue';

const dashboardElement = document.querySelector('#app');
const loginElement = document.querySelector('#login-app');

if (dashboardElement) {
    createApp(WorkshopDashboard).mount(dashboardElement);
}

if (loginElement) {
    createApp(LoginPage).mount(loginElement);
}
