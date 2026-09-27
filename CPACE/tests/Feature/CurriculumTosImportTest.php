<?php

namespace Tests\Feature;

use App\Models\CurriculumImportBatch;
use App\Models\CurriculumImportItem;
use App\Models\Topic;
use App\Services\CurriculumTosParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsCurriculumSchema;
use Tests\TestCase;

/**
 * Importing the PRC Table of Specifications: the parser rebuilds the outline
 * from the PDF's text, the chair reviews it, and only the commit creates topics.
 */
class CurriculumTosImportTest extends TestCase
{
    use BuildsCurriculumSchema;

    /** Text as smalot/pdfparser extracts it from the official TOS (trimmed). */
    private const SAMPLE = <<<'TXT'
Table of Specifications
in FINANCIAL ACCOUNTING AND REPORTING
Effective October 2022 Licensure Examination for Certified Public Accountants (LECPA)
Difficulty Level Easy (30%) Moderate (40%) Difficult (30%)
Topics and Outcomes
Weight
In
Percent
The examinee can perform the following competencies under each
topic:
A. Development of Financial Reporting Framework, Standard-Setting
Bodies and Regulation of the Accountancy Profession
1. History, Development and Functions of the Standard-Setting Bodies
1.1 Explain the history, development and functions of IASB, IFRIC
and SIC, FRSC AND PIC.
5.71% 4
2
2

B. Non-financial Assets
1. Inventories
2. Property, plant and equipment 4
94.29% 66
TOTAL 100% 70 21 28 21

TABLE OF SPECIFICATIONS
in ADVANCED FINANCIAL ACCOUNTING AND REPORTING
1.0 Partnership Accounting 100% 70 3 4 3
1. Nature, Scope, and Objectives
1.1.1 Describe the nature, scope, and objectives
1.2 Formation of Partnership
TXT;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildCurriculumSchema();
    }

    protected function tearDown(): void
    {
        $this->dropCurriculumSchema();
        parent::tearDown();
    }

    public function test_the_parser_rebuilds_each_subjects_outline_with_its_weights(): void
    {
        $outline = (new CurriculumTosParser())->parseText(self::SAMPLE);

        $this->assertSame(['FAR', 'AFAR'], array_keys($outline));

        $far = $outline['FAR'];
        $names = array_column($far, 'name');
        $this->assertSame('Development of Financial Reporting Framework, Standard-Setting Bodies and Regulation of the Accountancy Profession', $far[0]['name'], 'wrapped heading lines are joined');
        $this->assertSame([5.71, 4], [$far[0]['weight'], $far[0]['items']], 'the weight column attaches to its area');
        $this->assertSame(1, $far[1]['depth']);
        $this->assertSame(0, $far[1]['parent'], '"1." sits under "A."');
        $this->assertSame(2, $far[2]['depth'], '"1.1" sits under "1."');
        $this->assertContains('Property, plant and equipment', $names, 'stray item counts are stripped from names');
        $this->assertNotContains('TOTAL', $names);

        $afar = $outline['AFAR'];
        $this->assertSame('Partnership Accounting', $afar[0]['name']);
        $this->assertSame(100.0, $afar[0]['weight']);
        $this->assertSame('1.1', $afar[1]['ref'], 'a bare "1." under "1.0" is really 1.1');
        $this->assertSame(1, $afar[3]['depth'], '"1.2" is a sibling of 1.1, not its child');
    }

    public function test_uploading_stages_the_outline_without_touching_topics(): void
    {
        $chair = $this->chair();
        $far = $this->subject('FAR');
        $this->fakeParser();

        $response = $this->actingAs($chair)->post(route('chair.curriculum.import.store'), [
            'curriculum_version_id' => $this->activeId,
            'file' => UploadedFile::fake()->create('tos.pdf', 10, 'application/pdf'),
        ]);

        $batch = CurriculumImportBatch::firstOrFail();
        $response->assertRedirect(route('chair.curriculum.import.review', $batch));
        $this->assertSame(4, CurriculumImportItem::where('subject_id', $far)->count());
        $this->assertSame(0, Topic::count(), 'nothing is created before the chair commits');

        $this->actingAs($chair)->get(route('chair.curriculum.import.review', $batch))
            ->assertOk()
            ->assertSee('Cash and Cash Equivalents')
            ->assertSee('Area weights total 100%', false);
    }

    public function test_committing_creates_the_reviewed_topics_under_their_nearest_included_parent(): void
    {
        $chair = $this->chair();
        $far = $this->subject('FAR');
        $faculty = $this->faculty($far);
        $draftId = $this->draft();
        $this->fakeParser();

        $this->actingAs($chair)->post(route('chair.curriculum.import.store'), [
            'curriculum_version_id' => $draftId,
            'file' => UploadedFile::fake()->create('tos.pdf', 10, 'application/pdf'),
        ]);
        $batch = CurriculumImportBatch::firstOrFail();
        $items = $batch->items()->get()->keyBy('ref');

        $payload = json_encode([
            'subjects' => [$far],
            'items' => [
                $items['1']->id => ['include' => false, 'name' => $items['1']->name],       // drop a middle level
                $items['1.1']->id => ['include' => true, 'name' => 'Bank Reconciliation'], // renamed
            ],
        ]);

        $this->actingAs($chair)->post(route('chair.curriculum.import.commit', $batch), ['payload' => $payload])
            ->assertRedirect(route('chair.subjects', ['version' => $draftId]));

        $area = Topic::where('name', 'Cash and Cash Equivalents')->firstOrFail();
        $this->assertSame($draftId, (int) $area->curriculum_version_id);
        $this->assertSame(14.29, (float) $area->tos_weight);
        $this->assertSame(10, (int) $area->tos_items);

        $renamed = Topic::where('name', 'Bank Reconciliation')->firstOrFail();
        $this->assertSame($area->id, (int) $renamed->parent_id, 'with its parent excluded, the subtopic moves up to the area');
        $this->assertSame(0, Topic::where('name', 'Cash')->count());
        $this->assertSame('committed', $batch->fresh()->status);

        $this->assertSame(1, DB::table('notifications')->where('recipient_id', $faculty->id)->where('title', 'Curriculum topics updated')->count());
        $this->assertSame(1, DB::table('curriculum_audits')->where('action', 'topics_imported')->count());
    }

    public function test_re_importing_reuses_existing_topics_instead_of_duplicating_them(): void
    {
        $chair = $this->chair();
        $far = $this->subject('FAR');
        $this->topic($far, $this->activeId, 'Cash and Cash Equivalents');
        $this->fakeParser();

        $this->actingAs($chair)->post(route('chair.curriculum.import.store'), [
            'curriculum_version_id' => $this->activeId,
            'file' => UploadedFile::fake()->create('tos.pdf', 10, 'application/pdf'),
        ]);
        $batch = CurriculumImportBatch::firstOrFail();

        $this->actingAs($chair)->post(route('chair.curriculum.import.commit', $batch), [
            'payload' => json_encode(['subjects' => [$far], 'items' => []]),
        ]);

        $this->assertSame(1, Topic::where('name', 'Cash and Cash Equivalents')->count());
        $this->assertSame(4, Topic::where('subject_id', $far)->count());
    }

    public function test_an_archived_curriculum_cannot_be_imported_into(): void
    {
        $chair = $this->chair();
        $this->subject('FAR');
        DB::table('curriculum_versions')->where('id', $this->activeId)->update(['status' => 'archived']);
        $this->fakeParser();

        $this->actingAs($chair)->post(route('chair.curriculum.import.store'), [
            'curriculum_version_id' => $this->activeId,
            'file' => UploadedFile::fake()->create('tos.pdf', 10, 'application/pdf'),
        ])->assertSessionHas('error');

        $this->assertSame(0, CurriculumImportBatch::count());
    }

    public function test_a_non_pdf_upload_is_rejected(): void
    {
        $this->subject('FAR');

        $this->actingAs($this->chair())->post(route('chair.curriculum.import.store'), [
            'curriculum_version_id' => $this->activeId,
            'file' => UploadedFile::fake()->create('tos.docx', 10),
        ])->assertSessionHasErrors('file');
    }

    /** Stand in for reading a real PDF: a small FAR outline. */
    private function fakeParser(): void
    {
        $this->app->instance(CurriculumTosParser::class, new class extends CurriculumTosParser {
            public function parseFile(string $path): array
            {
                $item = fn ($ref, $name, $depth, $parent, $weight = null, $items = null) => [
                    'ref' => $ref, 'name' => $name, 'full_name' => $name, 'depth' => $depth,
                    'parent' => $parent, 'weight' => $weight, 'items' => $items,
                ];

                return ['FAR' => [
                    $item('A.', 'Cash and Cash Equivalents', 0, null, 14.29, 10),
                    $item('1', 'Cash', 1, 0),
                    $item('1.1', 'Prepare a bank reconciliation', 2, 1),
                    $item('B.', 'Everything else', 0, null, 85.71, 60),
                ]];
            }
        });
    }
}
