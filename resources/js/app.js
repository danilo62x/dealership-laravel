import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import ApexCharts from 'apexcharts';
import i18n, {
  LANGUAGES,
  formatCurrency,
  formatCurrencyCompact,
  formatCompact,
  formatDate,
  formatDateTime,
  formatDayMonth,
  formatTime,
  formatNumber,
  formatPercent,
} from './i18n.js';
import './pwa.js';

window.Alpine = Alpine;

// ─── Magics do Alpine expostos como globais ─────────────────────────────────
// As fábricas de página (homePage(), stockPage(), …) são funções declaradas
// num <script>, fora do escopo de expressão do Alpine — que é onde o with(scope)
// coloca os magics. O Alpine injeta os magics no OBJETO DE DADOS
// (injectMagics), não no window, então um `$store` ou `$t` nu dentro dessas
// funções seria ReferenceError. Espelhamos os magics em window para que elas
// continuem legíveis. Alpine.store() devolve o mesmo proxy reativo do magic,
// então os getters que leem daqui continuam reativos.
window.$store = new Proxy(
  {},
  { get: (_target, name) => (typeof name === 'string' ? Alpine.store(name) : undefined) }
);
const registerMagic = Alpine.magic.bind(Alpine);
Alpine.magic = (name, callback) => {
  registerMagic(name, callback);
  // ATENÇÃO: tem de ser `$${name}`. Um `${name}` solto definiria window.t no
  // lugar de window.$t e toda página quebraria com "t is not defined".
  Object.defineProperty(window, `$${name}`, { configurable: true, get: () => callback() });
};

Alpine.plugin(collapse);
window.ApexCharts = ApexCharts;

// ─── localStorage helpers (espelham o persist do zustand) ───────────────────
function load(key, fallback) {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return fallback;
    const parsed = JSON.parse(raw);
    // o persist do zustand embrulha o estado em { state, version }
    return parsed && parsed.state ? parsed.state : parsed;
  } catch (_) {
    return fallback;
  }
}
function save(key, state, version = 0) {
  try {
    localStorage.setItem(key, JSON.stringify({ state, version }));
  } catch (_) {}
}

const DL = window.DL || {};
const clone = (value) => JSON.parse(JSON.stringify(value ?? null));
const nextId = (rows) => (rows.length ? Math.max(...rows.map((r) => r.id)) + 1 : 1);

const MS_DAY = 86400000;
const MS_HOUR = 3600000;

// ===========================================================================
// Imagens — porte de dealership/react src/data/mock.ts
// Nunca use picsum.photos: a URL é determinística por semente.
// ===========================================================================
const IMG = {
  car: [
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
  city: ['1477959858617-67f85cf4f1df', '1496568816309-51d7c20e3b21', '1480714378408-67cf0d13bc1b'],
  people: [
    '1500648767791-00dcc994a43e',
    '1494790108377-be9c29b29330',
    '1507003211169-0a1dd7228f2d',
    '1438761681033-6461ffad8d80',
    '1633332755192-727a05c4013d',
    '1573496359142-b8d87734a5a2',
  ],
};

function imgHash(s) {
  let h = 0;
  const str = String(s);
  for (let i = 0; i < str.length; i++) h = (h * 31 + str.charCodeAt(i)) >>> 0;
  return h;
}

function imgCategory(seed) {
  const s = String(seed).toLowerCase();
  if (/avatar|cliente|comprador|people|person|profile|user|vendedor/.test(s)) return 'people';
  if (/bairro|city|fachada|loja|location|showroom|unidade/.test(s)) return 'city';
  return 'car';
}

function unsplash(id, w, h) {
  return `https://images.unsplash.com/photo-${id}?w=${w}&h=${h}&fit=crop&q=70&auto=format`;
}

/** URL determinística por semente — fotos de veículo, capas e avatares. */
export function dlImage(seed, w = 960, h = 640) {
  const ids = IMG[imgCategory(seed)];
  return unsplash(ids[imgHash('dl-' + String(seed)) % ids.length], w, h);
}
window.dlImage = dlImage;

export const OPTION_CATALOG = DL.optionCatalog || [];
window.OPTION_CATALOG = OPTION_CATALOG;

/** Cliente que representa o visitante da demonstração. */
const VISITOR_CUSTOMER_ID = DL.visitorCustomerId ?? 1;
window.VISITOR_CUSTOMER_ID = VISITOR_CUSTOMER_ID;

const TODAY_KEY = DL.todayKey;
window.TODAY_KEY = TODAY_KEY;

/** `YYYY-MM-DD` de um dia relativo a hoje — o mesmo `dayKey()` da semente. */
function dayKey(offset) {
  const base = new Date(DL.anchor ?? Date.now());
  const d = new Date(base.getTime() + offset * MS_DAY);
  const pad = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}
window.dlDayKey = dayKey;

const slugify = (value) =>
  String(value)
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');

// ===========================================================================
// Consultas derivadas — porte 1:1 de dealership/react src/data/query.ts
//
// Regra de ouro deste template: **nada de parcela nem de margem gravadas**. A
// parcela sai sempre da Tabela Price, a margem sempre de venda − custos, e a
// avaliação de troca sempre da referência 0 km com os ajustes abertos.
// ===========================================================================
const DLQ = {
  // ---- buscas simples -----------------------------------------------------
  brandById: (list, id) => list.find((b) => b.id === id),
  modelById: (list, id) => list.find((m) => m.id === id),
  vehicleById: (list, id) => list.find((v) => v.id === id),
  vehicleBySlug: (list, slug) => list.find((v) => v.slug === slug),
  customerById: (list, id) => list.find((c) => c.id === id),
  sellerById: (list, id) => list.find((s) => s.id === id),
  dealById: (list, id) => list.find((d) => d.id === id),
  tradeInById: (list, id) => list.find((t) => t.id === id),

  /** "Aurex Lumia" — marca + modelo. */
  modelLabel(models, brands, modelId) {
    const model = DLQ.modelById(models, modelId);
    if (!model) return '—';
    const brand = DLQ.brandById(brands, model.brandId);
    return brand ? `${brand.name} ${model.name}` : model.name;
  },

  /** "Aurex Lumia 1.0 Turbo" — o nome que aparece em toda listagem. */
  vehicleLabel(models, brands, vehicle) {
    if (!vehicle) return '—';
    return `${DLQ.modelLabel(models, brands, vehicle.modelId)} ${vehicle.version}`.trim();
  },

  /** "2021/2022" — ano de fabricação e ano-modelo. */
  yearLabel(vehicle) {
    if (!vehicle) return '—';
    return vehicle.year === vehicle.modelYear ? String(vehicle.year) : `${vehicle.year}/${vehicle.modelYear}`;
  },

  // =========================================================================
  // 1. FINANCIAMENTO — Tabela Price com memória do cálculo e CET
  // =========================================================================

  /**
   * Parcela da Tabela Price: `PMT = PV × i / (1 − (1+i)^−n)`.
   * Com juros zero a parcela é a divisão simples — o denominador zeraria.
   */
  pmt(pv, monthlyRate, months) {
    if (months <= 0) return 0;
    if (monthlyRate === 0) return pv / months;
    return (pv * monthlyRate) / (1 - Math.pow(1 + monthlyRate, -months));
  },

  /**
   * Taxa mensal efetiva que iguala o dinheiro que o cliente REALMENTE recebe
   * (financiado menos tarifa e IOF) ao fluxo das parcelas. É daqui que sai o
   * CET: bisseção simples, sem dependência externa.
   */
  effectiveMonthlyRate(net, installment, months) {
    if (net <= 0 || installment <= 0 || months <= 0) return 0;
    let low = 0;
    let high = 1;
    for (let i = 0; i < 80; i++) {
      const mid = (low + high) / 2;
      const value = mid === 0 ? installment * months : (installment * (1 - Math.pow(1 + mid, -months))) / mid;
      if (value > net) low = mid;
      else high = mid;
    }
    return (low + high) / 2;
  },

  /** Entrada mínima exigida pelo estoque. O simulador nunca desce disso. */
  minDown(price, rules) {
    return Math.round(price * rules.minDownPct);
  },

  /**
   * Simulação completa. Devolve os números E a memória do cálculo linha a
   * linha — é a memória que a tela imprime, não um resumo escrito à mão.
   */
  simulate(input, rules) {
    const price = Math.max(0, input.price);
    const tradeIn = Math.max(0, input.tradeIn ?? 0);
    const down = Math.max(0, Math.min(input.down + tradeIn, price));
    const months = Math.max(1, Math.round(input.months));
    const i = Math.max(0, input.rate);

    const financed = Math.max(0, price - down);
    const installment = DLQ.pmt(financed, i, months);
    const total = installment * months;
    const interest = total - financed;

    const fees = financed > 0 ? rules.registrationFee : 0;
    // IOF aproximado: a alíquota anual proporcional ao prazo, limitada a 12 meses.
    const iof = financed * rules.iocPct * (Math.min(months, 12) / 12);

    const net = financed - fees - iof;
    const monthlyCet = DLQ.effectiveMonthlyRate(net, installment, months);
    const cet = Math.pow(1 + monthlyCet, 12) - 1;

    const pct = (v) => `${(v * 100).toFixed(2).replace('.', ',')}%`;
    const brl = (v) => v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const factor = Math.pow(1 + i, -months);

    const steps = [
      { key: 'financed', formula: `${brl(price)} − ${brl(down)}`, value: financed, kind: 'money' },
      { key: 'rate', formula: `i = ${pct(i)} a.m.`, value: i, kind: 'rate' },
      { key: 'months', formula: `n = ${months}`, value: months, kind: 'plain' },
      {
        key: 'factor',
        formula: `(1 + ${pct(i)})^−${months} = ${factor.toFixed(6).replace('.', ',')}`,
        value: factor,
        kind: 'plain',
      },
      {
        key: 'installment',
        formula: `${brl(financed)} × ${pct(i)} ÷ (1 − ${factor.toFixed(6).replace('.', ',')})`,
        value: installment,
        kind: 'money',
      },
      { key: 'total', formula: `${brl(installment)} × ${months}`, value: total, kind: 'money' },
      { key: 'interest', formula: `${brl(total)} − ${brl(financed)}`, value: interest, kind: 'money' },
      { key: 'fee', formula: `${brl(rules.registrationFee)}`, value: fees, kind: 'money' },
      {
        key: 'iof',
        formula: `${brl(financed)} × ${pct(rules.iocPct)} × ${Math.min(months, 12)}/12`,
        value: iof,
        kind: 'money',
      },
      { key: 'cet', formula: `(1 + ${pct(monthlyCet)})^12 − 1`, value: cet, kind: 'rate' },
    ];

    return { price, down, months, monthlyRate: i, financed, installment, total, interest, fees, iof, cet, steps };
  },

  // =========================================================================
  // 2. AVALIAÇÃO DE TROCA — cada ajuste em R$, nada escondido
  // =========================================================================

  /**
   * Parte do valor de referência 0 km do modelo e desce até o valor da troca,
   * registrando CADA ajuste em reais. `at` é o "agora" resolvido pela página —
   * a função nunca chama `Date.now()` por conta própria.
   */
  appraise(input, models, rules, at) {
    const model = DLQ.modelById(models, input.modelId);
    const reference = model?.referenceNew ?? 0;
    const currentYear = new Date(at).getFullYear();
    const age = Math.max(0, currentYear - input.year);

    const adjustments = [];

    // a) depreciação por ano de uso
    const afterAge = reference * Math.pow(1 - rules.depreciationPerYear, age);
    adjustments.push({ key: 'depreciation', detail: String(age), amount: Math.round(afterAge - reference) });

    // b) km rodado contra a média anual esperada
    const expectedKm = age * rules.kmPerYearRef;
    const kmDiff = input.km - expectedKm;
    const kmAmount = Math.round((-kmDiff / 1000) * rules.kmAdjustPer1000);
    adjustments.push({
      key: kmDiff >= 0 ? 'kmAbove' : 'kmBelow',
      detail: Math.abs(Math.round(kmDiff)).toLocaleString('pt-BR'),
      amount: kmAmount,
    });

    // c) estado de conservação
    const conditionAmount = Math.round(afterAge * rules.conditionAdjust[input.condition]);
    adjustments.push({ key: `condition.${input.condition}`, detail: '', amount: conditionAmount });

    // d) opcionais valorizados
    const optionAmount = (input.options || []).reduce((sum, opt) => sum + (rules.optionValue[opt] ?? 0), 0);
    adjustments.push({ key: 'options', detail: String((input.options || []).length), amount: optionAmount });

    const raw = reference + adjustments.reduce((sum, a) => sum + a.amount, 0);
    const floor = Math.round(reference * 0.12);
    const value = Math.max(floor, Math.round(raw / 100) * 100);

    return {
      reference,
      value,
      low: Math.round((value * 0.94) / 100) * 100,
      high: Math.round((value * 1.06) / 100) * 100,
      adjustments,
    };
  },

  /** Avaliação de uma troca já registrada. */
  appraiseTradeIn(tradeIn, models, rules, at) {
    return DLQ.appraise(
      {
        modelId: tradeIn.modelId,
        year: tradeIn.year,
        km: tradeIn.km,
        condition: tradeIn.condition,
        options: tradeIn.options,
      },
      models,
      rules,
      at
    );
  },

  // =========================================================================
  // 3. TEST-DRIVE — agenda dupla (veículo E vendedor) + CNH
  // =========================================================================

  plusHour: (from) => `${String(Number(String(from).slice(0, 2)) + 1).padStart(2, '0')}:${String(from).slice(3)}`,

  /**
   * Aponta os dois choques SEPARADAMENTE: o horário pode estar livre para o
   * vendedor e ocupado para o veículo, e a tela precisa dizer qual dos dois é.
   */
  driveConflict(drive, drives) {
    const overlaps = (aFrom, aTo, bFrom, bTo) => aFrom < bTo && bFrom < aTo;
    const active = drives.filter((d) => d.id !== drive.id && d.date === drive.date && d.status !== 'cancelado');
    return {
      vehicle: active.find((d) => d.vehicleId === drive.vehicleId && overlaps(drive.from, drive.to, d.from, d.to)),
      seller: active.find((d) => d.sellerId === drive.sellerId && overlaps(drive.from, drive.to, d.from, d.to)),
    };
  },

  /** Sem CNH ou vencida na data do agendamento, o test-drive é recusado. */
  cnhValid(customer, date) {
    if (!customer || !customer.cnhUntil) return false;
    return customer.cnhUntil.slice(0, 10) >= date;
  },

  /** Agenda de um dia, em ordem de horário. */
  drivesOfDay(drives, date) {
    return drives.filter((d) => d.date === date).sort((a, b) => a.from.localeCompare(b.from));
  },

  drivesOfVehicle(drives, vehicleId) {
    return drives
      .filter((d) => d.vehicleId === vehicleId)
      .sort((a, b) => b.date.localeCompare(a.date) || b.from.localeCompare(a.from));
  },

  /** Horários livres do dia para um par veículo + vendedor. */
  freeSlots(slots, drives, date, vehicleId, sellerId) {
    return slots.filter((from) => {
      const to = DLQ.plusHour(from);
      const clash = DLQ.driveConflict({ vehicleId, sellerId, customerId: 0, date, from, to }, drives);
      return !clash.vehicle && !clash.seller;
    });
  },

  // =========================================================================
  // 4. ESTOQUE — a vitrine reflete o funil, sem zerar os dias em estoque
  // =========================================================================

  /** A vitrine pública nunca mostra reservado nem vendido. */
  availableStock(vehicles) {
    return vehicles.filter((v) => v.state !== 'reservado' && v.state !== 'vendido');
  },

  /**
   * Dias parados: da chegada até a venda, ou até `at` se ainda está na loja.
   * Uma proposta perdida devolve o veículo à vitrine SEM tocar em `arrivedAt`,
   * então o contador continua de onde parou.
   */
  daysInStock(vehicle, at) {
    const from = new Date(vehicle.arrivedAt).getTime();
    const to = vehicle.soldAt ? new Date(vehicle.soldAt).getTime() : at;
    return Math.max(0, Math.floor((to - from) / MS_DAY));
  },

  // =========================================================================
  // 5. MARGEM, COMISSÃO E GIRO LENTO
  // =========================================================================

  /** Margem = venda − (custo de aquisição + preparação). */
  margin(vehicle, salePrice) {
    return salePrice - vehicle.acquisitionCost - vehicle.prepCost;
  },

  marginPct(vehicle, salePrice) {
    if (salePrice <= 0) return 0;
    return DLQ.margin(vehicle, salePrice) / salePrice;
  },

  /** A maior faixa de `commissionTiers` que couber na margem. */
  commissionRate(marginValue, rules) {
    let rate = 0;
    for (const [min, pct] of rules.commissionTiers) {
      if (marginValue >= min) rate = pct;
    }
    return rate;
  },

  /** Comissão do vendedor: fatia da margem, pela faixa alcançada. */
  commission(marginValue, rules) {
    if (marginValue <= 0) return 0;
    return Math.round(marginValue * DLQ.commissionRate(marginValue, rules));
  },

  /** Desconto sugerido cresce por faixa de idade do estoque. */
  suggestedDiscountPct(days, rules) {
    const over = days - rules.slowStockDays;
    if (over <= 0) return 0;
    if (over < 30) return 0.03;
    if (over < 60) return 0.05;
    if (over < 90) return 0.08;
    return 0.12;
  },

  /** Disponíveis parados há mais que `slowStockDays`, do mais parado ao menos. */
  slowMovers(vehicles, at, rules) {
    return vehicles
      .filter((v) => v.state === 'disponivel' && DLQ.daysInStock(v, at) > rules.slowStockDays)
      .map((vehicle) => {
        const days = DLQ.daysInStock(vehicle, at);
        const pct = DLQ.suggestedDiscountPct(days, rules);
        return {
          vehicle,
          days,
          suggestedDiscountPct: pct,
          suggestedPrice: Math.round((vehicle.price * (1 - pct)) / 100) * 100,
        };
      })
      .sort((a, b) => b.days - a.days);
  },

  // =========================================================================
  // Propostas
  // =========================================================================

  STAGES: ['contato', 'proposta', 'negociacao', 'aprovada', 'perdida'],

  /** Preço fechado da proposta: vitrine menos o desconto concedido. */
  dealPrice(deal, vehicle) {
    if (!vehicle) return 0;
    return Math.max(0, vehicle.price - deal.discount);
  },

  dealsOfStage(deals, stage) {
    return deals.filter((d) => d.stage === stage).sort((a, b) => b.movedAt.localeCompare(a.movedAt));
  },

  dealsOfVehicle(deals, vehicleId) {
    return deals.filter((d) => d.vehicleId === vehicleId).sort((a, b) => b.movedAt.localeCompare(a.movedAt));
  },

  dealsOfCustomer(deals, customerId) {
    return deals.filter((d) => d.customerId === customerId).sort((a, b) => b.movedAt.localeCompare(a.movedAt));
  },

  tradeInsOfCustomer(tradeIns, customerId) {
    return tradeIns.filter((t) => t.customerId === customerId).sort((a, b) => b.createdAt.localeCompare(a.createdAt));
  },

  /** Conta e soma valor por etapa, dentro da janela pedida. */
  funnel(deals, vehicles, rangeDays, at) {
    const from = at - rangeDays * MS_DAY;
    return DLQ.STAGES.map((stage) => {
      const rows = deals.filter((d) => d.stage === stage && new Date(d.createdAt).getTime() >= from);
      return {
        stage,
        count: rows.length,
        value: rows.reduce((sum, d) => sum + DLQ.dealPrice(d, DLQ.vehicleById(vehicles, d.vehicleId)), 0),
      };
    });
  },

  // =========================================================================
  // Indicadores da visão geral
  // =========================================================================

  /** Faturamento por dia dos últimos `days` dias — a série do gráfico. */
  salesByDay(vehicles, days, at) {
    const dayLabel = (ms) => {
      const d = new Date(ms);
      return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}`;
    };
    const buckets = new Map();
    const start = new Date(at);
    start.setHours(0, 0, 0, 0);
    const startMs = start.getTime() - (days - 1) * MS_DAY;

    for (let i = 0; i < days; i++) buckets.set(dayLabel(startMs + i * MS_DAY), 0);

    for (const v of vehicles) {
      if (v.state !== 'vendido' || !v.soldAt) continue;
      const ms = new Date(v.soldAt).getTime();
      if (ms < startMs || ms > at + MS_DAY) continue;
      const key = dayLabel(ms);
      if (buckets.has(key)) buckets.set(key, (buckets.get(key) ?? 0) + v.price);
    }

    return Array.from(buckets, ([label, value]) => ({ label, value }));
  },

  soldInRange(vehicles, days, at) {
    const from = at - days * MS_DAY;
    return vehicles.filter((v) => v.state === 'vendido' && v.soldAt && new Date(v.soldAt).getTime() >= from);
  },

  stockKpis(vehicles, at, rules) {
    const sold = DLQ.soldInRange(vehicles, 30, at);
    const revenue = sold.reduce((sum, v) => sum + v.price, 0);
    const totalMargin = sold.reduce((sum, v) => sum + DLQ.margin(v, v.price), 0);
    const inStock = vehicles.filter((v) => v.state === 'disponivel' || v.state === 'preparacao');
    const avgDays = inStock.length
      ? Math.round(inStock.reduce((sum, v) => sum + DLQ.daysInStock(v, at), 0) / inStock.length)
      : 0;

    return {
      sales: sold.length,
      revenue,
      avgTicket: sold.length ? Math.round(revenue / sold.length) : 0,
      totalMargin,
      avgDays,
      available: vehicles.filter((v) => v.state === 'disponivel').length,
      reserved: vehicles.filter((v) => v.state === 'reservado').length,
      stockValue: inStock.reduce((sum, v) => sum + v.price, 0),
      slow: DLQ.slowMovers(vehicles, at, rules).length,
    };
  },

  /** Metas, vendas, margem e comissão de cada vendedor no mês corrente. */
  sellerPerformance(sellers, deals, vehicles, at, rules) {
    const from = at - 30 * MS_DAY;
    return sellers
      .map((seller) => {
        const won = deals.filter(
          (d) => d.sellerId === seller.id && d.stage === 'aprovada' && new Date(d.movedAt).getTime() >= from
        );
        let revenue = 0;
        let marginSum = 0;
        let commissionSum = 0;
        for (const deal of won) {
          const vehicle = DLQ.vehicleById(vehicles, deal.vehicleId);
          if (!vehicle) continue;
          const price = DLQ.dealPrice(deal, vehicle);
          const m = DLQ.margin(vehicle, price);
          revenue += price;
          marginSum += m;
          commissionSum += DLQ.commission(m, rules);
        }
        return {
          seller,
          sales: won.length,
          revenue,
          margin: marginSum,
          commission: commissionSum,
          goal: seller.monthlyGoal,
          goalPct: seller.monthlyGoal ? won.length / seller.monthlyGoal : 0,
        };
      })
      .sort((a, b) => b.sales - a.sales || b.margin - a.margin);
  },

  // =========================================================================
  // Vitrine: filtros, busca e ordenação
  // =========================================================================

  normalize(value) {
    return String(value)
      .normalize('NFD')
      .replace(/[̀-ͯ]/g, '')
      .toLowerCase();
  },

  filterStock(vehicles, models, brands, filters) {
    const term = filters.term ? DLQ.normalize(String(filters.term).trim()) : '';
    return vehicles.filter((v) => {
      const model = DLQ.modelById(models, v.modelId);
      if (!model) return false;
      if (filters.brandId && model.brandId !== filters.brandId) return false;
      if (filters.modelId && model.id !== filters.modelId) return false;
      if (filters.category && model.category !== filters.category) return false;
      if (filters.transmission && v.transmission !== filters.transmission) return false;
      if (filters.fuel && v.fuel !== filters.fuel) return false;
      if (filters.color && v.color !== filters.color) return false;
      if (filters.yearMin && v.modelYear < filters.yearMin) return false;
      if (filters.priceMax && v.price > filters.priceMax) return false;
      if (filters.kmMax != null && v.km > filters.kmMax) return false;
      if (term) {
        const haystack = DLQ.normalize(`${DLQ.vehicleLabel(models, brands, v)} ${v.color} ${v.modelYear}`);
        if (!haystack.includes(term)) return false;
      }
      return true;
    });
  },

  sortStock(vehicles, sort) {
    const list = [...vehicles];
    switch (sort) {
      case 'price-asc':
        return list.sort((a, b) => a.price - b.price);
      case 'price-desc':
        return list.sort((a, b) => b.price - a.price);
      case 'km-asc':
        return list.sort((a, b) => a.km - b.km);
      case 'year-desc':
        return list.sort((a, b) => b.modelYear - a.modelYear || a.km - b.km);
      default:
        return list.sort((a, b) => b.arrivedAt.localeCompare(a.arrivedAt));
    }
  },

  /** Chegaram por último e ainda estão disponíveis. */
  freshArrivals(vehicles, limit = 8) {
    return DLQ.sortStock(DLQ.availableStock(vehicles), 'recent').slice(0, limit);
  },

  /** Destaques da semana: melhor relação preço/ano entre os disponíveis. */
  weeklyHighlights(vehicles, limit = 4) {
    return [...DLQ.availableStock(vehicles)]
      .sort((a, b) => b.modelYear - a.modelYear || a.km - b.km)
      .slice(0, limit);
  },

  /** Mesmo segmento, faixa de preço parecida, sem repetir o próprio veículo. */
  similarVehicles(vehicles, models, vehicle, limit = 4) {
    const model = DLQ.modelById(models, vehicle.modelId);
    return DLQ.availableStock(vehicles)
      .filter((v) => v.id !== vehicle.id && DLQ.modelById(models, v.modelId)?.category === model?.category)
      .sort((a, b) => Math.abs(a.price - vehicle.price) - Math.abs(b.price - vehicle.price))
      .slice(0, limit);
  },

  countByCategory(vehicles, models, category) {
    return vehicles.filter((v) => DLQ.modelById(models, v.modelId)?.category === category).length;
  },
};
window.DLQ = DLQ;

window.dlFormat = {
  currency: (value) => formatCurrency(value, i18n.lang),
  currencyCompact: (value) => formatCurrencyCompact(value, i18n.lang),
  compact: (value) => formatCompact(value, i18n.lang),
  number: (value) => formatNumber(value, i18n.lang),
  percent: (value) => formatPercent(value, i18n.lang),
  date: (iso) => formatDate(iso, i18n.lang),
  datetime: (iso) => formatDateTime(iso, i18n.lang),
  time: (iso) => formatTime(iso, i18n.lang),
  dayMonth: (iso) => formatDayMonth(iso, i18n.lang),
};

// ─── Magics de i18n e formatação ────────────────────────────────────────────
// Cada magic toca o `ui.lang` reativo para que x-text/x-bind rodem de novo
// quando o idioma muda (deixa a interface inteira reativa à troca de idioma).
const touchLang = () => {
  const ui = Alpine.store('ui');
  return ui ? ui.lang : i18n.lang;
};

Alpine.magic('t', () => (key, params) => {
  touchLang();
  return i18n.t(key, params || {});
});
Alpine.magic('tArray', () => (key) => {
  touchLang();
  return i18n.tArray(key);
});
Alpine.magic('lang', () => {
  return touchLang();
});
Alpine.magic('money', () => (value) => {
  touchLang();
  return formatCurrency(Number(value) || 0, i18n.lang);
});
Alpine.magic('currency', () => (value) => {
  touchLang();
  return formatCurrency(Number(value) || 0, i18n.lang);
});
Alpine.magic('compact', () => (value) => {
  touchLang();
  return formatCompact(Number(value) || 0, i18n.lang);
});
Alpine.magic('moneyCompact', () => (value) => {
  touchLang();
  return formatCurrencyCompact(Number(value) || 0, i18n.lang);
});
Alpine.magic('number', () => (value) => {
  touchLang();
  return formatNumber(Number(value) || 0, i18n.lang);
});
Alpine.magic('percent', () => (value) => {
  touchLang();
  return formatPercent(Number(value) || 0, i18n.lang);
});
Alpine.magic('date', () => (iso) => {
  touchLang();
  return iso ? formatDate(iso, i18n.lang) : '—';
});
Alpine.magic('datetime', () => (iso) => {
  touchLang();
  return iso ? formatDateTime(iso, i18n.lang) : '—';
});
Alpine.magic('time', () => (iso) => {
  touchLang();
  return iso ? formatTime(iso, i18n.lang) : '—';
});
Alpine.magic('dayMonth', () => (iso) => {
  touchLang();
  return iso ? formatDayMonth(iso, i18n.lang) : '—';
});

/** Percentual com duas casas, do jeito do React: `(v * 100).toFixed(2)`. */
Alpine.magic('rate', () => (value) => `${((Number(value) || 0) * 100).toFixed(2).replace('.', ',')}%`);

Alpine.magic('q', () => DLQ);
Alpine.magic('img', () => (seed, w, h) => dlImage(seed, w, h));
Alpine.magic('dayKey', () => (offset) => dayKey(offset));

// ===========================================================================
// Relógio global — o `useNow()` do React
//
// Uma leitura só para a página inteira: se cada card chamasse Date.now() por
// conta própria, dois cards da mesma lista poderiam cair em dias diferentes e
// o contador de dias em estoque mudaria a cada F5. O passo é de um minuto —
// nada aqui muda de segundo em segundo.
// ===========================================================================
Alpine.store('clock', {
  now: Date.now(),
  init() {
    setInterval(() => {
      this.now = Date.now();
    }, 60000);
  },
});

// ─── UI store — chave: dl-ui ────────────────────────────────────────────────
const savedUi = load('dl-ui', {});
Alpine.store('ui', {
  darkMode: savedUi.darkMode ?? false,
  sidebarCollapsed: savedUi.sidebarCollapsed ?? false,
  sidebarMobileOpen: false,
  cookiesAccepted: savedUi.cookiesAccepted ?? false,
  showCookieBanner: false,
  /** Grade ou lista na vitrine de veículos — persistido. */
  stockView: savedUi.stockView ?? 'grid',
  toasts: [],
  lang: i18n.lang,
  languages: LANGUAGES,
  _toastId: 0,

  init() {
    this.applyTheme();
    if (!this.cookiesAccepted) {
      setTimeout(() => {
        this.showCookieBanner = true;
      }, 1200);
    }
    Alpine.effect(() => {
      save('dl-ui', {
        darkMode: this.darkMode,
        sidebarCollapsed: this.sidebarCollapsed,
        cookiesAccepted: this.cookiesAccepted,
        stockView: this.stockView,
      });
    });
  },

  get currentLanguage() {
    return this.languages.find((l) => l.code === this.lang) || this.languages[0];
  },

  setLang(code) {
    i18n.setLang(code);
    this.lang = code;
  },

  applyTheme() {
    document.documentElement.classList.toggle('dark', this.darkMode);
  },
  toggleDarkMode() {
    this.darkMode = !this.darkMode;
    this.applyTheme();
  },
  setDarkMode(value) {
    this.darkMode = Boolean(value);
    this.applyTheme();
  },
  toggleSidebar() {
    this.sidebarCollapsed = !this.sidebarCollapsed;
  },
  toggleSidebarMobile(value) {
    this.sidebarMobileOpen = value !== undefined ? value : !this.sidebarMobileOpen;
  },
  setStockView(view) {
    this.stockView = view;
  },

  notify(message, type = 'info', duration = 4000) {
    const id = ++this._toastId;
    this.toasts.push({ id, message, type, duration });
    setTimeout(() => this.dismissToast(id), duration);
  },
  notifySuccess(message) {
    this.notify(message, 'success');
  },
  notifyError(message) {
    this.notify(message, 'error');
  },
  notifyWarning(message) {
    this.notify(message, 'warning');
  },
  dismissToast(id) {
    this.toasts = this.toasts.filter((t) => t.id !== id);
  },

  acceptCookies() {
    this.cookiesAccepted = true;
    this.showCookieBanner = false;
  },
  declineCookies() {
    this.cookiesAccepted = false;
    this.showCookieBanner = false;
  },
  openCookieBanner() {
    this.showCookieBanner = true;
  },
  closeCookieBanner() {
    this.showCookieBanner = false;
  },
});

document.addEventListener('i18n:changed', (e) => {
  const ui = Alpine.store('ui');
  if (ui) ui.lang = e.detail.lang;
});

// ─── Auth store — chave: dl-auth (demo gerente@garagem.com.br / demo123) ────
// A sessão real é do Laravel; este store só espelha o usuário para a interface
// e guarda as edições do perfil feitas em /app/configuracoes.
const savedAuth = load('dl-auth', {});
Alpine.store('auth', {
  user: DL.user || savedAuth.user || null,
  demo: DL.demo || { email: 'gerente@garagem.com.br', password: 'demo123' },

  init() {
    Alpine.effect(() => {
      save('dl-auth', { user: this.user });
    });
  },

  get isAuthenticated() {
    return this.user !== null;
  },

  get initials() {
    if (!this.user) return '?';
    return (
      String(this.user.name || '')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((p) => (p[0] || '').toUpperCase())
        .join('') || '?'
    );
  },

  updateProfile(patch) {
    if (!this.user) return;
    this.user = { ...this.user, ...patch };
  },

  logout() {
    this.user = null;
  },
});

// ===========================================================================
// Dealer store — chave: dl-data
//
// Espelha dealership/react src/stores/dealer.ts: estoque, propostas, trocas,
// test-drives, clientes e regras, com as mesmas ações (mover proposta,
// reservar/liberar veículo, fechar venda, agendar test-drive, CRUD).
// ===========================================================================
function seedDealer() {
  return {
    brands: clone(DL.brands || []),
    models: clone(DL.models || []),
    vehicles: clone(DL.vehicles || []),
    customers: clone(DL.customers || []),
    sellers: clone(DL.sellers || []),
    deals: clone(DL.deals || []),
    tradeIns: clone(DL.tradeIns || []),
    drives: clone(DL.drives || []),
    rules: clone(DL.rules || {}),
    seededAt: DL.seededAt || Date.now(),
  };
}

/**
 * A semente nasce com datas relativas a hoje. Se a demonstração ficar guardada
 * no navegador por horas, a agenda de test-drive e o funil de 90 dias saem do
 * lugar — por isso a base é refeita depois de algumas horas.
 */
const SEED_TTL_MS = DL.seedTtlMs || 6 * MS_HOUR;
const savedDealer = load('dl-data', null);
const freshEnough =
  savedDealer &&
  Array.isArray(savedDealer.vehicles) &&
  savedDealer.vehicles.length > 0 &&
  Date.now() - (savedDealer.seededAt || 0) <= SEED_TTL_MS;

Alpine.store('dealer', {
  ...(freshEnough ? savedDealer : seedDealer()),

  init() {
    Alpine.effect(() => {
      // Toca cada coleção para o efeito reagir a qualquer alteração.
      this.vehicles.length;
      this.deals.length;
      this.tradeIns.length;
      this.drives.length;
      this.customers.length;
      this.rules.slowStockDays;
      this.seededAt;
      save('dl-data', {
        brands: this.brands,
        models: this.models,
        vehicles: this.vehicles,
        customers: this.customers,
        sellers: this.sellers,
        deals: this.deals,
        tradeIns: this.tradeIns,
        drives: this.drives,
        rules: this.rules,
        seededAt: this.seededAt,
      });
    });
  },

  // ---- estoque ------------------------------------------------------------

  saveVehicle(patch) {
    if (patch.id) {
      const index = this.vehicles.findIndex((v) => v.id === patch.id);
      if (index < 0) return null;
      this.vehicles[index] = { ...this.vehicles[index], ...patch };
      return this.vehicles[index];
    }

    const id = nextId(this.vehicles);
    const model = DLQ.modelById(this.models, patch.modelId ?? this.models[0].id);
    const brand = this.brands.find((b) => b.id === model?.brandId);
    const version = patch.version ?? 'Padrão';
    const modelYear = patch.modelYear ?? patch.year ?? new Date().getFullYear();
    const slug = `${brand?.slug ?? 'veiculo'}-${slugify(model?.name ?? 'modelo')}-${slugify(version)}-${modelYear}-${id}`;

    const created = {
      id,
      slug,
      modelId: model?.id ?? 1,
      version,
      year: patch.year ?? modelYear,
      modelYear,
      km: patch.km ?? 0,
      color: patch.color ?? 'Branco',
      plateEnd: patch.plateEnd ?? '0',
      transmission: patch.transmission ?? 'manual',
      fuel: patch.fuel ?? 'flex',
      doors: patch.doors ?? 4,
      options: patch.options ?? [],
      photoSeeds: patch.photoSeeds ?? [`${slug}-a`, `${slug}-b`, `${slug}-c`],
      acquisitionCost: patch.acquisitionCost ?? 0,
      prepCost: patch.prepCost ?? 0,
      price: patch.price ?? 0,
      state: patch.state ?? 'preparacao',
      // A entrada define o início da contagem de dias em estoque.
      arrivedAt: patch.arrivedAt ?? new Date().toISOString(),
      soldAt: null,
    };
    this.vehicles.push(created);
    return created;
  },

  removeVehicle(id) {
    this.vehicles = this.vehicles.filter((v) => v.id !== id);
    this.deals = this.deals.filter((d) => d.vehicleId !== id);
    this.drives = this.drives.filter((d) => d.vehicleId !== id);
  },

  setVehicleState(id, state) {
    // `arrivedAt` nunca é tocado aqui: mudar de situação não zera os dias.
    const vehicle = this.vehicles.find((v) => v.id === id);
    if (!vehicle) return;
    vehicle.state = state;
    vehicle.soldAt = state === 'vendido' ? vehicle.soldAt ?? new Date().toISOString() : null;
  },

  // ---- propostas ----------------------------------------------------------

  /**
   * Mover a proposta mexe no estoque: entrar em `aprovada` reserva o veículo e
   * ele some da vitrine; sair de `aprovada` libera. Em nenhum dos dois
   * caminhos `arrivedAt` muda — os dias em estoque continuam.
   */
  moveDeal(id, stage, lostReason) {
    const deal = this.deals.find((d) => d.id === id);
    if (!deal || deal.stage === stage) return;

    const entering = stage === 'aprovada';
    const leaving = deal.stage === 'aprovada';
    const vehicle = this.vehicles.find((v) => v.id === deal.vehicleId);

    if (vehicle) {
      if (entering && vehicle.state === 'disponivel') vehicle.state = 'reservado';
      else if (leaving && vehicle.state === 'reservado') vehicle.state = 'disponivel';
    }

    deal.stage = stage;
    deal.movedAt = new Date().toISOString();
    if (stage === 'perdida') deal.lostReason = lostReason ?? deal.lostReason ?? '';
    else delete deal.lostReason;
  },

  saveDeal(patch) {
    if (patch.id) {
      const deal = this.deals.find((d) => d.id === patch.id);
      if (!deal) return null;
      Object.assign(deal, patch);
      return deal;
    }
    const now = new Date().toISOString();
    const created = {
      id: nextId(this.deals),
      vehicleId: patch.vehicleId ?? 0,
      customerId: patch.customerId ?? 0,
      sellerId: patch.sellerId ?? this.sellers[0].id,
      stage: patch.stage ?? 'contato',
      createdAt: patch.createdAt ?? now,
      movedAt: patch.movedAt ?? now,
      discount: patch.discount ?? 0,
      tradeInId: patch.tradeInId ?? null,
      finance: patch.finance ?? null,
    };
    this.deals.push(created);
    return created;
  },

  removeDeal(id) {
    const deal = this.deals.find((d) => d.id === id);
    if (deal && deal.stage === 'aprovada') {
      const vehicle = this.vehicles.find((v) => v.id === deal.vehicleId);
      if (vehicle && vehicle.state === 'reservado') vehicle.state = 'disponivel';
    }
    this.deals = this.deals.filter((d) => d.id !== id);
  },

  /** Fecha a venda de uma proposta aprovada: o veículo sai do estoque. */
  closeSale(dealId) {
    const deal = this.deals.find((d) => d.id === dealId);
    if (!deal || deal.stage !== 'aprovada') return;
    const vehicle = this.vehicles.find((v) => v.id === deal.vehicleId);
    if (!vehicle) return;
    vehicle.state = 'vendido';
    vehicle.soldAt = vehicle.soldAt ?? new Date().toISOString();
  },

  // ---- trocas -------------------------------------------------------------

  saveTradeIn(patch) {
    if (patch.id) {
      const tradeIn = this.tradeIns.find((t) => t.id === patch.id);
      if (!tradeIn) return null;
      Object.assign(tradeIn, patch);
      return tradeIn;
    }
    const created = {
      id: nextId(this.tradeIns),
      dealId: patch.dealId ?? null,
      customerId: patch.customerId ?? 0,
      modelId: patch.modelId ?? this.models[0].id,
      year: patch.year ?? new Date().getFullYear() - 3,
      km: patch.km ?? 0,
      condition: patch.condition ?? 'bom',
      options: patch.options ?? [],
      accepted: patch.accepted ?? false,
      createdAt: patch.createdAt ?? new Date().toISOString(),
    };
    this.tradeIns.push(created);
    return created;
  },

  setTradeInAccepted(id, accepted) {
    const tradeIn = this.tradeIns.find((t) => t.id === id);
    if (tradeIn) tradeIn.accepted = accepted;
  },

  // ---- test-drives --------------------------------------------------------

  /**
   * O agendamento devolve os três motivos de recusa separados: a agenda do
   * veículo, a agenda do vendedor e a CNH. A tela mostra qual deles bateu.
   */
  scheduleDrive(input) {
    const customer = DLQ.customerById(this.customers, input.customerId);
    const clash = DLQ.driveConflict(input, this.drives);
    const cnhOk = DLQ.cnhValid(customer, input.date);

    if (clash.vehicle || clash.seller || !cnhOk) {
      return { ok: false, cnhOk, vehicleClash: clash.vehicle, sellerClash: clash.seller };
    }

    const drive = {
      id: nextId(this.drives),
      vehicleId: input.vehicleId,
      customerId: input.customerId,
      sellerId: input.sellerId,
      date: input.date,
      from: input.from,
      to: input.to,
      status: 'agendado',
      cnhOk: true,
    };
    this.drives.push(drive);
    return { ok: true, cnhOk: true, drive };
  },

  setDriveStatus(id, status) {
    const drive = this.drives.find((d) => d.id === id);
    if (drive) drive.status = status;
  },

  // ---- clientes -----------------------------------------------------------

  saveCustomer(patch) {
    if (patch.id) {
      const customer = this.customers.find((c) => c.id === patch.id);
      if (!customer) return null;
      Object.assign(customer, patch);
      return customer;
    }
    const created = {
      id: nextId(this.customers),
      name: patch.name ?? 'Novo cliente',
      email: patch.email ?? '',
      phone: patch.phone ?? '',
      document: patch.document ?? '',
      avatar: patch.avatar ?? '',
      cnhUntil: patch.cnhUntil ?? null,
      createdAt: patch.createdAt ?? new Date().toISOString(),
    };
    this.customers.push(created);
    return created;
  },

  removeCustomer(id) {
    this.customers = this.customers.filter((c) => c.id !== id);
  },

  // ---- regras -------------------------------------------------------------

  saveRules(patch) {
    Object.assign(this.rules, patch);
  },

  reset() {
    Object.assign(this, seedDealer());
  },
});

Alpine.start();
