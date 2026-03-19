<nav id="nav-container" class="bg-white border-b border-gray-200 fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center text-lg font-bold text-black">
                    <img
                        src="{{ asset('logo.svg') }}"
                        alt="{{ __('Recipes logo') }}"
                        class="h-9 w-9 object-contain mr-2"
                        loading="lazy"
                    >
                    <span>Recipes</span>
                </a>
            </div>

            <!-- Desktop Navigation Links - Wave Style Horizontal -->
            <div class="hidden md:flex md:items-center md:space-x-1 bg-gray-100 rounded-lg px-2 py-1">
                <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                    {{ __('Home') }}
                </x-nav-link>
                
                <!-- Cuisines Dropdown (Public) -->
                <x-dropdown align="left" width="64">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium {{ request()->routeIs('cuisines.*') ? 'text-black bg-white shadow-sm font-semibold' : 'text-gray-600 hover:text-gray-900' }} focus:outline-none transition duration-150 ease-in-out">
                            {{ __('Cuisines') }}
                            <span class="ml-1 text-xs">▼</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-200">
                            <a href="{{ route('cuisines.index') }}" class="text-sm font-semibold text-black hover:underline">
                                View All Cuisines →
                            </a>
                        </div>
                        <div class="max-h-80 overflow-y-auto py-1">
                            @php
                                $cuisines = app(\App\Services\RecipeService::class)->getCuisinesWithFlags();
                            @endphp
                            @foreach($cuisines as $cuisine)
                                @if($cuisine['code'])
                                    <a href="{{ route('cuisines.show', $cuisine['name']) }}" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->is('cuisines/' . $cuisine['name']) ? 'bg-gray-100 font-medium' : '' }}">
                                        <img 
                                            src="https://flagcdn.com/w20/{{ $cuisine['code'] }}.png"
                                            width="20"
                                            height="15"
                                            alt="{{ $cuisine['name'] }}"
                                            class="mr-2 rounded-sm"
                                            loading="lazy"
                                        >
                                        {{ $cuisine['name'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </x-slot>
                </x-dropdown>

                @auth
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('ingredients.index')" :active="request()->routeIs('ingredients.*')">
                        {{ __('Ingredients') }}
                    </x-nav-link>
                    <x-nav-link :href="route('allergies.index')" :active="request()->routeIs('allergies.*')">
                        {{ __('Allergies') }}
                    </x-nav-link>
                    <x-nav-link :href="route('my-recipes.index')" :active="request()->routeIs('my-recipes.*')">
                        {{ __('My Recipes') }}
                    </x-nav-link>
                    <x-nav-link :href="route('favorites.index')" :active="request()->routeIs('favorites.*')">
                        {{ __('Favorites') }}
                    </x-nav-link>
                @endauth
            </div>

            <!-- Right Side Actions -->
            <div class="hidden md:flex md:items-center md:space-x-3">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-full hover:bg-gray-200 focus:outline-none transition">
                                {{ Auth::user()->name }}
                                <span class="ml-1.5 text-xs">▼</span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-black px-3 py-1.5 rounded-full hover:bg-gray-100 transition">
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary rounded-full px-4 py-1.5">
                        {{ __('Register') }}
                    </a>
                @endauth
            </div>

        </div>
    </div>
</nav>
