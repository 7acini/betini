-- Betini ERP - dados ficticios para apresentacao
-- Banco alvo: MySQL 8.x
-- Uso sugerido dentro do container:
--   docker compose exec -T database mysql -ubetini -psecret betini < database/demo_presentation_data.sql
--
-- O script remove apenas os dados de demonstracao conhecidos antes de reinserir,
-- evitando duplicidade quando executado mais de uma vez.

START TRANSACTION;

SET @now := NOW();

DELETE os
FROM order_services os
INNER JOIN orders o ON o.id = os.order_id
INNER JOIN clients c ON c.id = o.client_id
WHERE c.cpf IN ('11122233344', '22233344455', '33344455566', '44455566677', '55566677788');

DELETE oi
FROM order_items oi
INNER JOIN orders o ON o.id = oi.order_id
INNER JOIN clients c ON c.id = o.client_id
WHERE c.cpf IN ('11122233344', '22233344455', '33344455566', '44455566677', '55566677788');

DELETE o
FROM orders o
INNER JOIN clients c ON c.id = o.client_id
WHERE c.cpf IN ('11122233344', '22233344455', '33344455566', '44455566677', '55566677788');

DELETE FROM vehicles WHERE plate IN ('BET1A23', 'BTI2B34', 'OFI3C45', 'MEC4D56', 'CAR5E67');
DELETE FROM products WHERE barcode IN ('789BET000001', '789BET000002', '789BET000003', '789BET000004', '789BET000005', '789BET000006');
DELETE FROM services WHERE name IN ('Revisao preventiva completa', 'Troca de oleo e filtros', 'Diagnostico eletronico', 'Alinhamento e balanceamento', 'Servico de freios', 'Higienizacao automotiva');
DELETE FROM providers WHERE cnpj IN ('11222333000144', '22333444000155', '33444555000166');
DELETE FROM clients WHERE cpf IN ('11122233344', '22233344455', '33344455566', '44455566677', '55566677788');
DELETE FROM users WHERE email = 'demo@betini.local';

INSERT INTO users (name, email, role, email_verified_at, password, remember_token, created_at, updated_at)
VALUES (
    'Consultor Demo Betini',
    'demo@betini.local',
    'admin',
    @now,
    '$2y$12$ebGOZikzpVZyNGg36Jg0AumuT4PjnsJNn7k/GnI/fNkqP2ommRgbe',
    NULL,
    @now,
    @now
);

INSERT INTO clients (name, cpf, phone, postal_code, address, address_number, complement, city, state, created_at, updated_at)
VALUES
    ('Mariana Lopes Almeida', '11122233344', '(11) 98840-1001', '09020000', 'Avenida Portugal', '812', 'Apto 42', 'Santo Andre', 'SP', @now, @now),
    ('Renato Vieira Costa', '22233344455', '(11) 97731-2050', '09510010', 'Rua Amazonas', '1440', NULL, 'Sao Caetano do Sul', 'SP', @now, @now),
    ('Patricia Gomes Nunes', '33344455566', '(11) 96622-3311', '09720000', 'Rua Marechal Deodoro', '220', 'Casa 2', 'Sao Bernardo do Campo', 'SP', @now, @now),
    ('Bruno Henrique Silva', '44455566677', '(11) 95513-7744', '09190000', 'Rua das Figueiras', '620', NULL, 'Santo Andre', 'SP', @now, @now),
    ('Camila Rocha Martins', '55566677788', '(11) 94404-8899', '09615000', 'Avenida Kennedy', '1313', 'Sala 5', 'Sao Bernardo do Campo', 'SP', @now, @now);

INSERT INTO providers (name, cnpj, phone, postal_code, address, address_number, complement, city, state, website_url, created_at, updated_at)
VALUES
    ('Auto Pecas Rota ABC Ltda', '11222333000144', '(11) 4002-1010', '09080010', 'Rua Catequese', '900', NULL, 'Santo Andre', 'SP', 'https://rotabc.example.com', @now, @now),
    ('Distribuidora Prime Filtros', '22333444000155', '(11) 4010-2020', '09531000', 'Avenida Goias', '1550', 'Galpao B', 'Sao Caetano do Sul', 'SP', 'https://primefiltros.example.com', @now, @now),
    ('Freios Forte Comercio Automotivo', '33444555000166', '(11) 4020-3030', '09750000', 'Rua Jurubatuba', '2100', NULL, 'Sao Bernardo do Campo', 'SP', 'https://freiosforte.example.com', @now, @now);

INSERT INTO services (name, description, base_price, created_at, updated_at)
VALUES
    ('Revisao preventiva completa', 'Checklist completo com fluidos, filtros, freios, pneus, scanner e teste de rodagem.', 420.00, @now, @now),
    ('Troca de oleo e filtros', 'Substituicao de oleo do motor, filtro de oleo, filtro de ar e filtro de cabine.', 180.00, @now, @now),
    ('Diagnostico eletronico', 'Leitura de scanner, analise de falhas e relatorio tecnico.', 150.00, @now, @now),
    ('Alinhamento e balanceamento', 'Alinhamento 3D, balanceamento das rodas e calibragem.', 160.00, @now, @now),
    ('Servico de freios', 'Inspecao e substituicao de pastilhas, discos e fluido quando necessario.', 280.00, @now, @now),
    ('Higienizacao automotiva', 'Higienizacao interna, limpeza tecnica e oxi-sanitizacao.', 240.00, @now, @now);

SET @provider_rota := (SELECT id FROM providers WHERE cnpj = '11222333000144');
SET @provider_filtros := (SELECT id FROM providers WHERE cnpj = '22333444000155');
SET @provider_freios := (SELECT id FROM providers WHERE cnpj = '33444555000166');

INSERT INTO products (provider_id, category, name, price, description, barcode, photo_path, created_at, updated_at)
VALUES
    (@provider_filtros, 'Filtros', 'Filtro de oleo Wega WO120', 42.90, 'Filtro de oleo para motores flex 1.0 a 1.6.', '789BET000001', NULL, @now, @now),
    (@provider_filtros, 'Filtros', 'Filtro de ar Tecfil ARL8832', 59.90, 'Filtro de ar do motor para veiculos compactos.', '789BET000002', NULL, @now, @now),
    (@provider_rota, 'Lubrificantes', 'Oleo sintetico 5W30 SN 1L', 48.50, 'Oleo sintetico para motores flex e gasolina.', '789BET000003', NULL, @now, @now),
    (@provider_freios, 'Freios', 'Pastilha de freio dianteira Cobreq', 189.90, 'Jogo de pastilhas dianteiras com baixo ruido.', '789BET000004', NULL, @now, @now),
    (@provider_freios, 'Freios', 'Fluido de freio DOT 4 500ml', 39.90, 'Fluido DOT 4 para manutencao preventiva.', '789BET000005', NULL, @now, @now),
    (@provider_rota, 'Eletrica', 'Bateria 60Ah Moura equivalente', 429.90, 'Bateria automotiva 60Ah com garantia de fabrica.', '789BET000006', NULL, @now, @now);

SET @client_mariana := (SELECT id FROM clients WHERE cpf = '11122233344');
SET @client_renato := (SELECT id FROM clients WHERE cpf = '22233344455');
SET @client_patricia := (SELECT id FROM clients WHERE cpf = '33344455566');
SET @client_bruno := (SELECT id FROM clients WHERE cpf = '44455566677');
SET @client_camila := (SELECT id FROM clients WHERE cpf = '55566677788');

INSERT INTO vehicles (client_id, model, brand, plate, year, current_km, color, fuel_type, created_at, updated_at)
VALUES
    (@client_mariana, 'Onix LTZ 1.0 Turbo', 'Chevrolet', 'BET1A23', '2021', 48200, 'Prata', 'Flex', @now, @now),
    (@client_renato, 'Corolla XEi 2.0', 'Toyota', 'BTI2B34', '2020', 73600, 'Preto', 'Flex', @now, @now),
    (@client_patricia, 'Compass Longitude', 'Jeep', 'OFI3C45', '2022', 38900, 'Branco', 'Diesel', @now, @now),
    (@client_bruno, 'HB20 Comfort', 'Hyundai', 'MEC4D56', '2019', 91400, 'Cinza', 'Flex', @now, @now),
    (@client_camila, 'T-Cross Highline', 'Volkswagen', 'CAR5E67', '2023', 21450, 'Vermelho', 'Flex', @now, @now);

SET @service_revisao := (SELECT id FROM services WHERE name = 'Revisao preventiva completa');
SET @service_oleo := (SELECT id FROM services WHERE name = 'Troca de oleo e filtros');
SET @service_scanner := (SELECT id FROM services WHERE name = 'Diagnostico eletronico');
SET @service_alinhamento := (SELECT id FROM services WHERE name = 'Alinhamento e balanceamento');
SET @service_freios := (SELECT id FROM services WHERE name = 'Servico de freios');

SET @produto_filtro_oleo := (SELECT id FROM products WHERE barcode = '789BET000001');
SET @produto_filtro_ar := (SELECT id FROM products WHERE barcode = '789BET000002');
SET @produto_oleo := (SELECT id FROM products WHERE barcode = '789BET000003');
SET @produto_pastilha := (SELECT id FROM products WHERE barcode = '789BET000004');
SET @produto_fluido := (SELECT id FROM products WHERE barcode = '789BET000005');
SET @produto_bateria := (SELECT id FROM products WHERE barcode = '789BET000006');

INSERT INTO orders (client_id, service_id, payment_method, status, observation, service_total, items_total, total, created_at, updated_at)
VALUES (@client_mariana, @service_revisao, 'Cartao de credito', 'Em andamento', 'Cliente relatou ruido ao frear e pediu revisao antes de viagem.', 700.00, 375.30, 1075.30, DATE_SUB(@now, INTERVAL 2 DAY), @now);
SET @order_mariana := LAST_INSERT_ID();

INSERT INTO orders (client_id, service_id, payment_method, status, observation, service_total, items_total, total, created_at, updated_at)
VALUES (@client_renato, @service_oleo, 'Pix', 'Concluido', 'Troca de oleo finalizada com reset do painel e alinhamento preventivo.', 340.00, 296.80, 636.80, DATE_SUB(@now, INTERVAL 5 DAY), DATE_SUB(@now, INTERVAL 4 DAY));
SET @order_renato := LAST_INSERT_ID();

INSERT INTO orders (client_id, service_id, payment_method, status, observation, service_total, items_total, total, created_at, updated_at)
VALUES (@client_patricia, @service_scanner, 'Dinheiro', 'Aberto', 'Luz de injecao acesa. Aguardando diagnostico completo.', 150.00, 0.00, 150.00, DATE_SUB(@now, INTERVAL 1 DAY), @now);
SET @order_patricia := LAST_INSERT_ID();

INSERT INTO orders (client_id, service_id, payment_method, status, observation, service_total, items_total, total, created_at, updated_at)
VALUES (@client_bruno, @service_freios, 'Cartao de debito', 'Em andamento', 'Pastilhas no limite e fluido escuro. Peca solicitada ao fornecedor.', 280.00, 229.80, 509.80, DATE_SUB(@now, INTERVAL 3 DAY), @now);
SET @order_bruno := LAST_INSERT_ID();

INSERT INTO orders (client_id, service_id, payment_method, status, observation, service_total, items_total, total, created_at, updated_at)
VALUES (@client_camila, @service_alinhamento, 'Pix', 'Aberto', 'Cliente agendou atendimento para sabado de manha.', 160.00, 0.00, 160.00, DATE_ADD(@now, INTERVAL 2 DAY), @now);
SET @order_camila := LAST_INSERT_ID();


INSERT INTO order_services (order_id, service_id, price, quantity, subtotal, created_at, updated_at)
VALUES
    (@order_mariana, @service_revisao, 420.00, 1, 420.00, @now, @now),
    (@order_mariana, @service_freios, 280.00, 1, 280.00, @now, @now),
    (@order_renato, @service_oleo, 180.00, 1, 180.00, @now, @now),
    (@order_renato, @service_alinhamento, 160.00, 1, 160.00, @now, @now),
    (@order_patricia, @service_scanner, 150.00, 1, 150.00, @now, @now),
    (@order_bruno, @service_freios, 280.00, 1, 280.00, @now, @now),
    (@order_camila, @service_alinhamento, 160.00, 1, 160.00, @now, @now);

INSERT INTO order_items (order_id, product_id, price, quantity, subtotal, created_at, updated_at)
VALUES
    (@order_mariana, @produto_pastilha, 189.90, 1, 189.90, @now, @now),
    (@order_mariana, @produto_fluido, 39.90, 1, 39.90, @now, @now),
    (@order_mariana, @produto_oleo, 48.50, 3, 145.50, @now, @now),
    (@order_renato, @produto_filtro_oleo, 42.90, 1, 42.90, @now, @now),
    (@order_renato, @produto_filtro_ar, 59.90, 1, 59.90, @now, @now),
    (@order_renato, @produto_oleo, 48.50, 4, 194.00, @now, @now),
    (@order_bruno, @produto_pastilha, 189.90, 1, 189.90, @now, @now),
    (@order_bruno, @produto_fluido, 39.90, 1, 39.90, @now, @now);

COMMIT;

SELECT 'Dados ficticios Betini inseridos com sucesso.' AS message;
SELECT 'Login demo: demo@betini.local / Betini@123' AS credentials;
