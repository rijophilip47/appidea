<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Document;
use App\Models\DocumentAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentAttachmentController extends Controller
{
    public function download(Document $document, DocumentAttachment $attachment): Response
    {
        $this->authorize('downloadAttachment', $document);

        if ($attachment->document_id !== $document->id) {
            abort(404, 'Attachment not found for this document.');
        }

        if (!Storage::disk($attachment->disk)->exists($attachment->storage_path)) {
            abort(404, 'The requested attachment file is missing from private storage.');
        }

        AuditEvent::log(
            $document->family_id,
            'attachment.downloaded',
            'DocumentAttachment',
            $attachment->id,
            ['document_title' => $document->title, 'file_name' => $attachment->original_name]
        );

        return Storage::disk($attachment->disk)->download(
            $attachment->storage_path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'Cache-Control' => 'no-cache, private',
            ]
        );
    }

    public function destroy(Document $document, DocumentAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $document);

        if ($attachment->document_id !== $document->id) {
            abort(404);
        }

        if (Storage::disk($attachment->disk)->exists($attachment->storage_path)) {
            Storage::disk($attachment->disk)->delete($attachment->storage_path);
        }

        $fileName = $attachment->original_name;
        $attachment->delete();

        AuditEvent::log(
            $document->family_id,
            'attachment.deleted',
            'DocumentAttachment',
            null,
            ['file_name' => $fileName, 'document_title' => $document->title]
        );

        return back()->with('success', "Attachment '{$fileName}' was removed.");
    }
}
