@extends('layouts.base')
@section('title', 'Mes contrats — CyaoWork')

@section('body')
<div class="min-h-dvh bg-[#F4F6FB]">
    <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
        <a href="{{ route('worker.dashboard') }}" class="grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Retour"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-bold text-lg">Mes contrats</h1>
    </header>

    <main class="mx-auto max-w-3xl p-4 sm:p-6 space-y-4">
        @if(session('status'))
        <div class="flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
        </div>
        @endif

        @forelse($contracts as $c)
        @php
            $offer = $c->application->jobOffer;
            $signed = (bool) $c->worker_signed_at;
            $full = $c->isFullySigned();
            $state = $full ? ['t' => 'Finalisé', 'c' => 'text-accent-dark bg-accent/10', 'i' => 'check-check']
                   : ($signed ? ['t' => 'En attente employeur', 'c' => 'text-warn bg-warn/10', 'i' => 'clock']
                              : ['t' => 'À signer', 'c' => 'text-primary bg-primary/10', 'i' => 'pen-line']);
        @endphp
        <article class="reveal card-hov rounded-3xl bg-white border border-line p-5">
            <div class="flex items-start gap-4">
                <span class="grid place-items-center w-12 h-12 rounded-2xl bg-gradient-to-br {{ $offer->category?->gradient ?? 'from-primary to-secondary' }} text-white shadow-lg shrink-0"><i data-lucide="file-signature" class="w-6 h-6"></i></span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-semibold leading-tight">{{ $offer->title }}</h3>
                            <p class="text-sm text-slate-500">{{ $offer->employer->name }} · réf. {{ $c->reference() }}</p>
                        </div>
                        <span class="text-xs font-semibold {{ $state['c'] }} rounded-full px-2.5 py-1 whitespace-nowrap inline-flex items-center gap-1"><i data-lucide="{{ $state['i'] }}" class="w-3.5 h-3.5"></i>{{ $state['t'] }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ number_format($offer->salary_amount, 0, ',', ' ') }} FCFA · {{ $offer->city }}</p>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <a href="{{ route('contracts.show', $c) }}" class="btn-press flex-1 h-10 rounded-xl text-white text-sm font-semibold inline-flex items-center justify-center gap-2 {{ $signed ? 'bg-gradient-to-r from-primary to-secondary' : 'bg-gradient-to-r from-accent to-accent-dark shadow-md shadow-accent/25' }}">
                    <i data-lucide="{{ $signed ? 'eye' : 'pen-line' }}" class="w-4 h-4"></i>{{ $signed ? 'Voir le contrat' : 'Voir / signer' }}
                </a>
                <a href="{{ route('contracts.pdf', $c) }}" class="btn-press h-10 px-4 rounded-xl border border-line text-sm font-semibold hover:border-primary hover:text-primary inline-flex items-center gap-2 transition-colors"><i data-lucide="file-down" class="w-4 h-4"></i>PDF</a>
            </div>
        </article>
        @empty
        <div class="rounded-3xl bg-white border border-line p-12 text-center text-slate-400">
            <i data-lucide="file-x" class="w-10 h-10 mx-auto"></i>
            <p class="mt-3 font-medium">Aucun contrat pour le moment.</p>
            <p class="text-sm">Vos contrats de mission apparaîtront ici une fois une candidature acceptée.</p>
        </div>
        @endforelse
    </main>
</div>
@endsection
