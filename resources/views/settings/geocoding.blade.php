<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header">
            <div>
                <p class="dashboard-kicker">Configurações</p>
                <h2>Geocodificação</h2>
            </div>
        </div>
    </x-slot>

    <section class="settings-page">
        <div class="settings-card">
            <p class="settings-card-description">Escolha a fonte das coordenadas e mantenha os endpoints atualizados para o painel.</p>

            @if (session('status'))
                <p class="settings-status settings-status--success">
                    {{ session('status') }}
                </p>
            @endif

            <form method="POST" action="{{ route('settings.google_maps.update') }}">
                @csrf
                @method('PATCH')

                <div class="settings-section">
                    <h3>Fonte de geocodificação</h3>
                    <p>Defina se os endereços devem ser consultados pelo Google Maps ou pelo OpenStreetMap/Nominatim.</p>

                    <label class="settings-radio">
                        <input
                            type="radio"
                            name="geocoding_provider"
                            value="google"
                            @if (old('geocoding_provider', $geocodingProvider) === 'google') checked @endif
                        >
                        <span>Google Maps</span>
                    </label>

                    <label class="settings-radio">
                        <input
                            type="radio"
                            name="geocoding_provider"
                            value="openstreet"
                            @if (old('geocoding_provider', $geocodingProvider) === 'openstreet') checked @endif
                        >
                        <span>OpenStreetMap (via Nominatim)</span>
                    </label>

                    @error('geocoding_provider')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="settings-section">
                    <h3>Chave do Google Maps</h3>
                    <p>Informe a chave para carregar o mapa e realizar o fallback caso o OpenStreetMap não seja suficiente.</p>

                    <input
                        class="settings-input"
                        id="google_maps_key"
                        name="google_maps_key"
                        type="text"
                        placeholder="pk.********************************"
                        value="{{ old('google_maps_key', $googleMapsKey) ?? '' }}"
                    >

                    @error('google_maps_key')
                        <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-secondary">
                    Salvar configurações
                </button>
            </form>
        </div>
    </section>
</x-app-layout>
