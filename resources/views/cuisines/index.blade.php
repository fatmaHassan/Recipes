<x-app-layout :title="__('Explore Cuisines')">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-black mb-4">Explore World Cuisines</h1>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Discover delicious recipes from around the world. Click on a country to explore its traditional dishes.
                </p>
            </div>

            <!-- Cuisine Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($cuisines as $cuisine)
                    @if($cuisine['code'])
                        <a href="{{ route('cuisines.show', $cuisine['name']) }}" 
                           class="card card-hover p-4 text-center group transition-all duration-200 hover:scale-105">
                            <div class="mb-3">
                                <img 
                                    src="https://flagcdn.com/w80/{{ $cuisine['code'] }}.png"
                                    srcset="https://flagcdn.com/w160/{{ $cuisine['code'] }}.png 2x"
                                    width="80"
                                    height="60"
                                    alt="{{ $cuisine['name'] }} flag"
                                    class="mx-auto rounded shadow-sm"
                                    loading="lazy"
                                >
                            </div>
                            <h3 class="font-semibold text-gray-800 group-hover:text-black">
                                {{ $cuisine['name'] }}
                            </h3>
                        </a>
                    @else
                        <a href="{{ route('cuisines.show', $cuisine['name']) }}" 
                           class="card card-hover p-4 text-center group transition-all duration-200 hover:scale-105">
                            <div class="mb-3 w-20 h-[60px] mx-auto bg-gray-100 rounded flex items-center justify-center">
                                <span class="text-2xl">🍽️</span>
                            </div>
                            <h3 class="font-semibold text-gray-800 group-hover:text-black">
                                {{ $cuisine['name'] }}
                            </h3>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
