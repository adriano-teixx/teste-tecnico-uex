<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header">
            <div>
                <p class="dashboard-kicker">Registro de contatos</p>
                <h2>Visão geral dos pontos atendidos</h2>
            </div>
            <div class="dashboard-header__actions">
                <span class="dashboard-tag">Últimos dados sincronizados</span>
                @if (config('features.mock_contacts'))
                    <x-secondary-button
                        type="button"
                        x-data=""
                        x-on:click.prevent="window.dispatchEvent(new CustomEvent('create-mock-contact'))"
                    >
                        {{ __('Cadastrar contato mock') }}
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
                    <label for="contract-type">Tipo de Contrato</label>
                    <div class="select-wrapper">
                        <select id="contract-type" x-model="contractType" @change="updateMapMarkers()">
                            <option value="all">Todos</option>
                            <option value="hudsoft">HudSoft</option>
                            <option value="ag4">AG4</option>
                            <option value="beats">Beats</option>
                            <option value="maroto">Maroto Bagari</option>
                        </select>
                        <span class="material-symbols-outlined select-icon">expand_more</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="search-query">Buscar</label>
                    <div class="search-field">
                        <input
                            id="search-query"
                            type="text"
                            x-model="search"
                            @keyup.enter.prevent="goToPage(1)"
                            placeholder="Conta, Unidade"
                        >
                        <button type="button" class="btn-icon" @click="goToPage(1)" aria-label="Buscar">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                        <button
                            type="button"
                            class="btn-icon btn-icon--ghost"
                            @click="clearSearch()"
                            aria-label="Limpar busca"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
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
                            <div class="contact-avatar">
                                <span x-text="contact.name.charAt(0)"></span>
                            </div>
                            <div class="contact-info">
                                <h3 x-text="contact.name"></h3>
                                <p x-text="contact.subtitle"></p>
                            </div>
                            <div class="contact-meta">
                                <span class="contact-badge" x-text="contact.contract_label"></span>
                                <span class="contact-location" x-text="contact.address.city + ' - ' + contact.address.state"></span>
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
            <div class="map-toolbar">
                <div class="map-toolbar__group">
                    <span class="map-toolbar__tab map-toolbar__tab--active">Map</span>
                    <span class="map-toolbar__tab">Satellite</span>
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
