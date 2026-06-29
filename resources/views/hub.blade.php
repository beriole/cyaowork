@extends('layouts.base')
@section('title', 'CyaoWork — Navigation')

@php
    $screens = [
        ['url' => route('home'),               't' => 'Landing publique',     'd' => 'Hero, recherche, catégories, profils, confiance.',       'i' => 'globe',            'g' => 'from-blue-600 to-sky-500',     'tag' => 'Public'],
        ['url' => route('onboarding'),          't' => 'Inscription + OTP',     'd' => 'Choix du rôle, téléphone +237, code SMS.',               'i' => 'user-plus',        'g' => 'from-fuchsia-500 to-rose-500', 'tag' => 'Auth'],
        ['url' => route('worker.dashboard'),    't' => 'Espace Worker',        'd' => 'Reco IA, candidatures, messagerie, complétion profil.',  'i' => 'user-round',       'g' => 'from-violet-600 to-fuchsia-500', 'tag' => 'Chercheur'],
        ['url' => route('employer.search'),     't' => 'Recherche Employeur',  'd' => 'Filtres à facettes, carte interactive, données réelles.','i' => 'search',           'g' => 'from-emerald-500 to-teal-500', 'tag' => 'Recruteur'],
        ['url' => route('employer.dashboard'),  't' => 'Espace Employeur',     'd' => 'Mes offres, candidatures reçues, abonnement, stats.',    'i' => 'layout-dashboard', 'g' => 'from-amber-500 to-orange-500', 'tag' => 'Recruteur'],
        ['url' => route('messaging.index'),     't' => 'Messagerie',           'd' => 'Conversations et fil de discussion temps réel.',         'i' => 'messages-square',  'g' => 'from-cyan-500 to-blue-600',    'tag' => 'Worker · Employeur'],
        ['url' => route('admin.dashboard'),     't' => 'Administration',       'd' => 'Vérifications, modération, paiements, supervision.',      'i' => 'shield',           'g' => 'from-slate-700 to-slate-900',  'tag' => 'Admin'],
    ];
@endphp

@section('body')
<div class="min-h-dvh relative overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 -z-10">
        <div class="blob w-[28rem] h-[28rem] bg-blue-600/40 -top-20 -left-10"></div>
        <div class="blob w-[24rem] h-[24rem] bg-violet-600/40 top-20 right-0" style="animation-delay:-5s"></div>
        <div class="blob w-[22rem] h-[22rem] bg-emerald-500/30 bottom-0 left-1/3" style="animation-delay:-9s"></div>
    </div>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="flex items-center gap-3">
            <span class="grid place-items-center w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 via-sky-500 to-emerald-500"><i data-lucide="handshake" class="w-6 h-6"></i></span>
            <span class="text-2xl font-head font-extrabold">Cyao<span class="text-blue-400">Work</span></span>
        </div>
        <h1 class="mt-8 text-4xl sm:text-5xl font-extrabold">Application Laravel — écrans</h1>
        <p class="mt-3 text-white/60 max-w-xl">Tous les écrans tournent sur la vraie base de données (Laravel + Tailwind). Cliquez pour explorer.</p>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($screens as $s)
            <a href="{{ $s['url'] }}" class="card-hov group rounded-3xl bg-white/5 backdrop-blur border border-white/10 p-6 hover:bg-white/10 block">
                <div class="flex items-center justify-between">
                    <span class="grid place-items-center w-12 h-12 rounded-2xl bg-gradient-to-br {{ $s['g'] }} shadow-lg"><i data-lucide="{{ $s['i'] }}" class="w-6 h-6"></i></span>
                    <span class="text-xs font-semibold rounded-full bg-white/10 px-2.5 py-1 text-white/70">{{ $s['tag'] }}</span>
                </div>
                <h3 class="mt-4 text-xl font-bold">{{ $s['t'] }}</h3>
                <p class="mt-1.5 text-sm text-white/60">{{ $s['d'] }}</p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-blue-300 group-hover:gap-2 transition-all">Ouvrir <i data-lucide="arrow-right" class="w-4 h-4"></i></span>
            </a>
            @endforeach
        </div>
        <p class="mt-12 text-sm text-white/40">Comptes de démo (mot de passe <code>password</code>) : admin@cyaowork.cm · employeur@cyaowork.cm · worker1@cyaowork.cm</p>
    </div>
</div>
@endsection
