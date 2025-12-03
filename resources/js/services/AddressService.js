export default class AddressService {
    constructor(csrfToken) {
        this.csrfToken = csrfToken;
    }

    async search({ state, city, street }) {
        const url = new URL('/addresses', window.location.origin);
        url.searchParams.set('uf', state.toUpperCase());
        url.searchParams.set('city', city);
        url.searchParams.set('street', street);

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
        return payload.data ?? [];
    }

    async geocode(details) {
        const url = new URL('/addresses/geocode', window.location.origin);
        url.searchParams.set('street', details.street);
        url.searchParams.set('city', details.city);
        url.searchParams.set('state', details.state);

        if (details.district) {
            url.searchParams.set('district', details.district);
        }

        if (details.number) {
            url.searchParams.set('number', details.number);
        }

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

        return payload.data ?? null;
    }

    async findByCep(cep) {
        const url = new URL('/addresses/cep', window.location.origin);
        url.searchParams.set('cep', cep);

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

        return payload.data ?? null;
    }
}
