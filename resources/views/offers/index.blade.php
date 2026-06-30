@extends('layouts.base')
@section('title', 'Offres d\'emploi — CyaoWork')

@php $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois']; @endphp

@section('body')
<x-public-nav />

<main class="mx-auto max-w-6xl px-4 sm:px-6 pt-28 pb-16">
    <div class="reveal text-center max-w-2xl mx-auto">
        <h1 class="text-3xl sm:text-4xl font-extrabold">Trouvez votre prochaine mission</h1>
        <p class="mt-2 text-slate-600">Parcourez les offres vérifiées partout au Cameroun.</p>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('offers.index') }}" class="reveal mt-6 bg-white rounded-2xl border border-line shadow-sm p-3 grid sm:grid-cols-[1.4fr_1fr_1fr_auto] gap-2.5">
        <label class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary">
            <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Métier, mot-clé…" class="bg-transparent outline-none text-sm w-full" />
        </label>
        <label class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary">
            <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
            <input type="text" name="city" value="{{ request('city') }}" placeholder="Ville" class="bg-transparent outline-none text-sm w-full" />
        </label>
        <select name="category" class="h-12 px-4 rounded-xl bg-muted outline-none text-sm focus:ring-2 focus:ring-primary">
            <option value="">Toutes catégories</option>
            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <button class="btn-press h-12 px-6 rounded-xl bg-primary text-white font-semibold text-sm inline-flex items-center justify-center gap-2"><i data-lucide="search" class="w-4 h-4"></i>Rechercher</button>
    </form>

    <p class="mt-6 text-slate-600"><b class="text-ink">{{ $offers->total() }}</b> offre{{ $offers->total() > 1 ? 's' : '' }}</p>

    {{-- Grille --}}
    <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($offers as $k => $o)
        <article class="reveal card-hov rounded-3xl bg-white border border-line p-5 flex flex-col" style="transition-delay:{{ $k*40 }}ms">
            <div class="flex items-start gap-3">
                <span class="grid place-items-center w-12 h-12 rounded-2xl bg-gradient-to-br {{ $o->category?->gradient ?? 'from-primary to-secondary' }} text-white shadow-lg shrink-0"><i data-lucide="briefcase" class="w-6 h-6"></i></span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold leading-tight">{{ $o->title }} @if($o->is_boosted)<span class="align-middle ml-1 text-[10px] font-bold text-white bg-gradient-to-r from-amber to-orange-500 rounded-full px-1.5 py-0.5">BOOST</span>@endif</h3>
                    <p class="text-sm text-slate-500">{{ $o->category?->name }}</p>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
                <span class="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose"></i>{{ $o->city ?? '—' }}</span>
                <span class="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1"><i data-lucide="wallet" class="w-3.5 h-3.5 text-accent"></i>{{ number_format($o->salary_amount, 0, ',', ' ') }} FCFA/{{ $periodFr[$o->salary_period] ?? $o->salary_period }}</span>
            </div>
            <div class="mt-4 pt-4 border-t border-line flex items-center gap-2">
                <a href="{{ route('offers.show', $o) }}" class="btn-press flex-1 h-10 rounded-xl border border-line text-sm font-semibold hover:border-primary hover:text-primary inline-flex items-center justify-center gap-2 transition-colors">Détails</a>
                @auth
                    @if(auth()->user()->isWorker())
                        @if(in_array($o->id, $appliedIds))
                        <span class="h-10 px-3 rounded-xl bg-accent/10 text-accent-dark text-sm font-semibold inline-flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4"></i>Postulé</span>
                        @else
                        <form method="POST" action="{{ route('worker.apply', $o) }}">@csrf
                            <button class="btn-press h-10 px-4 rounded-xl bg-accent text-white text-sm font-semibold inline-flex items-center gap-1.5 shadow-md shadow-accent/25"><i data-lucide="zap" class="w-4 h-4"></i>Postuler</button>
                        </form>
                        @endif
                    @endif
                @endauth
            </div>
        </article>
        @empty
        <div class="sm:col-span-2 lg:col-span-3 rounded-3xl border border-dashed border-line bg-white p-12 text-center text-slate-400">
            <i data-lucide="search-x" class="w-10 h-10 mx-auto"></i>
            <p class="mt-3 font-medium">Aucune offre ne correspond à votre recherche.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $offers->links() }}</div>
</main>
@endsection
