@extends('layouts.base')
@section('title', 'CyaoWork — Inscription')

@section('body')
<div class="min-h-dvh lg:grid lg:grid-cols-2">
    {{-- BRAND PANEL --}}
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-primary via-primary-dark to-grape text-white p-12">
        <div class="blob w-96 h-96 bg-secondary/40 -top-24 -left-20"></div>
        <div class="blob w-80 h-80 bg-accent/40 bottom-0 right-0" style="animation-delay:-6s"></div>
        <a href="{{ route('home') }}" class="relative flex items-center gap-2">
            <span class="grid place-items-center w-10 h-10 rounded-xl bg-white/15 backdrop-blur"><i data-lucide="handshake" class="w-6 h-6"></i></span>
            <span class="text-2xl font-head font-extrabold">CyaoWork</span>
        </a>
        <div class="relative">
            <h2 class="text-4xl font-extrabold leading-tight">Rejoignez la plateforme<br/>de confiance.</h2>
            <p class="mt-4 text-white/80 max-w-md text-lg">Des milliers de travailleurs vérifiés et d'employeurs se connectent chaque jour au Cameroun.</p>
            <div class="float mt-10 max-w-sm rounded-3xl bg-white/10 backdrop-blur-xl border border-white/15 p-5">
                <div class="flex items-center gap-3">
                    <img src="https://images.unsplash.com/photo-1589156280159-27698a70f29e?w=120&h=120&fit=crop&q=78" class="w-12 h-12 rounded-full object-cover ring-2 ring-white/30" alt="" />
                    <div><div class="flex items-center gap-1.5"><p class="font-semibold">Mireille K.</p><i data-lucide="badge-check" class="w-4 h-4 text-accent-light"></i></div><p class="text-sm text-white/70">Nounou · Yaoundé</p></div>
                </div>
                <p class="mt-3 text-white/85">« J'ai trouvé un emploi stable en 3 jours. Mon profil vérifié rassure les familles. »</p>
            </div>
        </div>
        <div class="relative flex items-center gap-6 text-white/70 text-sm">
            <span class="flex items-center gap-2"><i data-lucide="shield-check" class="w-4 h-4"></i> Données sécurisées</span>
            <span class="flex items-center gap-2"><i data-lucide="smartphone" class="w-4 h-4"></i> Mobile Money</span>
        </div>
    </div>

    {{-- FORM PANEL --}}
    <div class="flex flex-col min-h-dvh">
        <div class="lg:hidden flex items-center justify-between px-5 h-16 border-b border-line">
            <a href="{{ route('home') }}" class="flex items-center gap-2"><span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span><span class="font-head font-bold text-lg">CyaoWork</span></a>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-primary">Connexion</a>
        </div>

        <div class="flex-1 flex items-center justify-center p-5 sm:p-8">
            <form method="POST" action="{{ route('register') }}" class="w-full max-w-md">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="{{ old('role', 'worker') }}" />

                <div class="flex items-center gap-2 mb-8">
                    <div class="prog h-1.5 flex-1 rounded-full bg-primary"></div>
                    <div class="prog h-1.5 flex-1 rounded-full {{ $errors->any() ? 'bg-primary' : 'bg-line' }}"></div>
                    <div class="prog h-1.5 flex-1 rounded-full bg-line"></div>
                </div>

                {{-- STEP 1 : role --}}
                <section class="step" data-step="1" style="{{ $errors->any() ? 'display:none' : '' }}">
                    <h1 class="text-2xl sm:text-3xl font-extrabold">Bienvenue 👋</h1>
                    <p class="mt-2 text-slate-600">Comment souhaitez-vous utiliser CyaoWork ?</p>
                    <div class="mt-6 space-y-3">
                        <button type="button" data-role="worker" class="role-card w-full text-left rounded-2xl border-2 border-line bg-white p-5 flex items-center gap-4">
                            <span class="grid place-items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shrink-0"><i data-lucide="user-round" class="w-7 h-7"></i></span>
                            <div class="flex-1"><p class="font-bold text-lg">Je cherche du travail</p><p class="text-sm text-slate-500">Créez un profil, postulez aux offres.</p></div>
                            <span class="check grid place-items-center w-6 h-6 rounded-full bg-line text-transparent"><i data-lucide="check" class="w-4 h-4"></i></span>
                        </button>
                        <button type="button" data-role="employer" class="role-card w-full text-left rounded-2xl border-2 border-line bg-white p-5 flex items-center gap-4">
                            <span class="grid place-items-center w-14 h-14 rounded-2xl bg-gradient-to-br from-accent to-teal text-white shadow-lg shrink-0"><i data-lucide="briefcase" class="w-7 h-7"></i></span>
                            <div class="flex-1"><p class="font-bold text-lg">Je recrute</p><p class="text-sm text-slate-500">Publiez des offres, trouvez du personnel.</p></div>
                            <span class="check grid place-items-center w-6 h-6 rounded-full bg-line text-transparent"><i data-lucide="check" class="w-4 h-4"></i></span>
                        </button>
                    </div>
                    <button type="button" data-next class="btn-press mt-6 w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2">Continuer <i data-lucide="arrow-right" class="w-5 h-5"></i></button>
                    <p class="mt-4 text-center text-sm text-slate-500">Déjà inscrit ? <a href="{{ route('login') }}" class="text-primary font-semibold">Se connecter</a></p>
                </section>

                {{-- STEP 2 : infos --}}
                <section class="step" data-step="2" style="{{ $errors->any() ? '' : 'display:none' }}">
                    <button type="button" data-back class="text-slate-400 hover:text-primary inline-flex items-center gap-1 text-sm mb-4"><i data-lucide="arrow-left" class="w-4 h-4"></i> Retour</button>
                    <h1 class="text-2xl sm:text-3xl font-extrabold">Créez votre compte</h1>
                    <p class="mt-2 text-slate-600">Un code de vérification vous sera envoyé par SMS.</p>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Nom complet</label>
                            <div class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary"><i data-lucide="user" class="w-5 h-5 text-slate-400"></i><input name="name" value="{{ old('name') }}" class="bg-transparent outline-none w-full" placeholder="Aïssa Mballa" required /></div>
                            @error('name')<p class="mt-1 text-xs text-rose">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Numéro de téléphone</label>
                            <div class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary"><span class="flex items-center gap-1.5 pr-2 border-r border-slate-300 text-slate-600 font-medium"><span class="text-lg">🇨🇲</span> +237</span><input name="phone" value="{{ old('phone') }}" type="tel" class="bg-transparent outline-none w-full" placeholder="6 XX XX XX XX" required /></div>
                            @error('phone')<p class="mt-1 text-xs text-rose">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">Mot de passe</label>
                            <div class="flex items-center gap-2 h-12 px-4 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary"><i data-lucide="lock" class="w-5 h-5 text-slate-400"></i><input id="pwd" name="password" type="password" class="bg-transparent outline-none w-full" placeholder="••••••••" required /><button type="button" id="pwdToggle" class="text-slate-400 hover:text-primary"><i data-lucide="eye" class="w-5 h-5"></i></button></div>
                            @error('password')<p class="mt-1 text-xs text-rose">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-press mt-6 w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2">Recevoir le code <i data-lucide="send" class="w-5 h-5"></i></button>
                </section>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .role-card{transition:transform .25s,box-shadow .25s,border-color .25s;}
    .role-card.sel{border-color:#17266A;box-shadow:0 0 0 4px rgba(23,38,106,.12);}
</style>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        let step = {{ $errors->any() ? 2 : 1 }};
        const steps = document.querySelectorAll('.step');
        const roleInput = document.getElementById('roleInput');
        const show = n => { step = n; steps.forEach(s => s.style.display = (+s.dataset.step === n ? 'block' : 'none')); window.scrollTo({ top: 0 }); };

        const applyRole = role => {
            roleInput.value = role;
            document.querySelectorAll('.role-card').forEach(x => {
                const on = x.dataset.role === role;
                x.classList.toggle('sel', on);
                x.querySelector('.check').className = 'check grid place-items-center w-6 h-6 rounded-full ' + (on ? 'bg-primary text-white' : 'bg-line text-transparent');
            });
            if (window.lucide) lucide.createIcons();
        };
        document.querySelectorAll('.role-card').forEach(c => c.addEventListener('click', () => applyRole(c.dataset.role)));
        applyRole(roleInput.value);

        document.querySelectorAll('[data-next]').forEach(b => b.addEventListener('click', () => show(2)));
        document.querySelectorAll('[data-back]').forEach(b => b.addEventListener('click', () => show(1)));

        const pwd = document.getElementById('pwd'), pt = document.getElementById('pwdToggle');
        if (pt) pt.addEventListener('click', () => { const t = pwd.type === 'password'; pwd.type = t ? 'text' : 'password'; pt.innerHTML = `<i data-lucide="${t ? 'eye-off' : 'eye'}" class="w-5 h-5"></i>`; lucide.createIcons(); });
    });
</script>
@endpush
