# Concessionária / Laravel

[Read in English](./README.md)

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE) ![Free](https://img.shields.io/badge/price-free-brightgreen)

Revenda de veículos em Laravel 11 + Tailwind: vitrine pública com filtros de marca, modelo, ano, km, câmbio e faixa de preço, ficha completa e simulador de financiamento pela Tabela Price com a memória do cálculo linha a linha e o CET aproximado, que recalcula ao mexer na entrada e respeita a entrada mínima. Avaliação de troca que parte do valor de referência e mostra cada ajuste em reais — depreciação por ano, quilometragem acima ou abaixo da média, estado de conservação e opcionais —, abatendo a entrada. Test-drive que aponta separadamente o conflito de veículo e o de vendedor e exige CNH válida. Showroom com funil kanban que reserva o veículo ao aprovar e o devolve à vitrine se a proposta cai, sem zerar os dias em estoque, mais margem, comissão por faixa e lista de giro lento. i18n pt-BR/en/es, PWA, dark mode.

## Destaques

- Financiamento Price com memória e CET
- Avaliação de troca com cada ajuste em reais
- Test-drive com conflito de veículo e vendedor
- Funil que reserva e devolve ao estoque
- i18n pt-BR/en/es, PWA, dark mode

## Telas em destaque

- Veículo: Ficha e simulador
- Financiamento: Memória e CET
- Funil: Kanban e reserva
- Avaliação: Ajustes da troca

## Stack

- Laravel 11
- PHP 8.2+
- Blade
- Alpine.js
- Tailwind CSS v4
- ApexCharts
- Vite

## Requisitos

- PHP 8.2 ou mais novo, com Composer
- Node.js 20 ou mais novo, com npm

## Como rodar

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan serve
```

Não precisa de banco de dados: os dados de exemplo ficam em classes PHP e o login é simulado por middleware.

Durante o desenvolvimento, use `npm run dev` no lugar de `npm run build` para ter hot reload dos assets.

Conta de demonstração:

- `gerente@garagem.com.br` / `demo123`

## Estrutura do projeto

```
app/
├── Http/  Controllers, Middleware
├── Models/
├── Providers/
├── Support/
resources/  css/, js/, views/
routes/  console.php, web.php
```

## Outras tecnologias

Este template também existe em outras 4 tecnologias, com as mesmas telas e a mesma identidade visual. Todas são gratuitas:

- React 19: https://github.com/danilo62x/dealership-react
- Vue 3: https://github.com/danilo62x/dealership-vue3
- Angular 19: https://github.com/danilo62x/dealership-angular
- HTML estático: https://github.com/danilo62x/dealership-html

Mais templates gratuitos (painéis admin, sistemas por segmento e apps Flutter): https://github.com/danilo62x?tab=repositories

## Licença

[MIT](./LICENSE), copyright 2026 Danilo Quinelato.
