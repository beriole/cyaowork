@extends('layouts.base')
@section('title', 'CyaoWork — Rechercher des travailleurs')

@push('head')
@endpush

@php
    $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois', 'intervention' => 'intervention'];
    $stars = function ($r) {
        $r = (float) $r; $out = ''; $full = floor($r); $half = ($r - $full) >= .5;
        for ($k = 0; $k < $full; $k++) $out .= '<i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>';
        if ($half) $out .= '<i data-lucide="star-half" class="w-3.5 h-3.5 fill-current"></i>';
        return $out;
    };
    $selectedCats = array_filter((array) ($filters['categories'] ?? []));
    $mapPoints = $workers->filter(fn ($w) => $w->latitude && $w->longitude)->map(fn ($w) => [
        'name' => $w->user->name, 'job' => $w->headline, 'rate' => number_format($w->rating_avg, 1, ',', ' '),
        'pay' => number_format($w->expected_salary, 0, ',', ' ').' FCFA', 'lat' => $w->latitude, 'lng' => $w->longitude,
        'photo' => $w->photoUrl(60),
    ])->values();
@endphp

@section('body')
{{-- TOPBAR --}}
<header class="sticky top-0 z-[1000] h-16 bg-white/85 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
        <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span>
        <span class="text-xl font-head font-bold hidden xs:block">Cyao<span class="text-primary">Work</span></span>
    </a>
    <nav class="hidden lg:flex items-center gap-6 ml-6 text-[15px] font-medium text-slate-600">
        <a href="#" class="text-primary font-semibold">Rechercher</a>
        <a href="{{ route('employer.dashboard') }}" class="hover:text-primary">Mes offres</a>
        <a href="{{ route('employer.dashboard') }}" class="hover:text-primary">Candidatures</a>
    </nav>
    <div class="flex items-center gap-2 ml-auto">
        <a href="#" class="btn-press hidden sm:inline-flex items-center gap-2 h-11 px-4 rounded-xl text-white text-sm font-semibold bg-gradient-to-r from-primary to-secondary shadow-md shadow-primary/25"><i data-lucide="plus" class="w-4 h-4"></i>Publier une offre</a>
        <div class="flex items-center gap-2"><div class="w-9 h-9 rounded-full bg-gradient-to-br from-grape to-rose grid place-items-center text-white font-semibold">MT</div><div class="hidden sm:block leading-tight"><p class="text-sm font-semibold">Mme Tchoua</p><p class="text-xs text-slate-500">Recruteur</p></div></div>
    </div>
</header>

<form method="GET" action="{{ route('employer.search') }}">
{{-- SEARCH BAR --}}
<div class="bg-white border-b border-line sticky top-16 z-[900]">
    <div class="mx-auto max-w-[1500px] px-4 sm:px-6 py-3 flex items-center gap-2.5">
        <label class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted flex-1 focus-within:ring-2 focus-within:ring-primary">
            <i data-lucide="search" class="w-5 h-5 text-primary"></i>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Métier (ménagère, plombier…)" class="bg-transparent outline-none text-[15px] w-full" />
        </label>
        <label class="hidden sm:flex items-center gap-2 h-12 px-4 rounded-xl bg-muted w-48 focus-within:ring-2 focus-within:ring-primary">
            <i data-lucide="map-pin" class="w-5 h-5 text-rose"></i>
            <input name="city" value="{{ $filters['city'] ?? '' }}" placeholder="Ville" class="bg-transparent outline-none text-[15px] w-full" />
        </label>
        <button class="btn-press h-12 px-5 rounded-xl text-white font-semibold inline-flex items-center gap-2 bg-gradient-to-r from-accent to-accent-dark shadow-md shadow-accent/25"><i data-lucide="search" class="w-5 h-5"></i><span class="hidden sm:inline">Rechercher</span></button>
    </div>
</div>

<div class="mx-auto max-w-[1500px] px-4 sm:px-6 py-6 grid lg:grid-cols-[280px_1fr] xl:grid-cols-[280px_1fr_380px] gap-6">
    {{-- FILTERS --}}
    <aside class="hidden lg:block">
        <div class="lg:sticky lg:top-36 space-y-5 rounded-3xl bg-white border border-line p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-bold flex items-center gap-2"><i data-lucide="sliders-horizontal" class="w-5 h-5 text-primary"></i>Filtres</h2>
                <a href="{{ route('employer.search') }}" class="text-sm text-primary font-semibold hover:underline">Réinitialiser</a>
            </div>

            <div>
                <p class="font-semibold text-sm mb-2">Catégorie de métier</p>
                <div class="space-y-1.5">
                    @foreach($categories as $c)
                    <label class="flex items-center gap-2.5 cursor-pointer py-1 group">
                        <input type="checkbox" name="categories[]" value="{{ $c->id }}" class="w-4 h-4 accent-primary" @checked(in_array($c->id, $selectedCats)) />
                        <span class="text-sm flex-1 group-hover:text-primary transition-colors">{{ $c->name }}</span>
                        <span class="text-xs text-slate-400">{{ $c->worker_profiles_count }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <hr class="border-line" />
            <div>
                <div class="flex items-center justify-between mb-2"><p class="font-semibold text-sm">Salaire max</p><span id="payVal" class="text-sm font-bold text-primary">{{ number_format($filters['salary_max'] ?? 90000, 0, ',', ' ') }} FCFA</span></div>
                <input type="range" name="salary_max" min="2000" max="100000" step="1000" value="{{ $filters['salary_max'] ?? 90000 }}" id="payRange" class="w-full accent-primary" />
            </div>

            <hr class="border-line" />
            <div>
                <p class="font-semibold text-sm mb-2">Note minimale</p>
                <div class="flex gap-1.5">
                    @foreach(['4.5' => '4,5+', '4.0' => '4,0+', '' => 'Toutes'] as $val => $lbl)
                    <label class="cursor-pointer">
                        <input type="radio" name="rating_min" value="{{ $val }}" class="peer sr-only" @checked((string)($filters['rating_min'] ?? '') === $val) />
                        <span class="block text-sm rounded-lg border border-line px-3 py-1.5 peer-checked:border-amber peer-checked:bg-amber/5 peer-checked:text-amber hover:border-amber transition-colors">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <hr class="border-line" />
            <label class="flex items-center justify-between cursor-pointer">
                <span class="font-semibold text-sm flex items-center gap-2"><i data-lucide="badge-check" class="w-4 h-4 text-accent"></i>Profils vérifiés</span>
                <input type="checkbox" name="verified" value="1" class="w-5 h-5 accent-accent" @checked($filters['verified'] ?? true) />
            </label>

            <button class="btn-press w-full h-11 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary">Appliquer les filtres</button>
        </div>
    </aside>

    {{-- RESULTS --}}
    <main>
        <div class="flex items-center justify-between gap-3 mb-4">
            <p class="text-slate-600"><b class="text-ink text-lg">{{ $workers->count() }}</b> travailleur{{ $workers->count() > 1 ? 's' : '' }} trouvé{{ $workers->count() > 1 ? 's' : '' }}</p>
            <div class="flex items-center gap-1 rounded-xl bg-white border border-line p-1">
                <button type="button" id="viewList" class="px-3 h-9 rounded-lg text-sm font-semibold bg-primary text-white inline-flex items-center gap-1.5"><i data-lucide="layout-grid" class="w-4 h-4"></i>Liste</button>
                <button type="button" id="viewMap" class="px-3 h-9 rounded-lg text-sm font-semibold text-slate-600 hover:bg-muted inline-flex items-center gap-1.5"><i data-lucide="map" class="w-4 h-4"></i>Carte</button>
            </div>
        </div>

        <div id="grid" class="grid sm:grid-cols-2 gap-4">
            @forelse($workers as $k => $w)
            @php $verified = $w->verification_status === 'verified'; @endphp
            <article class="reveal card-hov rounded-3xl bg-white border border-line p-5 hover:shadow-xl" style="transition-delay:{{ $k*60 }}ms">
                <div class="flex items-start gap-3">
                    <div class="relative shrink-0">
                        <div class="p-0.5 rounded-2xl bg-gradient-to-br {{ $w->category?->gradient ?? 'from-primary to-secondary' }}"><img src="{{ $w->photoUrl(140) }}" alt="{{ $w->user->name }}" class="w-16 h-16 rounded-[14px] object-cover ring-2 ring-white" loading="lazy" /></div>
                        <span class="absolute -bottom-1.5 -right-1.5 grid place-items-center w-6 h-6 rounded-full {{ $verified ? 'bg-accent' : 'bg-warn' }} text-white ring-2 ring-white"><i data-lucide="{{ $verified ? 'check' : 'clock' }}" class="w-3.5 h-3.5"></i></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2"><h3 class="font-semibold truncate">{{ $w->user->name }}</h3>@if($verified)<i data-lucide="badge-check" class="w-4 h-4 text-accent shrink-0"></i>@endif</div>
                        <p class="text-sm text-slate-500">{{ $w->headline }} · {{ $w->experience_years }} ans</p>
                        <div class="mt-1 flex items-center gap-1 text-sm"><span class="flex text-amber">{!! $stars($w->rating_avg) !!}</span><span class="text-slate-600 font-medium">{{ number_format($w->rating_avg, 1, ',', ' ') }}</span><span class="text-slate-400">({{ $w->reviews_count }})</span></div>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5">@foreach($w->skills as $s)<span class="text-xs rounded-full bg-muted text-slate-600 px-2.5 py-1">{{ $s->name }}</span>@endforeach</div>
                <div class="mt-3 flex items-center justify-between text-sm">
                    <span class="inline-flex items-center gap-1 text-slate-600"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose"></i>{{ $w->city }}</span>
                    <span class="font-head font-bold">{{ number_format($w->expected_salary, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-400">FCFA/{{ $periodFr[$w->salary_period] ?? $w->salary_period }}</span></span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <form method="POST" action="{{ route('workers.contact', $w) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn-press w-full h-11 rounded-xl text-white text-sm font-semibold inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-secondary shadow-md shadow-primary/25"><i data-lucide="send" class="w-4 h-4"></i>Contacter</button>
                    </form>
                    <a href="{{ route('workers.show', $w) }}" class="btn-press h-11 px-4 rounded-xl border border-line text-sm font-semibold hover:border-primary hover:text-primary inline-flex items-center">Voir profil</a>
                </div>
            </article>
            @empty
            <div class="sm:col-span-2 rounded-3xl border border-dashed border-line bg-white p-12 text-center">
                <span class="mx-auto grid place-items-center w-14 h-14 rounded-2xl bg-muted text-slate-400"><i data-lucide="search-x" class="w-7 h-7"></i></span>
                <p class="mt-4 font-semibold">Aucun travailleur trouvé</p>
                <p class="text-sm text-slate-500">Élargissez vos critères ou réinitialisez les filtres.</p>
            </div>
            @endforelse
        </div>

        <div id="mapMobile" class="hidden mt-4"><div id="map2" class="h-[60vh] w-full rounded-3xl border border-line"></div></div>
    </main>

    {{-- MAP (xl) --}}
    <aside class="hidden xl:block">
        <div class="sticky top-36 rounded-3xl overflow-hidden border border-line shadow-sm">
            <div id="map" class="h-[calc(100dvh-11rem)] w-full"></div>
        </div>
    </aside>
</div>
</form>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    .leaflet-container{font-family:inherit;border-radius:1.25rem;}
    .pin{display:grid;place-items:center;width:40px;height:40px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 10px rgba(0,0,0,.3);border:2px solid #fff;}
    .pin img{width:30px;height:30px;border-radius:50%;transform:rotate(45deg);object-fit:cover;}
</style>
<script>
    const points = @json($mapPoints);
    const payRange = document.getElementById('payRange'), payVal = document.getElementById('payVal');
    if (payRange) payRange.oninput = () => payVal.textContent = (+payRange.value).toLocaleString('fr-FR') + ' FCFA';

    function buildMap(elId) {
        const center = points.length ? [points[0].lat, points[0].lng] : [4.058, 9.704];
        const m = L.map(elId, { scrollWheelZoom: false }).setView(center, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(m);
        const colors = ['#17266A','#EC4899','#F59E0B','#0D9488','#7C3AED','#F26A21'];
        const group = [];
        points.forEach((p, i) => {
            const icon = L.divIcon({ html: `<div class="pin" style="background:${colors[i%colors.length]}"><img src="${p.photo}"/></div>`, className: '', iconSize: [40,40], iconAnchor: [20,40] });
            const mk = L.marker([p.lat, p.lng], { icon }).addTo(m).bindPopup(`<b>${p.name}</b><br>${p.job} · ${p.rate}★<br>${p.pay}`);
            group.push(mk);
        });
        if (group.length > 1) m.fitBounds(L.featureGroup(group).getBounds().pad(0.3));
        return m;
    }
    const mapD = document.getElementById('map') ? buildMap('map') : null;

    let mapM = null;
    const vList = document.getElementById('viewList'), vMap = document.getElementById('viewMap'), grid = document.getElementById('grid'), mm = document.getElementById('mapMobile');
    const setActive = (on, off) => { on.className = 'px-3 h-9 rounded-lg text-sm font-semibold bg-primary text-white inline-flex items-center gap-1.5'; off.className = 'px-3 h-9 rounded-lg text-sm font-semibold text-slate-600 hover:bg-muted inline-flex items-center gap-1.5'; if (window.lucide) lucide.createIcons(); };
    vMap.onclick = () => { grid.classList.add('hidden'); mm.classList.remove('hidden'); setActive(vMap, vList); if (!mapM) mapM = buildMap('map2'); setTimeout(() => mapM.invalidateSize(), 200); };
    vList.onclick = () => { mm.classList.add('hidden'); grid.classList.remove('hidden'); setActive(vList, vMap); };
</script>
@endpush
