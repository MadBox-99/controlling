<x-layouts.app>
    <!-- Header -->
    <header class="fixed top-0 inset-x-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="{{ config('app.name') }}" class="h-12">
                </div>
                <nav class="hidden lg:flex lg:items-center lg:gap-2">
                    <a href="#about" class="px-4 py-2 text-gray-700 hover:text-purple-600 transition-all">{{ __('About') }}</a>
                    <a href="#features" class="px-4 py-2 text-gray-700 hover:text-purple-600 transition-all">{{ __('Features') }}</a>
                    <a href="#contact" class="px-4 py-2 text-gray-700 hover:text-purple-600 transition-all">{{ __('Contact') }}</a>
                    <a href="/admin" class="px-6 py-2 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-all ml-2">{{ __('Admin Panel') }}</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="py-24 px-6 min-h-screen flex items-center justify-center bg-gray-900 text-white">
        <div class="container mx-auto max-w-4xl text-center">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-black mb-8 leading-tight">
                {{ __('Business Intelligence & Decision Support System') }}
            </h1>
            <p class="text-xl md:text-2xl font-light mb-12 text-gray-300">
                {{ __('Transform your data into actionable insights') }}
            </p>
            <div class="flex gap-4 justify-center">
                <a href="#contact" class="inline-flex items-center px-8 py-4 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-all shadow-xl hover:shadow-2xl">
                    {{ __('Get Started') }}
                </a>
                <a href="#features" class="inline-flex items-center px-8 py-4 bg-white text-gray-900 font-semibold rounded-lg hover:shadow-2xl transition-all">
                    {{ __('Learn More') }}
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 px-6 bg-white">
        <div class="container mx-auto max-w-4xl">
            <h2 class="text-4xl md:text-5xl font-extrabold mb-8 text-center">{{ __('About Us') }}</h2>
            <p class="text-lg font-light text-gray-600 text-center leading-relaxed">
                {{ __('We provide comprehensive business intelligence solutions that help organizations make data-driven decisions. Our platform integrates seamlessly with your existing systems to deliver real-time insights and analytics.') }}
            </p>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-6 bg-gray-50">
        <div class="container mx-auto max-w-7xl">
            <h2 class="text-4xl md:text-5xl font-extrabold mb-16 text-center">{{ __('Features') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-2xl transition-all">
                    <h3 class="text-2xl font-bold mb-4 text-purple-600">{{ __('Real-time Analytics') }}</h3>
                    <p class="text-gray-600 font-light">{{ __('Monitor your key performance indicators in real-time with intuitive dashboards and visualizations.') }}</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-2xl transition-all">
                    <h3 class="text-2xl font-bold mb-4 text-purple-600">{{ __('Data Integration') }}</h3>
                    <p class="text-gray-600 font-light">{{ __('Seamlessly connect with multiple data sources including Google Analytics, databases, and APIs.') }}</p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-2xl transition-all">
                    <h3 class="text-2xl font-bold mb-4 text-purple-600">{{ __('Custom Reports') }}</h3>
                    <p class="text-gray-600 font-light">{{ __('Create custom reports tailored to your business needs with our flexible reporting engine.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 px-6 bg-white">
        <div class="container mx-auto max-w-2xl">
            <h2 class="text-4xl md:text-5xl font-extrabold mb-8 text-center">{{ __('Contact Us') }}</h2>
            <p class="text-lg font-light text-gray-600 text-center mb-12">
                {{ __('Get in touch with us to learn more about how we can help your business.') }}
            </p>

            <form action="#" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Name') }}</label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Company') }}</label>
                    <input type="text" id="company" name="company" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Message') }}</label>
                    <textarea id="message" name="message" rows="5" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <button type="submit" class="w-full px-8 py-4 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-all shadow-lg hover:shadow-xl">
                    {{ __('Send Message') }}
                </button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 px-6 bg-gray-900 text-white">
        <div class="container mx-auto max-w-7xl text-center">
            <p class="text-gray-400 font-light">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
        </div>
    </footer>
</x-layouts.app>
