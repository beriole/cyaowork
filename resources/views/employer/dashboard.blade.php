@extends('layouts.base')
@section('title', 'CyaoWork — Espace recruteur')

@php
    $avatar = fn($id, $s = 80, $name = '') => $id
        ? "https://images.unsplash.com/photo-{$id}?w={$s}&h={$s}&fit=crop&q=78"
        : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=1D4ED8&color=fff';
    $offerStatus = [
        'published' => ['t' => 'Publiée',   'c' => 'text-accent-dark bg-accent/10'],
        'filled'    => ['t' => 'Pourvue',   'c' => 'text-primary bg-primary/10'],
        'draft'     => ['t' => 'Brouillon', 'c' => 'text-slate-500 bg-slate-100'],
        'expired'   => ['t' => 'Expirée',   'c' => 'text-warn bg-warn/10'],
        'archived'  => ['t' => 'Archivée',  'c' => 'text-slate-500 bg-slate-100'],
    ];
    $txStatus = ['success' => ['t' => 'Réussi', 'c' => 'text-accent-dark bg-accent/10'], 'pending' => ['t' => 'En attente', 'c' => 'text-warn bg-warn/10'], 'failed' => ['t' => 'Échoué', 'c' => 'text-rose bg-rose/10']];
    $sideItems = [
        ['i' => 'home', 'n' => 'Accueil', 'active' => true],
        ['i' => 'briefcase', 'n' => 'Mes offres', 'badge' => $offers->count()],
        ['i' => 'users', 'n' => 'Candidatures', 'badge' => $offers->sum('applications_count')],
        ['i' => 'search', 'n' => 'Rechercher', 'url' => route('employer.search')],
        ['i' => 'message-circle', 'n' => 'Messagerie'],
        ['i' => 'crown', 'n' => 'Abonnement'],
        ['i' => 'settings', 'n' => 'Paramètres'],
    ];
    $chart = [55, 62, 48, 70, 65, 88, 95]; $chartMax = max($chart);
@endphp

@section('body')
<div class="flex min-h-dvh">
    {{-- SIDEBAR --}}
    <aside class="hidden lg:flex w-64 shrink-0 flex-col border-r border-line bg-white sticky top-0 h-dvh">
        <div class="h-16 flex items-center gap-2 px-5 border-b border-line">
            <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span>
            <span class="text-xl font-head font-bold">Cyao<span class="text-primary">Work</span></span>
        </div>
        <nav class="flex-1 p-3 space-y-1 text-[15px]">
            @foreach($sideItems as $s)
            <a href="{{ $s['url'] ?? '#' }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all {{ ($s['active'] ?? false) ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-muted' }}">
                <i data-lucide="{{ $s['i'] }}" class="w-5 h-5"></i><span class="flex-1">{{ $s['n'] }}</span>
                @if(!empty($s['badge']))<span class="text-xs font-bold {{ ($s['active'] ?? false) ? 'bg-primary text-white' : 'bg-muted text-slate-500' }} rounded-full px-2 py-0.5">{{ $s['badge'] }}</span>@endif
            </a>
            @endforeach
        </nav>
        @if($subscription)
        <div class="p-3 border-t border-line">
            <div class="rounded-2xl bg-gradient-to-br from-accent to-teal text-white p-4">
                <p class="font-semibold flex items-center gap-2"><i data-lucide="crown" class="w-4 h-4"></i> Plan {{ ucfirst($subscription->plan) }}</p>
                <p class="text-sm text-white/85 mt-1">Expire {{ optional($subscription->ends_at)->diffForHumans() }}.</p>
                <form method="POST" action="{{ route('employer.subscription.renew') }}" onsubmit="return confirm('Renouveler le Plan Pro pour 15 000 FCFA (Mobile Money) ?');">
                    @csrf
                    <button class="btn-press mt-3 w-full h-9 rounded-lg bg-white text-accent-dark text-sm font-semibold">Renouveler</button>
                </form>
            </div>
        </div>
        @endif
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
            <span class="lg:hidden grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span>
            <label class="hidden sm:flex items-center gap-2 h-10 px-3 rounded-xl bg-muted ml-1 w-72 focus-within:ring-2 focus-within:ring-primary">
                <i data-lucide="search" class="w-4 h-4 text-slate-400"></i><input placeholder="Rechercher un travailleur…" class="bg-transparent outline-none text-sm w-full" />
            </label>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('employer.offer.create') }}" class="btn-press hidden sm:inline-flex items-center gap-2 h-10 px-4 rounded-xl text-white text-sm font-semibold bg-gradient-to-r from-primary to-secondary shadow-md shadow-primary/25"><i data-lucide="plus" class="w-4 h-4"></i>Publier une offre</a>
                <x-notification-bell />
                <x-logout-button />
                <div class="flex items-center gap-2"><div class="w-9 h-9 rounded-full bg-gradient-to-br from-grape to-rose grid place-items-center text-white font-semibold">{{ \Illuminate\Support\Str::of($employer->name)->explode(' ')->map(fn($w) => mb_substr($w,0,1))->take(2)->implode('') }}</div><div class="hidden sm:block leading-tight"><p class="text-sm font-semibold">{{ $employer->name }}</p><p class="text-xs text-slate-500">Recruteur</p></div></div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 pb-24 lg:pb-6 space-y-6">
            @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
            </div>
            @endif
            <section class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-accent via-accent-dark to-teal text-white p-6 sm:p-8">
                <div class="blob w-72 h-72 bg-emerald-300/40 -top-20 -right-10"></div>
                <div class="blob w-56 h-56 bg-primary/40 -bottom-20 left-10" style="animation-delay:-5s"></div>
                <div class="relative grid sm:grid-cols-[1fr_auto] gap-6 items-center">
                    <div>
                        <p class="text-white/80">Bonjour {{ $employer->name }} 👋</p>
                        <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold">Gérez vos recrutements</h1>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            @if($subscription)<span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur px-3 py-2 text-sm font-semibold"><i data-lucide="crown" class="w-4 h-4 text-amber"></i> Plan {{ ucfirst($subscription->plan) }} actif</span>@endif
                            @if($employer->is_verified)<span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur px-3 py-2 text-sm font-semibold"><i data-lucide="badge-check" class="w-4 h-4"></i> Entreprise vérifiée</span>@endif
                        </div>
                    </div>
                    <a href="{{ route('employer.offer.create') }}" class="btn-press inline-flex items-center justify-center gap-2 h-12 px-6 rounded-xl bg-white text-accent-dark font-semibold shadow-lg"><i data-lucide="plus" class="w-5 h-5"></i>Nouvelle offre</a>
                </div>
            </section>

            <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($stats as $k => $s)
                <div class="reveal card-hov rounded-3xl bg-white border border-line p-5" style="transition-delay:{{ $k*70 }}ms">
                    <span class="grid place-items-center w-11 h-11 rounded-2xl bg-gradient-to-br {{ $s['g'] }} text-white shadow-lg"><i data-lucide="{{ $s['i'] }}" class="w-5 h-5"></i></span>
                    <p class="mt-4 text-3xl font-extrabold font-head">{{ $s['val'] }}</p>
                    <p class="text-sm text-slate-500">{{ $s['label'] }}</p>
                    <p class="mt-0.5 text-xs font-medium text-slate-400">{{ $s['sub'] }}</p>
                </div>
                @endforeach
            </section>

            <div class="grid lg:grid-cols-3 gap-6">
                <section class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="briefcase" class="w-5 h-5 text-primary"></i> Mes offres</h2>
                    </div>
                    <div class="space-y-4">
                        @foreach($offers as $k => $o)
                        @php $st = $offerStatus[$o->status] ?? $offerStatus['draft']; @endphp
                        <article class="reveal card-hov rounded-3xl bg-white border border-line p-5 hover:shadow-xl" style="transition-delay:{{ $k*80 }}ms">
                            <div class="flex items-start gap-4">
                                <span class="grid place-items-center w-12 h-12 rounded-2xl bg-gradient-to-br {{ $o->category?->gradient ?? 'from-primary to-secondary' }} text-white shadow-lg shrink-0"><i data-lucide="briefcase" class="w-6 h-6"></i></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div><h3 class="font-semibold leading-tight">{{ $o->title }} @if($o->is_boosted)<span class="align-middle ml-1 text-[10px] font-bold text-white bg-gradient-to-r from-amber to-orange-500 rounded-full px-1.5 py-0.5">BOOST</span>@endif</h3>
                                            <p class="text-sm text-slate-500">{{ $o->category?->name }}</p></div>
                                        <span class="text-xs font-semibold {{ $st['c'] }} rounded-full px-2.5 py-1 whitespace-nowrap">{{ $st['t'] }}</span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                                        <span class="inline-flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4 text-primary"></i><b class="text-ink">{{ $o->applications_count }}</b> candidats</span>
                                        <span class="inline-flex items-center gap-1.5"><i data-lucide="eye" class="w-4 h-4 text-grape"></i><b class="text-ink">{{ $o->views }}</b> vues</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                <a href="{{ route('employer.offer.candidates', $o) }}" class="btn-press flex-1 h-10 rounded-xl text-white text-sm font-semibold inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-secondary shadow-md shadow-primary/25"><i data-lucide="users" class="w-4 h-4"></i>Voir les candidats ({{ $o->applications_count }})</a>
                                @if($o->status !== 'filled')
                                    @if($o->is_boosted)
                                    <span class="h-10 px-3 rounded-xl border border-amber/30 bg-amber/10 text-amber text-sm font-semibold inline-flex items-center gap-1.5"><i data-lucide="zap" class="w-4 h-4"></i>Boosté</span>
                                    @else
                                    <form method="POST" action="{{ route('employer.boost', $o) }}" onsubmit="return confirm('Booster cette offre pour 2 500 FCFA (Mobile Money) ?');">
                                        @csrf
                                        <button class="btn-press h-10 px-3 rounded-xl border border-line text-sm font-semibold hover:border-amber hover:text-amber inline-flex items-center gap-1.5"><i data-lucide="zap" class="w-4 h-4"></i>Booster</button>
                                    </form>
                                    @endif
                                @endif
                                <a href="{{ route('employer.offer.edit', $o) }}" class="btn-press w-10 h-10 grid place-items-center rounded-xl border border-line hover:border-primary hover:text-primary" aria-label="Modifier l'offre"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                <form method="POST" action="{{ route('employer.offer.archive', $o) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn-press w-10 h-10 grid place-items-center rounded-xl border border-line hover:border-warn hover:text-warn transition-colors" aria-label="{{ $o->status === 'archived' ? 'Republier' : 'Archiver' }}" title="{{ $o->status === 'archived' ? 'Republier' : 'Archiver' }}"><i data-lucide="{{ $o->status === 'archived' ? 'archive-restore' : 'archive' }}" class="w-4 h-4"></i></button>
                                </form>
                                <form method="POST" action="{{ route('employer.offer.destroy', $o) }}" onsubmit="return confirm('Supprimer définitivement l\'offre « {{ $o->title }} » et ses candidatures ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn-press w-10 h-10 grid place-items-center rounded-xl border border-line hover:border-rose hover:text-rose transition-colors" aria-label="Supprimer" title="Supprimer"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="reveal rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5">
                        <div class="flex items-center justify-between"><h2 class="font-bold flex items-center gap-2"><i data-lucide="bar-chart-3" class="w-5 h-5 text-accent-light"></i> Vues (7 j)</h2><span class="text-sm font-bold text-accent-light inline-flex items-center gap-1"><i data-lucide="trending-up" class="w-4 h-4"></i>+18%</span></div>
                        <div class="mt-4 flex items-end justify-between gap-2 h-28">
                            @foreach($chart as $i => $d)
                            <div class="flex-1 flex flex-col items-center gap-1.5 group">
                                <div class="w-full rounded-t-lg bg-gradient-to-t from-accent to-accent-light" style="height:{{ $d / $chartMax * 100 }}%"></div>
                                <span class="text-xs text-white/50">{{ ['L','M','M','J','V','S','D'][$i] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="reveal rounded-3xl bg-white border border-line p-5">
                        <div class="flex items-center justify-between mb-3"><h2 class="font-bold flex items-center gap-2"><i data-lucide="users" class="w-5 h-5 text-accent"></i> Candidatures récentes</h2></div>
                        <ul class="space-y-2">
                            @forelse($applications as $a)
                            @php $wp = $a->worker->workerProfile; $compat = $wp ? (int) round($wp->rating_avg / 5 * 100) : 80; @endphp
                            <li class="flex items-center gap-3 p-2 rounded-xl hover:bg-muted transition-colors">
                                <div class="relative shrink-0">
                                    <img src="{{ $avatar($a->worker->avatar, 72, $a->worker->name) }}" class="w-10 h-10 rounded-full object-cover" alt="{{ $a->worker->name }}" loading="lazy" />
                                    @if($a->worker->is_verified)<span class="absolute -bottom-0.5 -right-0.5 grid place-items-center w-4 h-4 rounded-full bg-accent text-white ring-2 ring-white"><i data-lucide="check" class="w-2.5 h-2.5"></i></span>@endif
                                </div>
                                <div class="flex-1 min-w-0"><p class="font-medium text-sm truncate">{{ $a->worker->name }}</p><p class="text-xs text-slate-500 truncate">{{ $a->jobOffer->title }} · <span class="text-accent-dark font-semibold">{{ $compat }}% compat.</span></p></div>
                                <div class="flex items-center gap-1 shrink-0">
                                    @if($a->status === 'accepted')
                                        @if($a->contract)
                                        <a href="{{ route('contracts.pdf', $a->contract) }}" class="btn-press h-8 px-2.5 grid place-items-center rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white text-xs font-semibold transition-colors" title="Télécharger le contrat"><i data-lucide="file-down" class="w-4 h-4"></i></a>
                                        @else
                                        <form method="POST" action="{{ route('employer.contract', $a) }}">
                                            @csrf
                                            <button class="btn-press h-8 px-2.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white text-xs font-semibold inline-flex items-center gap-1 transition-colors" title="Générer le contrat"><i data-lucide="file-signature" class="w-4 h-4"></i>Contrat</button>
                                        </form>
                                        @endif
                                        <span class="w-8 h-8 grid place-items-center rounded-lg bg-accent text-white" title="Acceptée"><i data-lucide="check" class="w-4 h-4"></i></span>
                                    @else
                                    <form method="POST" action="{{ route('employer.application.decision', [$a, 'accepter']) }}">
                                        @csrf
                                        <button class="btn-press w-8 h-8 grid place-items-center rounded-lg bg-accent/10 text-accent-dark hover:bg-accent hover:text-white transition-colors" title="Accepter"><i data-lucide="check" class="w-4 h-4"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('employer.application.decision', [$a, 'refuser']) }}">
                                        @csrf
                                        <button class="btn-press w-8 h-8 grid place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-rose hover:text-white transition-colors" title="Refuser"><i data-lucide="x" class="w-4 h-4"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </li>
                            @empty
                            <li class="text-sm text-slate-400 text-center py-4">Aucune candidature pour l'instant.</li>
                            @endforelse
                        </ul>
                    </div>

                    @if($transactions->isNotEmpty())
                    <div class="reveal rounded-3xl bg-white border border-line p-5">
                        <div class="flex items-center justify-between mb-3"><h2 class="font-bold flex items-center gap-2"><i data-lucide="wallet" class="w-5 h-5 text-accent"></i> Transactions</h2></div>
                        <ul class="space-y-2">
                            @foreach($transactions as $t)
                            @php $ts = $txStatus[$t->status] ?? $txStatus['pending']; $prov = $t->provider === 'momo' ? ['MTN','bg-amber-400'] : ['ORG','bg-orange-500']; @endphp
                            <li class="flex items-center gap-3 p-2 rounded-xl hover:bg-muted transition-colors">
                                <span class="grid place-items-center w-9 h-9 rounded-lg {{ $prov[1] }} text-white font-bold text-[11px] shrink-0">{{ $prov[0] }}</span>
                                <div class="flex-1 min-w-0"><p class="text-sm font-medium truncate">{{ ucfirst($t->type) }}</p><p class="text-xs text-slate-500">{{ $t->provider === 'momo' ? 'MTN' : 'Orange' }} Money</p></div>
                                <div class="text-right shrink-0"><p class="text-sm font-bold">{{ number_format($t->amount, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></p><span class="text-[11px] font-semibold {{ $ts['c'] }} rounded-full px-1.5 py-0.5">{{ $ts['t'] }}</span></div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</div>

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/90 backdrop-blur-xl border-t border-line flex justify-around items-center h-16 px-2" style="padding-bottom:env(safe-area-inset-bottom)">
    <a href="#" class="flex flex-col items-center gap-0.5 text-primary"><i data-lucide="home" class="w-6 h-6"></i><span class="text-[11px] font-semibold">Accueil</span></a>
    <a href="#" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="briefcase" class="w-6 h-6"></i><span class="text-[11px]">Offres</span></a>
    <a href="#" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="users" class="w-6 h-6"></i><span class="text-[11px]">Candid.</span></a>
    <a href="{{ route('employer.search') }}" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="search" class="w-6 h-6"></i><span class="text-[11px]">Recherche</span></a>
    <a href="#" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="message-circle" class="w-6 h-6"></i><span class="text-[11px]">Messages</span></a>
</nav>
@endsection
