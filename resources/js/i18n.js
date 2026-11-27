// ─── i18n — espelha dealership/react src/i18n + src/lib/format ─────────────────
// Os mesmos pacotes pt-BR/en/es (516 chaves em cada), com interpolação no
// estilo i18next (`{{x}}`) e pluralização por sufixo `_one`/`_other` resolvida
// por `Intl.PluralRules` — são 9 famílias nos três idiomas. Exposto como
// helper puro (atributos `data-i18n`) e como magic do Alpine (`$t`), para o
// Blade traduzir reativamente quando o idioma muda.
import ptBR from './locales/pt-BR.json';
import en from './locales/en.json';
import es from './locales/es.json';

export const LANGUAGES = [
  { code: 'pt-BR', label: 'Português', flag: '🇧🇷' },
  { code: 'en', label: 'English', flag: '🇺🇸' },
  { code: 'es', label: 'Español', flag: '🇪🇸' },
];

const RESOURCES = { 'pt-BR': ptBR, en, es };
const FALLBACK = 'pt-BR';

function detect() {
  try {
    const stored = localStorage.getItem('lang');
    if (stored && RESOURCES[stored]) return stored;
    const nav = (navigator.language || '').toLowerCase();
    if (nav.startsWith('pt')) return 'pt-BR';
    if (nav.startsWith('es')) return 'es';
    if (nav.startsWith('en')) return 'en';
  } catch (_) {}
  return FALLBACK;
}

function lookup(dict, key) {
  return key.split('.').reduce((o, k) => (o && o[k] != null ? o[k] : undefined), dict);
}

function interpolate(str, params) {
  return str.replace(/\{\{\s*(\w+)\s*\}\}/g, (_, name) =>
    params[name] != null ? String(params[name]) : ''
  );
}

const LOCALE_MAP = {
  'pt-BR': { locale: 'pt-BR', currency: 'BRL' },
  en: { locale: 'en-US', currency: 'USD' },
  es: { locale: 'es-ES', currency: 'EUR' },
};

function cfg(lang) {
  return LOCALE_MAP[lang] || LOCALE_MAP[FALLBACK];
}

// ─── Pluralização ───────────────────────────────────────────────────────────
// `Intl.PluralRules` devolve a categoria CLDR (`one`, `other`, …) do idioma
// ativo; os pacotes têm `_one` e `_other`, então a categoria vira sufixo e
// qualquer outra cai em `_other`.
const pluralCache = new Map();

function pluralCategory(lang, count) {
  try {
    let rules = pluralCache.get(lang);
    if (!rules) {
      rules = new Intl.PluralRules(cfg(lang).locale);
      pluralCache.set(lang, rules);
    }
    return rules.select(count);
  } catch (_) {
    return count === 1 ? 'one' : 'other';
  }
}

// ─── helpers de formatação — espelham src/lib/format.ts ─────────────────────

/** Valor em moeda com centavos — lances, preços e comissões. */
export function formatCurrency(value, lang) {
  const c = cfg(lang);
  return new Intl.NumberFormat(c.locale, {
    style: 'currency',
    currency: c.currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

/** Número compacto, ex.: 2840 -> 2,8 mil. */
export function formatCompact(value, lang) {
  return new Intl.NumberFormat(cfg(lang).locale, { notation: 'compact', maximumFractionDigits: 1 }).format(value);
}

export function formatDateTime(iso, lang) {
  return new Intl.DateTimeFormat(cfg(lang).locale, { dateStyle: 'short', timeStyle: 'short' }).format(new Date(iso));
}

export function formatDate(iso, lang) {
  return new Intl.DateTimeFormat(cfg(lang).locale, { dateStyle: 'medium' }).format(new Date(iso));
}

export function formatTime(iso, lang) {
  return new Intl.DateTimeFormat(cfg(lang).locale, { timeStyle: 'short' }).format(new Date(iso));
}

/** Dia/mês curto para os rótulos do gráfico, ex.: "22/08". */
export function formatDayMonth(iso, lang) {
  return new Intl.DateTimeFormat(cfg(lang).locale, { day: '2-digit', month: '2-digit' }).format(new Date(iso));
}

export function formatNumber(value, lang) {
  return new Intl.NumberFormat(cfg(lang).locale).format(value);
}

/** Percentual a partir de uma razão 0..1, ex.: 0,62 -> "62%". */
export function formatPercent(ratio, lang) {
  return new Intl.NumberFormat(cfg(lang).locale, { style: 'percent', maximumFractionDigits: 0 }).format(ratio);
}

/** Moeda compacta, ex.: 1284000 -> "R$ 1,3 mi". */
export function formatCurrencyCompact(value, lang) {
  const c = cfg(lang);
  return new Intl.NumberFormat(c.locale, {
    style: 'currency',
    currency: c.currency,
    notation: 'compact',
    maximumFractionDigits: 1,
  }).format(value);
}

/** Valor de um input `datetime-local`, ex.: "2026-08-22T15:00". */
export function toInputDateTime(iso) {
  const d = new Date(iso);
  const pad = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/** Lê o valor de um input `datetime-local` de volta em ISO. */
export function fromInputDateTime(value) {
  const d = new Date(value);
  return Number.isNaN(d.getTime()) ? new Date().toISOString() : d.toISOString();
}

/** Duração humana a partir de minutos, ex.: 90 -> "1h30". */
export function formatDuration(mins) {
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  if (h === 0) return `${m}min`;
  if (m === 0) return `${h}h`;
  return `${h}h${String(m).padStart(2, '0')}`;
}

class I18n {
  constructor() {
    this.lang = detect();
    this.dict = RESOURCES[this.lang] || RESOURCES[FALLBACK];
    this.fallbackDict = RESOURCES[FALLBACK];
  }

  t(key, params = {}) {
    // Pluralização: com `count` procura `${key}_${categoria CLDR}`, caindo em
    // `_other` quando o idioma não tem aquela categoria no pacote.
    let realKey = key;
    if (params && typeof params.count === 'number') {
      const category = pluralCategory(this.lang, params.count);
      const candidates = [`${key}_${category}`, `${key}_other`];
      for (const candidate of candidates) {
        if (lookup(this.dict, candidate) != null || lookup(this.fallbackDict, candidate) != null) {
          realKey = candidate;
          break;
        }
      }
    }
    let val = lookup(this.dict, realKey);
    if (val == null) val = lookup(this.fallbackDict, realKey);
    if (typeof val !== 'string') return key;
    return interpolate(val, params);
  }

  /** Valor de array em `key` (traduções que são listas). */
  tArray(key) {
    let val = lookup(this.dict, key);
    if (val == null) val = lookup(this.fallbackDict, key);
    return Array.isArray(val) ? val : [];
  }

  setLang(lang) {
    if (!RESOURCES[lang]) return;
    this.lang = lang;
    this.dict = RESOURCES[lang];
    try {
      localStorage.setItem('lang', lang);
    } catch (_) {}
    document.documentElement.lang = lang;
    this.applyDom(document);
    document.dispatchEvent(new CustomEvent('i18n:changed', { detail: { lang } }));
  }

  /** Tradução estática para nós fora do Alpine (antes da hidratação). */
  applyDom(root) {
    (root || document).querySelectorAll('[data-i18n]').forEach((el) => {
      const v = this.t(el.getAttribute('data-i18n'));
      if (typeof v === 'string') el.textContent = v;
    });
    (root || document).querySelectorAll('[data-i18n-ph]').forEach((el) => {
      const v = this.t(el.getAttribute('data-i18n-ph'));
      if (typeof v === 'string') el.setAttribute('placeholder', v);
    });
  }

  formatCurrency(value) {
    return formatCurrency(value, this.lang);
  }
  formatDate(iso) {
    return formatDate(iso, this.lang);
  }
}

const i18n = new I18n();
window.I18n = i18n;
document.documentElement.lang = i18n.lang;

export default i18n;
