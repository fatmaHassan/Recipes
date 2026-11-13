<x-app-layout :title="__('About')">
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-black mb-4">About</h1>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    The story behind this site and the people in it.
                </p>
            </div>

            <!-- About me -->
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-black mb-3">Hi, I'm Fatmah Hassan</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    I'm the developer and cook behind this site. I love discovering food from every corner of the
                    world, and I built this site to turn that curiosity into something useful — a simple place to
                    find meal inspiration, every day.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Feel free to
                    <a href="https://www.linkedin.com/in/fatmahasan/" target="_blank" rel="noopener"
                       class="font-medium text-black underline decoration-gray-300 hover:decoration-black transition">
                        connect with me on LinkedIn
                    </a>.
                </p>
            </section>

            <!-- About the website -->
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-black mb-3">About the website</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    This site is a source of inspiration for recipes from all over the world. Browse the
                    <a href="{{ route('cuisines.index') }}" class="font-medium text-black underline decoration-gray-300 hover:decoration-black transition">cuisines</a>,
                    explore new dishes, and get ideas for what to cook next.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Already have ingredients at home?
                    @auth
                        <a href="{{ route('ingredients.index') }}" class="font-medium text-black underline decoration-gray-300 hover:decoration-black transition">
                            Add what's in your pantry
                        </a> on the Ingredients page and find recipes you can make right now.
                    @else
                        add what's in your pantry on the Ingredients page and find recipes you can make right now.
                    @endauth
                </p>
            </section>

            <!-- TheMealDB & your own recipes -->
            <section>
                <h2 class="text-2xl font-bold text-black mb-3">The recipes</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    The recipe database on this site comes from
                    <a href="https://www.themealdb.com" target="_blank" rel="noopener"
                       class="font-medium text-black underline decoration-gray-300 hover:decoration-black transition">
                        TheMealDB
                    </a>
                    — I took their collection of dishes from around the world as the data source and built this
                    site around it.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    The collection doesn't have to stop there:
                    @auth
                        <a href="{{ route('custom-recipes.index') }}" class="font-medium text-black underline decoration-gray-300 hover:decoration-black transition">
                            add your own recipes
                        </a> on the Custom Recipes page and extend the site with dishes of your own.
                    @else
                        you can add your own recipes on the Custom Recipes page and extend the site with dishes of your own.
                    @endauth
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
