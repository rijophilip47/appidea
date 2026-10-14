<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Document;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentExportController extends Controller
{
    /**
     * Export all documents of the family to CSV with formula injection prevention.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        if (!$user->canManageFamily($family->id)) {
            abort(403, 'Only family administrators and owners can export workspace data.');
        }

        $documents = Document::with(['category', 'responsibleUser', 'creator'])
            ->where('family_id', $family->id)
            ->orderBy('id', 'asc')
            ->get();

        AuditEvent::log(
            $family->id,
            'export.csv',
            'Export',
            null,
            ['records_exported' => $documents->count()]
        );

        $fileName = 'family_documents_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($documents) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header Row
            fputcsv($handle, [
                'ID',
                'Title',
                'Category',
                'Status',
                'Expiry Date',
                'Days Remaining',
                'Issuer / Provider',
                'Reference Number',
                'Issue Date',
                'Purchase Date',
                'Responsible Person',
                'Notes',
                'Archived',
                'Created At',
                'Last Updated At',
            ]);

            foreach ($documents as $doc) {
                $status = $doc->computed_status;

                fputcsv($handle, [
                    $doc->id,
                    $this->sanitizeForCsv($doc->title),
                    $this->sanitizeForCsv($doc->category?->name ?? 'Uncategorized'),
                    $status['label'],
                    $doc->expiry_date ? $doc->expiry_date->format('Y-m-d') : 'None',
                    $status['days'] !== null ? $status['days'] : '',
                    $this->sanitizeForCsv($doc->issuer ?? ''),
                    $this->sanitizeForCsv($doc->reference_number ?? ''),
                    $doc->issue_date ? $doc->issue_date->format('Y-m-d') : '',
                    $doc->purchase_date ? $doc->purchase_date->format('Y-m-d') : '',
                    $this->sanitizeForCsv($doc->responsibleUser?->name ?? 'Unassigned'),
                    $this->sanitizeForCsv($doc->notes ?? ''),
                    $doc->archived_at ? 'Yes (' . $doc->archived_at->format('Y-m-d') . ')' : 'No',
                    $doc->created_at->format('Y-m-d H:i:s'),
                    $doc->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Escape spreadsheet formula injection triggers (=, +, -, @, \t, \r).
     */
    protected function sanitizeForCsv(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $firstChar = substr($value, 0, 1);
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}

