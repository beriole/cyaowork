@extends('layouts.base')
@section('title', $offer->title.' — CyaoWork')

@php
    $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois'];
    $contractFr = ['ponctuel' => 'Ponctuel', 'journalier' => 'Journalier', 'permanent' => 'Permanent'];
@endphp

@section('body')
<x-public-nav />

<main class="mx-auto max-w-4xl px-4 sm:px-6 pt-28 pb-16">
    <a href="{{ route('offers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-primary mb-4"><i data-lucide="arrow-left" class="w-4 h-4"></i>Toutes les offres</a>

    @if(session('status'))
    <div class="mb-5 flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
    </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section class="reveal rounded-3xl bg-white border border-line p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="grid place-items-center w-14 h-14 rounded-2xl bg-gradient-to-br {{ $offer->category?->gradient ?? 'from-primary to-secondary' }} text-white shadow-lg shrink-0"><i data-lucide="briefcase" class="w-7 h-7"></i></span>
                    <div>
                        <h1 class="text-2xl font-extrabold leading-tight">{{ $offer->title }} @if($offer->is_boosted)<span class="align-middle ml-1 text-[10px] font-bold text-white bg-gradient-to-r from-amber to-orange-500 rounded-full px-2 py-0.5">BOOST</span>@endif</h1>
                        <p class="mt-0.5 text-slate-500">{{ $offer->category?->name }}</p>
                    </div>
                </div>
                <div class="mt-5 grid sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-muted/60 p-3"><p class="text-xs text-slate-500">Lieu</p><p class="font-semibold flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-rose"></i>{{ $offer->city ?? '—' }}</p></div>
                    <div class="rounded-2xl bg-muted/60 p-3"><p class="text-xs text-slate-500">Rémunération</p><p class="font-semibold">{{ number_format($offer->salary_amount, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA/{{ $periodFr[$offer->salary_period] ?? $offer->salary_period }}</span></p></div>
                    <div class="rounded-2xl bg-muted/60 p-3"><p class="text-xs text-slate-500">Contrat</p><p class="font-semibold">{{ $contractFr[$offer->contract_type] ?? ucfirst($offer->contract_type) }}</p></div>
                </div>
                @if($offer->schedule)<p class="mt-4 text-sm text-slate-600 inline-flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 text-grape"></i>Horaires : {{ $offer->schedule }}</p>@endif
            </section>

            @if($offer->description)
            <section class="reveal rounded-3xl bg-white border border-line p-6 sm:p-8">
                <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="file-text" class="w-5 h-5 text-primary"></i> Description</h2>
                <p class="text-slate-600 whitespace-pre-line">{{ $offer->description }}</p>
            </section>
            @endif
        </div>

        {{-- Carte action --}}
        <aside class="lg:col-span-1">
            <div class="reveal lg:sticky lg:top-24 rounded-3xl bg-white border border-line p-6 space-y-4">
                <div class="flex items-center gap-2 text-sm">
                    <i data-lucide="building-2" class="w-4 h-4 text-primary"></i>
                    <span class="font-semibold">{{ $offer->employer->name }}</span>
                    @if($offer->employer->is_verified)<i data-lucide="badge-check" class="w-4 h-4 text-accent" title="Vérifié"></i>@endif
                </div>
                <p class="text-sm text-slate-500 flex items-center gap-2"><i data-lucide="users" class="w-4 h-4"></i>{{ $offer->applications_count }} candidature{{ $offer->applications_count > 1 ? 's' : '' }}</p>

                @auth
                    @if(auth()->user()->isWorker())
                        @if($hasApplied)
                        <div class="w-full h-12 rounded-xl bg-accent/10 text-accent-dark font-semibold inline-flex items-center justify-center gap-2"><i data-lucide="check-circle" class="w-5 h-5"></i>Candidature envoyée</div>
                        @else
                        <form method="POST" action="{{ route('worker.apply', $offer) }}">@csrf
                            <button class="btn-press w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-accent to-accent-dark shadow-lg shadow-accent/25 inline-flex items-center justify-center gap-2"><i data-lucide="zap" class="w-5 h-5"></i>Postuler en 1 clic</button>
                        </form>
                        @endif
                    @else
                    <a href="{{ route('employer.dashboard') }}" class="btn-press w-full h-12 rounded-xl border border-line font-semibold text-slate-600 hover:border-primary hover:text-primary inline-flex items-center justify-center gap-2 transition-colors"><i data-lucide="layout-dashboard" class="w-5 h-5"></i>Mon espace</a>
                    @endif
                @else
                <a href="{{ route('login') }}" class="btn-press w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-accent to-accent-dark shadow-lg shadow-accent/25 inline-flex items-center justify-center gap-2"><i data-lucide="log-in" class="w-5 h-5"></i>Se connecter pour postuler</a>
                @endauth
            </div>
        </aside>
    </div>
</main>
@endsection
