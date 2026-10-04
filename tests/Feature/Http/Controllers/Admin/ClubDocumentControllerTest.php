<?php

use App\Enums\ClubPosition;
use App\Models\Club;
use App\Models\ClubDocument;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    $this->club = Club::factory()->create(['name' => 'Bulan Eagles Club']);
    $this->secretary = Member::factory()->for($this->club)->officer(ClubPosition::Secretary)->withLogin()->create()->user;
});

/**
 * Get the visible text of a downloaded .docx response.
 */
function downloadedText(TestResponse $response): string
{
    $path = $response->baseResponse->getFile()->getPathname();

    $zip = new ZipArchive;
    $zip->open($path);
    $xml = (string) $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($path);

    return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1);
}

/**
 * Build an uploadable .docx letterhead containing the given header text.
 */
function letterheadUpload(string $headerText): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'docx');
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
        .'<w:p><w:r><w:t>'.$headerText.'</w:t></w:r></w:p>'
        .'<w:p><w:r><w:t>[title]</w:t></w:r></w:p>'
        .'<w:p><w:r><w:t>{date}</w:t></w:r></w:p>'
        .'<w:sectPr/></w:body></w:document>');
    $zip->close();

    return UploadedFile::fake()->createWithContent('letterhead.docx', (string) file_get_contents($path));
}

describe('documents', function () {
    it('lists the club\'s documents newest first', function () {
        ClubDocument::factory()->for($this->club)->create(['title' => 'Older', 'document_date' => '2026-01-10']);
        ClubDocument::factory()->for($this->club)->create(['title' => 'Newer', 'document_date' => '2026-09-01']);
        ClubDocument::factory()->create(['title' => 'Other club']);

        $this->actingAs($this->secretary)
            ->get(route('admin.clubs.documents.index', $this->club))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/clubs/documents/index')
                ->has('documents', 2)
                ->where('documents.0.title', 'Newer')
                ->where('hasOwnLetterhead', false)
                ->where('canManageLetterhead', false));
    });

    it('lets an officer save a document and stay on it to download', function () {
        $response = $this->actingAs($this->secretary)
            ->post(route('admin.clubs.documents.store', $this->club), [
                'title' => 'MEMORANDUM',
                'document_date' => '2026-10-05',
                'body' => "To all members:\nPlease attend.",
            ]);

        $document = $this->club->documents()->sole();

        $response->assertRedirect(route('admin.clubs.documents.edit', [$this->club, $document]));

        expect($document->title)->toBe('MEMORANDUM')
            ->and($document->created_by)->toBe($this->secretary->id);
    });

    it('saves rich text and strips anything the editor does not allow', function () {
        $this->actingAs($this->secretary)
            ->post(route('admin.clubs.documents.store', $this->club), [
                'title' => 'Notice',
                'document_date' => '2026-10-05',
                'body' => '<p style="text-align: center"><strong>Hello</strong></p><script>alert(1)</script><p onclick="x()">World</p>',
            ]);

        expect($this->club->documents()->sole()->body)
            ->toBe('<p style="text-align: center"><strong>Hello</strong></p><p>World</p>');
    });

    it('shows a plain-text excerpt of rich text in the list', function () {
        ClubDocument::factory()->for($this->club)->create([
            'body' => '<p><strong>Dear</strong> members,</p><ul><li><p>Bring&nbsp;IDs</p></li></ul>',
        ]);

        $this->actingAs($this->secretary)
            ->get(route('admin.clubs.documents.index', $this->club))
            ->assertInertia(fn (Assert $page) => $page
                ->where('documents.0.excerpt', 'Dear members, Bring IDs'));
    });

    it('requires a title and date', function () {
        $this->actingAs($this->secretary)
            ->post(route('admin.clubs.documents.store', $this->club), [])
            ->assertSessionHasErrors(['title', 'document_date']);
    });

    it('updates a document', function () {
        $document = ClubDocument::factory()->for($this->club)->create();

        $this->actingAs($this->secretary)
            ->put(route('admin.clubs.documents.update', [$this->club, $document]), [
                'title' => 'INVITATION',
                'document_date' => '2026-10-05',
                'body' => 'Join us.',
            ])
            ->assertRedirect(route('admin.clubs.documents.edit', [$this->club, $document]));

        expect($document->fresh()->title)->toBe('INVITATION');
    });

    it('duplicates a document dated today and opens the copy', function () {
        $document = ClubDocument::factory()->for($this->club)->create([
            'title' => 'Monthly Notice',
            'document_date' => '2025-01-01',
            'body' => 'Same every month.',
        ]);

        $response = $this->actingAs($this->secretary)
            ->post(route('admin.clubs.documents.duplicate', [$this->club, $document]));

        $copy = $this->club->documents()->whereKeyNot($document->id)->sole();

        $response->assertRedirect(route('admin.clubs.documents.edit', [$this->club, $copy]));

        expect($copy->title)->toBe('Monthly Notice')
            ->and($copy->body)->toBe('Same every month.')
            ->and($copy->document_date->isToday())->toBeTrue();
    });

    it('deletes a document', function () {
        $document = ClubDocument::factory()->for($this->club)->create();

        $this->actingAs($this->secretary)
            ->delete(route('admin.clubs.documents.destroy', [$this->club, $document]))
            ->assertRedirect(route('admin.clubs.documents.index', $this->club));

        $this->assertModelMissing($document);
    });

    it('downloads a Word file printed on the district letterhead', function () {
        $document = ClubDocument::factory()->for($this->club)->create([
            'title' => 'Memorandum',
            'document_date' => '2026-10-05',
            'body' => 'Please attend the assembly.',
        ]);

        $response = $this->actingAs($this->secretary)
            ->get(route('admin.clubs.documents.download', [$this->club, $document]))
            ->assertOk()
            ->assertDownload('memorandum-2026-10-05.docx');

        expect(downloadedText($response))
            ->toContain('Memorandum')
            ->toContain('October 5, 2026')
            ->toContain('Please attend the assembly.');
    });

    it('returns 404 for another club\'s document', function () {
        $otherDocument = ClubDocument::factory()->create();

        $this->actingAs($this->secretary)
            ->get(route('admin.clubs.documents.download', [$this->club, $otherDocument]))
            ->assertNotFound();
    });

    it('forbids officers of other clubs and regular members', function () {
        $outsider = Member::factory()->officer(ClubPosition::President)->withLogin()->create()->user;
        $member = Member::factory()->for($this->club)->withLogin()->create()->user;

        $this->actingAs($outsider)->get(route('admin.clubs.documents.index', $this->club))->assertForbidden();
        $this->actingAs($member)->get(route('admin.clubs.documents.index', $this->club))->assertForbidden();
        $this->actingAs($member)
            ->post(route('admin.clubs.documents.store', $this->club), ['title' => 'X', 'document_date' => '2026-10-05'])
            ->assertForbidden();
    });
});

describe('letterhead', function () {
    it('lets the club president upload a club letterhead that documents then use', function () {
        $president = Member::factory()->for($this->club)->officer(ClubPosition::President)->withLogin()->create()->user;
        $document = ClubDocument::factory()->for($this->club)->create(['title' => 'Notice']);

        $this->actingAs($president)
            ->post(route('admin.clubs.letterhead.store', $this->club), [
                'letterhead' => letterheadUpload('BULAN CLUB HEADER'),
            ])
            ->assertRedirect(route('admin.clubs.documents.index', $this->club));

        expect($this->club->fresh()->hasOwnLetterhead())->toBeTrue();

        $response = $this->actingAs($president)
            ->get(route('admin.clubs.documents.download', [$this->club, $document]));

        expect(downloadedText($response))
            ->toContain('BULAN CLUB HEADER')
            ->toContain('Notice');
    });

    it('fills the club name placeholder in a club letterhead', function () {
        $admin = User::factory()->districtAdmin()->create();
        $document = ClubDocument::factory()->for($this->club)->create();

        $this->actingAs($admin)->post(route('admin.clubs.letterhead.store', $this->club), [
            'letterhead' => letterheadUpload('{club_name}'),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.clubs.documents.download', [$this->club, $document]));

        expect(downloadedText($response))->toContain('Bulan Eagles Club');
    });

    it('rejects a file that is not a Word document', function () {
        $admin = User::factory()->districtAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.clubs.letterhead.store', $this->club), [
                'letterhead' => UploadedFile::fake()->createWithContent('fake.docx', 'plain text'),
            ])
            ->assertSessionHasErrors(['letterhead' => 'The letterhead must be a Word (.docx) document.']);

        expect($this->club->fresh()->letterhead_path)->toBeNull();
    });

    it('goes back to the district letterhead when the club letterhead is removed', function () {
        $admin = User::factory()->districtAdmin()->create();
        $this->actingAs($admin)->post(route('admin.clubs.letterhead.store', $this->club), [
            'letterhead' => letterheadUpload('OLD HEADER'),
        ]);
        $storedPath = $this->club->fresh()->letterhead_path;

        $this->actingAs($admin)
            ->delete(route('admin.clubs.letterhead.destroy', $this->club))
            ->assertRedirect(route('admin.clubs.documents.index', $this->club));

        Storage::disk('local')->assertMissing($storedPath);
        expect($this->club->fresh()->letterheadTemplatePath())->toBe(resource_path('templates/letterhead.docx'));
    });

    it('forbids officers other than the president from changing the letterhead', function () {
        $this->actingAs($this->secretary)
            ->post(route('admin.clubs.letterhead.store', $this->club), [
                'letterhead' => letterheadUpload('ROGUE'),
            ])
            ->assertForbidden();

        $this->actingAs($this->secretary)
            ->delete(route('admin.clubs.letterhead.destroy', $this->club))
            ->assertForbidden();
    });

    it('lets any officer download the current letterhead template', function () {
        $this->actingAs($this->secretary)
            ->get(route('admin.clubs.letterhead.show', $this->club))
            ->assertOk()
            ->assertDownload('district-letterhead.docx');
    });
});
