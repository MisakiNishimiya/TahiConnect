<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} | Professional Tailoring Management</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F5F1EA; color: #2D2D2D; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Poppins', sans-serif; }
        .bg-primary-custom { background-color: #2F5D50; }
        .text-primary-custom { color: #2F5D50; }
        .border-primary-custom { border-color: #2F5D50; }
        .bg-secondary-custom { background-color: #D6B98C; }
        .text-secondary-custom { color: #D6B98C; }
        .bg-cream { background-color: #F5F1EA; }
        
        .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 10px 25px -5px rgba(47, 93, 80, 0.15); }
        .glass-nav { background: rgba(245, 241, 234, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(47, 93, 80, 0.1); }
        
        /* Alpine.js cloak */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased overflow-x-hidden" x-data="{ mobileMenuOpen: false }">
    
    <!-- 1. Navigation Bar -->
    <nav class="fixed w-full z-50 glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <x-app-logo-icon class="size-8 text-primary-custom" />
                    <span class="font-bold text-2xl text-primary-custom tracking-tight" style="font-family: 'Poppins';">TahiConnect</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#" class="text-zinc-600 hover:text-primary-custom font-medium transition-colors">Home</a>
                    <a href="#services" class="text-zinc-600 hover:text-primary-custom font-medium transition-colors">Services</a>
                    <a href="#how-it-works" class="text-zinc-600 hover:text-primary-custom font-medium transition-colors">How It Works</a>
                    <a href="#features" class="text-zinc-600 hover:text-primary-custom font-medium transition-colors">Features</a>
                    <a href="#about" class="text-zinc-600 hover:text-primary-custom font-medium transition-colors">About</a>
                    <a href="#contact" class="text-zinc-600 hover:text-primary-custom font-medium transition-colors">Contact</a>
                </div>

                <!-- Desktop Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-zinc-700 hover:text-primary-custom font-medium transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-full bg-primary-custom text-white font-bold hover:bg-[#1E3D34] transition-colors shadow-lg shadow-primary-custom/30">Get Started</a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-primary-custom p-2 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-white border-t border-zinc-200 absolute w-full shadow-xl" x-transition>
            <div class="px-4 pt-2 pb-6 space-y-1">
                <a href="#" class="block px-3 py-3 text-base font-medium text-zinc-700 hover:bg-cream rounded-lg">Home</a>
                <a href="#services" class="block px-3 py-3 text-base font-medium text-zinc-700 hover:bg-cream rounded-lg">Services</a>
                <a href="#how-it-works" class="block px-3 py-3 text-base font-medium text-zinc-700 hover:bg-cream rounded-lg">How It Works</a>
                <a href="#features" class="block px-3 py-3 text-base font-medium text-zinc-700 hover:bg-cream rounded-lg">Features</a>
                <div class="border-t border-zinc-100 my-2 pt-2"></div>
                <a href="{{ route('login') }}" class="block px-3 py-3 text-base font-medium text-zinc-700">Login</a>
                <a href="{{ route('register') }}" class="block px-3 py-3 text-base font-medium text-primary-custom">Register as Customer</a>
            </div>
        </div>
    </nav>

    <main class="pt-20">
        <!-- 2. Hero Section -->
        <section class="relative overflow-hidden bg-cream py-16 sm:py-24 lg:py-32">
            <!-- Background Decorative Blobs -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-secondary-custom/20 blur-3xl opacity-60"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[30rem] h-[30rem] rounded-full bg-primary-custom/10 blur-3xl opacity-60"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">
                    
                    <!-- Left Side: Copy -->
                    <div class="lg:col-span-6 text-center lg:text-left">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-zinc-900 leading-tight tracking-tight mb-6">
                            Professional Tailoring, <span class="text-primary-custom">Beautifully Managed</span>
                        </h1>
                        <p class="text-lg sm:text-xl text-zinc-600 mb-8 max-w-2xl mx-auto lg:mx-0">
                            Book appointments, order custom garments, track your orders in real time, and experience AI-powered virtual try-on — all in one place.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-10">
                            <a href="{{ route('register') }}" class="px-8 py-4 rounded-full bg-primary-custom text-white font-bold text-lg hover:bg-[#1E3D34] transition-all shadow-xl shadow-primary-custom/30 text-center">
                                Get Started
                            </a>
                            <a href="{{ route('login') }}" class="px-8 py-4 rounded-full border-2 border-primary-custom text-primary-custom bg-transparent font-bold text-lg hover:bg-primary-custom/5 transition-all text-center">
                                Sign In
                            </a>
                        </div>
                        
                        <!-- Trust Badges -->
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm font-medium text-zinc-500">
                            <div class="flex items-center gap-2">
                                <flux:icon.sparkles class="size-5 text-secondary-custom" />
                                <span>AI Virtual Try-On</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon.map-pin class="size-5 text-secondary-custom" />
                                <span>Real-Time Tracking</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon.lock-closed class="size-5 text-secondary-custom" />
                                <span>Secure Platform</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side: Graphic -->
                    <div class="lg:col-span-6 mt-16 lg:mt-0 relative">
                        <div class="relative w-full aspect-square md:aspect-auto md:h-[600px] flex items-center justify-center">
                            <!-- Abstract layout representing the ecosystem -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary-custom/10 to-secondary-custom/20 rounded-[3rem] transform rotate-3"></div>
                            <div class="absolute inset-0 bg-white rounded-[3rem] shadow-2xl overflow-hidden transform -rotate-1 border border-zinc-100 flex flex-col p-6">
                                <!-- Mockup Top -->
                                <div class="flex justify-between items-center mb-6">
                                    <div class="flex gap-2"><div class="size-3 rounded-full bg-red-400"></div><div class="size-3 rounded-full bg-yellow-400"></div><div class="size-3 rounded-full bg-green-400"></div></div>
                                    <div class="w-1/3 h-4 bg-zinc-100 rounded-full"></div>
                                </div>
                                <!-- Mockup Content -->
                                <div class="grid grid-cols-2 gap-4 h-full">
                                    <div class="bg-zinc-50 rounded-2xl p-4 flex flex-col gap-4">
                                        <div class="h-32 bg-zinc-200 rounded-xl w-full flex items-center justify-center">
                                            <flux:icon.photo class="size-10 text-zinc-400" />
                                        </div>
                                        <div class="h-4 bg-zinc-200 rounded-full w-3/4"></div>
                                        <div class="h-4 bg-zinc-200 rounded-full w-1/2"></div>
                                        <div class="mt-auto h-10 bg-primary-custom rounded-xl w-full flex items-center justify-center">
                                            <div class="h-2 bg-white/50 rounded-full w-1/2"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-4">
                                        <div class="bg-secondary-custom/20 rounded-2xl p-4 flex-1 flex flex-col justify-center items-center">
                                            <flux:icon.chart-bar class="size-12 text-secondary-custom mb-2" />
                                            <div class="h-3 bg-secondary-custom/50 rounded-full w-20"></div>
                                        </div>
                                        <div class="bg-primary-custom/10 rounded-2xl p-4 flex-1 relative overflow-hidden">
                                            <div class="absolute -right-4 -bottom-4">
                                                <flux:icon.sparkles class="size-20 text-primary-custom/20" />
                                            </div>
                                            <div class="h-3 bg-primary-custom/40 rounded-full w-full mb-2"></div>
                                            <div class="h-3 bg-primary-custom/30 rounded-full w-3/4"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Floating UI Elements -->
                            <div class="absolute -left-6 top-1/4 bg-white p-4 rounded-2xl shadow-xl border border-zinc-100 hover-lift animate-pulse-soft hidden sm:block">
                                <div class="flex items-center gap-3">
                                    <div class="bg-green-100 p-2 rounded-full"><flux:icon.check class="size-5 text-green-600" /></div>
                                    <div>
                                        <p class="text-xs text-zinc-500 font-medium">Order Status</p>
                                        <p class="text-sm font-bold text-zinc-800">Ready for Pickup</p>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -right-8 bottom-1/3 bg-white p-4 rounded-2xl shadow-xl border border-zinc-100 hover-lift hidden sm:block">
                                <div class="flex items-center gap-3">
                                    <div class="bg-primary-100 p-2 rounded-full"><flux:icon.scissors class="size-5 text-primary-custom" /></div>
                                    <div>
                                        <p class="text-xs text-zinc-500 font-medium">New Booking</p>
                                        <p class="text-sm font-bold text-zinc-800">Fitting scheduled</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>

        <!-- 3. Business Statistics -->
        <section class="py-12 bg-white border-y border-zinc-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-zinc-100">
                    <div class="text-center px-4">
                        <p class="text-4xl font-extrabold text-primary-custom mb-2">Custom</p>
                        <p class="text-sm font-medium text-zinc-500 uppercase tracking-wider">Made-to-Measure Garments</p>
                    </div>
                    <div class="text-center px-4">
                        <p class="text-4xl font-extrabold text-secondary-custom mb-2">AI</p>
                        <p class="text-sm font-medium text-zinc-500 uppercase tracking-wider">Virtual Try-On</p>
                    </div>
                    <div class="text-center px-4">
                        <p class="text-4xl font-extrabold text-primary-custom mb-2">Real-Time</p>
                        <p class="text-sm font-medium text-zinc-500 uppercase tracking-wider">Order Tracking</p>
                    </div>
                    <div class="text-center px-4">
                        <p class="text-4xl font-extrabold text-secondary-custom mb-2">100%</p>
                        <p class="text-sm font-medium text-zinc-500 uppercase tracking-wider">Data Ownership</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Our Services -->
        <section id="services" class="py-20 bg-cream">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4">Our Tailoring Services</h2>
                    <p class="text-lg text-zinc-600">Quality craftsmanship for every garment, every occasion.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-zinc-100 overflow-hidden hover-lift p-6">
                        <div class="w-14 h-14 rounded-2xl bg-primary-custom/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-primary-custom" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-2">Custom Garments</h3>
                        <p class="text-zinc-600 text-sm">Made-to-measure clothing tailored precisely to your measurements and personal style preferences.</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-zinc-100 overflow-hidden hover-lift p-6">
                        <div class="w-14 h-14 rounded-2xl bg-secondary-custom/20 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-primary-custom" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-2">Ready-to-Wear</h3>
                        <p class="text-zinc-600 text-sm">Browse our collection of pre-made garments available in multiple sizes for immediate purchase.</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-zinc-100 overflow-hidden hover-lift p-6">
                        <div class="w-14 h-14 rounded-2xl bg-primary-custom/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-primary-custom" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-2">Fitting Appointments</h3>
                        <p class="text-zinc-600 text-sm">Book consultations, fittings, and pickup appointments at your convenience through our online scheduler.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. How It Works -->
        <section id="how-it-works" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4">How TahiConnect Works</h2>
                    <p class="text-lg text-zinc-600">A seamless tailoring experience from start to finish.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                    <!-- Connecting Line -->
                    <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-zinc-200 z-0"></div>
                    
                    <!-- Step 1 -->
                    <div class="relative z-10 text-center">
                        <div class="w-24 h-24 mx-auto bg-cream rounded-full border-4 border-white shadow-xl flex items-center justify-center mb-6">
                            <flux:icon.magnifying-glass class="size-10 text-primary-custom" />
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-2">1. Browse Our Catalog</h3>
                        <p class="text-zinc-600">View available garment types, ready-to-wear items, and fabric options.</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="relative z-10 text-center">
                        <div class="w-24 h-24 mx-auto bg-primary-custom rounded-full border-4 border-white shadow-xl flex items-center justify-center mb-6">
                            <flux:icon.calendar class="size-10 text-white" />
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-2">2. Book & Customize</h3>
                        <p class="text-zinc-600">Schedule appointments, submit measurements, and upload design inspirations.</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="relative z-10 text-center">
                        <div class="w-24 h-24 mx-auto bg-secondary-custom rounded-full border-4 border-white shadow-xl flex items-center justify-center mb-6">
                            <flux:icon.truck class="size-10 text-white" />
                        </div>
                        <h3 class="text-xl font-bold text-zinc-900 mb-2">3. Track & Receive</h3>
                        <p class="text-zinc-600">Monitor progress in real-time until your custom garment is completed.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. AI Virtual Try-On Showcase -->
        <section class="py-24 bg-zinc-900 text-white overflow-hidden relative">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-primary-custom via-zinc-900 to-zinc-900"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-custom/20 text-secondary-custom text-sm font-bold mb-6 border border-secondary-custom/30">
                            <flux:icon.sparkles class="size-4" /> New Feature
                        </div>
                        <h2 class="text-3xl md:text-5xl font-bold mb-6">See Your Design Before It Is Made</h2>
                        <p class="text-lg text-zinc-400 mb-8">
                            Take the guesswork out of bespoke tailoring. TahiConnect's AI Virtual Try-On lets you visualize your custom garment on your body before a single cut is made.
                        </p>
                        
                        <ul class="space-y-4 mb-10">
                            <li class="flex items-start gap-3">
                                <div class="mt-1 bg-green-500/20 p-1 rounded-full text-green-400"><flux:icon.check class="size-4" /></div>
                                <span class="text-zinc-300">Visualize garments on your exact body type</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="mt-1 bg-green-500/20 p-1 rounded-full text-green-400"><flux:icon.check class="size-4" /></div>
                                <span class="text-zinc-300">Improve fitting confidence before committing</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="mt-1 bg-green-500/20 p-1 rounded-full text-green-400"><flux:icon.check class="size-4" /></div>
                                <span class="text-zinc-300">Reduce design misunderstandings with tailors</span>
                            </li>
                        </ul>
                        
                        <a href="{{ route('register') }}" class="inline-block px-8 py-4 rounded-full bg-secondary-custom text-zinc-900 font-bold text-lg hover:bg-white transition-all shadow-lg shadow-secondary-custom/20">
                            Try TahiConnect Today
                        </a>
                    </div>
                    
                    <div class="mt-16 lg:mt-0 relative">
                        <!-- AI Visualizer Mockup -->
                        <div class="bg-zinc-800 rounded-3xl p-2 border border-zinc-700 shadow-2xl relative">
                            <div class="absolute -top-4 -right-4 bg-primary-custom text-white text-xs font-bold px-3 py-1 rounded-full animate-bounce">AI Powered</div>
                            
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-zinc-900 rounded-2xl h-48 sm:h-80 relative overflow-hidden group">
                                    <div class="absolute inset-0 flex items-center justify-center text-zinc-600 font-medium">Your Photo</div>
                                </div>
                                <div class="bg-zinc-900 rounded-2xl h-48 sm:h-80 relative overflow-hidden flex items-center justify-center border border-dashed border-zinc-700">
                                    <flux:icon.plus class="size-6 text-zinc-600" />
                                </div>
                                <div class="bg-primary-custom/20 rounded-2xl h-48 sm:h-80 relative overflow-hidden border border-primary-custom/50 flex flex-col justify-end p-4">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <flux:icon.sparkles class="size-10 text-primary-custom opacity-50" />
                                    </div>
                                    <div class="relative z-10 text-center">
                                        <div class="bg-black/50 backdrop-blur-sm text-white text-xs py-1 px-2 rounded">AI Result</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. Features Section -->
        <section id="features" class="py-24 bg-cream">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4">A Complete Tailoring Ecosystem</h2>
                    <p class="text-lg text-zinc-600">Everything you need to manage your tailoring business and serve customers well.</p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-16">
                    <!-- For Customers -->
                    <div>
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-primary-custom flex items-center justify-center text-white">
                                <flux:icon.user class="size-6" />
                            </div>
                            <h3 class="text-2xl font-bold text-zinc-900">For Customers</h3>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200">
                                <div class="mt-1 text-primary-custom"><flux:icon.magnifying-glass class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Browse Our Catalog</h4>
                                    <p class="text-zinc-600 text-sm">View available garment types, ready-to-wear items, and fabric selections.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200">
                                <div class="mt-1 text-primary-custom"><flux:icon.calendar class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Appointment Scheduling</h4>
                                    <p class="text-zinc-600 text-sm">Book fittings and consultations directly through the platform.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200 bg-white shadow-sm border-zinc-200">
                                <div class="mt-1 text-primary-custom"><flux:icon.map-pin class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Real-Time Order Tracking</h4>
                                    <p class="text-zinc-600 text-sm">Know exactly where your garment is in the 7-step production process.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200">
                                <div class="mt-1 text-primary-custom"><flux:icon.credit-card class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Secure Payments</h4>
                                    <p class="text-zinc-600 text-sm">Pay via GCash, Bank Transfer, or Cash with digital receipts.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- For Shop Owners -->
                    <div>
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 rounded-xl bg-secondary-custom flex items-center justify-center text-white">
                                <flux:icon.building-storefront class="size-6" />
                            </div>
                            <h3 class="text-2xl font-bold text-zinc-900">For Shop Owners</h3>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200">
                                <div class="mt-1 text-secondary-custom"><flux:icon.users class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Customer Management</h4>
                                    <p class="text-zinc-600 text-sm">Keep track of your clients and their digital measurement profiles.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200">
                                <div class="mt-1 text-secondary-custom"><flux:icon.clipboard-document-list class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Order Management</h4>
                                    <p class="text-zinc-600 text-sm">Assign tasks to staff and update order statuses easily.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200 bg-white shadow-sm border-zinc-200">
                                <div class="mt-1 text-secondary-custom"><flux:icon.chart-bar class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Analytics Dashboard</h4>
                                    <p class="text-zinc-600 text-sm">Track monthly revenue, popular garments, and staff performance.</p>
                                </div>
                            </div>
                            <div class="flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-zinc-200">
                                <div class="mt-1 text-secondary-custom"><flux:icon.banknotes class="size-6" /></div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 mb-1">Payment Monitoring</h4>
                                    <p class="text-zinc-600 text-sm">Track deposits and pending balances in one place.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. Why Choose Us (Split Section) -->
        <section class="py-24 bg-primary-custom text-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">

                    <div class="relative order-2 lg:order-1 mt-16 lg:mt-0">
                        <!-- Dashboard Mockup -->
                        <div class="bg-zinc-100 rounded-xl p-2 shadow-2xl transform -rotate-2 hover:rotate-0 transition-all duration-500">
                            <div class="bg-white rounded-lg overflow-hidden border border-zinc-200">
                                <div class="h-8 bg-zinc-100 border-b border-zinc-200 flex items-center px-4 gap-2">
                                    <div class="size-2.5 rounded-full bg-red-400"></div><div class="size-2.5 rounded-full bg-yellow-400"></div><div class="size-2.5 rounded-full bg-green-400"></div>
                                </div>
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-6">
                                        <div class="text-zinc-800 font-bold font-heading">Shop Dashboard</div>
                                        <div class="h-6 w-24 bg-primary-custom/10 rounded-full"></div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        <div class="bg-cream p-4 rounded-lg">
                                            <div class="text-xs text-zinc-500 mb-1">Monthly Revenue</div>
                                            <div class="text-xl font-bold text-zinc-900">₱45,200</div>
                                        </div>
                                        <div class="bg-cream p-4 rounded-lg">
                                            <div class="text-xs text-zinc-500 mb-1">Active Orders</div>
                                            <div class="text-xl font-bold text-zinc-900">12</div>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="h-10 bg-zinc-50 rounded border border-zinc-100"></div>
                                        <div class="h-10 bg-zinc-50 rounded border border-zinc-100"></div>
                                        <div class="h-10 bg-zinc-50 rounded border border-zinc-100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="order-1 lg:order-2">
                        <h2 class="text-3xl md:text-4xl font-bold mb-6">Powerful Tools for Your Tailoring Business</h2>
                        <p class="text-lg text-primary-100 mb-8">
                            Modernize your operations with a complete management system — from order intake to delivery — all in one place.
                        </p>

                        <div class="space-y-5 mb-10">
                            <div class="flex items-center gap-4">
                                <div class="bg-white/10 p-2 rounded-lg"><flux:icon.arrow-trending-up class="size-6" /></div>
                                <span class="font-medium">Complete order management dashboard</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="bg-white/10 p-2 rounded-lg"><flux:icon.cpu-chip class="size-6" /></div>
                                <span class="font-medium">Staff assignment and workload tracking</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="bg-white/10 p-2 rounded-lg"><flux:icon.document-minus class="size-6" /></div>
                                <span class="font-medium">Digital measurements — no more lost records</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="bg-white/10 p-2 rounded-lg"><flux:icon.face-smile class="size-6" /></div>
                                <span class="font-medium">Customer satisfaction through real-time tracking</span>
                            </div>
                        </div>

                        <a href="{{ route('register') }}" class="inline-block px-8 py-4 rounded-full bg-white text-primary-custom font-bold text-lg hover:bg-cream transition-all shadow-lg">
                            Get Started Today
                        </a>
                    </div>

                </div>
            </div>
        </section>

        <!-- 9. Customer Testimonials -->
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4">Trusted by Davao's Best</h2>
                    <p class="text-lg text-zinc-600">Hear from customers and shop owners using TahiConnect.</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Review 1 -->
                    <div class="bg-cream p-8 rounded-2xl relative">
                        <flux:icon.chat-bubble-left-ellipsis class="absolute top-8 right-8 size-10 text-primary-custom/10" />
                        <div class="flex gap-1 mb-4 text-yellow-500">
                            <flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" />
                        </div>
                        <p class="text-zinc-700 italic mb-6">"Finding a tailor for my wedding gown was stressful until I used TahiConnect. Being able to track the progress on my phone gave me so much peace of mind."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="size-12 bg-zinc-300 rounded-full"></div>
                            <div>
                                <h4 class="font-bold text-zinc-900">Sofia Ramirez</h4>
                                <p class="text-sm text-zinc-500">Customer</p>
                            </div>
                        </div>
                    </div>
                    <!-- Review 2 -->
                    <div class="bg-cream p-8 rounded-2xl relative">
                        <flux:icon.chat-bubble-left-ellipsis class="absolute top-8 right-8 size-10 text-primary-custom/10" />
                        <div class="flex gap-1 mb-4 text-yellow-500">
                            <flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" />
                        </div>
                        <p class="text-zinc-700 italic mb-6">"Since joining the platform, my shop's orders have increased by 40%. The digital measurement storage saves us from manual errors."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="size-12 bg-zinc-300 rounded-full"></div>
                            <div>
                                <h4 class="font-bold text-zinc-900">Jun Mendoza</h4>
                                <p class="text-sm text-zinc-500">Owner, Tahi ni Jun</p>
                            </div>
                        </div>
                    </div>
                    <!-- Review 3 -->
                    <div class="bg-cream p-8 rounded-2xl relative">
                        <flux:icon.chat-bubble-left-ellipsis class="absolute top-8 right-8 size-10 text-primary-custom/10" />
                        <div class="flex gap-1 mb-4 text-yellow-500">
                            <flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" /><flux:icon.star class="size-4" />
                        </div>
                        <p class="text-zinc-700 italic mb-6">"The virtual try-on is magic! It helped me decide between two Barong designs without having to guess how they would look on me."</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <div class="size-12 bg-zinc-300 rounded-full"></div>
                            <div>
                                <h4 class="font-bold text-zinc-900">Marco V.</h4>
                                <p class="text-sm text-zinc-500">Customer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 10. Mobile App Preview -->
        <section class="py-24 bg-zinc-50 border-t border-zinc-100 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 mb-4">Manage Your Tailoring Experience Anywhere</h2>
                    <p class="text-lg text-zinc-600">Our mobile-first design means you have full control right from your pocket.</p>
                </div>
                
                <div class="flex justify-center items-end gap-4 md:gap-8 translate-y-12">
                    <!-- Phone 1 (Appointments) -->
                    <div class="w-48 md:w-64 bg-zinc-900 rounded-[2rem] p-3 shadow-2xl border-[6px] border-zinc-800 transform rotate-6">
                        <div class="bg-white h-96 md:h-[30rem] rounded-[1.5rem] overflow-hidden flex flex-col relative">
                            <div class="absolute top-0 w-full h-6 bg-zinc-900 rounded-b-xl px-2 flex justify-center"><div class="w-16 h-4 bg-black rounded-b-lg"></div></div>
                            <div class="p-4 pt-8 bg-primary-custom text-white pb-6"><div class="h-4 w-1/2 bg-white/20 rounded"></div></div>
                            <div class="flex-1 p-4 space-y-4 bg-cream">
                                <div class="bg-white p-3 rounded-xl shadow-sm border border-zinc-100"><div class="h-3 w-1/3 bg-zinc-200 rounded mb-2"></div><div class="h-8 bg-zinc-100 rounded"></div></div>
                                <div class="bg-white p-3 rounded-xl shadow-sm border border-zinc-100"><div class="h-3 w-1/4 bg-zinc-200 rounded mb-2"></div><div class="h-16 bg-zinc-100 rounded"></div></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phone 2 (Tracking - Center) -->
                    <div class="w-56 md:w-72 bg-zinc-900 rounded-[2rem] p-3 shadow-2xl border-[6px] border-zinc-800 z-10 -translate-y-8">
                        <div class="bg-white h-[26rem] md:h-[34rem] rounded-[1.5rem] overflow-hidden flex flex-col relative">
                            <div class="absolute top-0 w-full h-6 bg-zinc-900 rounded-b-xl px-2 flex justify-center"><div class="w-16 h-4 bg-black rounded-b-lg"></div></div>
                            <div class="p-4 pt-10 pb-4 border-b border-zinc-100 text-center"><div class="h-5 w-2/3 bg-zinc-200 rounded mx-auto"></div></div>
                            <div class="flex-1 p-6 flex flex-col items-center">
                                <!-- Circular Progress Mock -->
                                <div class="size-32 rounded-full border-8 border-zinc-100 border-t-primary-custom border-r-primary-custom mb-6 flex items-center justify-center">
                                    <flux:icon.scissors class="size-10 text-primary-custom" />
                                </div>
                                <div class="h-4 w-1/2 bg-zinc-800 rounded mb-2"></div>
                                <div class="h-3 w-3/4 bg-zinc-400 rounded"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phone 3 (Virtual Try-on) -->
                    <div class="w-48 md:w-64 bg-zinc-900 rounded-[2rem] p-3 shadow-2xl border-[6px] border-zinc-800 transform -rotate-6 hidden sm:block">
                        <div class="bg-white h-96 md:h-[30rem] rounded-[1.5rem] overflow-hidden flex flex-col relative">
                            <div class="absolute top-0 w-full h-6 bg-zinc-900 rounded-b-xl px-2 flex justify-center"><div class="w-16 h-4 bg-black rounded-b-lg"></div></div>
                            <div class="flex-1 bg-zinc-800 relative">
                                <div class="absolute inset-x-4 bottom-4 h-12 bg-white/20 backdrop-blur rounded-xl border border-white/30"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 11. FAQ Section -->
        <section class="py-24 bg-white" x-data="{ activeAccordion: null }">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-zinc-900 mb-4">Frequently Asked Questions</h2>
                </div>
                
                <div class="space-y-4">
                    <!-- FAQ 1 -->
                    <div class="border border-zinc-200 rounded-2xl overflow-hidden">
                        <button @click="activeAccordion = activeAccordion === 1 ? null : 1" class="w-full flex justify-between items-center p-6 bg-white hover:bg-cream transition-colors text-left">
                            <span class="font-bold text-zinc-900">How does TahiConnect work?</span>
                            <flux:icon.chevron-down class="size-5 text-zinc-500 transition-transform" x-bind:class="activeAccordion === 1 ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="activeAccordion === 1" x-collapse x-cloak>
                            <div class="p-6 pt-0 text-zinc-600 bg-white">
                                TahiConnect is a tailoring management system that lets you register an account, save your body measurements, book appointments, and order custom garments directly from the shop — all with real-time order tracking.
                            </div>
                        </div>
                    </div>
                    <!-- FAQ 2 -->
                    <div class="border border-zinc-200 rounded-2xl overflow-hidden">
                        <button @click="activeAccordion = activeAccordion === 2 ? null : 2" class="w-full flex justify-between items-center p-6 bg-white hover:bg-cream transition-colors text-left">
                            <span class="font-bold text-zinc-900">How do I book a tailor?</span>
                            <flux:icon.chevron-down class="size-5 text-zinc-500 transition-transform" x-bind:class="activeAccordion === 2 ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="activeAccordion === 2" x-collapse x-cloak>
                            <div class="p-6 pt-0 text-zinc-600 bg-white">
                                After logging in, go to "Book Appointment" in your dashboard. Choose your preferred appointment type and time slot, and we'll confirm your booking shortly.
                            </div>
                        </div>
                    </div>
                    <!-- FAQ 3 -->
                    <div class="border border-zinc-200 rounded-2xl overflow-hidden">
                        <button @click="activeAccordion = activeAccordion === 3 ? null : 3" class="w-full flex justify-between items-center p-6 bg-white hover:bg-cream transition-colors text-left">
                            <span class="font-bold text-zinc-900">How does virtual try-on work?</span>
                            <flux:icon.chevron-down class="size-5 text-zinc-500 transition-transform" x-bind:class="activeAccordion === 3 ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="activeAccordion === 3" x-collapse x-cloak>
                            <div class="p-6 pt-0 text-zinc-600 bg-white">
                                Our AI Virtual Try-On feature allows you to upload a full-body photo and a design reference. The AI then processes these images to generate a realistic preview of how the garment will look on your body.
                            </div>
                        </div>
                    </div>
                    <!-- FAQ 4 -->
                    <div class="border border-zinc-200 rounded-2xl overflow-hidden">
                        <button @click="activeAccordion = activeAccordion === 4 ? null : 4" class="w-full flex justify-between items-center p-6 bg-white hover:bg-cream transition-colors text-left">
                            <span class="font-bold text-zinc-900">How do I manage my account?</span>
                            <flux:icon.chevron-down class="size-5 text-zinc-500 transition-transform" x-bind:class="activeAccordion === 4 ? 'rotate-180' : ''" />
                        </button>
                        <div x-show="activeAccordion === 4" x-collapse x-cloak>
                            <div class="p-6 pt-0 text-zinc-600 bg-white">
                                After registering, go to Settings → Profile to update your name, contact number, and address. Shop owners and staff can manage business settings from the Shop Settings page in their dashboard.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 12. Final Call-To-Action -->
        <section class="py-24 bg-secondary-custom text-zinc-900 text-center px-4">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-4xl md:text-5xl font-extrabold mb-6">Experience Modern Tailoring</h2>
                <p class="text-xl opacity-90 mb-10 max-w-2xl mx-auto font-medium">
                    Register as a customer to book appointments, place orders, and track your garments — or sign in to your account to get started.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('register') }}" class="px-8 py-4 rounded-full bg-zinc-900 text-white font-bold text-lg hover:bg-black transition-colors shadow-xl">
                        Create Account
                    </a>
                    <a href="{{ route('login') }}" class="px-8 py-4 rounded-full bg-white text-zinc-900 font-bold text-lg hover:bg-cream transition-colors shadow-xl">
                        Sign In
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- 13. Footer -->
    <footer class="bg-zinc-900 text-zinc-400 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12 border-b border-zinc-800 pb-12">
                <!-- Company -->
                <div>
                    <h3 class="text-white font-bold mb-4 font-heading">Company</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
                    </ul>
                </div>
                <!-- Services -->
                <div>
                    <h3 class="text-white font-bold mb-4 font-heading">Services</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Custom Garments</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Ready-to-Wear</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Book Appointment</a></li>
                    </ul>
                </div>
                <!-- Support -->
                <div>
                    <h3 class="text-white font-bold mb-4 font-heading">Support</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQs</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                    </ul>
                </div>
                <!-- Social -->
                <div>
                    <h3 class="text-white font-bold mb-4 font-heading">Social Media</h3>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center hover:bg-primary-custom hover:text-white transition-colors">
                            <flux:icon.building-storefront class="size-5" /> <!-- Placeholder for FB -->
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center hover:bg-primary-custom hover:text-white transition-colors">
                            <flux:icon.camera class="size-5" /> <!-- Placeholder for IG -->
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-zinc-800 flex items-center justify-center hover:bg-primary-custom hover:text-white transition-colors">
                            <flux:icon.video-camera class="size-5" /> <!-- Placeholder for TikTok -->
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                <div class="flex items-center gap-2 mb-4 md:mb-0">
                    <x-app-logo-icon class="size-6 text-zinc-500" />
                    <span class="font-bold text-white tracking-tight" style="font-family: 'Poppins';">TahiConnect</span>
                </div>
                <p>&copy; 2026 TahiConnect. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js is included via app.js/Livewire, but let's ensure scrolling scripts -->
    <script>
        // Simple script to add background to nav on scroll
        document.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 10) {
                nav.classList.add('shadow-sm');
                nav.style.background = 'rgba(253, 252, 250, 0.95)';
            } else {
                nav.classList.remove('shadow-sm');
                nav.style.background = 'rgba(245, 241, 234, 0.9)';
            }
        });
    </script>
</body>
</html>
