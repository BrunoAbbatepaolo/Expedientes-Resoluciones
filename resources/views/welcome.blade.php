<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiRex - Sistema de Resoluciones y Expedientes</title>

    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Importante --}}
    
    <style>
        /* Tus animaciones personalizadas */
        @keyframes fadeIn { ... }
        @keyframes pulse { ... }
        @keyframes float { ... }
        
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        .animate-pulse { animation: pulse 3s infinite ease-in-out; }
        .animate-float { animation: float 6s infinite ease-in-out; }

        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-500 { animation-delay: 0.5s; }

        .glass-effect {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .hero-gradient {
            background: radial-gradient(circle at 50% 50%, rgba(64, 64, 64, 0.3) 0%, rgba(32, 32, 32, 0) 70%);
        }
    </style>
</head>
    <body class="bg-[#0a0a0a] text-[#EDEDEC] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <!-- Header Navigation -->
    @if (Route::has('login'))
    <header class="w-full lg:max-w-6xl max-w-[335px] text-sm mb-6 z-10 absolute top-0 left-0 right-0 p-6 lg:p-8">
        <nav class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span class="font-bold text-xl">SiRex</span>
            </div>
            <div class="flex items-center justify-end gap-4">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-block px-5 py-1.5 text-[#EDEDEC] border-[#3E3E3A] hover:border-[#62605b] border rounded-sm text-sm leading-normal transition-all duration-300">
                    Dashboard
                </a>
                @else
                <a href="{{ route('login') }}" class="inline-block px-5 py-1.5 text-[#EDEDEC] border border-transparent hover:border-[#3E3E3A] rounded-sm text-sm leading-normal transition-all duration-300">
                    Iniciar Sesión
                </a>

                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="inline-block px-5 py-1.5 text-[#EDEDEC] border-[#3E3E3A] hover:border-[#62605b] border rounded-sm text-sm leading-normal transition-all duration-300 bg-blue-600 hover:bg-blue-700">
                    Registrarse
                </a>
                @endif
                @endauth
            </div>
        </nav>
    </header>
    @endif

    <!-- Main Content -->
    <div class="flex items-center justify-center w-full transition-opacity pt-16 opacity-100 duration-750 lg:grow starting:opacity-0">
        <main class="flex max-w-[335px] w-full flex-col lg:max-w-6xl lg:flex-row items-center justify-center pt-16 lg:pt-0">
            <!-- Left Content Column -->
            <div class="lg:w-1/2 flex flex-col justify-center lg:pr-12 animate-fade-in">
                <h1 class="text-4xl lg:text-6xl font-bold mb-6 text-gradient bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-purple-600">
                    Sistema de Resoluciones y Expedientes
                </h1>
                
                <p class="text-lg lg:text-xl text-gray-300 mb-8 animate-fade-in animate-delay-100">
                    Administra, organiza y gestiona tus expedientes y resoluciones de manera eficiente y segura con SiRex.
                </p>
                
                <div class="flex flex-wrap gap-4 mb-12 animate-fade-in animate-delay-200">
                    <a href="{{ route('login') }}" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-all duration-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Comenzar ahora
                    </a>
                    <a href="#features" class="px-8 py-3 border border-[#3E3E3A] hover:border-[#62605b] text-[#EDEDEC] rounded-md font-medium transition-all duration-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Más información
                    </a>
                </div>
                
                <div class="grid grid-cols-3 gap-6 animate-fade-in animate-delay-300">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-500 mb-1">+500</div>
                        <div class="text-sm text-gray-400">Resoluciones procesadas</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-500 mb-1">99%</div>
                        <div class="text-sm text-gray-400">Satisfacción de usuarios</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-500 mb-1">24/7</div>
                        <div class="text-sm text-gray-400">Disponibilidad del sistema</div>
                    </div>
                </div>
            </div>
            
            <!-- Right Image/Graphic Column -->
            <div class="lg:w-1/2 mt-12 lg:mt-0 animate-fade-in animate-delay-400">
                <div class="relative">
                    <!-- Background Gradient -->
                    <div class="absolute inset-0 hero-gradient"></div>
                    
                    <!-- Floating elements -->
                    <div class="relative">
                        <!-- Main interface mockup -->
                        <div class="bg-[#121212] border border-[#2a2a2a] p-4 rounded-xl shadow-2xl animate-float">
                            <!-- Header bar mockup -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <div class="h-6 w-40 bg-[#2a2a2a] rounded-md"></div>
                            </div>
                            
                            <!-- Content mockup -->
                            <div class="flex gap-4">
                                <!-- Sidebar -->
                                <div class="w-1/4">
                                    <div class="h-8 w-full bg-[#2a2a2a] rounded-md mb-3"></div>
                                    <div class="h-6 w-full bg-[#232323] rounded-md mb-3"></div>
                                    <div class="h-6 w-full bg-[#232323] rounded-md mb-3"></div>
                                    <div class="h-6 w-full bg-blue-600 rounded-md mb-3"></div>
                                    <div class="h-6 w-full bg-[#232323] rounded-md mb-3"></div>
                                    <div class="h-6 w-full bg-[#232323] rounded-md"></div>
                                </div>
                                
                                <!-- Main content -->
                                <div class="w-3/4">
                                    <div class="h-10 w-full bg-[#2a2a2a] rounded-md mb-4"></div>
                                    
                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                        <div class="h-24 bg-[#1e1e1e] rounded-md p-3">
                                            <div class="h-4 w-2/3 bg-[#333] rounded mb-2"></div>
                                            <div class="h-4 w-1/2 bg-[#333] rounded mb-3"></div>
                                            <div class="h-6 w-1/3 bg-blue-600 rounded"></div>
                                        </div>
                                        <div class="h-24 bg-[#1e1e1e] rounded-md p-3">
                                            <div class="h-4 w-2/3 bg-[#333] rounded mb-2"></div>
                                            <div class="h-4 w-1/2 bg-[#333] rounded mb-3"></div>
                                            <div class="h-6 w-1/3 bg-blue-600 rounded"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-[#1e1e1e] rounded-md p-3">
                                        <div class="h-6 w-1/4 bg-[#333] rounded mb-3"></div>
                                        <div class="flex flex-col gap-2">
                                            <div class="h-4 w-full bg-[#333] rounded"></div>
                                            <div class="h-4 w-full bg-[#333] rounded"></div>
                                            <div class="h-4 w-3/4 bg-[#333] rounded"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating element 1 -->
                        <div class="absolute -top-12 -right-4 bg-[#1a1a1a] border border-[#2a2a2a] p-3 rounded-lg shadow-lg animate-float" style="animation-delay: 1s;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                    PDF
                                </div>
                                <div>
                                    <div class="h-3 w-24 bg-[#333] rounded mb-2"></div>
                                    <div class="h-3 w-16 bg-[#444] rounded"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating element 2 -->
                        <div class="absolute -bottom-6 -left-8 bg-[#1a1a1a] border border-[#2a2a2a] p-3 rounded-lg shadow-lg animate-float" style="animation-delay: 2s;">
                            <div class="flex items-center gap-2">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="h-4 w-24 bg-[#333] rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Features Section -->
    <section id="features" class="w-full max-w-6xl py-24 animate-fade-in animate-delay-500">
        <h2 class="text-3xl font-bold text-center mb-16">Características principales</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-[#121212] border border-[#2a2a2a] p-6 rounded-xl hover:border-blue-500 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Gestión de Expedientes</h3>
                <p class="text-gray-400">Organiza y administra todos tus expedientes en un solo lugar con búsquedas avanzadas y filtros personalizados.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="bg-[#121212] border border-[#2a2a2a] p-6 rounded-xl hover:border-blue-500 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Seguimiento de Resoluciones</h3>
                <p class="text-gray-400">Mantén un registro detallado de todas las resoluciones con fechas, estado actual y documentación relacionada.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="bg-[#121212] border border-[#2a2a2a] p-6 rounded-xl hover:border-blue-500 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Gestión de Documentos</h3>
                <p class="text-gray-400">Sube, almacena y organiza todos los documentos PDF relacionados con tus expedientes y resoluciones.</p>
            </div>
            
            <!-- Feature 4 -->
            <div class="bg-[#121212] border border-[#2a2a2a] p-6 rounded-xl hover:border-blue-500 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Control por Barrios y Casas</h3>
                <p class="text-gray-400">Clasifica y filtra expedientes por barrios y casas para una gestión localizada y eficiente.</p>
            </div>
            
            <!-- Feature 5 -->
            <div class="bg-[#121212] border border-[#2a2a2a] p-6 rounded-xl hover:border-blue-500 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Seguridad Avanzada</h3>
                <p class="text-gray-400">Sistema de permisos por roles y registro detallado de actividades para mantener la integridad de los datos.</p>
            </div>
            
            <!-- Feature 6 -->
            <div class="bg-[#121212] border border-[#2a2a2a] p-6 rounded-xl hover:border-blue-500 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Reportes y Estadísticas</h3>
                <p class="text-gray-400">Genera informes detallados y visualiza estadísticas para tomar decisiones informadas sobre tus expedientes.</p>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="w-full max-w-6xl py-12 border-t border-[#2a2a2a] mt-12">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center gap-2 mb-6 md:mb-0">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span class="font-bold text-xl">SiRex</span>
            </div>
            
            <div class="text-center md:text-right text-sm text-gray-400">
                <p>© 2025 SiRex - Sistema de Resoluciones y Expedientes</p>
                <p>Todos los derechos reservados</p>
            </div>
        </div>
    </footer>
    
    @if (Route::has('login'))
        <div class="h-14 hidden lg:block"></div>
    @endif
</body>
</html>
