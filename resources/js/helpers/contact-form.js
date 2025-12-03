const sanitizeDigits = (value = '', limit = 11) => value.replace(/\D/g, '').slice(0, limit);

export const createEmptyContactForm = () => ({
    name: '',
    cpf: '',
    phone: '+55 ',
    cep: '',
    street: '',
    number: '',
    complement: '',
    district: '',
    city: '',
    state: '',
});

export const formatCpfValue = (value = '') => {
    const digits = sanitizeDigits(value, 11);
    if (!digits) {
        return '';
    }

    const part1 = digits.slice(0, 3);
    const part2 = digits.slice(3, 6);
    const part3 = digits.slice(6, 9);
    const suffix = digits.slice(9);

    let formatted = part1;
    if (part2) {
        formatted += `.${part2}`;
    }
    if (part3) {
        formatted += `.${part3}`;
    }
    if (suffix) {
        formatted += `-${suffix}`;
    }

    return formatted;
};

const calculateCpfVerifier = (digits, factor) => {
    let total = 0;
    for (let i = 0; i < factor - 1; i += 1) {
        total += Number(digits[i]) * (factor - i);
    }

    const remainder = total % 11;
    return remainder < 2 ? 0 : 11 - remainder;
};

const isRepeatedDigits = (digits) => /^(\d)\1+$/.test(digits);

const stripCountryCode = (value = '') => value.replace(/^\+?55\s*/, '');

const extractLocalPhoneDigits = (value = '') => sanitizeDigits(stripCountryCode(value), 11);

export const validateCpfValue = (value = '') => {
    const digits = sanitizeDigits(value, 11);
    if (digits.length !== 11 || isRepeatedDigits(digits)) {
        return false;
    }

    return (
        calculateCpfVerifier(digits, 10) === Number(digits[9]) &&
        calculateCpfVerifier(digits, 11) === Number(digits[10])
    );
};

const formatPhonePattern = (digits) => {
    if (!digits) {
        return '';
    }

    if (digits.length <= 2) {
        return `(${digits}`;
    }

    const ddd = digits.slice(0, 2);
    const body = digits.slice(2);

    if (body.length <= 4) {
        return `(${ddd}) ${body}`;
    }

    if (body.length <= 8) {
        const prefix = body.slice(0, body.length - 4);
        const suffix = body.slice(body.length - 4);
        return `(${ddd}) ${prefix}-${suffix}`;
    }

    return `(${ddd}) ${body.slice(0, 5)}-${body.slice(5)}`;
};

export const formatPhoneValue = (value = '') => {
    const localDigits = extractLocalPhoneDigits(value);
    if (!localDigits) {
        return '+55 ';
    }

    return `+55 ${formatPhonePattern(localDigits)}`;
};

export const validatePhoneValue = (value = '') => {
    const localDigits = extractLocalPhoneDigits(value);
    if (localDigits.length < 10) {
        return false;
    }

    return !isRepeatedDigits(localDigits);
};

export const formatCepValue = (value = '') => {
    const digits = sanitizeDigits(value, 8);
    if (!digits) {
        return '';
    }

    const prefix = digits.slice(0, 5);
    const suffix = digits.slice(5);

    return suffix ? `${prefix}-${suffix}` : prefix;
};
