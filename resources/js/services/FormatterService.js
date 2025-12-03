import {
    createEmptyContactForm,
    formatCpfValue,
    formatPhoneValue,
    formatCepValue,
    validateCpfValue,
    validatePhoneValue,
} from '../helpers/contact-form';

export default class FormatterService {
    createEmptyForm() {
        return createEmptyContactForm();
    }

    formatCpf(value) {
        return formatCpfValue(value);
    }

    validateCpf(value) {
        return validateCpfValue(value);
    }

    formatPhone(value) {
        return formatPhoneValue(value);
    }

    validatePhone(value) {
        return validatePhoneValue(value);
    }

    formatCep(value) {
        return formatCepValue(value);
    }
}
