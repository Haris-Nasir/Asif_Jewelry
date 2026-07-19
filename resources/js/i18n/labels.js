import en from './en';
import ur from './ur';

// Shared with index.js via same localStorage key — read live locale from DOM/body class
const LOCALE_KEY = 'ayyub_jewelry_locale';

function currentLocale() {
    if (typeof document !== 'undefined' && document.body && document.body.classList.contains('locale-urdu')) {
        return 'ur';
    }
    const saved = localStorage.getItem(LOCALE_KEY);
    return saved === 'ur' ? 'ur' : 'en';
}

function translateKey(key) {
    const locale = currentLocale();
    const table = locale === 'ur' ? ur : en;
    return table[key] || en[key] || key;
}

/**
 * Known DB / seed labels → i18n keys.
 * User-entered custom names fall through unchanged.
 */
const LABEL_KEYS = {
    'Gold Items': 'data.goldItems',
    'Silver (Chandi) Items': 'data.silverItems',
    'Silver Items': 'data.silverItems',
    gold: 'common.gold',
    silver: 'common.silver',
    Gold: 'common.gold',
    Silver: 'common.silver',
    purchase: 'stock.purchase',
    sale: 'stock.sale',
    Purchase: 'stock.purchase',
    Sale: 'stock.sale',
    Tops: 'data.item.tops',
    Angothi: 'data.item.angothi',
    Kanty: 'data.item.kanty',
    Balian: 'data.item.balian',
    'Kady Set': 'data.item.kadySet',
    Nail: 'data.item.nail',
    Daddy: 'data.item.daddy',
    Pazeb: 'data.item.pazeb',
    Tweeeez: 'data.item.tweeeez',
    'Locket Set': 'data.item.locketSet',
};

/**
 * Translate a stored English label for the active locale.
 */
export function label(text) {
    if (text == null || text === '') {
        return text == null ? '' : text;
    }
    const raw = String(text).trim();
    const key = LABEL_KEYS[raw];
    if (key) {
        return translateKey(key);
    }
    const withMetal = raw.match(/^(.+?)\s*\((gold|silver)\)$/i);
    if (withMetal) {
        return label(withMetal[1]) + ' (' + label(withMetal[2].toLowerCase()) + ')';
    }
    return raw;
}

/**
 * Build a vue-search-select option from API category row.
 */
export function categoryOption(category, { withMetal = false } = {}) {
    const id = category.qualityCategoryId
        ?? category.sell_quality_category_id
        ?? category.inward_quality_category_id
        ?? category.value;
    const name = category.qualityCategoryName
        ?? category.sell_category_name
        ?? category.inward_category_name
        ?? category.text
        ?? '';
    const metal = category.metalType ?? category.metal_type;
    let text = label(name);
    if (withMetal && metal) {
        text += ' (' + label(metal) + ')';
    }
    return { value: id, text };
}
