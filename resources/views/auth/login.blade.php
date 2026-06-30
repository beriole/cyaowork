@extends('layouts.base')
@section('title', 'CyaoWork — Connexion')

@section('body')
<div class="min-h-dvh lg:grid lg:grid-cols-2">
    {{-- BRAND PANEL --}}
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-primary via-primary-dark to-grape text-white p-12">
        <div class="blob w-96 h-96 bg-secondary/40 -top-24 -left-20"></div>
        <div class="blob w-80 h-80 bg-accent/40 bottom-0 right-0" style="animation-delay:-6s"></div>
        <a href="{{ route('home') }}" class="relative flex items-center gap-2">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-white/15 backdrop-blur"><i data-lucide="handshake" class="w-6 h-6"></i></span>
            <span class="text-2xl font-head font-extrabold">CyaoWork</span>
        </a>
        <div class="relative">
            <h2 class="text-4xl font-extrabold leading-tight">Bon retour parmi nous.</h2>
            <p class="mt-4 text-white/80 max-w-md text-lg">Connectez-vous pour gérer vos missions, vos offres et vos messages.</p>
        </div>
        <div class="relative flex items-center gap-6 text-white/70 text-sm">
            <span class="flex items-center gap-2"><i data-lucide="badge-check" class="w-4 h-4"></i> Profils vérifiés</span>
            <span class="flex items-center gap-2"><i data-lucide="smartphone" class="w-4 h-4"></i> Mobile Money</span>
        </div>
    </div>

    {{-- FORM --}}
    <div class="flex flex-col min-h-dvh">
        <div class="lg:hidden flex items-center justify-between px-5 h-16 border-b border-line">
            <a href="{{ route('home') }}" class="flex items-center gap-2"><span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span><span class="font-head font-bold text-lg">CyaoWork</span></a>
            <a href="{{ route('register') }}" class="text-sm font-semibold text-primary">Inscription</a>
        </div>

        <div class="flex-1 flex items-center justify-center p-5 sm:p-8">
            <form method="POST" action="{{ route('login') }}" class="w-full max-w-md">
                @csrf
                <h1 class="text-2xl sm:text-3xl font-extrabold">Connexion</h1>
                <p class="mt-2 text-slate-600">Accédez à votre espace CyaoWork.</p>

                @if(session('status'))<p class="mt-4 text-sm rounded-xl bg-accent/10 text-accent-dark px-3 py-2">{{ session('status') }}</p>@endif
                @error('login')<p class="mt-4 text-sm rounded-xl bg-rose/10 text-rose px-3 py-2">{{ $message }}</p>@enderror

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Email ou téléphone</label>
                        <div class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary"><i data-lucide="at-sign" class="w-5 h-5 text-slate-400"></i><input name="login" value="{{ old('login') }}" class="bg-transparent outline-none w-full" placeholder="email@exemple.cm ou +237…" required autofocus /></div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Mot de passe</label>
                        <div class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary"><i data-lucide="lock" class="w-5 h-5 text-slate-400"></i><input id="pwd" name="password" type="password" class="bg-transparent outline-none w-full" placeholder="••••••••" required /><button type="button" id="pwdToggle" class="text-slate-400 hover:text-primary"><i data-lucide="eye" class="w-5 h-5"></i></button></div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer"><input type="checkbox" name="remember" class="w-4 h-4 accent-primary" /> Se souvenir de moi</label>
                        <a href="{{ route('password.forgot') }}" class="text-sm font-semibold text-primary hover:underline">Mot de passe oublié ?</a>
                    </div>
                </div>

                <button type="submit" class="btn-press mt-6 w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2">Se connecter <i data-lucide="arrow-right" class="w-5 h-5"></i></button>
                <p class="mt-4 text-center text-sm text-slate-500">Pas encore de compte ? <a href="{{ route('register') }}" class="text-primary font-semibold">S'inscrire</a></p>

                <div class="mt-6 rounded-2xl bg-muted/60 border border-line p-3 text-xs text-slate-500">
                    <p class="font-semibold text-slate-600 mb-1">Comptes de démo (mot de passe : <code>password</code>)</p>
                    <p>👩 worker1@cyaowork.cm · 🏢 employeur@cyaowork.cm · 🛡️ admin@cyaowork.cm</p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const pwd = document.getElementById('pwd'), pt = document.getElementById('pwdToggle');
        if (pt) pt.addEventListener('click', () => { const t = pwd.type === 'password'; pwd.type = t ? 'text' : 'password'; pt.innerHTML = `<i data-lucide="${t ? 'eye-off' : 'eye'}" class="w-5 h-5"></i>`; lucide.createIcons(); });
    });
</script>
@endpush
