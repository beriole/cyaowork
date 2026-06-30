@php
    $user = auth()->user();
    $recent = $user->notifications()->latest()->limit(8)->get();
    $unread = $user->unreadNotifications()->count();
@endphp

<div class="relative" id="notif-bell">
    <button type="button" data-bell class="relative grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Notifications">
        <i data-lucide="bell" class="w-5 h-5 text-slate-600"></i>
        <span data-bell-count class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 grid place-items-center text-[10px] font-bold text-white bg-rose rounded-full ring-2 ring-white {{ $unread ? '' : 'hidden' }}">{{ $unread }}</span>
    </button>

    <div data-bell-panel class="hidden absolute right-0 mt-2 w-80 max-w-[90vw] rounded-2xl bg-white border border-line shadow-2xl shadow-slate-900/10 z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 h-12 border-b border-line">
            <p class="font-semibold text-sm">Notifications</p>
            <form method="POST" action="{{ route('notifications.read') }}">@csrf
                <button class="text-xs font-semibold text-primary hover:underline">Tout marquer lu</button>
            </form>
        </div>
        <ul data-bell-list class="max-h-96 overflow-y-auto divide-y divide-line">
            @forelse($recent as $n)
            <li class="flex items-start gap-3 px-4 py-3 {{ $n->read_at ? '' : 'bg-primary/5' }}">
                <span class="grid place-items-center w-9 h-9 rounded-xl bg-primary/10 text-primary shrink-0"><i data-lucide="{{ $n->data['icon'] ?? 'bell' }}" class="w-4 h-4"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium leading-tight">{{ $n->data['title'] ?? 'Notification' }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $n->data['message'] ?? '' }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                </div>
            </li>
            @empty
            <li data-bell-empty class="px-4 py-8 text-center text-sm text-slate-400"><i data-lucide="bell-off" class="w-7 h-7 mx-auto mb-2"></i>Aucune notification</li>
            @endforelse
        </ul>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const root = document.getElementById('notif-bell'); if (!root) return;
        const btn = root.querySelector('[data-bell]'), panel = root.querySelector('[data-bell-panel]'),
              list = root.querySelector('[data-bell-list]'), badge = root.querySelector('[data-bell-count]');

        btn.addEventListener('click', e => { e.stopPropagation(); panel.classList.toggle('hidden'); });
        document.addEventListener('click', e => { if (!root.contains(e.target)) panel.classList.add('hidden'); });

        const bump = () => {
            const n = (parseInt(badge.textContent) || 0) + 1;
            badge.textContent = n; badge.classList.remove('hidden');
        };
        const prepend = (data) => {
            root.querySelector('[data-bell-empty]')?.remove();
            const li = document.createElement('li');
            li.className = 'flex items-start gap-3 px-4 py-3 bg-primary/5';
            li.innerHTML = `<span class="grid place-items-center w-9 h-9 rounded-xl bg-primary/10 text-primary shrink-0"><i data-lucide="${data.icon || 'bell'}" class="w-4 h-4"></i></span>
                <div class="min-w-0 flex-1"><p class="text-sm font-medium leading-tight"></p><p class="text-xs text-slate-500 truncate"></p><p class="text-[11px] text-slate-400 mt-0.5">À l'instant</p></div>`;
            li.querySelectorAll('p')[0].textContent = data.title || 'Notification';
            li.querySelectorAll('p')[1].textContent = data.message || '';
            list.prepend(li);
            window.lucide?.createIcons();
        };

        // Écoute temps réel via Reverb (canal privé de l'utilisateur).
        if (window.Echo) {
            window.Echo.private('App.Models.User.{{ $user->id }}')
                .notification((notif) => { bump(); prepend(notif); });
        }
    })();
</script>
@endpush
