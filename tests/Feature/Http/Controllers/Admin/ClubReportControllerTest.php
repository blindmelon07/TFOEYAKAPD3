<?php

use App\Enums\ClubPosition;
use App\Enums\MemberStatus;
use App\Models\Club;
use App\Models\DuesPayment;
use App\Models\DuesRate;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    $this->club = Club::factory()->create(['name' => 'Bulan Eagles Club']);
    $this->treasurer = Member::factory()->for($this->club)->officer(ClubPosition::Treasurer)->withLogin()->create([
        'first_name' => 'Tess',
        'middle_name' => null,
        'last_name' => 'Treasurer',
    ]);
});

/**
 * Find a summary tile by label in a rendered report.
 *
 * @param  array<string, mixed>  $data
 */
function summaryValue(array $data, string $label): mixed
{
    return collect($data['summary'])->firstWhere('label', $label)['value'] ?? null;
}

describe('index', function () {
    it('lists the four reports for a club officer', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.index', $this->club))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/clubs/reports/index')
                ->has('reports', 4)
                ->where('reports.0.key', 'dues-collection')
                ->where('reports.1.key', 'payment-ledger')
                ->where('reports.2.key', 'membership-roster')
                ->where('reports.3.key', 'membership-summary'));
    });

    it('forbids regular members and officers of other clubs', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();
        $outsider = Member::factory()->officer(ClubPosition::President)->withLogin()->create();

        $this->actingAs($member->user)->get(route('admin.clubs.reports.index', $this->club))->assertForbidden();
        $this->actingAs($outsider->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'dues-collection']))
            ->assertForbidden();
    });

    it('returns 404 for an unknown report', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'secret-report']))
            ->assertNotFound();
    });
});

describe('dues collection', function () {
    beforeEach(function () {
        DuesRate::factory()->for($this->club)->create(['year' => 2026, 'amount' => '1000.00']);
        DuesPayment::factory()->for($this->treasurer)->create(['year' => 2026, 'amount' => '1000.00']);

        $this->partial = Member::factory()->for($this->club)->create();
        DuesPayment::factory()->for($this->partial)->create(['year' => 2026, 'amount' => '400.00']);

        Member::factory()->for($this->club)->create();
        Member::factory()->for($this->club)->create(['status' => MemberStatus::Deceased]);
        DuesPayment::factory()->for(Member::factory())->create(['year' => 2026, 'amount' => '999.00']);
    });

    it('totals what was collected and what is still owed', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'dues-collection', 'year' => 2026]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/clubs/reports/show')
                ->where('filters.year', 2026)
                ->where('data.title', 'Dues Collection 2026')
                ->where('data.notice', null)
                ->has('data.sections.0.rows', 3)
                ->where('data.sections.0.totals.paid', 1400)
                ->where('data.sections.0.totals.balance', 1600)
                ->where('data.charts.0.points.0.value', 1)
                ->where('data.charts.0.points.1.value', 1)
                ->where('data.charts.0.points.2.value', 1)
                ->where('data', fn ($data) => summaryValue($data->toArray(), 'Members billed') === 3
                    && summaryValue($data->toArray(), 'Collected') == 1400
                    && summaryValue($data->toArray(), 'Expected') == 3000
                    && summaryValue($data->toArray(), 'Outstanding') == 1600
                    && summaryValue($data->toArray(), 'Collection rate') == 46.7));
    });

    it('explains when no dues amount is set for the year', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'dues-collection', 'year' => 2025]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('data.notice', fn (string $notice) => str_contains($notice, 'No dues amount is set for 2025'))
                ->where('data.summary.0.value', 'Not set'));
    });

    it('rejects a year outside the allowed range', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'dues-collection', 'year' => 1800]))
            ->assertSessionHasErrors('year');
    });
});

describe('payment ledger', function () {
    it('lists this club\'s payments within the date range with a total', function () {
        DuesPayment::factory()->for($this->treasurer)->create(['paid_at' => '2026-02-10', 'amount' => '500.00', 'reference' => 'OR-1']);
        DuesPayment::factory()->for($this->treasurer)->create(['paid_at' => '2026-03-15', 'amount' => '700.00', 'reference' => 'OR-2']);
        DuesPayment::factory()->for($this->treasurer)->create(['paid_at' => '2025-12-31', 'amount' => '100.00']);
        DuesPayment::factory()->for(Member::factory())->create(['paid_at' => '2026-02-11', 'amount' => '999.00']);

        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'payment-ledger', 'from' => '2026-01-01', 'to' => '2026-03-31']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('data.sections.0.rows', 2)
                ->where('data.sections.0.rows.0.reference', 'OR-1')
                ->where('data.sections.0.totals.amount', 1200)
                ->has('data.charts.0.points', 3)
                ->where('data.charts.0.points.0.label', 'Jan 2026')
                ->where('data.charts.0.points.0.value', 0)
                ->where('data.charts.0.points.2.value', 700));
    });

    it('rejects an end date before the start date', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'payment-ledger', 'from' => '2026-05-01', 'to' => '2026-01-01']))
            ->assertSessionHasErrors('to');
    });
});

describe('membership', function () {
    it('lists the roster with officers first and filters by status', function () {
        Member::factory()->for($this->club)->create(['last_name' => 'Aaron', 'status' => MemberStatus::Suspended]);

        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'membership-roster']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('data.sections.0.rows', 2)
                ->where('data.sections.0.rows.0.name', 'Tess Treasurer'));

        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'membership-roster', 'status' => 'suspended']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('data.sections.0.rows', 1)
                ->where('data.sections.0.rows.0.status', 'Suspended'));
    });

    it('summarises statuses, vacant offices and inductions', function () {
        Member::factory()->for($this->club)->create(['status' => MemberStatus::Inactive, 'inducted_at' => '2020-05-01']);
        $this->treasurer->update(['inducted_at' => '2020-01-15']);

        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.show', [$this->club, 'membership-summary']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('data.summary.0.value', 2)
                ->where('data.summary.2.value', '1 of 5')
                ->where('data.sections.0.rows.0.name', 'Vacant')
                ->where('data.sections.0.rows.3.name', 'Tess Treasurer')
                ->where('data.sections.1.rows.1.count', 1)
                ->where('data.sections.2.rows.0.year', '2020')
                ->where('data.sections.2.rows.0.count', 2));
    });
});

describe('exports', function () {
    beforeEach(function () {
        DuesRate::factory()->for($this->club)->create(['year' => 2026, 'amount' => '1200.00']);
        DuesPayment::factory()->for($this->treasurer)->create(['year' => 2026, 'amount' => '1200.00']);
    });

    it('renders a print page', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.print', [$this->club, 'dues-collection', 'year' => 2026]))
            ->assertOk()
            ->assertSee('Dues Collection 2026')
            ->assertSee('Bulan Eagles Club')
            ->assertSee('Tess Treasurer')
            ->assertSee('₱1,200.00')
            ->assertSee('size: 8.5in 13in', false);
    });

    it('downloads a Word file on the club letterhead', function () {
        $response = $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.word', [$this->club, 'dues-collection', 'year' => 2026]))
            ->assertOk()
            ->assertDownload('bulan-eagles-club-dues-collection-2026-'.now()->format('Y-m-d').'.docx');

        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($path);
        $xml = (string) $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        expect(html_entity_decode(strip_tags($xml)))
            ->toContain('DUES COLLECTION 2026')
            ->toContain('Tess Treasurer')
            ->toContain('₱1,200.00')
            ->and($xml)->toContain('<w:tbl>');
    });

    it('downloads a CSV that keeps numbers numeric', function () {
        $response = $this->actingAs($this->treasurer->user)
            ->get(route('admin.clubs.reports.csv', [$this->club, 'dues-collection', 'year' => 2026]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->getContent();

        expect($csv)
            ->toStartWith("\u{FEFF}\"Dues Collection 2026\"\n\"Bulan Eagles Club\"\n")
            ->toContain('Member,"Member no.",Position,Paid,Balance,Standing')
            ->toContain('Tess Treasurer')
            ->toContain(',1200,0,Paid')
            ->and($response->headers->get('Content-Disposition'))
            ->toContain('bulan-eagles-club-dues-collection-2026');
    });
});

describe('reports hub', function () {
    it('shows district admins every club', function () {
        Club::factory()->create(['name' => 'Casiguran Eagles Club']);

        $this->actingAs(User::factory()->districtAdmin()->create())
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/reports')
                ->has('clubs', 2)
                ->has('reports', 4));
    });

    it('sends an officer to their own club\'s reports', function () {
        $this->actingAs($this->treasurer->user)
            ->get(route('admin.reports'))
            ->assertRedirect(route('admin.clubs.reports.index', $this->club));
    });

    it('forbids regular members', function () {
        $member = Member::factory()->for($this->club)->withLogin()->create();

        $this->actingAs($member->user)->get(route('admin.reports'))->assertForbidden();
    });
});
