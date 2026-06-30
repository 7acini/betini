<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Manutencao | Betini Centro Automotivo</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="betini-site">
        <header class="site-header">
            <a class="site-logo" href="/" aria-label="Betini Centro Automotivo">
                <img src="/images/betini-logo.png" alt="Betini Centro Automotivo">
            </a>
            <nav class="site-nav" aria-label="Navegacao principal">
                <a href="/">Inicio</a>
                <a href="/maintenance">Manutencao</a>
                <a href="#scheduling">Agendamento</a>
                <a href="/portal">Portal</a>
            </nav>
        </header>

        <main>
            <section class="maintenance-hero" style="--photo: url('https://images.unsplash.com/photo-1504222490345-c075b6008014?auto=format&fit=crop&w=1800&q=82')">
                <div>
                    <p>Manutencao Betini</p>
                    <h1>Seu carro cuidado com diagnostico claro, execucao precisa e atendimento sem surpresa.</h1>
                    <a href="#scheduling">Agendamento e Orcamento</a>
                </div>
            </section>

            <section class="site-section maintenance-services">
                <article>
                    <h2>Revisao preventiva</h2>
                    <p>Checagem de itens de seguranca, fluidos, freios, suspensao e sinais de desgaste.</p>
                </article>
                <article>
                    <h2>Diagnostico eletronico</h2>
                    <p>Leitura tecnica para encontrar falhas com mais velocidade e menos tentativa.</p>
                </article>
                <article>
                    <h2>Orcamento orientado</h2>
                    <p>Separacao entre urgente, recomendado e acompanhamento futuro para melhor decisao.</p>
                </article>
            </section>

            <section id="scheduling" class="site-section scheduling-shell">
                <div class="scheduling-copy">
                    <p>Agendamento e Orcamento</p>
                    <h2>Envie os dados essenciais e escolha um horario disponivel.</h2>
                    <span>Esse cadastro entra no Portal como lead. Quando o atendimento acontecer, a equipe converte o agendamento em cliente, veiculo e nova ordem de servico.</span>
                </div>

                <form class="lead-form" data-appointment-form>
                    <div class="lead-form__grid">
                        <label>
                            <span>Nome</span>
                            <input name="lead_name" required maxlength="120" autocomplete="name">
                        </label>
                        <label>
                            <span>CPF</span>
                            <input name="lead_cpf" required maxlength="14" inputmode="numeric" placeholder="000.000.000-00" data-mask="cpf">
                        </label>
                        <label>
                            <span>Telefone</span>
                            <input name="lead_phone" required maxlength="15" autocomplete="tel" inputmode="numeric" placeholder="(00) 00000-0000" data-mask="phone">
                        </label>
                        <label>
                            <span>E-mail</span>
                            <input name="lead_email" type="email" maxlength="160" autocomplete="email">
                        </label>
                        <label class="betini-autocomplete-field">
                            <span>Marca</span>
                            <input name="vehicle_brand" required maxlength="80" autocomplete="off" placeholder="Digite Chev, Volks, Fiat..." data-brand-input>
                            <div class="betini-autocomplete-menu is-hidden" data-brand-menu></div>
                        </label>
                        <label class="betini-autocomplete-field">
                            <span>Modelo</span>
                            <input name="vehicle_model" required maxlength="100" autocomplete="off" placeholder="Selecione uma marca para sugerir modelos" data-model-input>
                            <div class="betini-autocomplete-menu is-hidden" data-model-menu></div>
                        </label>
                        <label>
                            <span>Placa</span>
                            <input name="vehicle_plate" required maxlength="12">
                        </label>
                        <label>
                            <span>Ano</span>
                            <input name="vehicle_year" maxlength="4" inputmode="numeric">
                        </label>
                        <label>
                            <span>Quilometragem</span>
                            <input name="vehicle_current_km" type="number" min="0">
                        </label>
                        <label>
                            <span>Servico desejado</span>
                            <input name="desired_service" maxlength="160" placeholder="Revisao, freio, diagnostico...">
                        </label>
                    </div>
                    <label>
                        <span>Mensagem</span>
                        <textarea name="message" rows="4" maxlength="3000"></textarea>
                    </label>
                    <input type="hidden" name="scheduled_date" data-scheduled-date>
                    <input type="hidden" name="scheduled_time" data-scheduled-time>
                    <button type="submit">Agendar</button>
                    <output data-form-message></output>
                </form>

                <aside class="public-calendar" aria-label="Escolha de data e horario">
                    <div>
                        <button type="button" data-prev-month aria-label="Mes anterior">‹</button>
                        <strong data-calendar-title></strong>
                        <button type="button" data-next-month aria-label="Proximo mes">›</button>
                    </div>
                    <div class="public-calendar__week">
                        <span>D</span><span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span>
                    </div>
                    <div class="public-calendar__days" data-calendar-days></div>
                    <div class="public-calendar__slots" data-time-slots></div>
                </aside>
            </section>
        </main>

        <footer class="site-footer">
            <span>Betini Centro Automotivo</span>
            <a href="/portal">Acessar Portal</a>
        </footer>

        <script>
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const monthNames = ['Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            const availableTimes = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
            const title = document.querySelector('[data-calendar-title]');
            const days = document.querySelector('[data-calendar-days]');
            const slots = document.querySelector('[data-time-slots]');
            const dateInput = document.querySelector('[data-scheduled-date]');
            const timeInput = document.querySelector('[data-scheduled-time]');
            const message = document.querySelector('[data-form-message]');
            const cpfInput = document.querySelector('[data-mask="cpf"]');
            const phoneInput = document.querySelector('[data-mask="phone"]');
            const brandInput = document.querySelector('[data-brand-input]');
            const modelInput = document.querySelector('[data-model-input]');
            const brandMenu = document.querySelector('[data-brand-menu]');
            const modelMenu = document.querySelector('[data-model-menu]');
            let viewedDate = new Date();
            let selectedDate = new Date();
            let selectedBrandCode = '';
            let isSelectingVehicleSuggestion = false;

            function keyFromDate(date) {
                return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            }

            function onlyDigits(value) {
                return String(value || '').replace(/\D+/g, '');
            }

            function maskCpf(value) {
                return onlyDigits(value)
                    .slice(0, 11)
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }

            function maskPhone(value) {
                return onlyDigits(value)
                    .slice(0, 11)
                    .replace(/(\d{2})(\d)/, '($1) $2')
                    .replace(/(\d{5})(\d{1,4})$/, '$1-$2');
            }

            function normalizePlate(value) {
                return String(value || '').replace(/[^a-zA-Z0-9]+/g, '').toUpperCase();
            }

            function debounce(callback, delay = 260) {
                let timer = null;

                return (...args) => {
                    window.clearTimeout(timer);
                    timer = window.setTimeout(() => callback(...args), delay);
                };
            }

            function hideMenu(menu) {
                menu.classList.add('is-hidden');
                menu.innerHTML = '';
            }

            function renderVehicleSuggestions(menu, items, onSelect, loadingText = '') {
                menu.innerHTML = '';

                if (loadingText) {
                    const loading = document.createElement('span');
                    loading.textContent = loadingText;
                    menu.appendChild(loading);
                }

                items.forEach((item) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = item.name;
                    button.addEventListener('click', () => onSelect(item));
                    menu.appendChild(button);
                });

                menu.classList.toggle('is-hidden', !loadingText && !items.length);
            }

            async function loadBrandSuggestions(searchTerm) {
                if (String(searchTerm || '').trim().length < 2) {
                    hideMenu(brandMenu);
                    return;
                }

                renderVehicleSuggestions(brandMenu, [], null, 'Consultando marcas...');

                const response = await fetch(`/api/landing/vehicle-catalog/brands?search=${encodeURIComponent(searchTerm)}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    hideMenu(brandMenu);
                    return;
                }

                const payload = await response.json();
                renderVehicleSuggestions(brandMenu, payload.data || [], (brand) => {
                    isSelectingVehicleSuggestion = true;
                    selectedBrandCode = brand.code;
                    brandInput.value = brand.name;
                    modelInput.value = '';
                    modelInput.placeholder = 'Digite Onix, Corolla, Gol...';
                    hideMenu(brandMenu);
                    hideMenu(modelMenu);
                });
            }

            async function loadModelSuggestions(searchTerm) {
                if (!selectedBrandCode || String(searchTerm || '').trim().length < 2) {
                    hideMenu(modelMenu);
                    return;
                }

                renderVehicleSuggestions(modelMenu, [], null, 'Consultando modelos...');

                const response = await fetch(`/api/landing/vehicle-catalog/models?brand_code=${encodeURIComponent(selectedBrandCode)}&search=${encodeURIComponent(searchTerm)}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    hideMenu(modelMenu);
                    return;
                }

                const payload = await response.json();
                renderVehicleSuggestions(modelMenu, payload.data || [], (model) => {
                    isSelectingVehicleSuggestion = true;
                    modelInput.value = model.name;
                    hideMenu(modelMenu);
                });
            }

            const debouncedLoadBrandSuggestions = debounce(loadBrandSuggestions);
            const debouncedLoadModelSuggestions = debounce(loadModelSuggestions);

            function renderSlots() {
                dateInput.value = keyFromDate(selectedDate);
                slots.innerHTML = '';
                availableTimes.forEach((time) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = time;
                    button.className = timeInput.value === time ? 'is-selected' : '';
                    button.addEventListener('click', () => {
                        timeInput.value = time;
                        renderSlots();
                    });
                    slots.appendChild(button);
                });
            }

            function renderCalendar() {
                const year = viewedDate.getFullYear();
                const month = viewedDate.getMonth();
                const today = new Date();
                title.textContent = `${monthNames[month]} ${year}`;
                days.innerHTML = '';
                days.style.setProperty('--calendar-offset', new Date(year, month, 1).getDay());

                for (let day = 1; day <= new Date(year, month + 1, 0).getDate(); day += 1) {
                    const date = new Date(year, month, day);
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = day;
                    button.disabled = date < new Date(today.getFullYear(), today.getMonth(), today.getDate());
                    button.className = keyFromDate(date) === keyFromDate(selectedDate) ? 'is-selected' : '';
                    button.addEventListener('click', () => {
                        selectedDate = date;
                        timeInput.value = '';
                        renderCalendar();
                        renderSlots();
                    });
                    days.appendChild(button);
                }
            }

            document.querySelector('[data-prev-month]').addEventListener('click', () => {
                viewedDate = new Date(viewedDate.getFullYear(), viewedDate.getMonth() - 1, 1);
                renderCalendar();
            });

            document.querySelector('[data-next-month]').addEventListener('click', () => {
                viewedDate = new Date(viewedDate.getFullYear(), viewedDate.getMonth() + 1, 1);
                renderCalendar();
            });

            cpfInput.addEventListener('input', () => {
                cpfInput.value = maskCpf(cpfInput.value);
            });

            phoneInput.addEventListener('input', () => {
                phoneInput.value = maskPhone(phoneInput.value);
            });

            brandInput.addEventListener('input', () => {
                if (isSelectingVehicleSuggestion) {
                    isSelectingVehicleSuggestion = false;
                    return;
                }

                selectedBrandCode = '';
                modelInput.value = '';
                modelInput.placeholder = 'Selecione uma marca para sugerir modelos';
                hideMenu(modelMenu);
                debouncedLoadBrandSuggestions(brandInput.value);
            });

            modelInput.addEventListener('input', () => {
                if (isSelectingVehicleSuggestion) {
                    isSelectingVehicleSuggestion = false;
                    return;
                }

                debouncedLoadModelSuggestions(modelInput.value);
            });

            document.addEventListener('click', (event) => {
                if (!brandInput.contains(event.target) && !brandMenu.contains(event.target)) {
                    hideMenu(brandMenu);
                }

                if (!modelInput.contains(event.target) && !modelMenu.contains(event.target)) {
                    hideMenu(modelMenu);
                }
            });

            document.querySelector('[data-appointment-form]').addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!timeInput.value) {
                    message.value = 'Escolha um horario para continuar.';
                    return;
                }

                const payload = Object.fromEntries(new FormData(event.currentTarget).entries());
                payload.lead_cpf = maskCpf(payload.lead_cpf);
                payload.lead_phone = maskPhone(payload.lead_phone);
                payload.vehicle_plate = normalizePlate(payload.vehicle_plate);

                const response = await fetch('/api/landing/appointments', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();
                message.value = response.ok ? 'Agendamento solicitado. A equipe Betini fara a confirmacao.' : (data.message || 'Nao foi possivel agendar.');

                if (response.ok) {
                    event.currentTarget.reset();
                    timeInput.value = '';
                    dateInput.value = keyFromDate(selectedDate);
                    renderSlots();
                }
            });

            renderCalendar();
            renderSlots();
        </script>
    </body>
</html>
