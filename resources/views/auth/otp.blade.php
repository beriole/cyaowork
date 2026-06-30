@extends('layouts.base')
@section('title', 'CyaoWork — Vérification')

@section('body')
<div class="min-h-dvh grid place-items-center p-5 relative overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="blob w-96 h-96 bg-primary/20 -top-24 -left-20"></div>
        <div class="blob w-80 h-80 bg-accent/20 bottom-0 right-0" style="animation-delay:-6s"></div>
    </div>

    <div class="w-full max-w-md rounded-3xl bg-white border border-line shadow-xl p-7 sm:p-9">
        <a href="{{ route('home') }}" class="flex items-center gap-2 mb-6">
            <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span>
            <span class="text-xl font-head font-bold">Cyao<span class="text-primary">Work</span></span>
        </a>

        <span class="grid place-items-center w-14 h-14 rounded-2xl bg-accent/10 text-accent-dark mb-4"><i data-lucide="message-square-lock" class="w-7 h-7"></i></span>
        <h1 class="text-2xl sm:text-3xl font-extrabold">Vérification</h1>
        <p class="mt-2 text-slate-600">Saisissez le code à 6 chiffres reçu par SMS.</p>

        @if(session('dev_otp'))
        <p class="mt-3 text-sm rounded-xl bg-amber/10 text-warn px-3 py-2 flex items-center gap-2"><i data-lucide="info" class="w-4 h-4"></i> Mode démo — votre code : <b class="tracking-widest">{{ session('dev_otp') }}</b></p>
        @endif
        @error('code')<p class="mt-3 text-sm text-rose">{{ $message }}</p>@enderror

        <form method="POST" action="{{ route('otp.verify') }}" class="mt-6">
            @csrf
            <input type="hidden" name="code" id="codeField" />
            <div class="flex gap-2 sm:gap-3 justify-between">
                @for($i = 0; $i < 6; $i++)<input class="otp w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 border-line bg-white" maxlength="1" inputmode="numeric" style="transition:border-color .2s,box-shadow .2s,transform .15s" />@endfor
            </div>
            <button type="submit" class="btn-press mt-6 w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-accent to-accent-dark shadow-lg shadow-accent/25 inline-flex items-center justify-center gap-2">Vérifier <i data-lucide="shield-check" class="w-5 h-5"></i></button>
        </form>

        <form method="POST" action="{{ route('otp.resend') }}" class="mt-4 text-sm text-slate-500">
            @csrf
            Vous n'avez rien reçu ? <button type="submit" id="resend" class="text-primary font-semibold disabled:text-slate-400" disabled>Renvoyer le code (<span id="timer">30</span>s)</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .otp:focus{border-color:#17266A;box-shadow:0 0 0 4px rgba(23,38,106,.15);transform:scale(1.05);outline:none;}
    .otp.filled{border-color:#F26A21;background:#FFF3EC;}
</style>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const otps = [...document.querySelectorAll('.otp')], field = document.getElementById('codeField');
        const sync = () => field.value = otps.map(o => o.value).join('');
        otps.forEach((o, i) => {
            o.addEventListener('input', () => { o.value = o.value.replace(/\D/g, ''); o.classList.toggle('filled', !!o.value); if (o.value && i < 5) otps[i + 1].focus(); sync(); });
            o.addEventListener('keydown', e => { if (e.key === 'Backspace' && !o.value && i > 0) otps[i - 1].focus(); });
            o.addEventListener('paste', e => { e.preventDefault(); const d = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6); [...d].forEach((c, j) => { if (otps[j]) { otps[j].value = c; otps[j].classList.add('filled'); } }); sync(); if (otps[Math.min(d.length, 5)]) otps[Math.min(d.length, 5)].focus(); });
        });
        otps[0]?.focus();

        const resend = document.getElementById('resend'), timer = document.getElementById('timer'); let n = 30;
        const t = setInterval(() => { n--; if (timer) timer.textContent = n; if (n <= 0) { clearInterval(t); resend.disabled = false; resend.innerHTML = 'Renvoyer le code'; } }, 1000);
    });
</script>
@endpush
