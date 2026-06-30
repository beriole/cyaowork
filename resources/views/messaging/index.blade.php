@extends('layouts.base')
@section('title', 'CyaoWork — Messagerie')

@php
    $avatar = fn($id, $s = 96, $name = '') => $id
        ? "https://images.unsplash.com/photo-{$id}?w={$s}&h={$s}&fit=crop&q=78"
        : 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=17266A&color=fff';
@endphp

@section('body')
<div class="h-dvh flex flex-col">
    <header class="h-16 shrink-0 bg-white/85 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
        <a href="{{ route('worker.dashboard') }}" class="flex items-center gap-2">
            <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-primary via-secondary to-accent text-white"><i data-lucide="handshake" class="w-5 h-5"></i></span>
            <span class="text-xl font-head font-bold hidden xs:block">Cyao<span class="text-primary">Work</span></span>
        </a>
        <h1 class="font-bold text-lg ml-2">Messagerie</h1>
        <div class="ml-auto flex items-center gap-2">
            <img src="{{ $avatar($me->avatar, 80, $me->name) }}" class="w-9 h-9 rounded-full object-cover" alt="{{ $me->name }}" />
            <span class="hidden sm:block text-sm font-semibold">{{ $me->name }}</span>
        </div>
    </header>

    <div class="flex-1 min-h-0 grid md:grid-cols-[340px_1fr]">
        {{-- CONVERSATION LIST --}}
        <aside class="flex flex-col border-r border-line bg-white min-h-0 {{ $active ? 'hidden md:flex' : '' }}">
            <div class="p-3 border-b border-line">
                <label class="flex items-center gap-2 h-11 px-3 rounded-xl bg-muted focus-within:ring-2 focus-within:ring-primary">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i><input placeholder="Rechercher une conversation…" class="bg-transparent outline-none text-sm w-full" />
                </label>
            </div>
            <div class="flex-1 overflow-y-auto">
                @foreach($conversations as $c)
                @php $other = $me->isEmployer() ? $c->worker : $c->employer; $last = $c->messages->first(); $unread = $last && !$last->read_at && $last->sender_id !== $me->id; @endphp
                <a href="{{ route('messaging.index', ['c' => $c->id]) }}" class="{{ $active && $active->id === $c->id ? 'bg-[#EEF4FF]' : '' }} w-full text-left flex items-center gap-3 px-3 py-3 hover:bg-muted/60 transition-colors border-b border-line/60">
                    <img src="{{ $avatar($other->avatar, 96, $other->name) }}" class="w-12 h-12 rounded-full object-cover shrink-0" alt="{{ $other->name }}" loading="lazy" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5"><p class="font-semibold text-sm truncate">{{ $other->name }}</p>@if($other->is_verified)<i data-lucide="badge-check" class="w-3.5 h-3.5 text-accent shrink-0"></i>@endif<span class="ml-auto text-[11px] text-slate-400 shrink-0">{{ optional($c->last_message_at)->diffForHumans(short: true) }}</span></div>
                        <div class="flex items-center gap-2"><p class="text-xs {{ $unread ? 'text-ink font-medium' : 'text-slate-500' }} truncate flex-1">{{ $last?->body }}</p>@if($unread)<span class="shrink-0 w-2.5 h-2.5 rounded-full bg-accent"></span>@endif</div>
                    </div>
                </a>
                @endforeach
            </div>
        </aside>

        {{-- CHAT THREAD --}}
        <section class="flex flex-col min-h-0 bg-white {{ $active ? '' : 'hidden md:flex' }}">
            @if($active)
            @php $activeOther = $me->isEmployer() ? $active->worker : $active->employer; @endphp
            <div class="h-16 shrink-0 border-b border-line flex items-center gap-3 px-4">
                <a href="{{ route('messaging.index') }}" class="md:hidden w-9 h-9 grid place-items-center rounded-lg hover:bg-muted"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
                <img src="{{ $avatar($activeOther->avatar, 80, $activeOther->name) }}" class="w-10 h-10 rounded-full object-cover" alt="" />
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5"><p class="font-semibold truncate">{{ $activeOther->name }}</p>@if($activeOther->is_verified)<i data-lucide="badge-check" class="w-4 h-4 text-accent shrink-0"></i>@endif</div>
                    <p class="text-xs text-accent-dark font-medium">en ligne</p>
                </div>
                <button class="btn-press w-10 h-10 grid place-items-center rounded-xl hover:bg-muted"><i data-lucide="phone" class="w-5 h-5 text-slate-600"></i></button>
            </div>

            @if($active->jobOffer)
            <div class="shrink-0 mx-3 mt-3 rounded-2xl bg-gradient-to-r from-primary/10 to-secondary/10 border border-primary/20 p-3 flex items-center gap-3">
                <span class="grid place-items-center w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 text-white shrink-0"><i data-lucide="briefcase" class="w-5 h-5"></i></span>
                <div class="flex-1 min-w-0"><p class="text-sm font-semibold truncate">{{ $active->jobOffer->title }}</p><p class="text-xs text-slate-500">{{ number_format($active->jobOffer->salary_amount, 0, ',', ' ') }} FCFA · {{ $active->jobOffer->city }}</p></div>
                <a href="#" class="text-xs font-semibold text-primary whitespace-nowrap">Voir l'offre</a>
            </div>
            @endif

            <div id="messages" data-cid="{{ $active->id }}" data-me="{{ $me->id }}" class="flex-1 overflow-y-auto px-4 py-4 space-y-3" style="background-image:radial-gradient(#E2E8F0 1px,transparent 1px);background-size:22px 22px;">
                @foreach($active->messages as $m)
                @php $me_msg = $m->sender_id === $me->id; @endphp
                <div class="flex {{ $me_msg ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[78%] sm:max-w-[65%] px-3.5 py-2 rounded-2xl {{ $me_msg ? 'bg-gradient-to-br from-primary to-primary-dark text-white rounded-br-md' : 'bg-white border border-line rounded-bl-md' }} shadow-sm">
                        <p class="text-[15px] leading-snug">{{ $m->body }}</p>
                        <p class="text-[10px] mt-1 {{ $me_msg ? 'text-white/70 text-right' : 'text-slate-400' }}">{{ $m->created_at->format('H:i') }}@if($me_msg && $m->read_at)<span class="ml-1 text-secondary">✓✓</span>@elseif($me_msg)<span class="ml-1 text-white/50">✓</span>@endif</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="shrink-0 border-t border-line p-3">
                <form id="sendForm" class="flex items-end gap-2">
                    <button type="button" class="btn-press w-11 h-11 grid place-items-center rounded-xl hover:bg-muted text-slate-500"><i data-lucide="paperclip" class="w-5 h-5"></i></button>
                    <div class="flex-1 flex items-end gap-2 bg-muted rounded-2xl px-3 py-2 focus-within:ring-2 focus-within:ring-primary">
                        <textarea id="input" rows="1" placeholder="Écrivez un message…" class="flex-1 bg-transparent outline-none resize-none max-h-28 text-[15px] leading-6 py-1"></textarea>
                    </div>
                    <button type="submit" class="btn-press w-11 h-11 grid place-items-center rounded-xl text-white bg-gradient-to-br from-accent to-accent-dark shadow-md shadow-accent/25"><i data-lucide="send" class="w-5 h-5"></i></button>
                </form>
            </div>
            @else
            <div class="flex-1 grid place-items-center text-slate-400"><div class="text-center"><i data-lucide="messages-square" class="w-10 h-10 mx-auto"></i><p class="mt-2">Sélectionnez une conversation</p></div></div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const messages = document.getElementById('messages');
        if (!messages) return;
        messages.scrollTop = messages.scrollHeight;
        const cid = messages.dataset.cid, me = messages.dataset.me;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const form = document.getElementById('sendForm');
        const input = document.getElementById('input');

        const bubble = (text, mine, time) => {
            const cls = mine
                ? 'bg-gradient-to-br from-primary to-primary-dark text-white rounded-br-md'
                : 'bg-white border border-line rounded-bl-md';
            const meta = mine ? 'text-white/70 text-right' : 'text-slate-400';
            const tick = mine ? '<span class="ml-1 text-white/50">✓</span>' : '';
            const wrap = document.createElement('div');
            wrap.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');
            wrap.innerHTML = `<div class="max-w-[78%] sm:max-w-[65%] px-3.5 py-2 rounded-2xl ${cls} shadow-sm"><p class="text-[15px] leading-snug"></p><p class="text-[10px] mt-1 ${meta}">${time}${tick}</p></div>`;
            wrap.querySelector('p').textContent = text;
            messages.appendChild(wrap);
            messages.scrollTop = messages.scrollHeight;
        };

        if (input) input.addEventListener('input', () => { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 112) + 'px'; });

        if (form) form.addEventListener('submit', async e => {
            e.preventDefault();
            const v = input.value.trim(); if (!v) return;
            input.value = ''; input.style.height = 'auto';
            try {
                const res = await fetch(`/messagerie/${cid}/messages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ body: v }),
                });
                const data = await res.json();
                bubble(data.body, true, data.created_at);
            } catch (_) {
                bubble(v, true, new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }));
            }
        });

        // Réception temps réel (Reverb) : messages des autres participants.
        if (window.Echo) {
            window.Echo.private(`conversation.${cid}`).listen('.message.sent', e => {
                if (String(e.sender_id) !== String(me)) {
                    const t = new Date(e.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                    bubble(e.body, false, t);
                }
            });
        }
    });
</script>
@endpush
