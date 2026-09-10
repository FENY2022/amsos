<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR Caraga System Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        denr: {
                            light: '#34d399', // emerald-400
                            DEFAULT: '#10b981', // emerald-500
                            dark: '#059669', // emerald-600
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-denr selection:text-white">

    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center md:justify-end h-16 items-center">
                <ul class="flex space-x-6 md:space-x-8 text-sm font-medium text-slate-600">
                    <li><a href="#home" class="hover:text-denr transition-colors duration-200">Home</a></li>
                    <li><a href="#about" class="hover:text-denr transition-colors duration-200">About</a></li>
                    <li><a href="#office" class="hover:text-denr transition-colors duration-200">Office</a></li>
                    <li><a href="#directory" class="hover:text-denr transition-colors duration-200">Directory</a></li>
                    <li><a href="#help" class="hover:text-denr transition-colors duration-200">Help</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="home" class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-emerald-50/50 to-slate-50 flex flex-col items-center text-center">
        <div class="relative inline-block mb-6 group">
            <div class="absolute inset-0 bg-denr blur-2xl opacity-20 rounded-full group-hover:opacity-30 transition-opacity duration-500"></div>
            <img src="logo/denrlogo.png" alt="DENR Logo" class="relative h-28 w-auto object-contain drop-shadow-xl transform transition-transform duration-500 hover:scale-105">
        </div>
        
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
            DENR Caraga <span class="text-transparent bg-clip-text bg-gradient-to-r from-denr to-teal-500">System Services</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-600 max-w-2xl font-medium bg-white/60 px-6 py-2 rounded-full border border-slate-200 shadow-sm inline-block">
            ICT Innovation Towards Digital Transformation
        </p>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            
            <a href="#" class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1 flex flex-col items-center text-center">
                <div class="h-20 w-20 flex items-center justify-center bg-slate-50 rounded-2xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-inner">
                    <img src="logo/otos.png" alt="OTOS Logo" class="h-12 w-12 object-contain">
                </div>
                <h3 class="text-lg font-bold text-slate-800 group-hover:text-denr transition-colors leading-tight">
                    Online Travel Order System
                </h3>
            </a>

            <a href="#" class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1 flex flex-col items-center text-center">
                <div class="h-20 w-20 flex items-center justify-center bg-slate-50 rounded-2xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-inner">
                    <img src="logo/oldpmslogin.png" alt="OLD PMS Logo" class="h-12 w-12 object-contain">
                </div>
                <h3 class="text-lg font-bold text-slate-800 group-hover:text-denr transition-colors leading-tight">
                    Online Lumber Dealer Permitting & Monitoring System
                </h3>
            </a>

            <a href="#" class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1 flex flex-col items-center text-center">
                <div class="h-20 w-20 flex items-center justify-center bg-slate-50 rounded-2xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-inner">
                    <img src="logo/amsos.png" alt="AMSOS Logo" class="h-12 w-12 object-contain">
                </div>
                <h3 class="text-lg font-bold text-slate-800 group-hover:text-denr transition-colors leading-tight">
                    Asset Management & Service Optimization System
                </h3>
            </a>

            <a href="#" class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1 flex flex-col items-center text-center">
                <div class="h-20 w-20 flex items-center justify-center bg-slate-50 rounded-2xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-inner">
                    <img src="logo/edats.png" alt="EDATS Logo" class="h-12 w-12 object-contain">
                </div>
                <h3 class="text-lg font-bold text-slate-800 group-hover:text-denr transition-colors leading-tight">
                    Enhanced Document Action Tracking System 4.2.0
                </h3>
            </a>

            <a href="#" class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1 flex flex-col items-center text-center">
                <div class="h-20 w-20 flex items-center justify-center bg-emerald-50 text-denr-dark rounded-2xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-inner font-bold text-sm">
                    LOGO
                </div>
                <h3 class="text-lg font-bold text-slate-800 group-hover:text-denr transition-colors leading-tight">
                    Name of Services
                </h3>
            </a>

            <a href="#" class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300 transform hover:-translate-y-1 flex flex-col items-center text-center">
                <div class="h-20 w-20 flex items-center justify-center bg-emerald-50 text-denr-dark rounded-2xl mb-5 group-hover:scale-110 transition-transform duration-300 shadow-inner font-bold text-sm">
                    LOGO
                </div>
                <h3 class="text-lg font-bold text-slate-800 group-hover:text-denr transition-colors leading-tight">
                    Name of Services
                </h3>
            </a>

        </div>
    </main>

</body>
</html>