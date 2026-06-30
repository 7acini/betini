<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Betini Centro Automotivo</title>
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
                <a href="#avaliacoes">Avaliacoes</a>
                <a href="#conteudos">Conteudos</a>
                <a href="/portal">Portal</a>
            </nav>
        </header>

        <main>
            <section class="site-hero" aria-label="Servicos automotivos">
                <div class="site-hero__slides" data-carousel>
                    <article class="site-hero__slide is-active" style="--photo: url('https://images.unsplash.com/photo-1632823471565-1ecdf5c7d879?auto=format&fit=crop&w=1800&q=82')">
                        <div>
                            <p>Oficina mecanica completa</p>
                            <h1>Precisao para manter seu carro pronto para a rua.</h1>
                            <a href="/maintenance#scheduling">Agendamento e Orcamento</a>
                        </div>
                    </article>
                    <article class="site-hero__slide" style="--photo: url('https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1800&q=82')">
                        <div>
                            <p>Diagnostico e revisao</p>
                            <h2>Equipamentos, metodo e atencao em cada detalhe.</h2>
                            <a href="/maintenance">Conhecer manutencao</a>
                        </div>
                    </article>
                    <article class="site-hero__slide" style="--photo: url('https://images.unsplash.com/photo-1625047509248-ec889cbff17f?auto=format&fit=crop&w=1800&q=82')">
                        <div>
                            <p>Atendimento transparente</p>
                            <h2>Seu veiculo acompanhado do primeiro contato ate a OS.</h2>
                            <a href="#avaliacoes">Ver avaliacoes</a>
                        </div>
                    </article>
                </div>

                <div class="site-hero__controls" aria-label="Controle do carrossel">
                    <button class="is-active" type="button" data-slide-dot="0" aria-label="Slide 1"></button>
                    <button type="button" data-slide-dot="1" aria-label="Slide 2"></button>
                    <button type="button" data-slide-dot="2" aria-label="Slide 3"></button>
                </div>
            </section>

            <section id="avaliacoes" class="site-section site-section--reviews">
                <div class="site-section__head">
                    <p>Google Reviews</p>
                    <h2>As ultimas experiencias de quem passou pela Betini.</h2>
                </div>

                <div class="review-rail" aria-label="Avaliacoes de clientes">
                    @foreach ([
                        ['name' => 'Marcos H.', 'text' => 'Atendimento claro, revisao feita dentro do prazo e explicaram tudo antes de executar.'],
                        ['name' => 'Priscila R.', 'text' => 'Gostei da organizacao. Recebi o orcamento e aprovei sem surpresa no final.'],
                        ['name' => 'Renato F.', 'text' => 'Levei para diagnostico e resolveram uma falha que outras oficinas nao acharam.'],
                        ['name' => 'Carla M.', 'text' => 'Equipe cuidadosa, local limpo e retorno rapido pelo WhatsApp.'],
                        ['name' => 'Eduardo S.', 'text' => 'Servico bem feito na suspensao e carro entregue alinhado. Recomendo.'],
                        ['name' => 'Bianca L.', 'text' => 'Primeira visita e ja senti confianca. Muito profissionais.'],
                        ['name' => 'Andre P.', 'text' => 'Orcamento honesto, fotos das pecas e explicacao tecnica sem enrolacao.'],
                        ['name' => 'Juliana T.', 'text' => 'Agendamento simples e atendimento pontual. Voltarei nas proximas revisoes.'],
                        ['name' => 'Felipe C.', 'text' => 'Mecanicos atenciosos e servico de freio ficou perfeito.'],
                        ['name' => 'Nadia A.', 'text' => 'Excelente pos-atendimento. Conferiram tudo antes da entrega.'],
                    ] as $review)
                        <article class="review-card">
                            <div>
                                <strong>{{ $review['name'] }}</strong>
                                <span>★★★★★</span>
                            </div>
                            <p>{{ $review['text'] }}</p>
                        </article>
                    @endforeach
                </div>

                <p class="site-note">A integracao automatica com Google pode ser feita via Google Places API usando Place ID e chave da conta da empresa.</p>
            </section>

            <section class="site-section service-band">
                <div>
                    <p>Servicos</p>
                    <h2>Manutencao preventiva, corretiva e diagnostico eletronico.</h2>
                </div>
                <a href="/maintenance">Ver manutencao</a>
            </section>

            <section id="conteudos" class="site-section content-grid">
                <article>
                    <img src="https://images.unsplash.com/photo-1613214149922-f1809c99b414?auto=format&fit=crop&w=900&q=80" alt="Mecanico analisando motor">
                    <div>
                        <p>Instagram</p>
                        <h3>Bastidores, entregas e servicos recentes.</h3>
                    </div>
                </article>
                <article>
                    <img src="https://images.unsplash.com/photo-1603386329225-868f9b1ee6c9?auto=format&fit=crop&w=900&q=80" alt="Detalhe de roda esportiva">
                    <div>
                        <p>YouTube</p>
                        <h3>Dicas para cuidar melhor do seu veiculo.</h3>
                    </div>
                </article>
                <article>
                    <img src="https://images.unsplash.com/photo-1565043666747-69f6646db940?auto=format&fit=crop&w=900&q=80" alt="Ferramentas de oficina">
                    <div>
                        <p>Cursos</p>
                        <h3>Conteudos tecnicos e treinamentos em breve.</h3>
                    </div>
                </article>
            </section>
        </main>

        <footer class="site-footer">
            <span>Betini Centro Automotivo</span>
            <a href="/maintenance#scheduling">Agendar atendimento</a>
        </footer>

        <script>
            const slides = [...document.querySelectorAll('.site-hero__slide')];
            const dots = [...document.querySelectorAll('[data-slide-dot]')];
            let activeSlide = 0;

            function showSlide(index) {
                activeSlide = index;
                slides.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === index));
                dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === index));
            }

            dots.forEach((dot) => dot.addEventListener('click', () => showSlide(Number(dot.dataset.slideDot))));
            window.setInterval(() => showSlide((activeSlide + 1) % slides.length), 5200);
        </script>
    </body>
</html>
