export default class ContactApiService {
    constructor(csrfToken) {
        this.csrfToken = csrfToken;
    }

    async list({ search = '', page = 1, perPage = 12 } = {}) {
        const url = new URL('/contacts', window.location.origin);

        if (search?.trim()) {
            url.searchParams.set('search', search.trim());
        }

        url.searchParams.set('page', page);
        url.searchParams.set('per_page', perPage);

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
            },
        });

        if (!response.ok) {
            throw new Error('Não foi possível carregar os contatos.');
        }

        const payload = await response.json();
        return payload;
    }

    async createMock() {
        const response = await fetch('/contacts/mock', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload?.message ?? 'Não foi possível cadastrar contato mock.');
        }

        return payload.data ?? null;
    }

    async save(contact, editingId) {
        const url = editingId ? `/contacts/${editingId}` : '/contacts';
        const method = editingId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
            },
            body: JSON.stringify(contact),
        });

        const payload = await response.json();

        return {
            ok: response.ok,
            payload,
        };
    }

    async delete(contactId) {
        const response = await fetch(`/contacts/${contactId}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
            },
        });

        if (!response.ok) {
            throw new Error('Não foi possível excluir o contato.');
        }
    }
}
