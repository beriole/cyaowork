<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()->hasRole('admin'), 403, 'Réservé aux administrateurs.');
    }

    /** GET /admin/verifications — file des pièces en attente. */
    public function verifications(Request $request)
    {
        $this->ensureAdmin($request);

        $docs = Document::where('status', 'pending')
            ->with('user:id,name,phone')->latest()->paginate(20)
            ->through(fn ($d) => [
                'id' => $d->id,
                'type' => $d->type,
                'status' => $d->status,
                'submitted_at' => $d->created_at,
                'preview_url' => $d->fileUrl(),
                'user' => ['id' => $d->user->id, 'name' => $d->user->name, 'phone' => $d->user->phone],
            ]);

        return response()->json($docs);
    }

    /** POST /admin/documents/{document}/approve */
    public function approve(Request $request, Document $document)
    {
        $this->ensureAdmin($request);
        $document->update(['status' => 'approved']);

        // Une pièce d'identité validée → profil « vérifié ».
        if (in_array($document->type, ['cni', 'passeport'], true)) {
            $document->user->workerProfile?->update(['verification_status' => 'verified']);
            $document->user->update(['is_verified' => true]);
        }

        return response()->json(['id' => $document->id, 'status' => 'approved']);
    }

    /** POST /admin/documents/{document}/reject */
    public function reject(Request $request, Document $document)
    {
        $this->ensureAdmin($request);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);
        $document->update(['status' => 'rejected']);

        if (in_array($document->type, ['cni', 'passeport'], true)) {
            $document->user->workerProfile?->update(['verification_status' => 'rejected']);
        }

        return response()->json(['id' => $document->id, 'status' => 'rejected', 'reason' => $data['reason'] ?? null]);
    }

    /** GET /admin/documents/{document}/preview — flux du fichier privé. */
    public function preview(Request $request, Document $document): StreamedResponse
    {
        $this->ensureAdmin($request);
        $media = $document->getFirstMedia('file');
        abort_unless($media, 404);

        return response()->stream(
            fn () => print(file_get_contents($media->getPath())),
            200,
            ['Content-Type' => $media->mime_type, 'Content-Disposition' => 'inline; filename="'.$media->file_name.'"']
        );
    }
}
