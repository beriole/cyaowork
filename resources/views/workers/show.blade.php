@extends('layouts.base')
@section('title', $worker->user->name.' — '.$worker->headline.' · CyaoWork')

@php
    $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois'];
    $availFr = ['immediate' => 'Disponible immédiatement', 'week' => 'Disponible sous une semaine', 'flexible' => 'Disponibilité flexible'];
    $stars = function ($r) {
        $r = (float) $r; $out = ''; $full = floor($r); $half = ($r - $full) >= .5;
        for ($k = 0; $k < $full; $k++) $out .= '<i data-lucide="star" class="w-4 h-4 fill-current"></i>';
        if ($half) $out .= '<i data-lucide="star-half" class="w-4 h-4 fill-current"></i>';
        for ($k = $full + ($half ? 1 : 0); $k < 5; $k++) $out .= '<i data-lucide="star" class="w-4 h-4 text-slate-200 fill-current"></i>';
        return $out;
    };
    $verified = $worker->isVerified();
@endphp

@section('body')
<x-public-nav />

<main class="mx-auto max-w-5xl px-4 sm:px-6 pt-28 pb-16">
    @if(session('status'))
    <div class="mb-5 flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
    </div>
    @endif

    {{-- EN-TÊTE PROFIL --}}
    <section class="reveal relative overflow-hidden rounded-3xl bg-white border border-line p-6 sm:p-8">
        <div class="blob w-60 h-60 bg-primary/10 -top-20 -right-10"></div>
        <div class="relative flex flex-col sm:flex-row items-start gap-5">
            <div class="relative shrink-0">
                <div class="p-1 rounded-3xl bg-gradient-to-br {{ $worker->category?->gradient ?? 'from-primary to-secondary' }}">
                    <img src="{{ $worker->photoUrl(220) }}" alt="{{ $worker->user->name }}" class="w-28 h-28 rounded-[20px] object-cover ring-4 ring-white" />
                </div>
                <span class="absolute -bottom-2 -right-2 grid place-items-center w-9 h-9 rounded-full {{ $verified ? 'bg-accent' : 'bg-warn' }} text-white ring-4 ring-white" title="{{ $verified ? 'Profil vérifié' : 'En vérification' }}"><i data-lucide="{{ $verified ? 'badge-check' : 'clock' }}" class="w-5 h-5"></i></span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-extrabold">{{ $worker->user->name }}</h1>
                    @if($verified)<span class="inline-flex items-center gap-1 text-xs font-semibold text-accent-dark bg-accent/10 rounded-full px-2.5 py-1"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i>Vérifié</span>@endif
                </div>
                <p class="mt-1 text-lg text-slate-600">{{ $worker->headline }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1.5"><span class="flex text-amber">{!! $stars($worker->rating_avg) !!}</span><b class="text-ink ml-1">{{ number_format($worker->rating_avg, 1, ',', ' ') }}</b><span class="text-slate-400">({{ $worker->reviews_count }} avis)</span></span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-rose"></i>{{ $worker->city }}</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="briefcase" class="w-4 h-4 text-grape"></i>{{ $worker->experience_years }} ans d'expérience</span>
                </div>
                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-accent/10 text-accent-dark px-3 py-1.5 text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-accent"></span>{{ $availFr[$worker->availability] ?? 'Disponibilité à convenir' }}
                </div>
            </div>
        </div>
    </section>

    <div class="mt-6 grid lg:grid-cols-3 gap-6">
        {{-- COLONNE PRINCIPALE --}}
        <div class="lg:col-span-2 space-y-6">
            @if($worker->bio)
            <section class="reveal rounded-3xl bg-white border border-line p-6">
                <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="user" class="w-5 h-5 text-primary"></i> À propos</h2>
                <p class="text-slate-600 whitespace-pre-line">{{ $worker->bio }}</p>
            </section>
            @endif

            @if($worker->skills->isNotEmpty())
            <section class="reveal rounded-3xl bg-white border border-line p-6">
                <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="sparkles" class="w-5 h-5 text-grape"></i> Compétences</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($worker->skills as $s)
                    <span class="inline-flex items-center gap-1.5 text-sm rounded-full bg-muted text-slate-700 px-3 py-1.5 font-medium"><i data-lucide="check" class="w-3.5 h-3.5 text-accent"></i>{{ $s->name }}</span>
                    @endforeach
                </div>
            </section>
            @endif

            <section class="reveal rounded-3xl bg-white border border-line p-6">
                <h2 class="font-bold flex items-center gap-2 mb-4"><i data-lucide="star" class="w-5 h-5 text-amber"></i> Avis ({{ $worker->reviews_count }})</h2>
                @forelse($reviews as $r)
                <div class="py-4 border-b border-line last:border-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold text-sm">{{ $r->reviewer->name ?? 'Utilisateur' }}</p>
                        <span class="flex text-amber">{!! $stars($r->rating) !!}</span>
                    </div>
                    @if($r->comment)<p class="mt-1.5 text-sm text-slate-600">« {{ $r->comment }} »</p>@endif
                    <p class="mt-1 text-xs text-slate-400">{{ $r->created_at->diffForHumans() }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-400 py-4 text-center">Aucun avis pour le moment.</p>
                @endforelse
            </section>
        </div>

        {{-- CARTE LATÉRALE --}}
        <aside class="lg:col-span-1">
            <div class="reveal lg:sticky lg:top-24 rounded-3xl bg-white border border-line p-6 space-y-4">
                <div>
                    <p class="text-sm text-slate-500">Tarif souhaité</p>
                    <p class="font-head font-extrabold text-2xl">{{ number_format($worker->expected_salary, 0, ',', ' ') }} <span class="text-base font-normal text-slate-400">FCFA/{{ $periodFr[$worker->salary_period] ?? $worker->salary_period }}</span></p>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <i data-lucide="folder" class="w-4 h-4 text-primary"></i>{{ $worker->category?->name ?? 'Polyvalent' }}
                </div>

                @auth
                    @if(auth()->user()->isEmployer())
                    <form method="POST" action="{{ route('workers.contact', $worker) }}">
                        @csrf
                        <button class="btn-press w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2"><i data-lucide="send" class="w-5 h-5"></i>Contacter</button>
                    </form>
                    @elseif(auth()->id() === $worker->user_id)
                    <a href="{{ route('worker.dashboard') }}" class="btn-press w-full h-12 rounded-xl border border-line font-semibold text-slate-600 hover:border-primary hover:text-primary inline-flex items-center justify-center gap-2 transition-colors"><i data-lucide="layout-dashboard" class="w-5 h-5"></i>Mon espace</a>
                    @endif
                @else
                <a href="{{ route('login') }}" class="btn-press w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2"><i data-lucide="log-in" class="w-5 h-5"></i>Se connecter pour contacter</a>
                @endauth

                <div class="flex items-center gap-2 text-xs text-slate-400 pt-1">
                    <i data-lucide="shield-check" class="w-4 h-4 text-accent"></i>{{ $verified ? 'Identité vérifiée par CyaoWork' : 'Vérification en cours' }}
                </div>
            </div>
        </aside>
    </div>
</main>
@endsection
