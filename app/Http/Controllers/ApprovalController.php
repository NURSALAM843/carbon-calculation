<?php

namespace App\Http\Controllers;

use App\Http\Requests\Approval\ApproveCalculationRequest;
use App\Http\Requests\Approval\RejectCalculationRequest;
use App\Models\Approval;
use App\Models\CalculationRun;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function index(): Response
    {
        $pendingRuns = CalculationRun::with([
            'landBlock.estate',
            'methodologyVersion.methodology',
            'executor',
            'result',
        ])
            ->pendingApproval()
            ->latest()
            ->paginate(15);

        $recentApprovals = Approval::with(['calculationRun.landBlock', 'reviewer'])
            ->latest('reviewed_at')
            ->take(10)
            ->get();

        return Inertia::render('approvals/Index', [
            'pendingRuns' => $pendingRuns,
            'recentApprovals' => $recentApprovals,
        ]);
    }

    public function submit(string $id): RedirectResponse
    {
        $run = CalculationRun::findOrFail($id);

        if ($run->status !== 'draft') {
            return back()->with('error', 'Hanya kalkulasi berstatus Draft yang dapat diajukan untuk persetujuan.');
        }

        $oldStatus = $run->status;
        $run->update(['status' => 'pending_approval']);

        AuditLogger::log('submit_approval', $run, ['status' => $oldStatus], ['status' => 'pending_approval']);

        return back()->with('success', 'Kalkulasi berhasil diajukan ke antrean persetujuan auditor.');
    }

    public function approve(ApproveCalculationRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();
        $run = CalculationRun::findOrFail($id);

        if ($run->status !== 'pending_approval') {
            return back()->with('error', 'Hanya kalkulasi dengan status Pending Approval yang dapat disetujui.');
        }

        $oldStatus = $run->status;

        Approval::create([
            'calculation_run_id' => $run->id,
            'reviewer_id' => Auth::id() ?? 1,
            'status' => 'approved',
            'notes' => $validated['notes'] ?? 'Calculation run verified and approved in accordance with standard methodology guidelines.',
            'reviewed_at' => now(),
        ]);

        $run->update(['status' => 'approved']);

        AuditLogger::log('approve', $run, ['status' => $oldStatus], ['status' => 'approved', 'notes' => $validated['notes'] ?? null]);

        return back()->with('success', 'Kalkulasi resmi disetujui dan terkunci permanen untuk sertifikasi audit.');
    }

    public function reject(RejectCalculationRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();
        $run = CalculationRun::findOrFail($id);

        if ($run->status !== 'pending_approval') {
            return back()->with('error', 'Hanya kalkulasi berstatus Pending Approval yang dapat ditolak.');
        }

        $oldStatus = $run->status;

        Approval::create([
            'calculation_run_id' => $run->id,
            'reviewer_id' => Auth::id() ?? 1,
            'status' => 'rejected',
            'notes' => $request->notes,
            'reviewed_at' => now(),
        ]);

        $run->update(['status' => 'rejected']);

        AuditLogger::log('reject', $run, ['status' => $oldStatus], ['status' => 'rejected', 'notes' => $request->notes]);

        return back()->with('info', 'Kalkulasi dikembalikan dengan catatan revisi untuk surveyor.');
    }
}
