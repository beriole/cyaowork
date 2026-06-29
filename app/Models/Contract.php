<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $guarded = [];

    protected $casts = [
        'worker_signed_at' => 'datetime',
        'employer_signed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function reference(): string
    {
        return 'CTR-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function isFullySigned(): bool
    {
        return $this->worker_signed_at && $this->employer_signed_at;
    }

    /** Données pour le template PDF. */
    public function viewData(): array
    {
        $app = $this->application->load(['worker', 'jobOffer.category', 'jobOffer.employer']);
        $offer = $app->jobOffer;

        return [
            'reference' => $this->reference(),
            'issued_at' => $this->created_at,
            'employer' => $offer->employer,
            'worker' => $app->worker,
            'offer' => $offer,
            'terms' => $this->terms,
            'employer_signed_at' => $this->employer_signed_at,
            'worker_signed_at' => $this->worker_signed_at,
            'periodFr' => ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois', 'intervention' => 'intervention'],
        ];
    }

    /** Termes par défaut générés à partir de l'offre. */
    public static function defaultTerms($offer, $worker, $employer): string
    {
        $salary = number_format($offer->salary_amount, 0, ',', ' ');
        $period = ['hour' => 'heure', 'day' => 'jour', 'month' => 'mois'][$offer->salary_period] ?? $offer->salary_period;

        return implode("\n", [
            "1. {$worker->name} s'engage à exécuter la mission « {$offer->title} » avec sérieux, ponctualité et discrétion.",
            "2. {$employer->name} s'engage à verser la rémunération convenue de {$salary} FCFA par {$period}, via Mobile Money (MTN MoMo / Orange Money).",
            "3. Le lieu d'exécution est : {$offer->city}. Les horaires sont convenus entre les parties.",
            "4. Chaque partie peut mettre fin à la mission moyennant un préavis raisonnable.",
            "5. En cas de litige, CyaoWork peut être sollicité comme médiateur. Les parties s'engagent à un comportement respectueux.",
            "6. Les deux parties pourront s'évaluer mutuellement à l'issue de la mission.",
        ]);
    }
}
