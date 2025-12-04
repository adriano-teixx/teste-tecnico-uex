import './bootstrap';

import Alpine from 'alpinejs';

import AlertService from './services/AlertService';
import FormatterService from './services/FormatterService';
import ContactApiService from './services/ContactApiService';
import AddressService from './services/AddressService';

window.Alpine = Alpine;

window.contactsManager = function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const googleMapsKey = document.querySelector('meta[name="google-maps-api-key"]')?.content ?? '';
    const alertService = new AlertService();
    const formatter = new FormatterService();
    const contactApi = new ContactApiService(csrfToken);
    const addressService = new AddressService(csrfToken);

    return {
        csrfToken,
        googleMapsKey,
        search: '',
        loading: false,
        addressLoading: false,
        addressSuggestions: [],
        contacts: [],
        editingId: null,
        errors: {},
        form: formatter.createEmptyForm(),
        map: null,
        activeMarker: null,
        activeContactId: null,
        mapError: '',
        mapView: 'roadmap',
        selectedCoordinates: null,
        geocodeLoading: false,
        mockLoading: false,
        perPage: 10,
        currentPage: 1,
        totalPages: 1,
        totalContacts: 0,
        alertService,
        formatter,
        contactApi,
        addressService,

        normalizeCpf() {
            this.form.cpf = this.formatter.formatCpf(this.form.cpf);
        },

        handleCpfInput(event) {
            const formatted = this.formatter.formatCpf(event.target.value);
            event.target.value = formatted;
            this.form.cpf = formatted;
        },

        isCpfValid(value) {
            return this.formatter.validateCpf(value);
        },

        normalizePhone() {
            this.form.phone = this.formatter.formatPhone(this.form.phone);
        },

        handlePhoneInput(event) {
            const formatted = this.formatter.formatPhone(event.target.value);
            event.target.value = formatted;
            this.form.phone = formatted;
        },

        isPhoneValid(value) {
            return this.formatter.validatePhone(value);
        },

        normalizeCep() {
            this.form.cep = this.formatter.formatCep(this.form.cep);
        },

        handleCepInput(event) {
            const formatted = this.formatter.formatCep(event.target.value);
            event.target.value = formatted;
            this.form.cep = formatted;
        },

        async fetchAddressCoordinates() {
            if (!this.form.street || !this.form.city || !this.form.state) {
                this.selectedCoordinates = null;
                return;
            }

            this.geocodeLoading = true;
            this.selectedCoordinates = null;

            try {
                const coordinates = await this.addressService.geocode({
                    street: this.form.street,
                    city: this.form.city,
                    state: this.form.state,
                    district: this.form.district,
                    number: this.form.number,
                });

                this.selectedCoordinates = coordinates;

                if (coordinates) {
                    this.alertService.success('Coordenadas aproximadas carregadas.');
                } else {
                    this.alertService.info('Nenhuma coordenada encontrada.');
                }
            } catch (error) {
                this.alertService.error(error.message);
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
                mapTypeId: this.mapView,
            });

        },

        setMapView(view) {
            if (
                this.mapView === view ||
                !['roadmap', 'satellite'].includes(view)
            ) {
                return;
            }

            this.mapView = view;

            if (this.map) {
                this.map.setMapTypeId(view);
            }
        },

        focusContact(contact) {
            if (!this.map) {
                this.alertService.error('O mapa ainda não está disponível.');
                return;
            }

            const lat = Number(contact.coordinates?.latitude);
            const lng = Number(contact.coordinates?.longitude);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                this.alertService.info('Coordenadas deste contato não estão disponíveis.');
                return;
            }

            this.clearActiveMarker();

            const position = { lat, lng };

            this.activeMarker = new google.maps.Marker({
                position,
                map: this.map,
                title: contact.name,
            });

            this.map.panTo(position);
            this.map.setZoom(Math.max(this.map.getZoom(), 12));
            this.activeContactId = contact.id;
        },

        clearActiveMarker() {
            if (!this.activeMarker) {
                return;
            }

            this.activeMarker.setMap(null);
            this.activeMarker = null;
            this.activeContactId = null;
        },

        prepareContact(contact) {
            return {
                ...contact,
                subtitle: (contact.complement ?? '').trim(),
            };
        },

        displayedContacts() {
            return this.contacts;
        },

        clearSearch() {
            if (!this.search) {
                return;
            }

            this.search = '';
            this.goToPage(1);
        },

        goToPage(page) {
            const normalizedCurrent = Math.max(1, this.currentPage);
            const upperBound = Math.max(this.totalPages, normalizedCurrent);
            const targetPage = Math.min(Math.max(page, 1), upperBound);

            this.fetchContacts(targetPage);
        },

        async fetchContacts(page = this.currentPage) {
            this.loading = true;
            this.clearActiveMarker();

            this.currentPage = page;

            try {
                const payload = await this.contactApi.list({
                    search: this.search,
                    page: this.currentPage,
                    perPage: this.perPage,
                });
                const normalized = (payload.data ?? []).map((contact) => this.prepareContact(contact));
                this.contacts = normalized;
                const meta = payload.meta ?? {};
                this.totalPages = Math.max(meta.last_page ?? 1, 1);
                this.totalContacts = meta.total ?? normalized.length;
            } catch (error) {
                this.alertService.error(error.message);
            } finally {
                this.loading = false;
            }
        },

        async createMockContact() {
            if (this.mockLoading) {
                return;
            }

            this.mockLoading = true;

            try {
                await this.contactApi.createMock();
                this.alertService.success('Contato mock cadastrado.');
                await this.fetchContacts();
            } catch (error) {
                this.alertService.error(error.message);
            } finally {
                this.mockLoading = false;
            }
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            this.normalizeCpf();
            this.normalizePhone();
            this.normalizeCep();

            if (!this.isCpfValid(this.form.cpf)) {
                this.errors = { cpf: ['CPF inválido.'] };
                this.alertService.error('CPF inválido.');
                this.loading = false;
                return;
            }

            if (this.form.phone && !this.isPhoneValid(this.form.phone)) {
                this.errors = { phone: ['Telefone inválido.'] };
                this.alertService.error('Telefone inválido.');
                this.loading = false;
                return;
            }

            try {
                const response = await this.contactApi.save(this.form, this.editingId);

                if (!response.ok) {
                    this.errors = response.payload.errors ?? {};
                    this.alertService.error(response.payload.message ?? 'Não foi possível salvar o contato.');
                    return;
                }

                const message = this.editingId ? 'Contato atualizado.' : 'Contato cadastrado.';
                this.alertService.success(message);
                this.resetForm();
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'contact-registration' }));
                this.fetchContacts();
            } catch (error) {
                this.alertService.error(error.message);
            } finally {
                this.loading = false;
            }
        },

        openEditModal(contact) {
            this.startEditing(contact);
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'contact-registration' }));
        },

        startEditing(contact) {
            this.editingId = contact.id;
            this.form = {
                name: contact.name,
                cpf: contact.cpf,
                phone: this.formatter.formatPhone(contact.phone ?? ''),
                cep: this.formatter.formatCep(contact.address.cep ?? ''),
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
            this.form = this.formatter.createEmptyForm();
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
                this.addressSuggestions = [];
                this.alertService.error('Informe estado, cidade e rua para buscar as sugestões.');
                return;
            }

            this.addressLoading = true;
            this.addressSuggestions = [];
            this.selectedCoordinates = null;

            try {
                const suggestions = await this.addressService.search({
                    state: this.form.state,
                    city: this.form.city,
                    street: this.form.street,
                });

                this.addressSuggestions = suggestions;
                if (suggestions.length) {
                    this.alertService.success('Sugestões de endereço carregadas.');
                } else {
                    this.alertService.info('Nenhum endereço encontrado.');
                }
            } catch (error) {
                this.alertService.error(error.message);
            } finally {
                this.addressLoading = false;
            }
        },

        async lookupAddressByCep(cepDigits = null) {
            const cep = cepDigits ?? this.form.cep.replace(/\D/g, '');

            if (cep.length !== 8) {
                this.alertService.error('Informe um CEP válido.');
                return;
            }

            this.addressLoading = true;
            this.addressSuggestions = [];
            this.selectedCoordinates = null;

            try {
                const address = await this.addressService.findByCep(cep);

                if (!address) {
                    this.alertService.error('CEP não encontrado.');
                    return;
                }

                this.form.cep = this.formatter.formatCep(address.cep ?? '');
                this.form.street = address.street ?? this.form.street;
                this.form.district = address.district ?? this.form.district;
                this.form.city = address.city ?? this.form.city;
                this.form.state = (address.state ?? this.form.state).toUpperCase();

                this.alertService.success('Endereço preenchido automaticamente.');
                this.fetchAddressCoordinates();
            } catch (error) {
                this.alertService.error(error.message);
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
                await this.contactApi.delete(contactId);
                this.alertService.success('Contato removido.');
                this.fetchContacts();
            } catch (error) {
                this.alertService.error(error.message);
            }
        },
    };
};

Alpine.start();
