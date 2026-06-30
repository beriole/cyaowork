@extends('layouts.base')
@section('title', 'Candidats — '.$offer->title)

@php
    $avatar = fn($id, $s = 80, $name = '') => $id
        ? "https://images.unsplash.com/photo-{$id}?w={$s}&h={$s}&fit=crop&q=78"
        : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=17266A&color=fff';
    $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois', 'intervention' => 'intervention'];
    $appStatus = [
        'sent'      => ['t' => 'Nouvelle',   'c' => 'text-primary bg-primary/10'],
        'seen'      => ['t' => 'Vue',        'c' => 'text-slate-500 bg-slate-100'],
        'interview' => ['t' => 'Entretien',  'c' => 'text-warn bg-warn/10'],
        'accepted'  => ['t' => 'Acceptée',   'c' => 'text-accent-dark bg-accent/10'],
        'rejected'  => ['t' => 'Refusée',    'c' => 'text-rose bg-rose/10'],
    ];
@endphp

@section('body')
<div class="min-h-dvh bg-[#F4F6FB]">
    <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
        <a href="{{ route('employer.dashboard') }}" class="grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Retour"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <div class="min-w-0">
            <h1 class="font-bold text-lg truncate">Candidats</h1>
            <p class="text-xs text-slate-500 truncate">{{ $offer->title }}</p>
        </div>
        <a href="{{ route('employer.dashboard') }}" class="ml-auto text-sm font-semibold text-primary hover:underline">Tableau de bord</a>
    </header>

    <main class="mx-auto max-w-4xl p-4 sm:p-6 space-y-6">
        @if(session('status'))
        <div class="flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
        </div>
        @endif

        {{-- Résumé de l'offre --}}
        <section class="reveal rounded-3xl bg-white border border-line p-5 flex items-center gap-4">
            <span class="grid place-items-center w-14 h-14 rounded-2xl bg-gradient-to-br {{ $offer->category?->gradient ?? 'from-primary to-secondary' }} text-white shadow-lg shrink-0"><i data-lucide="briefcase" class="w-7 h-7"></i></span>
            <div class="flex-1 min-w-0">
                <h2 class="font-bold leading-tight">{{ $offer->title }} @if($offer->is_boosted)<span class="ml-1 text-[10px] font-bold text-white bg-gradient-to-r from-amber to-orange-500 rounded-full px-1.5 py-0.5 align-middle">BOOST</span>@endif</h2>
                <p class="text-sm text-slate-500">{{ $offer->category?->name }} · {{ $offer->city }} · {{ number_format($offer->salary_amount, 0, ',', ' ') }} FCFA/{{ $periodFr[$offer->salary_period] ?? $offer->salary_period }}</p>
            </div>
            <div class="text-center shrink-0">
                <p class="text-2xl font-extrabold font-head">{{ $offer->applications_count }}</p>
                <p class="text-xs text-slate-500">candidat{{ $offer->applications_count > 1 ? 's' : '' }}</p>
            </div>
        </section>

        {{-- Liste des candidats --}}
        <section class="space-y-4">
            @forelse($applications as $a)
            @php $wp = $a->worker->workerProfile; $compat = $wp ? (int) round($wp->rating_avg / 5 * 100) : 80; $st = $appStatus[$a->status] ?? $appStatus['sent']; @endphp
            <article class="reveal card-hov rounded-3xl bg-white border border-line p-5">
                <div class="flex items-start gap-4">
                    <div class="relative shrink-0">
                        <img src="{{ $avatar($a->worker->avatar, 120, $a->worker->name) }}" class="w-16 h-16 rounded-2xl object-cover" alt="{{ $a->worker->name }}" loading="lazy" />
                        @if($a->worker->is_verified)<span class="absolute -bottom-1.5 -right-1.5 grid place-items-center w-6 h-6 rounded-full bg-accent text-white ring-2 ring-white" title="Vérifié"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>@endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                @if($wp)
                                <a href="{{ route('workers.show', $wp) }}" class="font-semibold hover:text-primary transition-colors">{{ $a->worker->name }}</a>
                                @else
                                <h3 class="font-semibold">{{ $a->worker->name }}</h3>
                                @endif
                                <p class="text-sm text-slate-500">{{ $wp?->headline }}@if($wp?->city) · {{ $wp->city }}@endif</p>
                            </div>
                            <span class="text-xs font-semibold {{ $st['c'] }} rounded-full px-2.5 py-1 whitespace-nowrap">{{ $st['t'] }}</span>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                            <span class="inline-flex items-center gap-1"><i data-lucide="star" class="w-4 h-4 text-amber fill-current"></i><b class="text-ink">{{ number_format($wp?->rating_avg ?? 0, 1, ',', ' ') }}</b> ({{ $wp?->reviews_count ?? 0 }})</span>
                            <span class="inline-flex items-center gap-1 text-accent-dark font-semibold"><i data-lucide="sparkles" class="w-4 h-4"></i>{{ $compat }}% compat.</span>
                            @if($wp?->experience_years)<span class="inline-flex items-center gap-1"><i data-lucide="briefcase" class="w-4 h-4 text-grape"></i>{{ $wp->experience_years }} ans</span>@endif
                        </div>
                        @if($a->message)<p class="mt-2 text-sm text-slate-600 bg-muted/60 rounded-xl px-3 py-2">« {{ $a->message }} »</p>@endif
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if($a->status === 'accepted')
                        @if($a->contract)
                        <a href="{{ route('contracts.show', $a->contract) }}" class="btn-press h-10 px-4 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white text-sm font-semibold inline-flex items-center gap-2 transition-colors"><i data-lucide="file-signature" class="w-4 h-4"></i>{{ $a->contract->isFullySigned() ? 'Voir le contrat' : 'Voir / signer le contrat' }}</a>
                        @else
                        <form method="POST" action="{{ route('employer.contract', $a) }}">
                            @csrf
                            <button class="btn-press h-10 px-4 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-white text-sm font-semibold inline-flex items-center gap-2 transition-colors"><i data-lucide="file-signature" class="w-4 h-4"></i>Générer le contrat</button>
                        </form>
                        @endif
                        <span class="inline-flex items-center gap-1.5 h-10 px-4 rounded-xl bg-accent/10 text-accent-dark text-sm font-semibold"><i data-lucide="check-circle" class="w-4 h-4"></i>Candidat retenu</span>
                    @else
                        <form method="POST" action="{{ route('employer.application.decision', [$a, 'accepter']) }}">
                            @csrf
                            <button class="btn-press h-10 px-4 rounded-xl bg-accent text-white text-sm font-semibold inline-flex items-center gap-2 shadow-md shadow-accent/25"><i data-lucide="check" class="w-4 h-4"></i>Accepter</button>
                        </form>
                        <form method="POST" action="{{ route('employer.application.decision', [$a, 'refuser']) }}">
                            @csrf
                            <button class="btn-press h-10 px-4 rounded-xl border border-line text-slate-600 hover:border-rose hover:text-rose text-sm font-semibold inline-flex items-center gap-2 transition-colors"><i data-lucide="x" class="w-4 h-4"></i>Refuser</button>
                        </form>
                    @endif
                    <a href="{{ route('messaging.index') }}" class="btn-press h-10 px-4 rounded-xl border border-line text-slate-600 hover:border-primary hover:text-primary text-sm font-semibold inline-flex items-center gap-2 transition-colors ml-auto"><i data-lucide="message-circle" class="w-4 h-4"></i>Message</a>
                </div>
            </article>
            @empty
            <div class="rounded-3xl bg-white border border-line p-10 text-center text-slate-400">
                <i data-lucide="users" class="w-10 h-10 mx-auto"></i>
                <p class="mt-3 font-medium">Aucune candidature pour le moment.</p>
                <p class="text-sm">Boostez votre offre pour gagner en visibilité.</p>
            </div>
            @endforelse
        </section>
    </main>
</div>
@endsection
