<nav x-data="{ open: false }" class="md-app-nav">
    <div class="md-app-nav__inner">
        <div class="md-app-nav__brand">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white/70 border border-white/40 shadow-sm flex items-center justify-center">
                    <x-application-logo class="w-8 h-8 text-indigo-600" />
                </div>
                <div>
                    <span class="md-app-nav__brand-name">{{ config('app.name', 'Laravel') }}</span>
                    <div class="text-xs uppercase tracking-[0.4em] text-slate-400">Material</div>
                </div>
            </a>
        </div>

        <div class="md-app-nav__links">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-nav-link>
        </div>

        <div class="md-app-nav__actions">
            <x-dropdown align="right" width="48" contentClasses="py-1 bg-white/80 backdrop-blur rounded-2xl border border-white/40 shadow-lg">
                <x-slot name="trigger">
                    <button class="md-app-nav__account-button">
                        <span>{{ Auth::user()->name }}</span>
                        <span class="material-symbols-outlined">expand_more</span>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')" class="md-dropdown-link">
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <x-dropdown-link :href="route('settings.google_maps.edit')" class="md-dropdown-link">
                        {{ __('Configuração') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link
                            :href="route('logout')"
                            class="md-dropdown-link md-dropdown-link--danger"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                        >
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <button
            @click="open = ! open"
            type="button"
            class="md-app-nav__menu-toggle"
            :aria-expanded="open"
            aria-label="{{ __('Toggle navigation') }}"
        >
            <span class="md-app-nav__menu-icon material-symbols-outlined">menu</span>
        </button>
    </div>

    <div :class="{'md-app-nav__mobile-panel--open': open}" class="md-app-nav__mobile-panel">
        <div class="flex flex-col gap-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.google_maps.edit')" :active="request()->routeIs('settings.google_maps.edit')">
                {{ __('Configuração') }}
            </x-responsive-nav-link>
        </div>

        <div class="md-app-nav__mobile-profile">
            <div class="md-app-nav__mobile-name">{{ Auth::user()->name }}</div>
            <div class="md-app-nav__mobile-email">{{ Auth::user()->email }}</div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <x-responsive-nav-link
                    :href="route('logout')"
                    class="md-dropdown-link md-dropdown-link--danger"
                    onclick="event.preventDefault(); this.closest('form').submit();"
                >
                    {{ __('Log Out') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
</nav>
