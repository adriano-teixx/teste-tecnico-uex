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
    const googleMapsKey = document.querySelector('meta[name="google-maps-api-key"]')?.content ?? '';
    const contractLabels = {
        hudsoft: 'HudSoft',
        ag4: 'AG4',
        beats: 'Beats',
        maroto: 'Maroto Bagari',
    };

    return {
        csrfToken,
        googleMapsKey,
        search: '',
        contractType: 'all',
        loading: false,
        addressLoading: false,
        addressSuggestions: [],
        contacts: [],
        editingId: null,
        notice: '',
        errors: {},
        form: createEmptyContactForm(),
        map: null,
        mapMarkers: [],
        mapError: '',

        init() {
            this.fetchContacts();
            this.loadGoogleMaps();
        },

        loadGoogleMaps() {
            if (!this.googleMapsKey) {
                return;
            }

            if (window.google?.maps) {
                this.initializeMap();
                return;
            }

            if (document.getElementById('google-maps-sdk')) {
                return;
            }

            window.initContactsDashboardMap = () => this.initializeMap();

            const script = document.createElement('script');
            script.id = 'google-maps-sdk';
            script.src = `https://maps.googleapis.com/maps/api/js?key=${this.googleMapsKey}&callback=initContactsDashboardMap`;
            script.async = true;
            script.defer = true;
            script.onerror = () => {
                this.mapError = 'Não foi possível carregar o Google Maps.';
            };
            document.head.appendChild(script);
        },

        initializeMap() {
            if (this.map || !window.google?.maps) {
                return;
            }

            const container = document.getElementById('contacts-map');

            if (!container) {
                this.mapError = 'Não foi possível inicializar o mapa.';
                return;
            }

            this.mapError = '';
            this.map = new google.maps.Map(container, {
                center: { lat: -15.7801, lng: -47.9292 },
                zoom: 4,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            this.updateMapMarkers();
        },

        updateMapMarkers() {
            if (!this.map) {
                return;
            }

            this.mapMarkers.forEach((marker) => marker.setMap(null));
            this.mapMarkers = [];

            const bounds = new google.maps.LatLngBounds();
            let hasMarkers = false;

            const contacts = this.displayedContacts();

            contacts.forEach((contact) => {
                const lat = Number(contact.coordinates?.latitude);
                const lng = Number(contact.coordinates?.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }

                hasMarkers = true;

                const marker = new google.maps.Marker({
                    position: { lat, lng },
                    map: this.map,
                    title: contact.name,
                });

                this.mapMarkers.push(marker);
                bounds.extend(marker.getPosition());
            });

            if (hasMarkers) {
                this.map.fitBounds(bounds);

                if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
                    this.map.setZoom(10);
                }
            }
        },

        resolveContractType(contact) {
            const name = (contact.name ?? '').toLowerCase();

            if (name.includes('beats')) {
                return 'beats';
            }

            if (name.includes('maroto')) {
                return 'maroto';
            }

            if (name.includes('ag4')) {
                return 'ag4';
            }

            return 'hudsoft';
        },

        prepareContact(contact) {
            const contractType = contact.contract_type ?? this.resolveContractType(contact);

            return {
                ...contact,
                contract_type: contractType,
                contract_label: contractLabels[contractType] ?? contractLabels.hudsoft,
                subtitle: (contact.complement || 'Sem delimitações').trim(),
            };
        },

        displayedContacts() {
            if (this.contractType === 'all') {
                return this.contacts;
            }

            return this.contacts.filter((contact) => contact.contract_type === this.contractType);
        },

        clearSearch() {
            if (!this.search) {
                return;
            }

            this.search = '';
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
                const normalized = (payload.data ?? []).map((contact) => this.prepareContact(contact));
                this.contacts = normalized;
                this.updateMapMarkers();
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
