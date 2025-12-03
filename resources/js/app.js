import './bootstrap';

import Alpine from 'alpinejs';

import {
    createEmptyContactForm,
    formatCpfValue,
    formatPhoneValue,
    formatCepValue,
    validateCpfValue,
    validatePhoneValue,
} from './helpers/contact-form';

window.Alpine = Alpine;

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
        selectedCoordinates: null,
        geocodeLoading: false,

        normalizeCpf() {
            this.form.cpf = formatCpfValue(this.form.cpf);
        },

        handleCpfInput(event) {
            const formatted = formatCpfValue(event.target.value);
            event.target.value = formatted;
            this.form.cpf = formatted;
        },

        isCpfValid(value) {
            return validateCpfValue(value);
        },

        normalizePhone() {
            this.form.phone = formatPhoneValue(this.form.phone);
        },

        handlePhoneInput(event) {
            const formatted = formatPhoneValue(event.target.value);
            event.target.value = formatted;
            this.form.phone = formatted;
        },

        isPhoneValid(value) {
            return validatePhoneValue(value);
        },

        normalizeCep() {
            this.form.cep = formatCepValue(this.form.cep);
        },

        handleCepInput(event) {
            const formatted = formatCepValue(event.target.value);
            event.target.value = formatted;
            this.form.cep = formatted;
        },

        async fetchAddressCoordinates() {
            if (!this.form.street || !this.form.city || !this.form.state) {
                this.selectedCoordinates = null;
                return;
            }

            this.geocodeLoading = true;
            this.notice = '';
            this.selectedCoordinates = null;

            const url = new URL('/addresses/geocode', window.location.origin);
            url.searchParams.set('street', this.form.street);
            url.searchParams.set('city', this.form.city);
            url.searchParams.set('state', this.form.state);

            if (this.form.district) {
                url.searchParams.set('district', this.form.district);
            }

            if (this.form.number) {
                url.searchParams.set('number', this.form.number);
            }

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message ?? 'Não foi possível obter as coordenadas do endereço.');
                }

                this.selectedCoordinates = payload.data ?? null;
                this.notice = this.selectedCoordinates
                    ? 'Coordenadas aproximadas carregadas.'
                    : 'Nenhuma coordenada encontrada.';
            } catch (error) {
                this.notice = error.message;
            } finally {
                this.geocodeLoading = false;
            }
        },

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

            this.normalizeCpf();
            this.normalizePhone();
            this.normalizeCep();

            const method = this.editingId ? 'PUT' : 'POST';
            const url = this.editingId ? `/contacts/${this.editingId}` : '/contacts';

            if (!this.isCpfValid(this.form.cpf)) {
                this.errors = { cpf: ['CPF inválido.'] };
                this.notice = 'CPF inválido.';
                this.loading = false;
                return;
            }

            if (this.form.phone && !this.isPhoneValid(this.form.phone)) {
                this.errors = { phone: ['Telefone inválido.'] };
                this.notice = 'Telefone inválido.';
                this.loading = false;
                return;
            }

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
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'contact-registration' }));
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
                phone: formatPhoneValue(contact.phone ?? ''),
                cep: formatCepValue(contact.address.cep ?? ''),
                street: contact.address.street ?? '',
                number: contact.address.number ?? '',
                complement: contact.address.complement ?? '',
                district: contact.address.district ?? '',
                city: contact.address.city ?? '',
                state: (contact.address.state ?? '').toUpperCase(),
            };
            window.scrollTo({ top: 0, behavior: 'smooth' });
            this.selectedCoordinates =
                contact.coordinates?.latitude && contact.coordinates?.longitude
                    ? {
                          latitude: contact.coordinates.latitude,
                          longitude: contact.coordinates.longitude,
                      }
                    : null;
        },

        cancelEditing() {
            this.resetForm();
        },

        resetForm() {
            this.form = createEmptyContactForm();
            this.editingId = null;
            this.addressSuggestions = [];
            this.errors = {};
            this.selectedCoordinates = null;
            this.geocodeLoading = false;
        },

        async lookupAddress() {
            const cepDigits = this.form.cep.replace(/\D/g, '');

            if (cepDigits.length === 8) {
                await this.lookupAddressByCep(cepDigits);
                return;
            }

            if (!this.form.state || !this.form.city || !this.form.street) {
                this.notice = 'Informe estado, cidade e rua para buscar as sugestões.';
                this.addressSuggestions = [];
                return;
            }

            this.addressLoading = true;
            this.addressSuggestions = [];
            this.selectedCoordinates = null;

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
                const suggestions = payload.data ?? [];
                this.addressSuggestions = suggestions;
                this.notice = suggestions.length ? 'Sugestões de endereço carregadas.' : 'Nenhum endereço encontrado.';
            } catch (error) {
                this.notice = error.message;
            } finally {
                this.addressLoading = false;
            }
        },

        async lookupAddressByCep(cepDigits = null) {
            const cep = cepDigits ?? this.form.cep.replace(/\D/g, '');

            if (cep.length !== 8) {
                this.notice = 'Informe um CEP válido.';
                return;
            }

            this.addressLoading = true;
            this.addressSuggestions = [];
            this.selectedCoordinates = null;
            this.notice = '';

            const url = new URL('/addresses/cep', window.location.origin);
            url.searchParams.set('cep', cep);

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message ?? 'Não foi possível buscar o CEP informado.');
                }

                const address = payload.data ?? null;

                if (!address) {
                    this.notice = 'CEP não encontrado.';
                    return;
                }

                this.form.cep = formatCepValue(address.cep ?? '');
                this.form.street = address.street ?? this.form.street;
                this.form.district = address.district ?? this.form.district;
                this.form.city = address.city ?? this.form.city;
                this.form.state = (address.state ?? this.form.state).toUpperCase();

                this.notice = 'Endereço preenchido automaticamente.';
                this.fetchAddressCoordinates();
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
            this.fetchAddressCoordinates();
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
