<header class="fixed top-0 inset-x-0 z-50">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 mt-3">
        <div id="navbar" class="flex items-center justify-between h-16 rounded-2xl px-4 transition-all duration-300 bg-white/70 backdrop-blur-xl border border-white/60 shadow-lg shadow-slate-900/5">
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white shadow-md shadow-primary/30 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                    <i data-lucide="handshake" class="w-5 h-5"></i>
                </span>
                <span class="text-xl font-head font-bold tracking-tight">Cyao<span class="text-primary">Work</span></span>
            </a>
            <div class="hidden md:flex items-center gap-7 text-[15px] font-medium text-slate-600">
                <a href="#categories" class="hover:text-primary transition-colors">Métiers</a>
                <a href="{{ route('offers.index') }}" class="hover:text-primary transition-colors">Offres</a>
                <a href="#etapes" class="hover:text-primary transition-colors">Comment ça marche</a>
                <a href="#confiance" class="hover:text-primary transition-colors">Confiance</a>
            </div>
            <div class="flex items-center gap-2">
                @auth
                <a href="{{ \App\Http\Controllers\AuthController::homeFor(auth()->user()) }}" class="btn-press inline-flex items-center gap-2 h-11 px-4 rounded-xl text-[15px] font-semibold text-white bg-gradient-to-r from-primary to-secondary shadow-md shadow-primary/25"><i data-lucide="layout-dashboard" class="w-4 h-4"></i><span class="hidden xs:inline">Mon espace</span></a>
                @else
                <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center h-11 px-4 rounded-xl text-[15px] font-semibold text-primary hover:bg-primary/5 transition-colors">Connexion</a>
                <a href="{{ route('register') }}" class="btn-press inline-flex items-center gap-2 h-11 px-4 rounded-xl text-[15px] font-semibold text-white bg-gradient-to-r from-primary to-secondary shadow-md shadow-primary/25">
                    <i data-lucide="user-plus" class="w-4 h-4"></i><span class="hidden xs:inline">Inscription</span>
                </a>
                @endauth
                <button id="burger" class="md:hidden grid place-items-center w-11 h-11 rounded-xl text-slate-700 hover:bg-slate-100" aria-label="Menu"><i data-lucide="menu" class="w-6 h-6"></i></button>
            </div>
        </div>
        <div id="mobileMenu" class="md:hidden hidden mt-2 rounded-2xl bg-white/90 backdrop-blur-xl border border-line shadow-xl p-3">
            <a href="#categories" class="block px-4 py-3 rounded-xl hover:bg-muted font-medium">Métiers</a>
            <a href="{{ route('offers.index') }}" class="block px-4 py-3 rounded-xl hover:bg-muted font-medium">Offres</a>
            <a href="#etapes" class="block px-4 py-3 rounded-xl hover:bg-muted font-medium">Comment ça marche</a>
            <a href="#confiance" class="block px-4 py-3 rounded-xl hover:bg-muted font-medium">Confiance</a>
            <a href="#" class="block px-4 py-3 rounded-xl text-primary font-semibold">Connexion</a>
        </div>
    </nav>
</header>
