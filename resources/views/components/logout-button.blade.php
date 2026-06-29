@props(['dark' => false])
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" {{ $attributes->merge(['class' => 'btn-press grid place-items-center w-10 h-10 rounded-xl '.($dark ? 'hover:bg-white/10 text-slate-300' : 'hover:bg-muted text-slate-600')]) }} title="Se déconnecter" aria-label="Se déconnecter">
        <i data-lucide="log-out" class="w-5 h-5"></i>
    </button>
</form>
