<?php

namespace App\Support;

/**
 * Semente da concessionária — porte 1:1 de `dealership/react/src/data/mock.ts`.
 *
 * A mesma `mulberry32(20260905)` do React roda aqui, na mesma ordem de
 * chamadas, então o estoque, as propostas, as avaliações e a agenda saem
 * IDÊNTICOS aos do React. É essa semente que vai para `window.DL` e alimenta
 * os stores do Alpine.
 *
 * Duas armadilhas do porte, ambas resolvidas aqui:
 *
 * 1. `Math.round` do JS arredonda o meio para CIMA (−2,5 vira −2); o `round()`
 *    do PHP arredonda para longe do zero (−2,5 vira −3). Todo `Math.round` do
 *    original virou {@see self::jsRound()}.
 * 2. `Math.imul` e `>>> 0` trabalham em 32 bits sem sinal. O gerador mantém
 *    tudo mascarado em 0xFFFFFFFF e multiplica em duas metades de 16 bits para
 *    não estourar o inteiro de 64 bits do PHP.
 *
 * O "hoje" é ancorado à meia-noite local e resolvido UMA vez: assim os dias em
 * estoque não mudam a cada recarga da página.
 */
class DlData
{
    /** Passada a semente, a base é refeita — a agenda sairia do lugar. */
    public const SEED_TTL_MS = 6 * 3600 * 1000;

    /** O cliente 1 representa o visitante da demonstração no site público. */
    public const VISITOR_CUSTOMER_ID = 1;

    private const DAY_MS = 86400000;

    /** @var array<string,mixed>|null */
    private static ?array $seed = null;

    /** @var int|null Meia-noite de hoje, em milissegundos. */
    private static ?int $anchor = null;

    // =====================================================================
    // Relógio da semente
    // =====================================================================

    private static function tz(): \DateTimeZone
    {
        return new \DateTimeZone(date_default_timezone_get());
    }

    /** Meia-noite local de hoje, em ms — o `ANCHOR` do mock.ts. */
    public static function anchor(): int
    {
        if (self::$anchor === null) {
            $midnight = (new \DateTimeImmutable('now', self::tz()))->setTime(0, 0, 0);
            self::$anchor = $midnight->getTimestamp() * 1000;
        }

        return self::$anchor;
    }

    public static function nowMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    private static function at(int $offsetDays, int $hour = 10): \DateTimeImmutable
    {
        $ms = self::anchor() + $offsetDays * self::DAY_MS;

        return (new \DateTimeImmutable('@'.intdiv($ms, 1000)))
            ->setTimezone(self::tz())
            ->setTime($hour, 0, 0);
    }

    /** ISO em UTC de um dia relativo a hoje — o `dayAt()` do mock.ts. */
    public static function dayAt(int $offsetDays, int $hour = 10): string
    {
        return self::at($offsetDays, $hour)->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.v\Z');
    }

    /** Chave `YYYY-MM-DD` de um dia relativo a hoje — o formato da agenda. */
    public static function dayKey(int $offsetDays): string
    {
        return self::at($offsetDays, 12)->format('Y-m-d');
    }

    public static function todayKey(): string
    {
        return self::dayKey(0);
    }

    private static function currentYear(): int
    {
        return (int) self::at(0, 12)->format('Y');
    }

    // =====================================================================
    // Gerador determinístico — mulberry32 do JS, bit a bit
    // =====================================================================

    /** Multiplicação de 32 bits (o `Math.imul` do JS), sem estourar 64 bits. */
    private static function imul(int $a, int $b): int
    {
        $a &= 0xFFFFFFFF;
        $b &= 0xFFFFFFFF;
        $ah = ($a >> 16) & 0xFFFF;
        $al = $a & 0xFFFF;

        return ($al * $b + ((($ah * $b) & 0xFFFF) << 16)) & 0xFFFFFFFF;
    }

    /** @return \Closure():float */
    private static function mulberry32(int $seed): \Closure
    {
        $a = $seed & 0xFFFFFFFF;

        return function () use (&$a): float {
            $a = ($a + 0x6D2B79F5) & 0xFFFFFFFF;
            $t = self::imul($a ^ ($a >> 15), 1 | $a);
            $t = (($t + self::imul($t ^ ($t >> 7), 61 | $t)) & 0xFFFFFFFF) ^ $t;
            $t &= 0xFFFFFFFF;

            return (($t ^ ($t >> 14)) & 0xFFFFFFFF) / 4294967296;
        };
    }

    /** `Math.round` do JS: o meio sempre sobe (−2,5 → −2). */
    private static function jsRound(float $value): float
    {
        return floor($value + 0.5);
    }

    // =====================================================================
    // Fotos — conjuntos Unsplash curados, nada de picsum
    // =====================================================================

    private const IMG = [
        'car' => [
            '1558618666-fcd25c85cd64',
            '1619642751034-765dfdf7c58e',
            '1487754180451-c456f719a1fc',
            '1503376780353-7e6692767b70',
            '1530046339160-ce3e530c7d2f',
            '1492144534655-ae79c964c9d7',
            '1625047509168-a7026f36de04',
            '1614026480209-cd9934144671',
            '1517524008697-84bbe3c3fd98',
            '1615906655593-ad0386982a0f',
        ],
        'city' => ['1477959858617-67f85cf4f1df', '1496568816309-51d7c20e3b21', '1480714378408-67cf0d13bc1b'],
        'people' => [
            '1500648767791-00dcc994a43e',
            '1494790108377-be9c29b29330',
            '1507003211169-0a1dd7228f2d',
            '1438761681033-6461ffad8d80',
            '1633332755192-727a05c4013d',
            '1573496359142-b8d87734a5a2',
        ],
    ];

    private static function imgHash(string $s): int
    {
        $h = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $h = ($h * 31 + ord($s[$i])) % 4294967296;
        }

        return $h;
    }

    private static function imgCategory(string $seed): string
    {
        $s = mb_strtolower($seed);
        if (preg_match('/avatar|cliente|comprador|people|person|profile|user|vendedor/', $s)) {
            return 'people';
        }
        if (preg_match('/bairro|city|fachada|loja|location|showroom|unidade/', $s)) {
            return 'city';
        }

        return 'car';
    }

    private static function unsplash(string $id, int $w, int $h): string
    {
        return "https://images.unsplash.com/photo-{$id}?w={$w}&h={$h}&fit=crop&q=70&auto=format";
    }

    /** URL determinística por semente — fotos de veículo, capas e avatares. */
    public static function image(string $seed, int $w = 960, int $h = 640): string
    {
        $ids = self::IMG[self::imgCategory($seed)];

        return self::unsplash($ids[self::imgHash('dl-'.$seed) % count($ids)], $w, $h);
    }

    private static function avatar(string $seed): string
    {
        $ids = self::IMG['people'];

        return self::unsplash($ids[self::imgHash('dl-av-'.$seed) % count($ids)], 120, 120);
    }

    // =====================================================================
    // Regras da loja
    // =====================================================================

    public const OPTION_CATALOG = [
        'Ar-condicionado',
        'Direção elétrica',
        'Central multimídia',
        'Câmera de ré',
        'Sensor de estacionamento',
        'Piloto automático',
        'Faróis de LED',
        'Rodas de liga leve',
        'Bancos de couro',
        'Teto solar',
        'Chave presencial',
        'Controle de estabilidade',
        'Engate reboque',
        'Blindagem',
    ];

    /** @return array<string,mixed> */
    public static function rules(): array
    {
        return [
            'defaultMonthlyRate' => 0.0189,
            'minDownPct' => 0.2,
            'registrationFee' => 890,
            'iocPct' => 0.0338,
            'slowStockDays' => 60,
            // [margem mínima em R$, fatia da margem que vai para o vendedor]
            'commissionTiers' => [[0, 0.1], [4000, 0.15], [9000, 0.2], [16000, 0.28]],
            'depreciationPerYear' => 0.11,
            'kmPerYearRef' => 12000,
            'kmAdjustPer1000' => 420,
            'conditionAdjust' => ['excelente' => 0.05, 'bom' => 0, 'regular' => -0.07, 'ruim' => -0.16],
            'optionValue' => [
                'Ar-condicionado' => 900,
                'Direção elétrica' => 700,
                'Central multimídia' => 1400,
                'Câmera de ré' => 900,
                'Sensor de estacionamento' => 700,
                'Piloto automático' => 800,
                'Faróis de LED' => 1100,
                'Rodas de liga leve' => 1200,
                'Bancos de couro' => 1800,
                'Teto solar' => 3200,
                'Chave presencial' => 900,
                'Controle de estabilidade' => 600,
                'Engate reboque' => 500,
                'Blindagem' => 12000,
            ],
        ];
    }

    /** Dados da loja usados no site público e nas configurações. */
    public static function store(): array
    {
        return [
            'name' => 'Garagem',
            'legalName' => 'Garagem Veículos Ltda.',
            'address' => 'Av. Brigadeiro Faria Lima, 2140 — Jardim Paulistano, São Paulo/SP',
            'phone' => '(11) 3555-2040',
            'whatsapp' => '(11) 98877-2040',
            'email' => 'contato@garagem.com.br',
            'hours' => 'Seg a sex 9h–19h · Sáb 9h–15h',
            'coverSeed' => 'vitrine-destaque',
            'storeSeed' => 'loja-fachada-garagem',
        ];
    }

    // =====================================================================
    // Marcas e modelos — todos FICTÍCIOS
    // =====================================================================

    private const BRAND_SEEDS = [
        [1, 'aurex', 'Aurex'],
        [2, 'bravano', 'Bravano'],
        [3, 'corvell', 'Corvell'],
        [4, 'delvara', 'Delvara'],
        [5, 'ferun', 'Ferun'],
        [6, 'kastor', 'Kastor'],
        [7, 'miraldo', 'Miraldo'],
        [8, 'norvent', 'Norvent'],
        [9, 'solvia', 'Solvia'],
        [10, 'trevani', 'Trevani'],
    ];

    private const MODEL_SEEDS = [
        [1, 'Lumia', 'hatch', 89900],
        [1, 'Vento', 'sedan', 118500],
        [1, 'Serra', 'suv', 164900],
        [1, 'Carga', 'utilitario', 142000],
        [2, 'Pixel', 'hatch', 82400],
        [2, 'Onda', 'sedan', 124900],
        [2, 'Cume', 'suv', 189900],
        [3, 'Ágil', 'hatch', 78900],
        [3, 'Brisa', 'sedan', 132000],
        [3, 'Rocha', 'picape', 214900],
        [3, 'Duna', 'suv', 176500],
        [4, 'Nix', 'hatch', 95700],
        [4, 'Aria', 'sedan', 145300],
        [4, 'Titan', 'picape', 239900],
        [5, 'Trilha', 'suv', 158900],
        [5, 'Metro', 'hatch', 74300],
        [5, 'Furgo', 'utilitario', 168400],
        [6, 'Forte', 'picape', 226700],
        [6, 'Aço', 'utilitario', 152900],
        [6, 'Urbano', 'hatch', 86200],
        [7, 'Prata', 'sedan', 139900],
        [7, 'Nuvem', 'suv', 198400],
        [7, 'Vivo', 'hatch', 91100],
        [8, 'Elo', 'hatch', 172900],
        [8, 'Fluxo', 'sedan', 209700],
        [8, 'Volt', 'suv', 259900],
        [9, 'Sol', 'hatch', 79900],
        [9, 'Costa', 'sedan', 128600],
        [9, 'Monte', 'suv', 183200],
        [9, 'Campo', 'picape', 205400],
        [10, 'Lince', 'suv', 171800],
        [10, 'Corso', 'sedan', 149500],
        [10, 'Faixa', 'hatch', 88700],
        [10, 'Bloco', 'utilitario', 159900],
    ];

    /**
     * O `slugify` do React normaliza em NFD e joga fora os acentos combinados.
     * Aqui a tabela faz o mesmo sem depender da extensão intl.
     */
    public static function slugify(string $value): string
    {
        $map = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n', 'ý' => 'y', 'ÿ' => 'y',
            'Á' => 'a', 'À' => 'a', 'Ã' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a',
            'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'Ó' => 'o', 'Ò' => 'o', 'Õ' => 'o', 'Ô' => 'o', 'Ö' => 'o',
            'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
            'Ç' => 'c', 'Ñ' => 'n',
        ];
        $out = mb_strtolower(strtr($value, $map));
        $out = preg_replace('/[^a-z0-9]+/', '-', $out);

        return trim($out, '-');
    }

    /** @return array<int,array<string,mixed>> */
    public static function brands(): array
    {
        $out = [];
        foreach (self::BRAND_SEEDS as [$id, $slug, $name]) {
            $out[] = ['id' => $id, 'slug' => $slug, 'name' => $name, 'logoSeed' => $slug.'-marca'];
        }

        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    public static function models(): array
    {
        $brands = self::brands();
        $out = [];
        foreach (self::MODEL_SEEDS as $i => [$brandId, $name, $category, $referenceNew]) {
            $out[] = [
                'id' => $i + 1,
                'brandId' => $brandId,
                'slug' => $brands[$brandId - 1]['slug'].'-'.self::slugify($name),
                'name' => $name,
                'category' => $category,
                'referenceNew' => $referenceNew,
            ];
        }

        return $out;
    }

    // =====================================================================
    // Vendedores
    // =====================================================================

    /** @return array<int,array<string,mixed>> */
    public static function sellers(): array
    {
        $rows = [
            [1, 'Marcelo Tavares', 'marcelo.tavares', 'vendedor-marcelo', -1420, 8],
            [2, 'Renata Bulhões', 'renata.bulhoes', 'vendedor-renata', -1180, 9],
            [3, 'Aline Cordeiro', 'aline.cordeiro', 'vendedor-aline', -860, 7],
            [4, 'Vinícius Prado', 'vinicius.prado', 'vendedor-vinicius', -640, 6],
            [5, 'Tarso Meireles', 'tarso.meireles', 'vendedor-tarso', -410, 6],
            [6, 'Juliana Sampaio', 'juliana.sampaio', 'vendedor-juliana', -205, 5],
        ];
        $out = [];
        foreach ($rows as [$id, $name, $handle, $seed, $since, $goal]) {
            $out[] = [
                'id' => $id,
                'name' => $name,
                'email' => $handle.'@garagem.com.br',
                'avatar' => self::avatar($seed),
                'since' => self::dayAt($since),
                'monthlyGoal' => $goal,
            ];
        }

        return $out;
    }

    // =====================================================================
    // Clientes
    // =====================================================================

    private const FIRST_NAMES = [
        'Adriana', 'Bruno', 'Camila', 'Diego', 'Elaine', 'Fábio', 'Gisele', 'Henrique', 'Isabela', 'João',
        'Karina', 'Leonardo', 'Marina', 'Nelson', 'Otávio', 'Patrícia', 'Rafael', 'Sabrina', 'Thiago', 'Vanessa',
        'Wagner', 'Yara', 'Alexandre', 'Beatriz', 'Cristiano', 'Débora', 'Eduardo', 'Fernanda', 'Gustavo', 'Helena',
    ];

    private const LAST_NAMES = [
        'Almeida', 'Barreto', 'Camargo', 'Dutra', 'Esteves', 'Fontoura', 'Guimarães', 'Hirano', 'Iglesias', 'Junqueira',
        'Kowalski', 'Lacerda', 'Machado', 'Nogueira', 'Oliveira', 'Peixoto', 'Quirino', 'Rezende', 'Salgado', 'Teixeira',
        'Uchôa', 'Valente', 'Wanderley', 'Xavier', 'Zamora', 'Bettencourt', 'Coutinho', 'Delgado', 'Ferraz', 'Gaspar',
    ];

    private const DDDS = [11, 11, 11, 12, 13, 15, 16, 19, 21, 31, 41, 47, 51, 62, 71, 81];

    /** Índices dos 3 clientes com CNH vencida — o agendamento precisa recusar. */
    private const EXPIRED_CNH_CUSTOMERS = [4, 57, 118];

    /** Índices dos clientes que nunca informaram a CNH. */
    private const NO_CNH_CUSTOMERS = [22, 73, 131];

    /** @return array<int,array<string,mixed>> */
    private static function buildCustomers(\Closure $rnd): array
    {
        $list = [];
        $firstCount = count(self::FIRST_NAMES);
        $lastCount = count(self::LAST_NAMES);

        for ($i = 0; $i < 140; $i++) {
            $first = self::FIRST_NAMES[$i % $firstCount];
            $last = self::LAST_NAMES[((int) floor($i / $firstCount) * 7 + ($i % 7)) % $lastCount];
            $name = $first.' '.$last;
            $ddd = self::DDDS[(int) floor($rnd() * count(self::DDDS))];

            if (in_array($i, self::NO_CNH_CUSTOMERS, true)) {
                $cnhUntil = null;
            } elseif (in_array($i, self::EXPIRED_CNH_CUSTOMERS, true)) {
                $cnhUntil = self::dayAt(-(30 + (int) floor($rnd() * 300)));
            } else {
                $cnhUntil = self::dayAt(90 + (int) floor($rnd() * 1400));
            }

            $phone = sprintf('(%d) 9%d-%d', $ddd, 1000 + (int) floor($rnd() * 8999), 1000 + (int) floor($rnd() * 8999));
            $document = sprintf('%d.***.***-%02d', 100 + (int) floor($rnd() * 899), (int) floor($rnd() * 100));

            $list[] = [
                'id' => $i + 1,
                'name' => $name,
                'email' => self::slugify($first).'.'.self::slugify($last).$i.'@exemplo.com',
                'phone' => $phone,
                'document' => $document,
                'avatar' => self::avatar('cliente-'.$i),
                'cnhUntil' => $cnhUntil,
                'createdAt' => self::dayAt(-(20 + (int) floor($rnd() * 900))),
            ];
        }

        return $list;
    }

    // =====================================================================
    // Estoque — 96 veículos nos 4 estados
    // =====================================================================

    private const COLORS = ['Branco', 'Prata', 'Preto', 'Cinza', 'Vermelho', 'Azul', 'Verde-oliva', 'Bege'];

    private const VERSIONS = [
        'hatch' => ['1.0 Turbo', '1.0 Vibe', '1.6 Comfort', '1.6 Sport'],
        'sedan' => ['1.6 Comfort', '2.0 Exclusive', '1.4 Turbo Prime', '2.0 Elegance'],
        'suv' => ['1.5 Turbo Nav', '2.0 AWD Prime', '1.8 Comfort', '2.0 Signature'],
        'picape' => ['2.8 4x4 Rally', '2.2 4x2 Work', '3.0 4x4 Peak', '2.4 Cabine Dupla'],
        'utilitario' => ['1.8 Furgão', '2.0 Chassi', '1.6 Carga Longa', '2.3 Baú'],
    ];

    private const TRANSMISSIONS = ['manual', 'automatico', 'cvt', 'automatizado'];

    /** Veículos parados há mais de 60 dias — a lista de giro lento tem 12 nomes. */
    private const SLOW_STOCK = [0, 6, 11, 18, 27, 35, 42, 49, 55, 64, 78, 87];

    private const RESERVED_STOCK = [3, 14, 22, 31, 45, 52, 60, 71, 83, 91];

    private const SOLD_STOCK = [1, 7, 12, 19, 26, 33, 39, 44, 50, 57, 63, 68, 74, 79, 85, 88, 92, 95];

    private const PREP_STOCK = [5, 24, 41, 58, 76, 90];

    /** Veículos comprados no prejuízo — a margem prevista fica negativa. */
    private const LOSS_STOCK = [9, 21, 38, 61, 82];

    /**
     * O veículo 1 é o que voltou de uma proposta perdida: segue disponível e o
     * contador de dias em estoque nunca zerou. É o caso de teste da regra 4.
     */
    private const RETURNED_VEHICLE_INDEX = 0;

    private static function stateOf(int $i): string
    {
        if (in_array($i, self::RESERVED_STOCK, true)) {
            return 'reservado';
        }
        if (in_array($i, self::SOLD_STOCK, true)) {
            return 'vendido';
        }
        if (in_array($i, self::PREP_STOCK, true)) {
            return 'preparacao';
        }

        return 'disponivel';
    }

    /** @return array<int,array<string,mixed>> */
    private static function buildVehicles(\Closure $rnd): array
    {
        $models = self::models();
        $brands = self::brands();
        $rules = self::rules();
        $currentYear = self::currentYear();
        $list = [];

        for ($i = 0; $i < 96; $i++) {
            $model = $models[(int) floor($rnd() * count($models))];
            $brand = $brands[$model['brandId'] - 1];
            $slow = in_array($i, self::SLOW_STOCK, true);
            $age = $slow ? 3 + (int) floor($rnd() * 6) : (int) floor($rnd() * 9);
            $year = $currentYear - $age;
            $modelYear = ($age > 0 && $rnd() < 0.45) ? $year + 1 : $year;

            $kmSpread = 0.55 + $rnd() * 0.95;
            $km = $age === 0
                ? (int) (self::jsRound($rnd() * 9000 / 100) * 100)
                : (int) (self::jsRound($age * $rules['kmPerYearRef'] * $kmSpread / 100) * 100);

            // Preço de vitrine: referência 0 km depreciada pela idade e pela rodagem.
            $byAge = $model['referenceNew'] * pow(1 - $rules['depreciationPerYear'], $age);
            $expectedKm = max($age, 1) * $rules['kmPerYearRef'];
            $kmPenalty = (($km - $expectedKm) / 1000) * $rules['kmAdjustPer1000'] * 0.5;
            $price = (int) max(28000, self::jsRound(($byAge - $kmPenalty) / 100) * 100);

            $prepCost = (int) (self::jsRound((600 + $rnd() * 4200) / 50) * 50);
            $marginPct = in_array($i, self::LOSS_STOCK, true)
                ? -(0.005 + $rnd() * 0.04)
                : 0.06 + $rnd() * 0.08;
            $acquisitionCost = (int) (self::jsRound(($price * (1 - $marginPct) - $prepCost) / 50) * 50);

            $versions = self::VERSIONS[$model['category']];
            $version = $versions[(int) floor($rnd() * count($versions))];

            if ($model['name'] === 'Elo' || $model['name'] === 'Volt') {
                $fuel = 'eletrico';
            } elseif ($model['name'] === 'Fluxo') {
                $fuel = 'hibrido';
            } elseif ($model['category'] === 'picape' || $model['category'] === 'utilitario') {
                $fuel = $rnd() < 0.7 ? 'diesel' : 'flex';
            } else {
                $fuel = $rnd() < 0.82 ? 'flex' : 'gasolina';
            }

            $transmission = $fuel === 'eletrico'
                ? 'automatico'
                : self::TRANSMISSIONS[(int) floor($rnd() * count(self::TRANSMISSIONS))];

            $optionCount = 3 + (int) floor($rnd() * 6);
            $options = [];
            for ($k = 0; $k < $optionCount; $k++) {
                $opt = self::OPTION_CATALOG[(int) floor($rnd() * count(self::OPTION_CATALOG))];
                if (! in_array($opt, $options, true)) {
                    $options[] = $opt;
                }
            }

            $state = self::stateOf($i);
            if ($slow) {
                $arrivedOffset = -(64 + (int) floor($rnd() * 118));
            } elseif ($state === 'vendido') {
                $arrivedOffset = -(35 + (int) floor($rnd() * 115));
            } else {
                $arrivedOffset = -(3 + (int) floor($rnd() * 55));
            }

            $soldAt = null;
            if ($state === 'vendido') {
                $span = max(1, -$arrivedOffset - 10);
                $soldOffset = (int) min(-1, $arrivedOffset + 7 + (int) floor($rnd() * $span));
                $soldAt = self::dayAt($soldOffset, 16);
            }

            $slug = $brand['slug'].'-'.self::slugify($model['name']).'-'.self::slugify($version).'-'.$modelYear.'-'.($i + 1);

            $color = self::COLORS[(int) floor($rnd() * count(self::COLORS))];
            $plateEnd = (string) (int) floor($rnd() * 10);
            $doors = $model['category'] === 'picape'
                ? ($rnd() < 0.75 ? 4 : 2)
                : ($rnd() < 0.9 ? 4 : 2);

            $list[] = [
                'id' => $i + 1,
                'slug' => $slug,
                'modelId' => $model['id'],
                'version' => $version,
                'year' => $year,
                'modelYear' => $modelYear,
                'km' => $km,
                'color' => $color,
                'plateEnd' => $plateEnd,
                'transmission' => $transmission,
                'fuel' => $fuel,
                'doors' => $doors,
                'options' => $options,
                'photoSeeds' => [$slug.'-a', $slug.'-b', $slug.'-c', $slug.'-d'],
                'acquisitionCost' => $acquisitionCost,
                'prepCost' => $prepCost,
                'price' => $price,
                'state' => $state,
                'arrivedAt' => self::dayAt($arrivedOffset, 9),
                'soldAt' => $soldAt,
            ];
        }

        return $list;
    }

    // =====================================================================
    // Propostas — 180 espalhadas por 90 dias nas 5 etapas
    // =====================================================================

    private const LOST_REASONS = [
        'Comprou em outra loja',
        'Crédito recusado',
        'Não fechou o valor da troca',
        'Desistiu da compra',
        'Prazo de entrega',
    ];

    /** @return array<int,array<string,mixed>> */
    private static function buildDeals(\Closure $rnd, array $vehicles, array $customers): array
    {
        $rules = self::rules();
        $sellers = self::sellers();
        $monthsOptions = [24, 36, 48, 60];
        $deals = [];
        $id = 1;

        $makeDeal = function (array $vehicle, string $stage, int $createdOffset, int $movedOffset) use (
            &$id, $rnd, $customers, $sellers, $rules, $monthsOptions
        ): array {
            $customer = $customers[(int) floor($rnd() * count($customers))];
            $seller = $sellers[(int) floor($rnd() * count($sellers))];
            $withFinance = $rnd() < 0.72;
            $discount = $stage === 'contato'
                ? 0
                : (int) (self::jsRound($rnd() * 0.045 * $vehicle['price'] / 100) * 100);

            $finance = null;
            if ($withFinance) {
                $finance = [
                    'down' => (int) (self::jsRound($vehicle['price'] * ($rules['minDownPct'] + $rnd() * 0.25) / 100) * 100),
                    'months' => $monthsOptions[(int) floor($rnd() * count($monthsOptions))],
                    'monthlyRate' => (float) number_format($rules['defaultMonthlyRate'] + ($rnd() - 0.5) * 0.004, 4, '.', ''),
                ];
            }

            return [
                'id' => $id++,
                'vehicleId' => $vehicle['id'],
                'customerId' => $customer['id'],
                'sellerId' => $seller['id'],
                'stage' => $stage,
                'createdAt' => self::dayAt($createdOffset, 11),
                'movedAt' => self::dayAt($movedOffset, 15),
                'discount' => $discount,
                'tradeInId' => null,
                'finance' => $finance,
            ];
        };

        // 1) Toda venda e toda reserva nasceram de uma proposta aprovada.
        foreach ($vehicles as $vehicle) {
            if ($vehicle['state'] !== 'vendido' && $vehicle['state'] !== 'reservado') {
                continue;
            }
            if ($vehicle['soldAt']) {
                $soldMs = strtotime($vehicle['soldAt']) * 1000;
                $soldOffset = (int) self::jsRound(($soldMs - self::anchor()) / self::DAY_MS);
            } else {
                $soldOffset = -(1 + (int) floor($rnd() * 12));
            }
            $created = (int) min(-2, $soldOffset - (5 + (int) floor($rnd() * 20)));
            $deals[] = $makeDeal($vehicle, 'aprovada', $created, $soldOffset);
        }

        // 2) A proposta perdida cujo veículo VOLTOU para a vitrine. O contador
        //    de dias em estoque continua de onde parou — nada zera aqui.
        $returnedDeal = $makeDeal($vehicles[self::RETURNED_VEHICLE_INDEX], 'perdida', -52, -19);
        $returnedDeal['lostReason'] = 'Crédito recusado';
        $deals[] = $returnedDeal;

        // 3) O resto do funil, sobre veículos que seguem na vitrine.
        $openStock = array_values(array_filter(
            $vehicles,
            fn ($v) => $v['state'] === 'disponivel' || $v['state'] === 'preparacao'
        ));
        $plan = [['contato', 44], ['proposta', 42], ['negociacao', 34], ['perdida', 31]];

        foreach ($plan as [$stage, $count]) {
            for ($k = 0; $k < $count; $k++) {
                $vehicle = $openStock[(int) floor($rnd() * count($openStock))];
                $created = -(2 + (int) floor($rnd() * 88));
                $moved = (int) min(-1, $created + (int) floor($rnd() * 12));
                $deal = $makeDeal($vehicle, $stage, $created, $moved);
                if ($stage === 'perdida') {
                    $deal['lostReason'] = self::LOST_REASONS[(int) floor($rnd() * count(self::LOST_REASONS))];
                }
                $deals[] = $deal;
            }
        }

        return $deals;
    }

    // =====================================================================
    // Avaliações de troca — ~50, algumas ligadas a propostas
    // =====================================================================

    private const CONDITIONS = ['excelente', 'bom', 'bom', 'regular', 'ruim'];

    /** @return array<int,array<string,mixed>> */
    private static function buildTradeIns(\Closure $rnd, array &$deals, array $customers): array
    {
        $models = self::models();
        $rules = self::rules();
        $currentYear = self::currentYear();
        $list = [];
        $linkableKeys = [];
        foreach ($deals as $key => $deal) {
            if ($deal['stage'] !== 'contato') {
                $linkableKeys[] = $key;
            }
        }
        $id = 1;

        for ($i = 0; $i < 52; $i++) {
            $linkedKey = null;
            if ($i < 34) {
                $linkedKey = $linkableKeys[(int) floor($rnd() * count($linkableKeys))];
            }
            $linked = $linkedKey === null ? null : $deals[$linkedKey];

            $customerId = $linked
                ? $linked['customerId']
                : $customers[(int) floor($rnd() * count($customers))]['id'];

            $model = $models[(int) floor($rnd() * count($models))];
            $age = 2 + (int) floor($rnd() * 10);
            $year = $currentYear - $age;
            $km = (int) (self::jsRound($age * $rules['kmPerYearRef'] * (0.5 + $rnd() * 1.1) / 500) * 500);

            $optionCount = 1 + (int) floor($rnd() * 4);
            $options = [];
            for ($k = 0; $k < $optionCount; $k++) {
                $opt = self::OPTION_CATALOG[(int) floor($rnd() * count(self::OPTION_CATALOG))];
                if (! in_array($opt, $options, true)) {
                    $options[] = $opt;
                }
            }

            $condition = self::CONDITIONS[(int) floor($rnd() * count(self::CONDITIONS))];

            if ($linked) {
                $accepted = $linked['stage'] === 'aprovada' ? true : $rnd() < 0.5;
            } else {
                $accepted = $rnd() < 0.3;
            }

            $tradeIn = [
                'id' => $id++,
                'dealId' => $linked ? $linked['id'] : null,
                'customerId' => $customerId,
                'modelId' => $model['id'],
                'year' => $year,
                'km' => $km,
                'condition' => $condition,
                'options' => $options,
                'accepted' => $accepted,
                'createdAt' => self::dayAt(-(1 + (int) floor($rnd() * 89)), 14),
            ];
            $list[] = $tradeIn;

            if ($linkedKey !== null && $deals[$linkedKey]['tradeInId'] === null) {
                $deals[$linkedKey]['tradeInId'] = $tradeIn['id'];
            }
        }

        return $list;
    }

    // =====================================================================
    // Test-drives — ~70, com UM conflito real de vendedor plantado nos dados
    // =====================================================================

    public const SLOTS = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

    public static function plusHour(string $from): string
    {
        return sprintf('%02d:%s', ((int) substr($from, 0, 2)) + 1, substr($from, 3));
    }

    /** A CNH vale na data? Sem CNH ou vencida, o agendamento recusa. */
    private static function cnhOkAt(array $customer, string $dateKey): bool
    {
        if (! $customer['cnhUntil']) {
            return false;
        }

        return substr($customer['cnhUntil'], 0, 10) >= $dateKey;
    }

    /** @return array<int,array<string,mixed>> */
    private static function buildDrives(\Closure $rnd, array $vehicles, array $customers): array
    {
        $sellers = self::sellers();
        $list = [];
        $taken = [];
        $bookable = array_values(array_filter($vehicles, fn ($v) => $v['state'] !== 'vendido'));
        $id = 1;

        $push = function (int $vehicleId, array $customer, int $sellerId, int $offset, string $from) use (
            &$list, &$id, $rnd
        ): void {
            $date = self::dayKey($offset);
            if ($offset >= 0) {
                $status = 'agendado';
            } else {
                $status = $rnd() < 0.72 ? 'realizado' : ($rnd() < 0.6 ? 'faltou' : 'cancelado');
            }
            $list[] = [
                'id' => $id++,
                'vehicleId' => $vehicleId,
                'customerId' => $customer['id'],
                'sellerId' => $sellerId,
                'date' => $date,
                'from' => $from,
                'to' => self::plusHour($from),
                'status' => $status,
                'cnhOk' => self::cnhOkAt($customer, $date),
            ];
        };

        $guard = 0;
        while (count($list) < 69 && $guard < 4000) {
            $guard++;
            $vehicle = $bookable[(int) floor($rnd() * count($bookable))];
            $customer = $customers[(int) floor($rnd() * count($customers))];
            $seller = $sellers[(int) floor($rnd() * count($sellers))];
            $offset = -21 + (int) floor($rnd() * 36);
            $from = self::SLOTS[(int) floor($rnd() * count(self::SLOTS))];
            $date = self::dayKey($offset);

            $vKey = "v:{$vehicle['id']}|{$date}|{$from}";
            $sKey = "s:{$seller['id']}|{$date}|{$from}";
            if (isset($taken[$vKey]) || isset($taken[$sKey])) {
                continue;
            }
            $taken[$vKey] = true;
            $taken[$sKey] = true;
            $push($vehicle['id'], $customer, $seller['id'], $offset, $from);
        }

        // O conflito plantado: dois test-drives amanhã, às 10h, com o MESMO
        // vendedor em veículos diferentes. A agenda precisa apontar isso.
        $conflictSeller = $sellers[1];
        $push($bookable[3]['id'], $customers[8], $conflictSeller['id'], 1, '10:00');
        $push($bookable[17]['id'], $customers[41], $conflictSeller['id'], 1, '10:00');

        return $list;
    }

    // =====================================================================
    // Semente completa
    // =====================================================================

    /** @return array<string,mixed> */
    public static function seed(): array
    {
        if (self::$seed !== null) {
            return self::$seed;
        }

        $rnd = self::mulberry32(20260905);
        $customers = self::buildCustomers($rnd);
        $vehicles = self::buildVehicles($rnd);
        $deals = self::buildDeals($rnd, $vehicles, $customers);
        $tradeIns = self::buildTradeIns($rnd, $deals, $customers);
        $drives = self::buildDrives($rnd, $vehicles, $customers);

        return self::$seed = [
            'brands' => self::brands(),
            'models' => self::models(),
            'vehicles' => $vehicles,
            'customers' => $customers,
            'sellers' => self::sellers(),
            'deals' => $deals,
            'tradeIns' => $tradeIns,
            'drives' => $drives,
            'rules' => self::rules(),
        ];
    }

    public static function vehicles(): array
    {
        return self::seed()['vehicles'];
    }

    public static function customers(): array
    {
        return self::seed()['customers'];
    }

    public static function deals(): array
    {
        return self::seed()['deals'];
    }

    public static function tradeIns(): array
    {
        return self::seed()['tradeIns'];
    }

    public static function drives(): array
    {
        return self::seed()['drives'];
    }

    // =====================================================================
    // Buscas usadas pelo controlador (para o <title> e o 404)
    // =====================================================================

    public static function vehicleBySlug(string $slug): ?array
    {
        foreach (self::vehicles() as $vehicle) {
            if ($vehicle['slug'] === $slug) {
                return $vehicle;
            }
        }

        return null;
    }

    public static function vehicleById(int $id): ?array
    {
        foreach (self::vehicles() as $vehicle) {
            if ($vehicle['id'] === $id) {
                return $vehicle;
            }
        }

        return null;
    }

    public static function dealById(int $id): ?array
    {
        foreach (self::deals() as $deal) {
            if ($deal['id'] === $id) {
                return $deal;
            }
        }

        return null;
    }

    public static function modelById(int $id): ?array
    {
        foreach (self::models() as $model) {
            if ($model['id'] === $id) {
                return $model;
            }
        }

        return null;
    }

    /** "Aurex Lumia 1.0 Turbo" — o nome que aparece em toda listagem. */
    public static function vehicleLabel(array $vehicle): string
    {
        $model = self::modelById($vehicle['modelId']);
        if (! $model) {
            return '—';
        }
        $brands = self::brands();
        $brand = $brands[$model['brandId'] - 1] ?? null;
        $name = $brand ? $brand['name'].' '.$model['name'] : $model['name'];

        return trim($name.' '.$vehicle['version']);
    }
}
