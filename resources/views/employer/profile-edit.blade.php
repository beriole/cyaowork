@extends('layouts.base')
@section('title', 'Mon profil entreprise — CyaoWork')

@php $val = fn ($f, $d = '') => old($f, $d); @endphp

@section('body')
<div class="min-h-dvh bg-[#F4F6FB]">
    <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-xl border-b border-line flex items-center gap-3 px-4 sm:px-6">
        <a href="{{ route('employer.dashboard') }}" class="grid place-items-center w-10 h-10 rounded-xl hover:bg-muted" aria-label="Retour"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
        <h1 class="font-bold text-lg">Mon profil</h1>
    </header>

    <main class="mx-auto max-w-2xl p-4 sm:p-6">
        @if($errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-rose/10 border border-rose/20 text-rose px-4 py-3 font-medium">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <ul class="text-sm list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('employer.profile.update') }}" class="reveal rounded-3xl bg-white border border-line p-6 sm:p-8 space-y-5">
            @csrf @method('PUT')

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Nom du contact <span class="text-rose">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $employer->name) }}" required class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $employer->email) }}" placeholder="vous@exemple.cm" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1.5">Type de recruteur</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="individual" class="peer sr-only" @checked(old('type', $profile->type) === 'individual') />
                        <span class="flex items-center gap-2 h-12 px-4 rounded-xl border border-line font-medium text-slate-600 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary transition-colors"><i data-lucide="user" class="w-4 h-4"></i>Particulier</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="company" class="peer sr-only" @checked(old('type', $profile->type) === 'company') />
                        <span class="flex items-center gap-2 h-12 px-4 rounded-xl border border-line font-medium text-slate-600 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary transition-colors"><i data-lucide="building-2" class="w-4 h-4"></i>Entreprise</span>
                    </label>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Nom de l'entreprise</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $profile->company_name) }}" placeholder="Ex. Maison Tchoua" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $profile->city) }}" placeholder="Ex. Douala" class="w-full h-12 px-4 rounded-xl bg-muted outline-none focus:ring-2 focus:ring-primary" />
                </div>
            </div>

            <button type="submit" class="btn-press w-full h-12 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow-lg shadow-primary/25 inline-flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>Enregistrer
            </button>
        </form>
    </main>
</div>
@endsection
