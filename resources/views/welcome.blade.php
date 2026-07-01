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
        @include('partials.site-header')

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

                <div class="review-rail" aria-label="Avaliacoes de clientes" data-google-reviews>
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

                <p class="site-note" data-review-note>Aguardando credenciais da Google Business Profile API para sincronizar automaticamente.</p>
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

        @include('partials.site-footer')

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

            const reviewsRail = document.querySelector('[data-google-reviews]');
            const reviewNote = document.querySelector('[data-review-note]');

            function starsForRating(rating) {
                const normalizedRating = Math.max(0, Math.min(5, Number(rating || 0)));
                return '★★★★★'.slice(0, normalizedRating).padEnd(5, '☆');
            }

            function renderGoogleReviews(reviews) {
                reviewsRail.innerHTML = '';

                reviews.forEach((review) => {
                    const card = document.createElement('article');
                    card.className = 'review-card';

                    const header = document.createElement('div');
                    const author = document.createElement('strong');
                    const rating = document.createElement('span');
                    const text = document.createElement('p');

                    author.textContent = review.author || 'Cliente Google';
                    rating.textContent = starsForRating(review.rating);
                    text.textContent = review.comment;

                    header.append(author, rating);
                    card.append(header, text);
                    reviewsRail.appendChild(card);
                });
            }

            async function loadGoogleReviews() {
                try {
                    const response = await fetch('/api/landing/google-reviews', {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();

                    if (!payload.synced || !payload.reviews?.length) {
                        return;
                    }

                    renderGoogleReviews(payload.reviews);
                    reviewNote.textContent = `Avaliacoes sincronizadas do Google: ${payload.totalReviewCount ?? payload.reviews.length} registros, nota media ${payload.averageRating ?? '-'}.`;
                } catch (error) {
                    // Mantem as avaliacoes estaticas como fallback visual da landing.
                }
            }

            loadGoogleReviews();
        </script>
    </body>
</html>
