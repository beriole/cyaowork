<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\EmployerProfile;
use App\Models\Message;
use App\Models\JobOffer;
use App\Models\Review;
use App\Models\Skill;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Rôles (spatie) ----
        foreach (['worker', 'employer', 'admin'] as $role) {
            Role::findOrCreate($role);
        }

        // ---- Catégories ----
        $catData = [
            ['name' => 'Ménage',           'icon' => 'sparkles', 'gradient' => 'from-sky-400 to-blue-600'],
            ['name' => "Garde d'enfants",  'icon' => 'baby',     'gradient' => 'from-rose-400 to-pink-600'],
            ['name' => 'Cuisine',          'icon' => 'chef-hat', 'gradient' => 'from-amber-400 to-orange-600'],
            ['name' => 'Gardiennage',      'icon' => 'shield',   'gradient' => 'from-indigo-400 to-violet-600'],
            ['name' => 'Plomberie',        'icon' => 'wrench',   'gradient' => 'from-teal-400 to-emerald-600'],
            ['name' => 'Jardinage',        'icon' => 'trees',    'gradient' => 'from-green-400 to-lime-600'],
        ];
        $cats = [];
        foreach ($catData as $c) {
            $cats[$c['name']] = Category::create([...$c, 'slug' => Str::slug($c['name'])]);
        }

        // ---- Compétences ----
        $skillNames = ['Ménage', 'Repassage', 'Cuisine', 'Petite enfance', 'Soins', 'Éveil', 'Plats locaux', 'Pâtisserie', 'Sécurité', 'Nuit', 'Sanitaire', 'Dépannage', 'Espaces verts', 'Élagage'];
        $skills = [];
        foreach ($skillNames as $s) {
            $skills[$s] = Skill::create(['name' => $s, 'slug' => Str::slug($s)]);
        }

        // ---- Admin ----
        $admin = User::create([
            'name' => 'Admin CyaoWork', 'email' => 'admin@cyaowork.cm',
            'phone' => '+237600000000', 'password' => Hash::make('password'),
            'role' => 'admin', 'is_verified' => true,
        ]);
        $admin->assignRole('admin');

        // ---- Employeur ----
        $employer = User::create([
            'name' => 'Mme Tchoua', 'email' => 'employeur@cyaowork.cm',
            'phone' => '+237699111222', 'password' => Hash::make('password'),
            'role' => 'employer', 'is_verified' => true,
        ]);
        $employer->assignRole('employer');
        EmployerProfile::create([
            'user_id' => $employer->id, 'company_name' => 'Particulier', 'type' => 'individual',
            'city' => 'Yaoundé', 'latitude' => 3.848, 'longitude' => 11.502, 'verification_status' => 'verified',
        ]);
        Subscription::create([
            'employer_id' => $employer->id, 'plan' => 'pro', 'status' => 'active',
            'starts_at' => now()->subDays(12), 'ends_at' => now()->addDays(18),
        ]);
        Transaction::create([
            'user_id' => $employer->id, 'type' => 'subscription', 'amount' => 15000,
            'provider' => 'momo', 'reference' => 'TX-'.Str::upper(Str::random(8)), 'status' => 'success',
        ]);

        // ---- Travailleurs ----
        $workers = [
            ['name' => 'Aïssa Mballa',  'phone' => '+237678000021', 'cat' => 'Ménage',          'head' => 'Aide ménagère', 'photo' => '1531123897727-8f129e1688ce', 'exp' => 4, 'rate' => 4.9, 'rev' => 38, 'sal' => 2500,  'per' => 'day',   'city' => 'Douala',  'lat' => 4.043, 'lng' => 9.694,  'verif' => 'verified', 'avail' => 'immediate', 'skills' => ['Ménage', 'Repassage', 'Cuisine']],
            ['name' => 'Jean Biya',     'phone' => '+237678000022', 'cat' => 'Gardiennage',     'head' => 'Gardien',       'photo' => '1463453091185-61582044d556', 'exp' => 7, 'rate' => 4.7, 'rev' => 21, 'sal' => 85000, 'per' => 'month', 'city' => 'Yaoundé', 'lat' => 3.866, 'lng' => 11.516, 'verif' => 'verified', 'avail' => 'week',      'skills' => ['Sécurité', 'Nuit']],
            ['name' => 'Flore Ngono',   'phone' => '+237678000023', 'cat' => 'Cuisine',         'head' => 'Cuisinière',    'photo' => '1507152832244-10d45c7eda57', 'exp' => 5, 'rate' => 4.6, 'rev' => 12, 'sal' => 3000,  'per' => 'day',   'city' => 'Douala',  'lat' => 4.060, 'lng' => 9.710,  'verif' => 'pending',  'avail' => 'immediate', 'skills' => ['Plats locaux', 'Pâtisserie']],
            ['name' => 'Mireille Kana', 'phone' => '+237678000024', 'cat' => "Garde d'enfants", 'head' => 'Nounou',        'photo' => '1589156280159-27698a70f29e', 'exp' => 6, 'rate' => 5.0, 'rev' => 44, 'sal' => 60000, 'per' => 'month', 'city' => 'Yaoundé', 'lat' => 3.870, 'lng' => 11.520, 'verif' => 'verified', 'avail' => 'immediate', 'skills' => ['Petite enfance', 'Soins', 'Éveil']],
            ['name' => 'Paul Etoa',     'phone' => '+237678000025', 'cat' => 'Plomberie',       'head' => 'Plombier',      'photo' => '1531384441138-2736e62e0919', 'exp' => 9, 'rate' => 4.8, 'rev' => 33, 'sal' => 15000, 'per' => 'day',   'city' => 'Douala',  'lat' => 4.070, 'lng' => 9.700,  'verif' => 'verified', 'avail' => 'week',      'skills' => ['Sanitaire', 'Dépannage']],
            ['name' => 'Samuel Tabi',   'phone' => '+237678000026', 'cat' => 'Jardinage',       'head' => 'Jardinier',     'photo' => '1522529599102-193c0d76b5b6', 'exp' => 3, 'rate' => 4.5, 'rev' => 9,  'sal' => 4000,  'per' => 'day',   'city' => 'Buea',    'lat' => 4.155, 'lng' => 9.241,  'verif' => 'verified', 'avail' => 'immediate', 'skills' => ['Espaces verts', 'Élagage']],
        ];

        $workerUsers = [];
        foreach ($workers as $i => $w) {
            $u = User::create([
                'name' => $w['name'], 'email' => 'worker'.($i + 1).'@cyaowork.cm',
                'phone' => $w['phone'], 'password' => Hash::make('password'),
                'role' => 'worker', 'is_verified' => $w['verif'] === 'verified', 'avatar' => $w['photo'],
            ]);
            $u->assignRole('worker');
            $profile = WorkerProfile::create([
                'user_id' => $u->id, 'category_id' => $cats[$w['cat']]->id, 'headline' => $w['head'],
                'bio' => $w['head'].' expérimenté(e), sérieux(se) et ponctuel(le).',
                'photo' => $w['photo'], 'experience_years' => $w['exp'], 'availability' => $w['avail'],
                'expected_salary' => $w['sal'], 'salary_period' => $w['per'],
                'city' => $w['city'], 'latitude' => $w['lat'], 'longitude' => $w['lng'],
                'verification_status' => $w['verif'], 'rating_avg' => $w['rate'], 'reviews_count' => $w['rev'],
            ]);
            $profile->skills()->attach(collect($w['skills'])->map(fn ($s) => $skills[$s]->id));
            $workerUsers[] = $u;
        }

        // ---- Offres ----
        $offers = [
            ['title' => 'Aide ménagère — 3j/semaine', 'cat' => 'Ménage',          'sal' => 2500,  'per' => 'day',   'type' => 'permanent', 'status' => 'published', 'boost' => true,  'views' => 340, 'city' => 'Douala',  'lat' => 4.05, 'lng' => 9.70],
            ['title' => 'Nounou à temps plein',       'cat' => "Garde d'enfants", 'sal' => 60000, 'per' => 'month', 'type' => 'permanent', 'status' => 'published', 'boost' => false, 'views' => 210, 'city' => 'Yaoundé', 'lat' => 3.87, 'lng' => 11.52],
            ['title' => 'Cuisinier — événementiel',   'cat' => 'Cuisine',         'sal' => 8000,  'per' => 'day',   'type' => 'ponctuel',  'status' => 'filled',    'boost' => false, 'views' => 155, 'city' => 'Douala',  'lat' => 4.06, 'lng' => 9.71],
            ['title' => 'Gardien de nuit',            'cat' => 'Gardiennage',     'sal' => 70000, 'per' => 'month', 'type' => 'permanent', 'status' => 'draft',     'boost' => false, 'views' => 0,   'city' => 'Douala',  'lat' => 4.07, 'lng' => 9.69],
        ];
        $offerModels = [];
        foreach ($offers as $o) {
            $offerModels[] = JobOffer::create([
                'employer_id' => $employer->id, 'category_id' => $cats[$o['cat']]->id,
                'title' => $o['title'], 'description' => 'Recherche une personne de confiance, vérifiée et expérimentée. Mission régulière, paiement via Mobile Money.',
                'salary_amount' => $o['sal'], 'salary_period' => $o['per'], 'schedule' => 'À convenir',
                'city' => $o['city'], 'latitude' => $o['lat'], 'longitude' => $o['lng'],
                'contract_type' => $o['type'], 'status' => $o['status'], 'is_boosted' => $o['boost'], 'views' => $o['views'],
            ]);
        }

        // ---- Candidatures ----
        $apps = [[0, 0, 'interview'], [0, 1, 'seen'], [1, 3, 'accepted'], [2, 2, 'sent']];
        foreach ($apps as [$wi, $oi, $status]) {
            Application::create([
                'job_offer_id' => $offerModels[$oi]->id, 'worker_id' => $workerUsers[$wi]->id,
                'status' => $status, 'message' => 'Bonjour, je suis intéressé(e) par votre offre.',
            ]);
        }

        // ---- Avis ----
        Review::create(['reviewer_id' => $employer->id, 'reviewee_id' => $workerUsers[0]->id, 'rating' => 5, 'comment' => 'Travail impeccable, très ponctuelle. Je recommande !']);
        Review::create(['reviewer_id' => $workerUsers[0]->id, 'reviewee_id' => $employer->id, 'rating' => 5, 'comment' => 'Employeuse respectueuse, paiement à temps.']);

        // ---- Conversations & messages ----
        $conv = Conversation::create([
            'employer_id' => $employer->id, 'worker_id' => $workerUsers[0]->id,
            'job_offer_id' => $offerModels[0]->id, 'last_message_at' => now()->subMinutes(2),
        ]);
        $thread = [
            [$employer->id, "Bonjour Aïssa, j'ai vu votre profil, il correspond à ce que je recherche.", 20],
            [$employer->id, 'Êtes-vous disponible lundi matin pour commencer ?', 19],
            [$workerUsers[0]->id, 'Bonjour Madame ! Oui, je suis disponible dès lundi.', 18],
            [$employer->id, "Parfait. C'est 3 jours par semaine : lundi, mercredi, vendredi.", 5],
            [$employer->id, 'Bonjour, êtes-vous disponible lundi ?', 2],
        ];
        foreach ($thread as [$sender, $body, $minAgo]) {
            Message::create([
                'conversation_id' => $conv->id, 'sender_id' => $sender, 'body' => $body,
                'read_at' => $sender === $workerUsers[0]->id ? now() : null,
                'created_at' => now()->subMinutes($minAgo), 'updated_at' => now()->subMinutes($minAgo),
            ]);
        }

        Conversation::create([
            'employer_id' => $employer->id, 'worker_id' => $workerUsers[3]->id,
            'job_offer_id' => $offerModels[1]->id, 'last_message_at' => now()->subHours(1),
        ])->messages()->create([
            'sender_id' => $employer->id, 'body' => 'Merci pour votre retour, à bientôt.', 'created_at' => now()->subHour(),
        ]);
    }
}
