import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const createEmptyContactForm = () => ({
    name: '',
    cpf: '',
    phone: '',
    cep: '',
    street: '',
    number: '',
    complement: '',
    district: '',
    city: '',
    state: '',
});

window.contactsManager = function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    return {
        csrfToken,
        search: '',
        loading: false,
        addressLoading: false,
        addressSuggestions: [],
        contacts: [],
        editingId: null,
        notice: '',
        errors: {},
        form: createEmptyContactForm(),

        init() {
            this.fetchContacts();
        },

        async fetchContacts() {
            this.loading = true;
            this.notice = '';

            const url = new URL('/contacts', window.location.origin);
            if (this.search.trim() !== '') {
                url.searchParams.set('search', this.search.trim());
            }

            try {
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
                this.contacts = payload.data ?? [];
            } catch (error) {
                this.notice = error.message;
            } finally {
                this.loading = false;
            }
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};
            this.notice = '';

            const method = this.editingId ? 'PUT' : 'POST';
            const url = this.editingId ? `/contacts/${this.editingId}` : '/contacts';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(this.form),
                });

                const payload = await response.json();

                if (!response.ok) {
                    this.errors = payload.errors ?? {};
                    this.notice = payload.message ?? 'Não foi possível salvar o contato.';
                    return;
                }

                this.notice = this.editingId ? 'Contato atualizado.' : 'Contato cadastrado.';
                this.resetForm();
                this.fetchContacts();
            } catch (error) {
                this.notice = error.message;
            } finally {
                this.loading = false;
            }
        },

        startEditing(contact) {
            this.editingId = contact.id;
            this.form = {
                name: contact.name,
                cpf: contact.cpf,
                phone: contact.phone,
                cep: contact.address.cep ?? '',
                street: contact.address.street ?? '',
                number: contact.address.number ?? '',
                complement: contact.address.complement ?? '',
                district: contact.address.district ?? '',
                city: contact.address.city ?? '',
                state: (contact.address.state ?? '').toUpperCase(),
            };
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        cancelEditing() {
            this.resetForm();
        },

        resetForm() {
            this.form = createEmptyContactForm();
            this.editingId = null;
            this.addressSuggestions = [];
            this.errors = {};
        },

        async lookupAddress() {
            if (!this.form.state || !this.form.city || !this.form.street) {
                this.addressSuggestions = [];
                return;
            }

            this.addressLoading = true;
            this.addressSuggestions = [];

            const url = new URL('/addresses', window.location.origin);
            url.searchParams.set('uf', this.form.state.toUpperCase());
            url.searchParams.set('city', this.form.city);
            url.searchParams.set('street', this.form.street);

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                if (!response.ok) {
                    throw new Error('Não foi possível consultar o endereço.');
                }

                const payload = await response.json();
                this.addressSuggestions = payload.data ?? [];
            } catch (error) {
                this.notice = error.message;
            } finally {
                this.addressLoading = false;
            }
        },

        selectSuggestion(item) {
            this.form.cep = item.cep ?? this.form.cep;
            this.form.street = item.street ?? this.form.street;
            this.form.district = item.district ?? this.form.district;
            this.form.city = item.city ?? this.form.city;
            this.form.state = (item.state ?? this.form.state).toUpperCase();
            this.addressSuggestions = [];
        },

        async deleteContact(contactId) {
            if (!confirm('Deseja remover este contato?')) {
                return;
            }

            try {
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

                this.notice = 'Contato removido.';
                this.fetchContacts();
            } catch (error) {
                this.notice = error.message;
            }
        },
    };
};

Alpine.start();
