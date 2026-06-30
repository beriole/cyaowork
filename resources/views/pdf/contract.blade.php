<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        @page { margin: 110px 40px 80px 40px; }
        body { color: #0F172A; font-size: 12px; line-height: 1.5; }
        header { position: fixed; top: -80px; left: 0; right: 0; height: 70px; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 50px; color: #94A3B8; font-size: 10px; text-align: center; border-top: 1px solid #E2E8F0; padding-top: 8px; }
        .brand { font-size: 22px; font-weight: bold; color: #17266A; }
        .brand span { color: #F26A21; }
        .badge { float: right; background: #F26A21; color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        h1 { font-size: 18px; color: #17266A; margin: 0 0 2px; }
        .ref { color: #64748B; font-size: 11px; }
        .parties { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .parties td { width: 50%; vertical-align: top; padding: 12px; border: 1px solid #E2E8F0; border-radius: 8px; }
        .label { color: #64748B; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
        .name { font-size: 14px; font-weight: bold; }
        .section-title { background: #EEF2F6; color: #17266A; font-weight: bold; padding: 6px 10px; border-radius: 6px; margin: 18px 0 8px; }
        table.details { width: 100%; border-collapse: collapse; }
        table.details td { padding: 6px 10px; border-bottom: 1px solid #EEF2F6; }
        table.details td.k { color: #64748B; width: 38%; }
        table.details td.v { font-weight: bold; }
        .terms p { margin: 6px 0; }
        .sign { width: 100%; border-collapse: collapse; margin-top: 26px; }
        .sign td { width: 50%; vertical-align: top; padding: 14px; border: 1px solid #E2E8F0; }
        .signed { color: #D2551A; font-weight: bold; }
        .pending { color: #D97706; font-weight: bold; }
        .stamp { margin-top: 8px; font-size: 10px; color: #64748B; }
        .chip { display: inline-block; background: #FFF3EC; color: #D2551A; border: 1px solid #FBD9C3; padding: 2px 8px; border-radius: 20px; font-size: 10px; }
    </style>
</head>
<body>
    <header>
        <span class="brand">Cyao<span>Work</span></span>
        <span class="badge">CONTRAT DE MISSION</span>
    </header>
    <footer>
        Document généré électroniquement par CyaoWork — Référence {{ $reference }}. Ce contrat fait foi entre les parties.
    </footer>

    <h1>Contrat de mission</h1>
    <p class="ref">Référence : <strong>{{ $reference }}</strong> · Émis le {{ $issued_at->format('d/m/Y à H:i') }}</p>

    <table class="parties">
        <tr>
            <td>
                <div class="label">Employeur</div>
                <div class="name">{{ $employer->name }}</div>
                <div>{{ $employer->phone }}</div>
                @if($employer->is_verified)<div class="chip">Entreprise vérifiée</div>@endif
            </td>
            <td>
                <div class="label">Travailleur</div>
                <div class="name">{{ $worker->name }}</div>
                <div>{{ $worker->phone }}</div>
                @if($worker->is_verified)<div class="chip">Profil vérifié</div>@endif
            </td>
        </tr>
    </table>

    <div class="section-title">Objet de la mission</div>
    <table class="details">
        <tr><td class="k">Intitulé du poste</td><td class="v">{{ $offer->title }}</td></tr>
        <tr><td class="k">Catégorie</td><td class="v">{{ $offer->category?->name ?? '—' }}</td></tr>
        <tr><td class="k">Lieu</td><td class="v">{{ $offer->city ?? '—' }}</td></tr>
        <tr><td class="k">Rémunération</td><td class="v">{{ number_format($offer->salary_amount, 0, ',', ' ') }} FCFA / {{ $periodFr[$offer->salary_period] ?? $offer->salary_period }}</td></tr>
        <tr><td class="k">Type de contrat</td><td class="v">{{ ucfirst($offer->contract_type) }}</td></tr>
        <tr><td class="k">Horaires</td><td class="v">{{ $offer->schedule ?? 'À convenir' }}</td></tr>
    </table>

    <div class="section-title">Conditions générales</div>
    <div class="terms">
        {!! nl2br(e($terms)) !!}
    </div>

    <table class="sign">
        <tr>
            <td>
                <div class="label">Signature Employeur</div>
                @if($employer_signed_at)
                    <div class="signed">✓ Signé électroniquement</div>
                    <div class="stamp">{{ $employer->name }}<br>Le {{ $employer_signed_at->format('d/m/Y à H:i') }}</div>
                @else
                    <div class="pending">En attente de signature</div>
                @endif
            </td>
            <td>
                <div class="label">Signature Travailleur</div>
                @if($worker_signed_at)
                    <div class="signed">✓ Signé électroniquement</div>
                    <div class="stamp">{{ $worker->name }}<br>Le {{ $worker_signed_at->format('d/m/Y à H:i') }}</div>
                @else
                    <div class="pending">En attente de signature</div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
