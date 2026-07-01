<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Contato | Betini Centro Automotivo</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="betini-site">
        @include('partials.site-header')

        <main>
            <section class="contact-hero" style="--photo: url('https://images.unsplash.com/photo-1613214149922-f1809c99b414?auto=format&fit=crop&w=1800&q=82')">
                <div>
                    <p>Contato Betini</p>
                    <h1>Fale com a oficina e encontre o melhor horario para cuidar do seu veiculo.</h1>
                    <a href="/maintenance#scheduling">Agendar atendimento</a>
                </div>
            </section>

            <section class="site-section contact-layout">
                <div class="contact-copy">
                    <p>Atendimento</p>
                    <h2>Estamos prontos para orientar seu proximo servico.</h2>
                    <span>Use os canais abaixo para tirar duvidas, pedir um orcamento ou acompanhar uma solicitacao ja enviada pelo site.</span>
                </div>

                <div class="contact-card-grid">
                    <article class="contact-card">
                        <p>WhatsApp</p>
                        <h3>(00) 00000-0000</h3>
                        <a href="https://wa.me/5500000000000" target="_blank" rel="noopener">Chamar no WhatsApp</a>
                    </article>
                    <article class="contact-card">
                        <p>Telefone</p>
                        <h3>(00) 0000-0000</h3>
                        <a href="tel:+550000000000">Ligar agora</a>
                    </article>
                    <article class="contact-card">
                        <p>E-mail</p>
                        <h3>contato@betini.com.br</h3>
                        <a href="mailto:contato@betini.com.br">Enviar e-mail</a>
                    </article>
                    <article class="contact-card">
                        <p>Endereco</p>
                        <h3>Atualize com o endereco da oficina</h3>
                        <a href="https://www.google.com/maps" target="_blank" rel="noopener">Abrir mapa</a>
                    </article>
                </div>

                <aside class="contact-hours">
                    <p>Horario</p>
                    <h2>Segunda a sexta</h2>
                    <strong>08:00 as 18:00</strong>
                    <span>Sabados mediante agendamento.</span>
                </aside>
            </section>
        </main>

        @include('partials.site-footer')
    </body>
</html>
