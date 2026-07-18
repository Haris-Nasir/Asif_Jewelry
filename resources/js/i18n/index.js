import Vue from 'vue';
import en from './en';
import ur from './ur';
import { label, categoryOption } from './labels';

const messages = { en, ur };
const LOCALE_KEY = 'ayub_jewelry_locale';

function readSavedLocale() {
    const saved = localStorage.getItem(LOCALE_KEY);
    return saved === 'ur' ? 'ur' : 'en';
}

export const i18nState = Vue.observable({
    locale: readSavedLocale(),
});

function lookup(locale, key) {
    if (!key) return '';
    const table = messages[locale] || {};
    if (Object.prototype.hasOwnProperty.call(table, key)) {
        return table[key];
    }
    return null;
}

/**
 * Translate a key. Optional params replace {name} placeholders.
 * Example: t('stock.piecesAvailable', { n: 3, weight: 2.5 })
 */
export function t(key, params) {
    const locale = i18nState.locale;
    let text = lookup(locale, key);
    if (text == null) {
        text = lookup('en', key);
    }
    if (text == null) {
        text = key;
    }
    if (params && typeof text === 'string') {
        Object.keys(params).forEach((name) => {
            text = text.replace(new RegExp('\\{' + name + '\\}', 'g'), String(params[name]));
        });
    }
    return text;
}

export { label, categoryOption };

export function applyLocale(locale) {
    const next = locale === 'ur' ? 'ur' : 'en';
    i18nState.locale = next;
    localStorage.setItem(LOCALE_KEY, next);
    document.documentElement.lang = next === 'ur' ? 'ur' : 'en';
    document.documentElement.dir = 'ltr';
    document.body.classList.toggle('locale-urdu', next === 'ur');
}

export function setLocale(locale) {
    applyLocale(locale);
}

export function initI18n() {
    applyLocale(i18nState.locale);

    Vue.prototype.$t = function (key, params) {
        void i18nState.locale;
        return t(key, params);
    };

    Vue.prototype.$label = function (text) {
        void i18nState.locale;
        return label(text);
    };

    Vue.prototype.$categoryOption = function (category, opts) {
        void i18nState.locale;
        return categoryOption(category, opts);
    };

    Vue.prototype.$locale = i18nState;
}
