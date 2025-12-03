export default class ContactApiService {
    constructor(csrfToken) {
        this.csrfToken = csrfToken;
    }

    async list(search) {
        const url = new URL('/contacts', window.location.origin);
        if (search?.trim()) {
            url.searchParams.set('search', search.trim());
        }

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
        return payload.data ?? [];
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
