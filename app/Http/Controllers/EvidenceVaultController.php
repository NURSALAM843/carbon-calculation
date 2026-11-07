<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterData\UploadEvidenceRequest;
use App\Models\EvidenceFile;
use App\Models\LandBlock;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EvidenceVaultController extends Controller
{
    public function index(): Response
    {
        $evidences = EvidenceFile::with(['landBlock.estate'])
            ->latest()
            ->paginate(15);

        return Inertia::render('data/EvidenceVault', [
            'evidences' => $evidences,
            'blocks' => LandBlock::select('id', 'name', 'code')->get(),
        ]);
    }

    public function upload(UploadEvidenceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();

        // Hitung SHA-256 hash dokumen untuk verifikasi kriptografis
        $hash = hash_file('sha256', $file->getRealPath());

        $path = $file->store('evidence', 'public');

        $evidence = EvidenceFile::create([
            'land_block_id' => $validated['land_block_id'] ?? null,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $mime,
            'hash' => $hash,
        ]);

        AuditLogger::log('upload_evidence', $evidence, null, [
            'file_name' => $originalName,
            'hash' => $hash,
        ]);

        return back()->with('success', "Berkas bukti terverifikasi SHA-256 ({$hash}) dan tersimpan di vault.");
    }
}
