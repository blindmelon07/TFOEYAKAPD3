<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClubDocumentRequest;
use App\Models\Club;
use App\Models\ClubDocument;
use App\Services\LetterheadRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClubDocumentController extends Controller
{
    /**
     * List the club's documents and show which letterhead they print on.
     */
    public function index(Request $request, Club $club): Response
    {
        Gate::authorize('manageDocuments', $club);

        $documents = $club->documents()
            ->with('author:id,name')
            ->latest('document_date')
            ->latest('id')
            ->get()
            ->map(fn (ClubDocument $document): array => [
                'id' => $document->id,
                'title' => $document->title,
                'document_date' => $document->document_date->format('Y-m-d'),
                'excerpt' => $document->plainTextExcerpt(),
                'author' => $document->author?->name,
                'updated_at' => $document->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/clubs/documents/index', [
            'club' => $club->only(['id', 'name']),
            'documents' => $documents,
            'hasOwnLetterhead' => $club->hasOwnLetterhead(),
            'canManageLetterhead' => $request->user()?->can('manageLetterhead', $club) ?? false,
        ]);
    }

    /**
     * Show the form for writing a new document.
     */
    public function create(Club $club): Response
    {
        Gate::authorize('manageDocuments', $club);

        return Inertia::render('admin/clubs/documents/form', [
            'club' => $club->only(['id', 'name']),
            'document' => null,
            'today' => now()->format('Y-m-d'),
            'hasOwnLetterhead' => $club->hasOwnLetterhead(),
        ]);
    }

    /**
     * Save a new document.
     */
    public function store(ClubDocumentRequest $request, Club $club): RedirectResponse
    {
        $document = new ClubDocument($request->validated());
        $document->created_by = $request->user()?->id;
        $club->documents()->save($document);

        Inertia::flash('success', "“{$document->title}” saved. You can download it now.");

        return to_route('admin.clubs.documents.edit', [$club, $document]);
    }

    /**
     * Show the form for editing a document.
     */
    public function edit(Club $club, ClubDocument $document): Response
    {
        Gate::authorize('manageDocuments', $club);

        return Inertia::render('admin/clubs/documents/form', [
            'club' => $club->only(['id', 'name']),
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'document_date' => $document->document_date->format('Y-m-d'),
                'body' => $document->body,
            ],
            'today' => now()->format('Y-m-d'),
            'hasOwnLetterhead' => $club->hasOwnLetterhead(),
        ]);
    }

    /**
     * Update a document.
     */
    public function update(ClubDocumentRequest $request, Club $club, ClubDocument $document): RedirectResponse
    {
        $document->update($request->validated());

        Inertia::flash('success', "“{$document->title}” saved. You can download it now.");

        return to_route('admin.clubs.documents.edit', [$club, $document]);
    }

    /**
     * Copy a document as a starting point for a new one, dated today.
     */
    public function duplicate(Request $request, Club $club, ClubDocument $document): RedirectResponse
    {
        Gate::authorize('manageDocuments', $club);

        $copy = $document->replicate(['created_by']);
        $copy->document_date = now();
        $copy->created_by = $request->user()?->id;
        $copy->save();

        Inertia::flash('success', 'Copy created. Adjust it and download when ready.');

        return to_route('admin.clubs.documents.edit', [$club, $copy]);
    }

    /**
     * Delete a document.
     */
    public function destroy(Club $club, ClubDocument $document): RedirectResponse
    {
        Gate::authorize('manageDocuments', $club);

        $document->delete();

        Inertia::flash('success', "“{$document->title}” deleted.");

        return to_route('admin.clubs.documents.index', $club);
    }

    /**
     * Download the document as a .docx printed on the club's letterhead.
     */
    public function download(LetterheadRenderer $renderer, Club $club, ClubDocument $document): BinaryFileResponse
    {
        Gate::authorize('manageDocuments', $club);

        $path = $renderer->render(
            $club->letterheadTemplatePath(),
            [
                '[title]' => $document->title,
                '{date}' => $document->document_date->format('F j, Y'),
                '{club_name}' => $club->name,
                '{club_location}' => (string) $club->location,
                '{charter_number}' => (string) $club->charter_number,
            ],
            (string) $document->body,
        );

        return response()
            ->download($path, $document->downloadName(), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->deleteFileAfterSend();
    }
}
