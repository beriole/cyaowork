@extends('layouts.base')
@section('title', 'Contrat '.$contract->reference().' — CyaoWork')

@php
    $offer = $contract->application->jobOffer;
    $worker = $contract->application->worker;
    $employer = $offer->employer;
    $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois'];
    $back = $role === 'employer' ? route('employer.dashboard') : route('worker.dashboard');
    $mySigned = $role === 'employer' ? $contract->employer_signed_at : $contract->worker_signed_at;
@endphp

@section('body')
<div class="min-h-dvh bg-[#F4F6FB]">
    <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
        <a href="{{ $back }}" class="grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Retour"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <div class="min-w-0">
            <h1 class="font-bold text-lg truncate">Contrat de mission</h1>
            <p class="text-xs text-slate-500">Réf. {{ $contract->reference() }}</p>
        </div>
        <a href="{{ route('contracts.pdf', $contract) }}" class="ml-auto btn-press inline-flex items-center gap-2 h-10 px-4 rounded-xl border border-line text-sm font-semibold hover:border-primary hover:text-primary transition-colors"><i data-lucide="file-down" class="w-4 h-4"></i><span class="hidden sm:inline">PDF</span></a>
    </header>

    <main class="mx-auto max-w-2xl p-4 sm:p-6 space-y-5">
        @if(session('status'))
        <div class="flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
        </div>
        @endif

        {{-- Statut global --}}
        <div class="reveal rounded-3xl p-5 text-white {{ $contract->isFullySigned() ? 'bg-gradient-to-br from-accent to-teal' : 'bg-gradient-to-br from-primary to-grape' }}">
            <div class="flex items-center gap-3">
                <span class="grid place-items-center w-12 h-12 rounded-2xl bg-white/15"><i data-lucide="{{ $contract->isFullySigned() ? 'check-check' : 'file-signature' }}" class="w-6 h-6"></i></span>
                <div>
                    <p class="font-bold text-lg">{{ $contract->isFullySigned() ? 'Contrat finalisé' : 'Signature en cours' }}</p>
                    <p class="text-white/80 text-sm">{{ $contract->isFullySigned() ? 'Signé par les deux parties.' : 'En attente des signatures électroniques.' }}</p>
                </div>
            </div>
        </div>

        {{-- Détails --}}
        <section class="reveal rounded-3xl bg-white border border-line p-5">
            <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="briefcase" class="w-5 h-5 text-primary"></i> Objet de la mission</h2>
            <dl class="text-sm divide-y divide-line">
                <div class="flex justify-between py-2"><dt class="text-slate-500">Poste</dt><dd class="font-semibold text-right">{{ $offer->title }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-slate-500">Catégorie</dt><dd class="font-semibold text-right">{{ $offer->category?->name ?? '—' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-slate-500">Lieu</dt><dd class="font-semibold text-right">{{ $offer->city ?? '—' }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-slate-500">Rémunération</dt><dd class="font-semibold text-right">{{ number_format($offer->salary_amount, 0, ',', ' ') }} FCFA / {{ $periodFr[$offer->salary_period] ?? $offer->salary_period }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-slate-500">Type de contrat</dt><dd class="font-semibold text-right">{{ ucfirst($offer->contract_type) }}</dd></div>
            </dl>
        </section>

        {{-- Parties & signatures --}}
        <section class="reveal grid sm:grid-cols-2 gap-4">
            @foreach([['Employeur', $employer, $contract->employer_signed_at], ['Travailleur', $worker, $contract->worker_signed_at]] as [$label, $party, $signedAt])
            <div class="rounded-3xl bg-white border border-line p-5">
                <p class="text-xs text-slate-500 uppercase tracking-wide">{{ $label }}</p>
                <p class="font-semibold mt-0.5">{{ $party->name }}</p>
                <p class="text-sm text-slate-500">{{ $party->phone }}</p>
                @if($signedAt)
                <p class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-accent-dark"><i data-lucide="check-circle" class="w-4 h-4"></i>Signé le {{ $signedAt->format('d/m/Y à H:i') }}</p>
                @else
                <p class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-warn"><i data-lucide="clock" class="w-4 h-4"></i>En attente de signature</p>
                @endif
            </div>
            @endforeach
        </section>

        {{-- Conditions --}}
        <section class="reveal rounded-3xl bg-white border border-line p-5">
            <h2 class="font-bold flex items-center gap-2 mb-3"><i data-lucide="scroll-text" class="w-5 h-5 text-grape"></i> Conditions générales</h2>
            <div class="text-sm text-slate-600 space-y-1.5 whitespace-pre-line">{{ $contract->terms }}</div>
        </section>

        {{-- Action de signature --}}
        @if(! $mySigned)
        <form method="POST" action="{{ route('contracts.sign', $contract) }}" onsubmit="return confirm('Confirmez-vous la signature électronique de ce contrat ?');">
            @csrf
            <button class="btn-press w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-accent to-accent-dark shadow-lg shadow-accent/25 inline-flex items-center justify-center gap-2">
                <i data-lucide="pen-line" class="w-5 h-5"></i>Signer électroniquement en tant que {{ $role === 'employer' ? 'employeur' : 'travailleur' }}
            </button>
        </form>
        @else
        <div class="rounded-2xl bg-muted text-slate-500 px-4 py-3 text-center text-sm font-medium inline-flex items-center justify-center gap-2 w-full"><i data-lucide="check" class="w-4 h-4 text-accent"></i>Vous avez déjà signé ce contrat.</div>
        @endif
    </main>
</div>
@endsection
