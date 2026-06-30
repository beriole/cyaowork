@extends('layouts.base')
@section('title', 'CyaoWork — Trouvez la bonne main-d\'œuvre, en toute confiance')

@php
    $img = fn($id, $s = 400) => "https://images.unsplash.com/photo-{$id}?w={$s}&h={$s}&fit=crop&q=78";

    $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois', 'intervention' => 'intervention'];
    $availFr  = ['immediate' => 'Disponible', 'week' => 'Cette semaine', 'flexible' => 'Flexible'];

    $steps = [
        ['n' => '1', 'i' => 'search',      't' => 'Recherchez',                  'd' => 'Indiquez le métier et la ville. Filtrez par note, salaire et disponibilité.',         'g' => 'from-primary to-secondary'],
        ['n' => '2', 'i' => 'badge-check', 't' => 'Choisissez un profil vérifié', 'd' => "Consultez l'expérience, les avis et le badge d'identité validée.",                    'g' => 'from-grape to-rose'],
        ['n' => '3', 'i' => 'handshake',   't' => 'Contractez & payez',          'd' => 'Signez le contrat numérique et payez via Mobile Money en toute sécurité.',            'g' => 'from-accent to-teal'],
    ];

    $trust = [
        ['i' => 'fingerprint',    't' => 'Identité vérifiée',   'd' => 'CNI validée avant le badge « Profil vérifié ».'],
        ['i' => 'file-signature', 't' => 'Contrats numériques', 'd' => 'Contrat horodaté, signé et téléchargeable en PDF.'],
        ['i' => 'smartphone',     't' => 'Mobile Money',        'd' => 'Paiements via MTN MoMo et Orange Money.'],
        ['i' => 'star',           't' => 'Notation mutuelle',   'd' => 'Avis bidirectionnels après chaque mission.'],
    ];

    $stars = function ($r) {
        $r = (float) str_replace(',', '.', $r); $out = ''; $full = floor($r); $half = ($r - $full) >= .5;
        for ($k = 0; $k < $full; $k++) $out .= '<i data-lucide="star" class="w-4 h-4 fill-current"></i>';
        if ($half) $out .= '<i data-lucide="star-half" class="w-4 h-4 fill-current"></i>';
        return $out;
    };
@endphp

@section('body')
<div id="progress" class="fixed top-0 left-0 h-[3px] w-0 z-[60]" style="background:linear-gradient(90deg,#17266A,#2F46B0,#F26A21,#FF8A3D)"></div>

<x-public-nav />

{{-- ============ HERO ============ --}}
<section class="relative pt-32 pb-16 sm:pt-40 sm:pb-24 overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="blob w-[30rem] h-[30rem] bg-primary/40 -top-28 -left-24"></div>
        <div class="blob w-[26rem] h-[26rem] bg-grape/30 top-0 right-10" style="animation-delay:-3s"></div>
        <div class="blob w-[24rem] h-[24rem] bg-accent/30 bottom-0 left-1/3" style="animation-delay:-6s"></div>
        <div class="blob w-[20rem] h-[20rem] bg-rose/25 top-32 right-1/3" style="animation-delay:-9s"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_bottom,transparent_60%,#F8FAFC)]"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 grid lg:grid-cols-[1.05fr_1fr] gap-12 items-center">
        <div class="reveal">
            <span class="inline-flex items-center gap-2 rounded-full bg-white shadow-sm border border-line px-3 py-1.5 text-sm font-semibold text-accent-dark">
                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-accent"></span></span>
                +250 mises en relation cette semaine
            </span>
            <h1 class="mt-5 text-4xl sm:text-6xl font-extrabold leading-[1.04] tracking-tight">
                La bonne <span class="shimmer">main-d'œuvre</span>,<br/>en toute confiance.
            </h1>
            <p class="mt-5 text-lg text-slate-600 max-w-xl">
                Ménagères, nounous, cuisiniers, gardiens, plombiers, jardiniers…
                CyaoWork connecte directement employeurs et travailleurs <b class="text-ink">vérifiés</b> au Cameroun.
            </p>

            <div class="mt-7 bg-white rounded-2xl shadow-2xl shadow-primary/10 border border-line p-2.5 sm:p-3">
                <form class="grid grid-cols-1 sm:grid-cols-[1.3fr_1fr_auto] gap-2.5">
                    <label class="flex items-center gap-3 h-14 px-4 rounded-xl bg-muted/70 focus-within:ring-2 focus-within:ring-primary transition-shadow">
                        <i data-lucide="search" class="w-5 h-5 text-primary shrink-0"></i>
                        <input type="text" placeholder="Métier (ménagère, plombier…)" class="w-full bg-transparent outline-none text-[15px] placeholder:text-slate-400" />
                    </label>
                    <label class="flex items-center gap-3 h-14 px-4 rounded-xl bg-muted/70 focus-within:ring-2 focus-within:ring-primary transition-shadow">
                        <i data-lucide="map-pin" class="w-5 h-5 text-rose shrink-0"></i>
                        <input type="text" placeholder="Ville (Douala…)" class="w-full bg-transparent outline-none text-[15px] placeholder:text-slate-400" />
                    </label>
                    <button type="submit" class="btn-press h-14 px-6 rounded-xl text-white font-semibold text-[15px] inline-flex items-center justify-center gap-2 bg-gradient-to-r from-accent to-accent-dark shadow-lg shadow-accent/30">
                        <i data-lucide="search" class="w-5 h-5"></i> Rechercher
                    </button>
                </form>
                <div class="mt-2.5 flex flex-wrap items-center gap-2 px-1">
                    <span class="text-sm text-slate-500">Populaire :</span>
                    <a href="#" class="text-sm rounded-full border border-line px-3 py-1 hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors">Ménagère · Douala</a>
                    <a href="#" class="text-sm rounded-full border border-line px-3 py-1 hover:border-rose hover:text-rose hover:bg-rose/5 transition-colors">Nounou</a>
                    <a href="#" class="text-sm rounded-full border border-line px-3 py-1 hover:border-amber hover:text-amber hover:bg-amber/5 transition-colors">Gardien</a>
                </div>
            </div>

            <dl class="mt-8 grid grid-cols-3 gap-4 max-w-md">
                <div><dt class="text-3xl font-extrabold font-head bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent" data-count="2400" data-suffix="+">0</dt><dd class="text-sm text-slate-500">Vérifiés</dd></div>
                <div><dt class="text-3xl font-extrabold font-head bg-gradient-to-r from-grape to-rose bg-clip-text text-transparent" data-count="850" data-suffix="+">0</dt><dd class="text-sm text-slate-500">Employeurs</dd></div>
                <div><dt class="text-3xl font-extrabold font-head bg-gradient-to-r from-accent to-teal bg-clip-text text-transparent" data-count="48" data-divide="10" data-suffix="/5">0</dt><dd class="text-sm text-slate-500">Note moy.</dd></div>
            </dl>
        </div>

        <div id="heroVisual" class="relative h-[28rem] sm:h-[32rem] reveal">
            <div class="spin-slow absolute -top-6 -right-2 w-24 h-24 rounded-full border-4 border-dashed border-primary/30"></div>
            <div class="tilt absolute inset-x-4 sm:inset-x-8 top-2 bottom-8 rounded-[2rem] overflow-hidden shadow-2xl shadow-primary/20 ring-4 ring-white">
                <img src="{{ $img('1531123897727-8f129e1688ce', 700) }}" alt="Travailleuse vérifiée CyaoWork" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-primary/40 via-transparent to-transparent"></div>
                <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/90 backdrop-blur px-3 py-1.5 text-sm font-semibold text-accent-dark shadow">
                    <i data-lucide="badge-check" class="w-4 h-4 text-accent"></i> Profil vérifié
                </span>
            </div>
            <div class="float absolute top-6 -left-1 sm:left-0 w-44 rounded-2xl bg-white shadow-xl border border-line p-3.5">
                <div class="flex items-center gap-1 text-amber">@for($i=0;$i<5;$i++)<i data-lucide="star" class="w-4 h-4 fill-current"></i>@endfor</div>
                <p class="mt-1 font-bold font-head">4,9 / 5</p><p class="text-xs text-slate-500">sur 38 missions</p>
            </div>
            <div class="float d1 absolute bottom-16 -right-1 sm:right-0 w-52 rounded-2xl bg-gradient-to-br from-grape to-primary text-white shadow-xl shadow-grape/30 p-4">
                <div class="flex items-center gap-2 text-white/80 text-sm"><i data-lucide="file-signature" class="w-4 h-4"></i> Contrat</div>
                <p class="mt-1 text-lg font-extrabold font-head">Signé en 2 min</p>
                <div class="mt-2 flex -space-x-2">
                    <img src="{{ $img('1463453091185-61582044d556', 80) }}" class="w-7 h-7 rounded-full ring-2 ring-grape object-cover" alt="" />
                    <img src="{{ $img('1522529599102-193c0d76b5b6', 80) }}" class="w-7 h-7 rounded-full ring-2 ring-grape object-cover" alt="" />
                    <img src="{{ $img('1589156280159-27698a70f29e', 80) }}" class="w-7 h-7 rounded-full ring-2 ring-grape object-cover" alt="" />
                </div>
            </div>
            <div class="float d2 absolute bottom-2 left-6 rounded-2xl bg-white shadow-xl border border-line px-4 py-3 flex items-center gap-2">
                <span class="pulse-ring grid place-items-center w-9 h-9 rounded-xl bg-accent text-white"><i data-lucide="smartphone" class="w-5 h-5"></i></span>
                <div class="leading-tight"><p class="font-semibold text-sm">MTN · Orange</p><p class="text-xs text-slate-500">Mobile Money</p></div>
            </div>
        </div>
    </div>
</section>

{{-- ============ MARQUEE ============ --}}
<div class="marquee border-y border-line bg-gradient-to-r from-primary/5 via-grape/5 to-accent/5 py-4 overflow-hidden">
    <div class="marquee-track gap-8 text-slate-500 font-semibold">
        @foreach(['Profils vérifiés' => ['badge-check','text-primary'], 'Contrats numériques' => ['file-signature','text-grape'], 'Mobile Money' => ['smartphone','text-accent-dark'], 'Notation mutuelle' => ['star','text-amber'], 'Géolocalisation' => ['map-pin','text-rose'], 'Messagerie temps réel' => ['messages-square','text-secondary']] as $label => $meta)
            <span class="flex items-center gap-2 px-2 {{ $meta[1] }}"><i data-lucide="{{ $meta[0] }}" class="w-5 h-5"></i> {{ $label }}</span>
        @endforeach
        @foreach(['Profils vérifiés' => ['badge-check','text-primary'], 'Contrats numériques' => ['file-signature','text-grape'], 'Mobile Money' => ['smartphone','text-accent-dark'], 'Notation mutuelle' => ['star','text-amber'], 'Géolocalisation' => ['map-pin','text-rose'], 'Messagerie temps réel' => ['messages-square','text-secondary']] as $label => $meta)
            <span class="flex items-center gap-2 px-2 {{ $meta[1] }}"><i data-lucide="{{ $meta[0] }}" class="w-5 h-5"></i> {{ $label }}</span>
        @endforeach
    </div>
</div>

{{-- ============ CATEGORIES ============ --}}
<section id="categories" class="mx-auto max-w-7xl px-4 sm:px-6 py-16 sm:py-20">
    <div class="flex items-end justify-between gap-4 mb-8 reveal">
        <div>
            <h2 class="text-3xl sm:text-4xl font-bold">Explorez par métier</h2>
            <p class="mt-2 text-slate-600">Chaque catégorie, ses talents vérifiés près de chez vous.</p>
        </div>
        <a href="#" class="hidden sm:inline-flex items-center gap-1 text-primary font-semibold hover:gap-2 transition-all">Tout voir <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        @foreach($categories as $k => $c)
        <a href="#" class="reveal card-hov sweep group rounded-2xl border border-line bg-white p-4 hover:shadow-xl" style="transition-delay:{{ $k*60 }}ms">
            <span class="grid place-items-center w-12 h-12 rounded-xl bg-gradient-to-br {{ $c->gradient }} text-white shadow-lg group-hover:rotate-6 group-hover:scale-110 transition-transform"><i data-lucide="{{ $c->icon }}" class="w-6 h-6"></i></span>
            <p class="mt-3 font-semibold">{{ $c->name }}</p><p class="text-sm text-slate-500">{{ $c->worker_profiles_count }} profil{{ $c->worker_profiles_count > 1 ? 's' : '' }}</p>
        </a>
        @endforeach
    </div>
</section>

{{-- ============ OFFRES ============ --}}
<section id="offres" class="relative bg-gradient-to-b from-muted/40 to-white border-y border-line">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 sm:py-20">
        <div class="flex items-end justify-between gap-4 mb-8 reveal">
            <div>
                <h2 class="text-3xl sm:text-4xl font-bold">Profils en vedette</h2>
                <p class="mt-2 text-slate-600">Des travailleurs notés et vérifiés, prêts à intervenir.</p>
            </div>
            <a href="#" class="inline-flex items-center gap-1 text-primary font-semibold hover:gap-2 transition-all">Voir tout <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($profiles as $k => $p)
            @php $verified = $p->verification_status === 'verified'; @endphp
            <article class="reveal card-hov rounded-3xl bg-white border border-line p-5 hover:shadow-2xl hover:shadow-slate-900/10" style="transition-delay:{{ $k*80 }}ms">
                <div class="flex items-start gap-4">
                    <div class="relative shrink-0">
                        <div class="p-0.5 rounded-2xl bg-gradient-to-br {{ $p->category?->gradient ?? 'from-primary to-secondary' }}">
                            <img src="{{ $p->photoUrl(160) }}" alt="Photo de {{ $p->user->name }}" class="w-16 h-16 rounded-[14px] object-cover ring-2 ring-white" loading="lazy" />
                        </div>
                        <span class="absolute -bottom-1.5 -right-1.5 grid place-items-center w-6 h-6 rounded-full {{ $verified ? 'bg-accent' : 'bg-warn' }} text-white ring-2 ring-white" title="{{ $verified ? 'Profil vérifié' : 'En vérification' }}"><i data-lucide="{{ $verified ? 'check' : 'clock' }}" class="w-3.5 h-3.5"></i></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2"><h3 class="font-semibold truncate">{{ $p->user->name }}</h3>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $verified ? 'text-accent-dark bg-accent/10' : 'text-warn bg-warn/10' }} px-2 py-0.5 rounded-full"><i data-lucide="{{ $verified ? 'badge-check' : 'loader' }}" class="w-3 h-3"></i>{{ $verified ? 'Vérifié' : 'En vérif.' }}</span>
                        </div>
                        <p class="text-sm text-slate-500">{{ $p->headline }} · {{ $p->experience_years }} ans</p>
                        <div class="mt-1 flex items-center gap-1 text-sm">
                            <span class="flex text-amber" aria-label="Note {{ $p->rating_avg }} sur 5">{!! $stars($p->rating_avg) !!}</span>
                            <span class="text-slate-600 font-medium">{{ number_format($p->rating_avg, 1, ',', ' ') }}</span><span class="text-slate-400">({{ $p->reviews_count }})</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose"></i>{{ $p->city }}</span>
                    <span class="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1"><i data-lucide="clock" class="w-3.5 h-3.5 text-primary"></i>{{ $availFr[$p->availability] ?? 'Disponible' }}</span>
                </div>
                <div class="mt-4 flex items-center justify-between gap-2">
                    <p class="font-head font-bold text-lg">{{ number_format($p->expected_salary, 0, ',', ' ') }} <span class="text-sm font-normal text-slate-500">FCFA/{{ $periodFr[$p->salary_period] ?? $p->salary_period }}</span></p>
                    <button class="btn-press h-11 px-4 rounded-xl text-white text-sm font-semibold inline-flex items-center gap-2 bg-gradient-to-r from-accent to-accent-dark shadow-md shadow-accent/25"><i data-lucide="send" class="w-4 h-4"></i>Contacter</button>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ ETAPES ============ --}}
<section id="etapes" class="mx-auto max-w-7xl px-4 sm:px-6 py-16 sm:py-24">
    <div class="text-center max-w-2xl mx-auto reveal">
        <span class="inline-flex items-center gap-2 rounded-full bg-grape/10 text-grape px-3 py-1.5 text-sm font-semibold"><i data-lucide="route" class="w-4 h-4"></i> Simple &amp; rapide</span>
        <h2 class="mt-4 text-3xl sm:text-4xl font-bold">Comment ça marche</h2>
        <p class="mt-3 text-slate-600">Trois étapes pour recruter en toute sérénité.</p>
    </div>
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($steps as $k => $s)
        <div class="reveal relative rounded-3xl bg-white border border-line p-7 hover:shadow-xl transition-shadow" style="transition-delay:{{ $k*120 }}ms">
            <span class="grid place-items-center w-14 h-14 rounded-2xl bg-gradient-to-br {{ $s['g'] }} text-white shadow-lg"><i data-lucide="{{ $s['i'] }}" class="w-7 h-7"></i></span>
            <span class="absolute top-6 right-7 text-6xl font-extrabold font-head text-slate-100">{{ $s['n'] }}</span>
            <h3 class="mt-5 text-xl font-bold">{{ $s['t'] }}</h3>
            <p class="mt-2 text-slate-600">{{ $s['d'] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ============ CONFIANCE ============ --}}
<section id="confiance" class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-primary via-primary-dark to-grape"></div>
    <div class="absolute inset-0 -z-10 opacity-20" style="background-image:radial-gradient(circle at 20% 20%,#fff 0,transparent 40%),radial-gradient(circle at 80% 60%,#F26A21 0,transparent 35%);"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 sm:py-24 text-white">
        <div class="text-center max-w-2xl mx-auto reveal">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur px-3 py-1.5 text-sm font-semibold"><i data-lucide="shield-check" class="w-4 h-4"></i> Une plateforme de confiance</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-bold">Du premier contact au paiement, sécurisé.</h2>
            <p class="mt-3 text-white/80">CyaoWork protège chaque étape de la relation employeur ↔ travailleur.</p>
        </div>
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($trust as $k => $t)
            <div class="reveal card-hov rounded-3xl bg-white/10 backdrop-blur border border-white/15 p-6 hover:bg-white/15" style="transition-delay:{{ $k*80 }}ms">
                <span class="grid place-items-center w-12 h-12 rounded-2xl bg-white/15"><i data-lucide="{{ $t['i'] }}" class="w-6 h-6"></i></span>
                <h3 class="mt-4 font-semibold text-lg">{{ $t['t'] }}</h3>
                <p class="mt-1 text-sm text-white/80">{{ $t['d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ DOUBLE CTA ============ --}}
<section class="mx-auto max-w-7xl px-4 sm:px-6 py-20">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="reveal group rounded-3xl bg-gradient-to-br from-primary to-secondary text-white p-8 sm:p-10 relative overflow-hidden sweep">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 group-hover:scale-150 transition-transform duration-700"></div>
            <i data-lucide="user-round" class="w-20 h-20 absolute right-6 bottom-6 opacity-20"></i>
            <h3 class="text-2xl font-bold relative">Je cherche du travail</h3>
            <p class="mt-2 text-white/85 max-w-sm relative">Créez votre profil, ajoutez vos compétences et recevez des offres près de chez vous.</p>
            <a href="#" class="btn-press mt-6 inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-white text-primary font-semibold hover:gap-3 transition-all relative">Créer mon profil <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        </div>
        <div class="reveal group rounded-3xl bg-gradient-to-br from-accent to-teal text-white p-8 sm:p-10 relative overflow-hidden sweep">
            <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 group-hover:scale-150 transition-transform duration-700"></div>
            <i data-lucide="briefcase" class="w-20 h-20 absolute right-6 bottom-6 opacity-20"></i>
            <h3 class="text-2xl font-bold relative">Je recrute</h3>
            <p class="mt-2 text-white/90 max-w-sm relative">Publiez une offre ou contactez directement des travailleurs vérifiés.</p>
            <a href="#" class="btn-press mt-6 inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-white text-accent-dark font-semibold hover:gap-3 transition-all relative">Publier une offre <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        </div>
    </div>
</section>

{{-- ============ FOOTER ============ --}}
<footer class="border-t border-line bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
        <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-2">
                <span class="grid place-items-center w-8 h-8 rounded-lg bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-4 h-4"></i></span>
                <span class="font-head font-bold">Cyao<span class="text-primary">Work</span></span>
            </div>
            <p class="mt-3 text-slate-500 max-w-xs">La plateforme de confiance pour la main-d'œuvre et le personnel domestique au Cameroun.</p>
        </div>
        <div><h4 class="font-semibold mb-3">Travailleurs</h4><ul class="space-y-2 text-slate-500"><li><a href="#" class="hover:text-primary">Créer un profil</a></li><li><a href="#" class="hover:text-primary">Voir les offres</a></li><li><a href="#" class="hover:text-primary">Vérification</a></li></ul></div>
        <div><h4 class="font-semibold mb-3">Employeurs</h4><ul class="space-y-2 text-slate-500"><li><a href="#" class="hover:text-primary">Publier une offre</a></li><li><a href="#" class="hover:text-primary">Rechercher</a></li><li><a href="#" class="hover:text-primary">Abonnements</a></li></ul></div>
        <div><h4 class="font-semibold mb-3">Aide</h4><ul class="space-y-2 text-slate-500"><li><a href="#" class="hover:text-primary">Centre d'aide</a></li><li><a href="#" class="hover:text-primary">Sécurité</a></li><li><a href="#" class="hover:text-primary">Contact</a></li></ul></div>
    </div>
    <div class="border-t border-line py-5 text-center text-sm text-slate-400">© {{ date('Y') }} CyaoWork — Tous droits réservés · Cameroun</div>
</footer>
@endsection

@push('scripts')
<script>
    /* navbar shrink + progress bar */
    const navbar = document.getElementById('navbar'), bar = document.getElementById('progress');
    addEventListener('scroll', () => {
        const s = scrollY > 20;
        navbar.classList.toggle('h-14', s); navbar.classList.toggle('h-16', !s); navbar.classList.toggle('bg-white/90', s);
        const h = document.documentElement; bar.style.width = (h.scrollTop / (h.scrollHeight - h.clientHeight) * 100) + '%';
    });

    /* mobile menu */
    const mm = document.getElementById('mobileMenu');
    document.getElementById('burger').addEventListener('click', () => mm.classList.toggle('hidden'));
    mm.querySelectorAll('a').forEach(a => a.addEventListener('click', () => mm.classList.add('hidden')));

    /* hero parallax tilt */
    const hv = document.getElementById('heroVisual'), tilt = hv.querySelector('.tilt');
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
        hv.addEventListener('mousemove', e => {
            const r = hv.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
            tilt.style.transform = `perspective(900px) rotateY(${x*8}deg) rotateX(${-y*8}deg) scale(1.02)`;
        });
        hv.addEventListener('mouseleave', () => tilt.style.transform = '');
    }
</script>
@endpush
