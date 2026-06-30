@extends('layouts.base')
@section('title', 'CyaoWork — Mot de passe oublié')

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
            <h2 class="text-4xl font-extrabold leading-tight">Pas de panique.</h2>
            <p class="mt-4 text-white/80 max-w-md text-lg">Recevez un code par SMS pour réinitialiser votre mot de passe en quelques secondes.</p>
        </div>
        <div class="relative flex items-center gap-2 text-white/70 text-sm"><i data-lucide="shield-check" class="w-4 h-4"></i> Réinitialisation sécurisée par SMS</div>
    </div>

    {{-- FORM --}}
    <div class="flex flex-col min-h-dvh">
        <div class="lg:hidden flex items-center justify-between px-5 h-16 border-b border-line">
            <a href="{{ route('home') }}" class="flex items-center gap-2"><span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span><span class="font-head font-bold text-lg">CyaoWork</span></a>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-primary">Connexion</a>
        </div>

        <div class="flex-1 flex items-center justify-center p-5 sm:p-8">
            <form method="POST" action="{{ route('password.email') }}" class="w-full max-w-md">
                @csrf
                <span class="grid place-items-center w-14 h-14 rounded-2xl bg-primary/10 text-primary mb-4"><i data-lucide="key-round" class="w-7 h-7"></i></span>
                <h1 class="text-2xl sm:text-3xl font-extrabold">Mot de passe oublié</h1>
                <p class="mt-2 text-slate-600">Entrez votre email ou téléphone : nous vous enverrons un code de réinitialisation par SMS.</p>

                @error('login')<p class="mt-4 text-sm rounded-xl bg-rose/10 text-rose px-3 py-2">{{ $message }}</p>@enderror

                <div class="mt-6">
                    <label class="block text-sm font-semibold mb-1.5">Email ou téléphone</label>
                    <div class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary"><i data-lucide="at-sign" class="w-5 h-5 text-slate-400"></i><input name="login" value="{{ old('login') }}" class="bg-transparent outline-none w-full" placeholder="email@exemple.cm ou +237…" required autofocus /></div>
                </div>

                <button type="submit" class="btn-press mt-6 w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2"><i data-lucide="send" class="w-5 h-5"></i>Envoyer le code</button>
                <p class="mt-4 text-center text-sm text-slate-500">Vous vous souvenez ? <a href="{{ route('login') }}" class="text-primary font-semibold">Se connecter</a></p>
            </form>
        </div>
    </div>
</div>
@endsection
