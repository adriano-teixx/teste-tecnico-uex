import Swal from 'sweetalert2';

export default class AlertService {
    constructor() {
        this.defaultTitles = {
            success: 'Sucesso',
            error: 'Erro',
            info: 'Informação',
        };
    }

    notify(type, text, title = null) {
        Swal.fire({
            icon: type,
            title: title ?? this.defaultTitles[type] ?? 'Aviso',
            text,
            confirmButtonText: 'Entendi',
        });
    }

    success(text, title = null) {
        this.notify('success', text, title);
    }

    error(text, title = null) {
        this.notify('error', text, title);
    }

    info(text, title = null) {
        this.notify('info', text, title);
    }
}
