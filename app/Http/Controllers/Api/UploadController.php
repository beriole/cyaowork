<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkerProfileResource;
use App\Models\Document;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /** POST /worker/photo — photo de profil (image). */
    public function photo(Request $request)
    {
        abort_unless($request->user()->isWorker(), 403);
        $request->validate(['photo' => ['required', 'image', 'max:4096']]);

        $profile = $request->user()->workerProfile()->firstOrCreate([]);
        $profile->addMediaFromRequest('photo')->toMediaCollection('avatar');

        return response()->json([
            'message' => 'Photo mise à jour.',
            'photo_url' => $profile->fresh()->photoUrl(320),
        ]);
    }

    /** POST /worker/documents — pièce justificative (CNI, diplôme…) → vérification en attente. */
    public function document(Request $request)
    {
        abort_unless($request->user()->isWorker(), 403);
        $data = $request->validate([
            'type' => ['required', 'in:cni,passeport,cv,diplome'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ]);

        $document = Document::create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'file_path' => '', // renseigné via medialibrary
            'status' => 'pending',
        ]);
        $media = $document->addMediaFromRequest('file')->toMediaCollection('file');
        $document->update(['file_path' => $media->getPath()]);

        // Une pièce d'identité déposée met le profil en cours de vérification.
        if (in_array($data['type'], ['cni', 'passeport'], true)) {
            $request->user()->workerProfile()->firstOrCreate([])->update(['verification_status' => 'pending']);
        }

        return response()->json([
            'message' => 'Document envoyé. Vérification sous 24-48h.',
            'document' => ['id' => $document->id, 'type' => $document->type, 'status' => $document->status],
        ], 201);
    }

    /** GET /worker/documents — mes documents. */
    public function myDocuments(Request $request)
    {
        return response()->json(
            $request->user()->documents()->latest()->get(['id', 'type', 'status', 'created_at'])
        );
    }

    /** GET /worker/profile/me — profil avec photo à jour (helper). */
    public function me(Request $request)
    {
        abort_unless($request->user()->isWorker(), 403);

        return new WorkerProfileResource(
            $request->user()->workerProfile()->firstOrCreate([])->load(['category', 'skills'])
        );
    }
}
