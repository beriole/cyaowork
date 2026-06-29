@extends('layouts.base')
@section('title', 'CyaoWork — Mon espace')

@php
    $avatar = fn($id, $s = 96, $name = '') => $id
        ? "https://images.unsplash.com/photo-{$id}?w={$s}&h={$s}&fit=crop&q=78"
        : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=1D4ED8&color=fff';
    $periodFr = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois', 'intervention' => 'intervention'];
    $statusMap = [
        'sent'      => ['t' => 'Envoyée',   'c' => 'text-slate-500 bg-slate-100',  'i' => 'send'],
        'seen'      => ['t' => 'Vue',       'c' => 'text-primary bg-primary/10',   'i' => 'eye'],
        'interview' => ['t' => 'Entretien', 'c' => 'text-amber bg-amber/10',       'i' => 'calendar'],
        'accepted'  => ['t' => 'Acceptée',  'c' => 'text-accent-dark bg-accent/10','i' => 'check-circle'],
        'rejected'  => ['t' => 'Refusée',   'c' => 'text-rose bg-rose/10',         'i' => 'x-circle'],
    ];
    $sideItems = [
        ['i' => 'home', 'n' => 'Accueil', 'active' => true],
        ['i' => 'search', 'n' => 'Offres'],
        ['i' => 'clipboard-list', 'n' => 'Mes candidatures', 'badge' => $applications->count()],
        ['i' => 'message-circle', 'n' => 'Messagerie', 'badge' => $conversations->count()],
        ['i' => 'user', 'n' => 'Mon profil'],
        ['i' => 'calendar-check', 'n' => 'Disponibilité'],
        ['i' => 'star', 'n' => 'Mes avis'],
        ['i' => 'settings', 'n' => 'Paramètres'],
    ];
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
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all {{ ($s['active'] ?? false) ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-muted' }}">
                <i data-lucide="{{ $s['i'] }}" class="w-5 h-5"></i><span class="flex-1">{{ $s['n'] }}</span>
                @if(!empty($s['badge']))<span class="text-xs font-bold {{ ($s['active'] ?? false) ? 'bg-primary text-white' : 'bg-muted text-slate-500' }} rounded-full px-2 py-0.5">{{ $s['badge'] }}</span>@endif
            </a>
            @endforeach
        </nav>
        <div class="p-3 border-t border-line">
            <div class="rounded-2xl bg-gradient-to-br from-grape to-primary text-white p-4">
                <p class="font-semibold flex items-center gap-2"><i data-lucide="rocket" class="w-4 h-4"></i> Boostez votre profil</p>
                <p class="text-sm text-white/80 mt-1">Apparaissez en tête des recherches.</p>
                <button class="btn-press mt-3 w-full h-9 rounded-lg bg-white text-grape text-sm font-semibold">Découvrir</button>
            </div>
        </div>
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
        {{-- TOPBAR --}}
        <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
            <span class="lg:hidden grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span>
            <label class="hidden sm:flex items-center gap-2 h-10 px-3 rounded-xl bg-muted flex-1 max-w-md focus-within:ring-2 focus-within:ring-primary">
                <i data-lucide="search" class="w-4 h-4 text-slate-400"></i><input placeholder="Rechercher une offre, un métier…" class="bg-transparent outline-none text-sm w-full" />
            </label>
            <div class="flex items-center gap-2 ml-auto">
                <button class="relative grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Notifications"><i data-lucide="bell" class="w-5 h-5 text-slate-600"></i><span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose ring-2 ring-white"></span></button>
                <x-logout-button />
                <div class="flex items-center gap-2 pl-2">
                    <div class="relative">
                        <img src="{{ $avatar($worker->avatar, 80, $worker->name) }}" class="w-9 h-9 rounded-full object-cover" alt="{{ $worker->name }}" />
                        @if($worker->is_verified)<span class="absolute -bottom-0.5 -right-0.5 grid place-items-center w-4 h-4 rounded-full bg-accent text-white ring-2 ring-white"><i data-lucide="check" class="w-2.5 h-2.5"></i></span>@endif
                    </div>
                    <div class="hidden sm:block leading-tight"><p class="text-sm font-semibold">{{ $worker->name }}</p><p class="text-xs text-slate-500">{{ $profile->headline }}</p></div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 pb-24 lg:pb-6 space-y-6">
            @if(session('status'))
            <div class="flex items-center gap-3 rounded-2xl bg-accent/10 border border-accent/20 text-accent-dark px-4 py-3 font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>{{ session('status') }}
            </div>
            @endif
            @if($errors->any())
            <div class="flex items-center gap-3 rounded-2xl bg-rose/10 border border-rose/20 text-rose px-4 py-3 font-medium">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>{{ $errors->first() }}
            </div>
            @endif
            {{-- WELCOME + COMPLETION --}}
            <section class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary via-primary-dark to-grape text-white p-6 sm:p-8">
                <div class="blob w-72 h-72 bg-secondary/40 -top-20 -right-10"></div>
                <div class="blob w-60 h-60 bg-accent/40 -bottom-24 left-10" style="animation-delay:-5s"></div>
                <div class="relative grid sm:grid-cols-[1fr_auto] gap-6 items-center">
                    <div>
                        <p class="text-white/80">Bonjour {{ \Illuminate\Support\Str::before($worker->name, ' ') }} 👋</p>
                        <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold">Trouvez votre prochaine mission</h1>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur px-3 py-2 text-sm font-semibold">
                                <span class="w-2 h-2 rounded-full {{ $profile->availability === 'immediate' ? 'bg-accent-light' : 'bg-amber' }}"></span>
                                {{ $profile->availability === 'immediate' ? 'Disponible immédiatement' : 'Disponibilité : '.$profile->availability }}
                            </span>
                            @if($profile->isVerified())<span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur px-3 py-2 text-sm font-semibold"><i data-lucide="badge-check" class="w-4 h-4 text-accent-light"></i> Identité vérifiée</span>@endif
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 backdrop-blur px-3 py-2 text-sm font-semibold"><i data-lucide="map-pin" class="w-4 h-4 text-rose"></i> {{ $profile->city }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-2xl bg-white/10 backdrop-blur p-4">
                        <div class="relative w-20 h-20 shrink-0">
                            <svg viewBox="0 0 80 80" class="w-20 h-20 -rotate-90">
                                <circle cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="8"/>
                                <circle id="ring" cx="40" cy="40" r="34" fill="none" stroke="#22C55E" stroke-width="8" stroke-linecap="round" stroke-dasharray="213.6" stroke-dashoffset="213.6" style="transition:stroke-dashoffset 1.4s cubic-bezier(.16,1,.3,1)"/>
                            </svg>
                            <span class="absolute inset-0 grid place-items-center font-head font-extrabold text-lg">{{ $completion }}%</span>
                        </div>
                        <div class="text-sm">
                            <p class="font-semibold">Profil complété</p>
                            <p class="text-white/75 mt-0.5">Complétez pour plus de visibilité.</p>
                            <a href="#" class="inline-flex items-center gap-1 mt-2 font-semibold text-accent-light hover:gap-2 transition-all">Compléter <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- VÉRIFICATION / DOCUMENTS --}}
            <section id="verification" class="reveal grid sm:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('worker.photo') }}" enctype="multipart/form-data" class="rounded-3xl bg-white border border-line p-5 flex items-center gap-4">
                    @csrf
                    <span class="grid place-items-center w-12 h-12 rounded-2xl bg-primary/10 text-primary shrink-0"><i data-lucide="camera" class="w-6 h-6"></i></span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm">Photo de profil</p>
                        <input type="file" name="photo" accept="image/*" required class="mt-1 block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-semibold" />
                    </div>
                    <button class="btn-press h-9 px-3 rounded-lg bg-primary text-white text-sm font-semibold shrink-0">Envoyer</button>
                </form>
                <form method="POST" action="{{ route('worker.documents') }}" enctype="multipart/form-data" class="rounded-3xl bg-white border border-line p-5 flex items-center gap-4">
                    @csrf
                    <span class="grid place-items-center w-12 h-12 rounded-2xl bg-accent/10 text-accent-dark shrink-0"><i data-lucide="id-card" class="w-6 h-6"></i></span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm">Pièce d'identité
                            @if($profile->verification_status === 'pending')<span class="ml-1 text-[10px] font-bold text-warn bg-warn/10 rounded-full px-1.5 py-0.5">EN ATTENTE</span>
                            @elseif($profile->isVerified())<span class="ml-1 text-[10px] font-bold text-accent-dark bg-accent/10 rounded-full px-1.5 py-0.5">VÉRIFIÉ</span>@endif
                        </p>
                        <div class="mt-1 flex gap-2">
                            <select name="type" class="text-xs rounded-lg border border-line bg-muted px-2 py-1.5 outline-none">
                                <option value="cni">CNI</option><option value="passeport">Passeport</option><option value="diplome">Diplôme</option><option value="cv">CV</option>
                            </select>
                            <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-accent/10 file:text-accent-dark file:font-semibold" />
                        </div>
                    </div>
                    <button class="btn-press h-9 px-3 rounded-lg bg-accent text-white text-sm font-semibold shrink-0">Déposer</button>
                </form>
            </section>

            {{-- STATS --}}
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($stats as $k => $s)
                <div class="reveal card-hov rounded-3xl bg-white border border-line p-5" style="transition-delay:{{ $k*70 }}ms">
                    <span class="grid place-items-center w-11 h-11 rounded-2xl bg-gradient-to-br {{ $s['g'] }} text-white shadow-lg"><i data-lucide="{{ $s['i'] }}" class="w-5 h-5"></i></span>
                    <p class="mt-4 text-3xl font-extrabold font-head">{{ $s['val'] }}</p>
                    <p class="text-sm text-slate-500">{{ $s['label'] }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ $s['sub'] }}</p>
                </div>
                @endforeach
            </section>

            <div class="grid lg:grid-cols-3 gap-6">
                {{-- RECOMMENDED OFFERS --}}
                <section class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="sparkles" class="w-5 h-5 text-grape"></i> Recommandées pour vous</h2>
                        <a href="#" class="text-sm text-primary font-semibold hover:gap-2 inline-flex items-center gap-1 transition-all">Tout voir <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                    </div>
                    <div class="space-y-4">
                        @php $appliedIds = ($applications ?? collect())->pluck('job_offer_id')->all(); @endphp
                        @foreach($offers as $k => $o)
                        <article class="reveal card-hov rounded-3xl bg-white border border-line p-5 hover:shadow-xl" style="transition-delay:{{ $k*90 }}ms">
                            <div class="flex items-start gap-4">
                                <span class="grid place-items-center w-12 h-12 rounded-2xl bg-gradient-to-br {{ $o->category?->gradient ?? 'from-primary to-secondary' }} text-white shadow-lg shrink-0"><i data-lucide="briefcase" class="w-6 h-6"></i></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h3 class="font-semibold leading-tight">{{ $o->title }} @if($o->is_boosted)<span class="align-middle ml-1 text-[10px] font-bold text-white bg-rose rounded-full px-1.5 py-0.5">BOOST</span>@endif</h3>
                                            <p class="text-sm text-slate-500">{{ $o->category?->name }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="inline-flex items-center gap-1 text-accent-dark bg-accent/10 rounded-full px-2.5 py-1 text-sm font-bold"><i data-lucide="sparkles" class="w-3.5 h-3.5"></i>{{ $o->match }}%</div>
                                            <p class="text-[11px] text-slate-400 mt-0.5">compatibilité</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose"></i>{{ $o->city }}</span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1"><i data-lucide="wallet" class="w-3.5 h-3.5 text-accent"></i>{{ number_format($o->salary_amount, 0, ',', ' ') }} FCFA/{{ $periodFr[$o->salary_period] ?? $o->salary_period }}</span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-muted px-2.5 py-1"><i data-lucide="file-text" class="w-3.5 h-3.5 text-grape"></i>{{ ucfirst($o->contract_type) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                @if(in_array($o->id, $appliedIds))
                                <span class="flex-1 h-11 rounded-xl text-accent-dark bg-accent/10 text-sm font-semibold inline-flex items-center justify-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i>Déjà postulé</span>
                                @else
                                <form method="POST" action="{{ route('worker.apply', $o) }}" class="flex-1">
                                    @csrf
                                    <button class="btn-press w-full h-11 rounded-xl text-white text-sm font-semibold inline-flex items-center justify-center gap-2 bg-gradient-to-r from-accent to-accent-dark shadow-md shadow-accent/25"><i data-lucide="zap" class="w-4 h-4"></i>Postuler en 1 clic</button>
                                </form>
                                @endif
                                <button class="btn-press w-11 h-11 grid place-items-center rounded-xl border border-line hover:border-primary hover:text-primary" aria-label="Enregistrer"><i data-lucide="bookmark" class="w-5 h-5"></i></button>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </section>

                {{-- RIGHT COLUMN --}}
                <section class="space-y-6">
                    <div class="reveal rounded-3xl bg-white border border-line p-5">
                        <div class="flex items-center justify-between mb-3"><h2 class="font-bold flex items-center gap-2"><i data-lucide="clipboard-list" class="w-5 h-5 text-primary"></i> Mes candidatures</h2><a href="#" class="text-sm text-primary font-semibold">Voir</a></div>
                        <ul class="space-y-3">
                            @forelse($applications as $a)
                            @php $st = $statusMap[$a->status] ?? $statusMap['sent']; @endphp
                            <li class="flex items-center gap-3 p-2 rounded-xl hover:bg-muted transition-colors">
                                <span class="grid place-items-center w-9 h-9 rounded-lg {{ $st['c'] }}"><i data-lucide="{{ $st['i'] }}" class="w-4 h-4"></i></span>
                                <div class="flex-1 min-w-0"><p class="font-medium text-sm truncate">{{ $a->jobOffer->title }}</p><p class="text-xs text-slate-500 truncate">{{ $a->created_at->diffForHumans() }}</p></div>
                                <span class="text-xs font-semibold {{ $st['c'] }} rounded-full px-2 py-1 whitespace-nowrap">{{ $st['t'] }}</span>
                            </li>
                            @empty
                            <li class="text-sm text-slate-400 text-center py-4">Aucune candidature pour l'instant.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="reveal rounded-3xl bg-white border border-line p-5">
                        <div class="flex items-center justify-between mb-3"><h2 class="font-bold flex items-center gap-2"><i data-lucide="messages-square" class="w-5 h-5 text-accent"></i> Messages</h2></div>
                        <ul class="space-y-1">
                            @forelse($conversations as $c)
                            @php $last = $c->messages->first(); $unread = $last && !$last->read_at && $last->sender_id !== $worker->id; @endphp
                            <li class="flex items-center gap-3 p-2 rounded-xl hover:bg-muted transition-colors cursor-pointer">
                                <img src="{{ $avatar($c->employer->avatar ?? null, 72) ?: 'https://ui-avatars.com/api/?name='.urlencode($c->employer->name).'&background=1D4ED8&color=fff' }}" class="w-10 h-10 rounded-full object-cover" alt="{{ $c->employer->name }}" loading="lazy" />
                                <div class="flex-1 min-w-0"><div class="flex items-center justify-between"><p class="font-medium text-sm truncate">{{ $c->employer->name }}</p><span class="text-[11px] text-slate-400 shrink-0">{{ optional($c->last_message_at)->diffForHumans(short: true) }}</span></div><p class="text-xs {{ $unread ? 'text-ink font-medium' : 'text-slate-500' }} truncate">{{ $last?->body }}</p></div>
                                @if($unread)<span class="w-2.5 h-2.5 rounded-full bg-accent shrink-0"></span>@endif
                            </li>
                            @empty
                            <li class="text-sm text-slate-400 text-center py-4">Aucune conversation.</li>
                            @endforelse
                        </ul>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

{{-- BOTTOM NAV (mobile) --}}
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/90 backdrop-blur-xl border-t border-line flex justify-around items-center h-16 px-2" style="padding-bottom:env(safe-area-inset-bottom)">
    <a href="#" class="flex flex-col items-center gap-0.5 text-primary"><i data-lucide="home" class="w-6 h-6"></i><span class="text-[11px] font-semibold">Accueil</span></a>
    <a href="#" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="search" class="w-6 h-6"></i><span class="text-[11px]">Offres</span></a>
    <a href="#" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="clipboard-list" class="w-6 h-6"></i><span class="text-[11px]">Candid.</span></a>
    <a href="#" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="message-circle" class="w-6 h-6"></i><span class="text-[11px]">Messages</span></a>
    <a href="#" class="flex flex-col items-center gap-0.5 text-slate-400"><i data-lucide="user" class="w-6 h-6"></i><span class="text-[11px]">Profil</span></a>
</nav>
@endsection

@push('scripts')
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const ring = document.getElementById('ring');
        if (ring) new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) ring.style.strokeDashoffset = 213.6 * (1 - {{ $completion }} / 100); }), { threshold: .5 }).observe(ring);
    });
</script>
@endpush
