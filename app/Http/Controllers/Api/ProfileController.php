<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\WorkerProfileResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /** GET /profile — profil de l'utilisateur connecté */
    public function show(Request $request)
    {
        $user = $request->user();
        $payload = ['user' => new UserResource($user)];

        if ($user->isWorker()) {
            $user->load(['workerProfile.category', 'workerProfile.skills']);
            $payload['worker_profile'] = $user->workerProfile
                ? new WorkerProfileResource($user->workerProfile) : null;
        }

        return response()->json($payload);
    }

    /** PUT /worker/profile — mise à jour du profil travailleur */
    public function updateWorker(Request $request)
    {
        abort_unless($request->user()->isWorker(), 403);

        $data = $request->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'availability' => ['nullable', 'in:immediate,week,flexible'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_period' => ['nullable', 'in:hour,day,month'],
            'city' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['integer', 'exists:skills,id'],
        ]);

        $profile = $request->user()->workerProfile()->firstOrCreate([]);
        $profile->update(collect($data)->except('skills')->toArray());

        if (array_key_exists('skills', $data)) {
            $profile->skills()->sync($data['skills'] ?? []);
        }

        return new WorkerProfileResource($profile->fresh()->load(['category', 'skills']));
    }
}
