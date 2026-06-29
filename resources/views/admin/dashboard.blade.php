@extends('layouts.base')
@section('title', 'CyaoWork — Administration')

@php
    $avatar = fn($id, $s = 96, $name = '') => $id
        ? "https://images.unsplash.com/photo-{$id}?w={$s}&h={$s}&fit=crop&q=78"
        : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=1D4ED8&color=fff';
    $sideItems = [
        ['i' => 'layout-dashboard', 'n' => "Vue d'ensemble", 'active' => true],
        ['i' => 'user-check', 'n' => 'Vérifications', 'badge' => $verifications->count()],
        ['i' => 'users', 'n' => 'Utilisateurs'],
        ['i' => 'briefcase', 'n' => 'Offres'],
        ['i' => 'flag', 'n' => 'Modération', 'badge' => $reports->count() ?: null],
        ['i' => 'wallet', 'n' => 'Paiements'],
        ['i' => 'bar-chart-3', 'n' => 'Statistiques'],
        ['i' => 'settings', 'n' => 'Paramètres'],
    ];
    $chart = [55, 62, 48, 70, 65, 88, 95]; $chartMax = max($chart);
    $txStatus = ['success' => ['t' => 'Réussi', 'c' => 'text-accent-dark bg-accent/10'], 'pending' => ['t' => 'En attente', 'c' => 'text-warn bg-warn/10'], 'failed' => ['t' => 'Échoué', 'c' => 'text-rose bg-rose/10']];
@endphp

@section('body')
<div class="flex min-h-dvh">
    {{-- SIDEBAR (dark) --}}
    <aside class="hidden lg:flex w-64 shrink-0 flex-col bg-slate-900 text-slate-300 sticky top-0 h-dvh">
        <div class="h-16 flex items-center gap-2 px-5 border-b border-white/10">
            <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span>
            <span class="text-xl font-head font-bold text-white">Cyao<span class="text-primary-light">Work</span></span>
            <span class="ml-auto text-[10px] font-bold tracking-wider text-slate-400 bg-white/10 rounded px-1.5 py-0.5">ADMIN</span>
        </div>
        <nav class="flex-1 p-3 space-y-1 text-[15px] overflow-y-auto">
            @foreach($sideItems as $s)
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all {{ ($s['active'] ?? false) ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <i data-lucide="{{ $s['i'] }}" class="w-5 h-5"></i><span class="flex-1">{{ $s['n'] }}</span>
                @if(!empty($s['badge']))<span class="text-xs font-bold {{ ($s['active'] ?? false) ? 'bg-primary text-white' : 'bg-warn text-white' }} rounded-full px-2 py-0.5">{{ $s['badge'] }}</span>@endif
            </a>
            @endforeach
        </nav>
        <div class="p-3 border-t border-white/10 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-grape to-rose grid place-items-center text-white font-semibold text-sm">AD</div>
            <div class="leading-tight flex-1"><p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p><p class="text-xs text-slate-400">Super-admin</p></div>
            <x-logout-button :dark="true" class="ml-auto" />
        </div>
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-40 h-16 bg-white/85 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
            <span class="lg:hidden grid place-items-center w-9 h-9 rounded-xl bg-slate-900 text-white"><i data-lucide="shield" class="w-5 h-5"></i></span>
            <h1 class="font-bold text-lg">Tableau de bord</h1>
            <div class="ml-auto flex items-center gap-2">
                <span class="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold text-accent-dark bg-accent/10 rounded-full px-3 py-1.5"><span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>Système OK</span>
                <button class="relative grid place-items-center w-10 h-10 rounded-xl hover:bg-muted"><i data-lucide="bell" class="w-5 h-5 text-slate-600"></i></button>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 space-y-6">
            @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
            </div>
            @endif
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($kpis as $k => $s)
                <div class="reveal card-hov rounded-3xl bg-white border border-line p-5" style="transition-delay:{{ $k*70 }}ms">
                    <span class="grid place-items-center w-11 h-11 rounded-2xl bg-gradient-to-br {{ $s['g'] }} text-white shadow-lg"><i data-lucide="{{ $s['i'] }}" class="w-5 h-5"></i></span>
                    <p class="mt-4 text-2xl sm:text-3xl font-extrabold font-head">{{ $s['val'] }}@if(!empty($s['unit']))<span class="text-sm font-semibold text-slate-400"> {{ $s['unit'] }}</span>@endif</p>
                    <p class="text-sm text-slate-500">{{ $s['label'] }}</p>
                    <p class="mt-0.5 text-xs font-medium text-slate-400">{{ $s['sub'] }}</p>
                </div>
                @endforeach
            </section>

            <div class="grid lg:grid-cols-3 gap-6">
                <section class="lg:col-span-2 space-y-4">
                    <div class="reveal rounded-3xl bg-white border border-line p-5">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="user-check" class="w-5 h-5 text-primary"></i> File de vérification d'identité</h2>
                            <span class="text-xs font-bold text-white bg-warn rounded-full px-2.5 py-1">{{ $verifications->count() }} en attente</span>
                        </div>
                        <ul class="space-y-3">
                            @forelse($verifications as $w)
                            <li class="flex items-center gap-3 p-3 rounded-2xl border border-line hover:bg-muted/50 transition-colors">
                                <img src="{{ $avatar($w->photo, 96, $w->user->name) }}" class="w-12 h-12 rounded-xl object-cover" alt="{{ $w->user->name }}" loading="lazy" />
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm truncate">{{ $w->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $w->headline }} · {{ $w->city }}</p>
                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-primary bg-primary/10 rounded-full px-2 py-0.5"><i data-lucide="id-card" class="w-3 h-3"></i>CNI</span>
                                        <span class="text-[11px] text-slate-400">{{ $w->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <form method="POST" action="{{ route('admin.verifications.approve', $w) }}">
                                        @csrf
                                        <button class="btn-press h-9 px-3 rounded-lg bg-accent/10 text-accent-dark hover:bg-accent hover:text-white text-sm font-semibold inline-flex items-center gap-1.5 transition-colors"><i data-lucide="check" class="w-4 h-4"></i>Valider</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.verifications.reject', $w) }}" onsubmit="return confirm('Rejeter la vérification de {{ $w->user->name }} ?');">
                                        @csrf
                                        <button class="btn-press w-9 h-9 grid place-items-center rounded-lg bg-slate-100 text-slate-500 hover:bg-rose hover:text-white transition-colors" aria-label="Rejeter"><i data-lucide="x" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-8 text-slate-400"><i data-lucide="check-check" class="w-8 h-8 mx-auto text-accent"></i><p class="mt-2 text-sm">Aucune vérification en attente. Tout est à jour !</p></li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="reveal rounded-3xl bg-white border border-line p-5">
                        <h2 class="text-lg font-bold flex items-center gap-2 mb-4"><i data-lucide="flag" class="w-5 h-5 text-rose"></i> Signalements à modérer</h2>
                        <ul class="space-y-3">
                            @forelse($reports as $r)
                            <li class="flex items-center gap-3 p-3 rounded-2xl border border-line">
                                <span class="grid place-items-center w-10 h-10 rounded-xl text-rose bg-rose/10 shrink-0"><i data-lucide="message-square-warning" class="w-5 h-5"></i></span>
                                <div class="flex-1 min-w-0"><p class="text-sm font-medium">Avis signalé</p><p class="text-xs text-slate-500 truncate">« {{ \Illuminate\Support\Str::limit($r->comment, 50) }} » — sur {{ $r->reviewee->name }}</p></div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button class="btn-press h-9 px-3 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-semibold">Ignorer</button>
                                    <button class="btn-press h-9 px-3 rounded-lg bg-rose/10 text-rose hover:bg-rose hover:text-white text-sm font-semibold transition-colors">Sanctionner</button>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-8 text-slate-400"><i data-lucide="shield-check" class="w-8 h-8 mx-auto text-accent"></i><p class="mt-2 text-sm">Aucun signalement en attente.</p></li>
                            @endforelse
                        </ul>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="reveal rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 text-white p-5">
                        <div class="flex items-center justify-between"><h2 class="font-bold flex items-center gap-2"><i data-lucide="line-chart" class="w-5 h-5 text-accent-light"></i> Revenus (7 j)</h2><span class="text-sm font-bold text-accent-light inline-flex items-center gap-1"><i data-lucide="trending-up" class="w-4 h-4"></i>+23%</span></div>
                        <p class="mt-2 text-3xl font-extrabold font-head">{{ number_format($revenue, 0, ',', ' ') }} <span class="text-sm font-semibold text-white/50">FCFA</span></p>
                        <div class="mt-4 flex items-end justify-between gap-1.5 h-24">
                            @foreach($chart as $i => $d)
                            <div class="flex-1 rounded-t bg-gradient-to-t from-accent to-accent-light" style="height:{{ $d / $chartMax * 100 }}%"></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="reveal rounded-3xl bg-white border border-line p-5">
                        <div class="flex items-center justify-between mb-3"><h2 class="font-bold flex items-center gap-2"><i data-lucide="wallet" class="w-5 h-5 text-accent"></i> Transactions</h2></div>
                        <ul class="space-y-2">
                            @forelse($transactions as $t)
                            @php $ts = $txStatus[$t->status] ?? $txStatus['pending']; $prov = $t->provider === 'momo' ? ['MTN','bg-amber-400'] : ['ORG','bg-orange-500']; @endphp
                            <li class="flex items-center gap-3 p-2 rounded-xl hover:bg-muted transition-colors">
                                <span class="grid place-items-center w-9 h-9 rounded-lg {{ $prov[1] }} text-white font-bold text-[11px] shrink-0">{{ $prov[0] }}</span>
                                <div class="flex-1 min-w-0"><p class="text-sm font-medium truncate">{{ ucfirst($t->type) }}</p><p class="text-xs text-slate-500">{{ $t->provider === 'momo' ? 'MTN' : 'Orange' }} Money · {{ $t->user->name }}</p></div>
                                <div class="text-right shrink-0"><p class="text-sm font-bold">{{ number_format($t->amount, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA</span></p><span class="text-[11px] font-semibold {{ $ts['c'] }} rounded-full px-1.5 py-0.5">{{ $ts['t'] }}</span></div>
                            </li>
                            @empty
                            <li class="text-sm text-slate-400 text-center py-4">Aucune transaction.</li>
                            @endforelse
                        </ul>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>
@endsection
