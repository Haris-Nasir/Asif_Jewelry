export const CURRENCY_CODE = 'Rs.';

export function formatAmount(value) {
    const amount = parseFloat(value || 0);

    if (Number.isNaN(amount)) {
        return '0.00';
    }

    return amount.toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

export function formatCurrency(value) {
    return `${CURRENCY_CODE} ${formatAmount(value)}`;
}

export function formatDate(value) {
    if (!value) {
        return '-';
    }

    const str = String(value).trim();

    if (/^\d{2}-\d{2}-\d{4}$/.test(str)) {
        const [day, month, year] = str.split('-');
        const parsed = new Date(`${year}-${month}-${day}T00:00:00`);

        if (!Number.isNaN(parsed.getTime())) {
            const parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Karachi',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            }).formatToParts(parsed);

            const get = (type) => parts.find((part) => part.type === type)?.value ?? '';
            const dateStr = `${get('day')}-${get('month')}-${get('year')}`;

            return `${dateStr}, ${get('hour')}:${get('minute')} ${get('dayPeriod')}`.trim();
        }
    }

    const parsed = new Date(str);

    if (Number.isNaN(parsed.getTime())) {
        const match = str.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);

        if (match) {
            const [, year, month, day, hour = '00', minute = '00'] = match;
            const fallback = new Date(`${year}-${month}-${day}T${hour}:${minute}:00`);

            if (!Number.isNaN(fallback.getTime())) {
                const parts = new Intl.DateTimeFormat('en-GB', {
                    timeZone: 'Asia/Karachi',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true,
                }).formatToParts(fallback);

                const get = (type) => parts.find((part) => part.type === type)?.value ?? '';
                const dateStr = `${get('day')}-${get('month')}-${get('year')}`;

                return `${dateStr}, ${get('hour')}:${get('minute')} ${get('dayPeriod')}`.trim();
            }
        }

        return str;
    }

    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Karachi',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).formatToParts(parsed);

    const get = (type) => parts.find((part) => part.type === type)?.value ?? '';
    const dateStr = `${get('day')}-${get('month')}-${get('year')}`;

    return `${dateStr}, ${get('hour')}:${get('minute')} ${get('dayPeriod')}`.trim();
}

export function getNowDateTime() {
    const d = new Date();
    const pad = (value) => String(value).padStart(2, '0');

    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

export function toInputDateTime(value) {
    if (!value) {
        return '';
    }

    const str = String(value).trim();

    if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(str)) {
        return str.slice(0, 16);
    }

    const parsed = new Date(str);

    if (Number.isNaN(parsed.getTime())) {
        const match = str.match(/^(\d{4}-\d{2}-\d{2})/);
        return match ? `${match[1]}T00:00` : '';
    }

    const pad = (value) => String(value).padStart(2, '0');

    return `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}T${pad(parsed.getHours())}:${pad(parsed.getMinutes())}`;
}

export function toInputDate(value) {
    if (!value) {
        return '';
    }

    const match = String(value).match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : String(value).slice(0, 10);
}

export function toDateOnly(value) {
    return toInputDate(value);
}
