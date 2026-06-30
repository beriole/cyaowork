@extends('layouts.base')
@section('title', 'Mon profil — CyaoWork')

@php $val = fn ($f, $d = '') => old($f, $profile->$f ?? $d); $mySkills = old('skills', $profile->skills->pluck('id')->all()); @endphp

@section('body')
<div class="min-h-dvh bg-[#F4F6FB]">
    <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
        <a href="{{ route('worker.dashboard') }}" class="grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Retour"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-bold text-lg">Mon profil</h1>
    </header>

    <main class="mx-auto max-w-2xl p-4 sm:p-6">
        @if($errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-rose/10 border border-rose/20 text-rose px-4 py-3 font-medium">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <ul class="text-sm list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('worker.profile.update') }}" class="reveal rounded-3xl bg-white border border-line p-6 sm:p-8 space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-semibold mb-1.5">Titre / métier</label>
                <input type="text" name="headline" value="{{ $val('headline') }}" placeholder="Ex. Aide ménagère expérimentée"
                    class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Catégorie</label>
                    <select name="category_id" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary">
                        <option value="">— Choisir —</option>
                        @foreach($categories as $c)<option value="{{ $c->id }}" @selected($val('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Ville</label>
                    <input type="text" name="city" value="{{ $val('city') }}" placeholder="Ex. Douala" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Expérience (ans)</label>
                    <input type="number" name="experience_years" value="{{ $val('experience_years') }}" min="0" max="60" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Tarif (FCFA)</label>
                    <input type="number" name="expected_salary" value="{{ $val('expected_salary') }}" min="0" step="500" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Par</label>
                    <select name="salary_period" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary">
                        <option value="day" @selected($val('salary_period','day')==='day')>jour</option>
                        <option value="hour" @selected($val('salary_period')==='hour')>heure</option>
                        <option value="month" @selected($val('salary_period')==='month')>mois</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Disponibilité</label>
                <select name="availability" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary">
                    <option value="immediate" @selected($val('availability','immediate')==='immediate')>Immédiate</option>
                    <option value="week" @selected($val('availability')==='week')>Sous une semaine</option>
                    <option value="flexible" @selected($val('availability')==='flexible')>Flexible</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">À propos</label>
                <textarea name="bio" rows="4" placeholder="Présentez votre expérience, vos atouts…" class="w-full px-4 py-3 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary resize-y">{{ $val('bio') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Compétences</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($skills as $s)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="skills[]" value="{{ $s->id }}" class="peer sr-only" @checked(in_array($s->id, $mySkills)) />
                        <span class="inline-flex items-center gap-1.5 text-sm rounded-full border border-line px-3 py-1.5 font-medium text-slate-600 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-colors"><i data-lucide="check" class="w-3.5 h-3.5"></i>{{ $s->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-press w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>Enregistrer le profil
            </button>
        </form>
    </main>
</div>
@endsection
