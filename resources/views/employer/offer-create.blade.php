@extends('layouts.base')
@section('title', ($offer ? 'Modifier' : 'Publier').' une offre — CyaoWork')

@php
    $editing = (bool) $offer;
    $val = fn ($field, $default = '') => old($field, $offer->$field ?? $default);
@endphp

@section('body')
<div class="min-h-dvh bg-[#F4F6FB]">
    <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
        <a href="{{ route('employer.dashboard') }}" class="grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Retour"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-bold text-lg">{{ $editing ? 'Modifier l\'offre' : 'Publier une offre' }}</h1>
    </header>

    <main class="mx-auto max-w-2xl p-4 sm:p-6">
        @if($errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-rose/10 border border-rose/20 text-rose px-4 py-3 font-medium">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <ul class="text-sm list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ $editing ? route('employer.offer.update', $offer) : route('employer.offer.store') }}" class="reveal rounded-3xl bg-white border border-line p-6 sm:p-8 space-y-5">
            @csrf
            @if($editing) @method('PUT') @endif

            <div>
                <label class="block text-sm font-semibold mb-1.5">Intitulé du poste <span class="text-rose">*</span></label>
                <input type="text" name="title" value="{{ $val('title') }}" required placeholder="Ex. Aide ménagère 3j/semaine"
                    class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Catégorie</label>
                    <select name="category_id" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary">
                        <option value="">— Choisir —</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected($val('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Ville</label>
                    <input type="text" name="city" value="{{ $val('city') }}" placeholder="Ex. Douala"
                        class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Rémunération (FCFA)</label>
                    <input type="number" name="salary_amount" value="{{ $val('salary_amount') }}" min="0" step="500" placeholder="Ex. 2500"
                        class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Périodicité <span class="text-rose">*</span></label>
                    <select name="salary_period" required class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary">
                        <option value="day" @selected($val('salary_period','day')==='day')>par jour</option>
                        <option value="hour" @selected($val('salary_period')==='hour')>par heure</option>
                        <option value="month" @selected($val('salary_period')==='month')>par mois</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Type de contrat <span class="text-rose">*</span></label>
                    <select name="contract_type" required class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary">
                        <option value="permanent" @selected($val('contract_type','permanent')==='permanent')>Permanent</option>
                        <option value="journalier" @selected($val('contract_type')==='journalier')>Journalier</option>
                        <option value="ponctuel" @selected($val('contract_type')==='ponctuel')>Ponctuel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Horaires</label>
                    <input type="text" name="schedule" value="{{ $val('schedule') }}" placeholder="Ex. Lun, Mer, Ven · matin"
                        class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Description</label>
                <textarea name="description" rows="4" placeholder="Décrivez la mission, les attentes, le profil recherché…"
                    class="w-full px-4 py-3 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary resize-y">{{ $val('description') }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" name="status" value="published"
                    class="btn-press flex-1 h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-accent to-accent-dark shadow-lg shadow-accent/25 inline-flex items-center justify-center gap-2">
                    <i data-lucide="{{ $editing ? 'save' : 'send' }}" class="w-5 h-5"></i>{{ $editing ? 'Enregistrer & publier' : "Publier l'offre" }}
                </button>
                <button type="submit" name="status" value="draft"
                    class="btn-press h-12 px-6 rounded-xl border border-line text-slate-600 font-semibold hover:border-primary hover:text-primary inline-flex items-center justify-center gap-2 transition-colors">
                    <i data-lucide="file-text" class="w-5 h-5"></i>Brouillon
                </button>
            </div>
        </form>
    </main>
</div>
@endsection
