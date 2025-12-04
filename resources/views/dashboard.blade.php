<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header">
            <div>
                <p class="dashboard-kicker">Registro de contatos</p>
                <h2>Visão geral dos contatos</h2>
            </div>
            <div class="dashboard-header__actions">
                @if (config('features.mock_contacts'))
                    <x-secondary-button
                        type="button"
                        x-data=""
                        x-on:click.prevent="window.dispatchEvent(new CustomEvent('create-mock-contact'))"
                    >
                        {{ __('Cadastrar contato simulado') }}
                    </x-secondary-button>
                @endif
                <x-primary-button
                    type="button"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'contact-registration')"
                >
                    {{ __('Cadastrar contato') }}
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    <section
        x-data="contactsManager()"
        x-init="init()"
        x-cloak
        x-on:create-mock-contact.window="createMockContact()"
        class="dashboard-grid"
    >
        <aside class="contacts-panel">
            <div class="contacts-panel__header">
                <div class="form-group">
                    <label for="search-query">Buscar</label>
                    <div class="search-field">
                        <input
                            id="search-query"
                            type="text"
                            x-model="search"
                            x-on:input.debounce.500="goToPage(1)"
                            placeholder="Digite Nome ou CPF"
                        >
                    </div>
                </div>
            </div>

            <div class="contacts-panel__body">
                <p class="notice" x-show="notice" x-text="notice"></p>
                <div class="contacts-list-wrapper">
                    <div class="contacts-list" data-simplebar>
                    <template x-if="loading">
                        <div class="list-empty">Carregando contatos...</div>
                    </template>
                    <template x-if="!loading && displayedContacts().length === 0">
                        <div class="list-empty">Nenhum contato encontrado.</div>
                    </template>
                    <template x-for="contact in displayedContacts()" :key="contact.id">
                        <article
                            class="contact-entry"
                            tabindex="0"
                            role="button"
                            x-on:click="focusContact(contact)"
                            x-on:keydown.enter.prevent="focusContact(contact)"
                        >
                            <div class="contact-content">
                                <div class="contact-info">
                                    <h3 x-text="contact.name"></h3>
                                    <p x-text="contact.subtitle"></p>
                                </div>
                                <div class="contact-meta">
                                    <span class="contact-location" x-text="contact.address.city + ' - ' + contact.address.state"></span>
                                </div>
                            </div>
                            <div class="contact-actions">
                                <button
                                    type="button"
                                    class="contact-action"
                                    x-on:click.stop.prevent="openEditModal(contact)"
                                >
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <button
                                    type="button"
                                    class="contact-action contact-action--ghost"
                                    x-on:click.stop.prevent="deleteContact(contact.id)"
                                >
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                        </article>
                    </template>
                    </div>
                    <div class="contacts-pagination" x-show="totalPages > 1">
                        <button
                            type="button"
                            class="pagination-btn"
                            :disabled="currentPage <= 1"
                            x-on:click.prevent="goToPage(currentPage - 1)"
                        >
                            Anterior
                        </button>
                        <p>
                            Página <strong x-text="currentPage"></strong>
                            de <span x-text="totalPages"></span>
                        </p>
                        <button
                            type="button"
                            class="pagination-btn"
                            :disabled="currentPage >= totalPages"
                            x-on:click.prevent="goToPage(currentPage + 1)"
                        >
                            Próxima
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <div class="contacts-map">
            @if ($needsGoogleMapsSetup)
                <div class="dashboard-map-notice">
                    <div class="dashboard-map-notice__content">
                        <p class="dashboard-map-notice__eyebrow">Atenção</p>
                        <h3>Chave do Google Maps não configurada</h3>
                        <p>
                            Os mapas do painel precisam de uma chave ativa do Google para exibir os contatos.
                            Configure o acesso nas configurações e volte para ver o mapa completo.
                        </p>
                    </div>
                    <div class="dashboard-map-notice__actions">
                        <a
                            class="btn-secondary"
                            href="{{ route('settings.google_maps.edit') }}"
                        >
                            Configurar Google Maps
                        </a>
                    </div>
                </div>
            @endif
            <div class="map-toolbar">
                <div class="map-toolbar__group">
                    <span
                        class="map-toolbar__tab"
                        :class="{ 'map-toolbar__tab--active': mapView === 'roadmap' }"
                        role="button"
                        tabindex="0"
                        :aria-pressed="mapView === 'roadmap'"
                        x-on:click.prevent="setMapView('roadmap')"
                        x-on:keydown.enter.prevent="setMapView('roadmap')"
                    >
                        Mapa
                    </span>
                    <span
                        class="map-toolbar__tab"
                        :class="{ 'map-toolbar__tab--active': mapView === 'satellite' }"
                        role="button"
                        tabindex="0"
                        :aria-pressed="mapView === 'satellite'"
                        x-on:click.prevent="setMapView('satellite')"
                        x-on:keydown.enter.prevent="setMapView('satellite')"
                    >
                        Satélite
                    </span>
                </div>
                <button type="button" class="btn-icon btn-icon--ghost" aria-hidden="true">
                    <span class="material-symbols-outlined">fullscreen</span>
                </button>
            </div>
            <div class="map-frame">
                <div id="contacts-map" class="map-canvas"></div>
                <p class="map-placeholder" x-show="!googleMapsKey">
                    Chave do Google Maps não configurada.
                </p>
                <p class="map-placeholder" x-show="mapError && googleMapsKey" x-text="mapError"></p>
            </div>
        </div>
        <x-dashboard-modal />
    </section>
</x-app-layout>
