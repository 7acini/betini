# Betini ERP Oficina

ERP moderno para gerenciamento de oficinas mecanicas, construido em Laravel e Vue.js a partir da base funcional do projeto legado `~/Documents/Github/oficina-mecanica`.

## Escopo Inicial

- Dashboard operacional em Vue.js.
- API JSON para resumo da oficina em `/api/workshop/dashboard`.
- Modelagem Laravel moderna para clientes, veiculos, fornecedores, produtos, servicos, ordens de servico e itens de ordem.
- Frontend responsivo sem CRUDBooster, jQuery ou AdminLTE.

## Stack

- PHP 8.3+
- Laravel 13
- Vue 3
- Vite 8
- Tailwind CSS 4

## Como Rodar

```bash
composer install
npm install
php artisan migrate
npm run dev
php artisan serve
```

Para validar a aplicacao:

```bash
php artisan test
npm run build
```

## Origem Legada

O projeto `oficina-mecanica` foi usado como referencia de negocio para preservar os principais modulos:

- Clientes
- Veiculos
- Fornecedores
- Produtos
- Servicos
- Pedidos/ordens de servico

A implementacao atual troca a abordagem CRUDBooster/AdminLTE por Laravel Eloquent, endpoints JSON e componentes Vue.

## Padrao de Commits

As alteracoes devem seguir o formato:

```text
tipo(escopo): descricao
```

Exemplos:

- `feat(dominio): adicionar entidades centrais da oficina`
- `feat(frontend): criar dashboard vue da oficina`
