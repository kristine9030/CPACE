<?php

namespace Database\Seeders;

use App\Models\ReviewNote;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a realistic set of personal review notes for every student account so
 * the Review Notes page renders live data out of the box. Notes are linked to
 * the real subjects/topics already in the database.
 *
 * Idempotent: clears existing notes for the seeded students before reinserting.
 */
class ReviewNoteSeeder extends Seeder
{
    public function run(): void
    {
        // Map subject code -> id and a topic id per subject for linking.
        $subjects = Subject::with('topics')->get()->keyBy('code');

        $studentIds = DB::table('users')->where('role_id', 2)->pluck('id');
        if ($studentIds->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        // Template notes (code, topic name, title, tags, body).
        $templates = [
            ['AUD', 'Audit Evidence and Procedures', 'Audit Sampling Key Concepts', 'Audit Sampling, Sample Size, Risk',
             "Sampling risk vs non-sampling risk. Statistical vs non-statistical sampling. Factors affecting sample size: tolerable misstatement (inverse), expected misstatement (direct), assurance required (direct)."],
            ['TAX', 'Income Tax - Individuals', 'Tax Basis of Property', 'Basis, Capital Gains, TRAIN Law',
             "Cost basis = purchase price + acquisition costs. For inherited property, basis = FMV at date of death. Gain = selling price - basis. First P250,000 of individual taxable income is exempt under TRAIN."],
            ['FAR', 'Receivables', 'Financial Statement Assertions', 'Assertions, Completeness, Valuation',
             "Existence, Completeness, Rights & Obligations, Valuation/Allocation, Presentation & Disclosure. NRV of receivables = gross AR less allowance for doubtful accounts."],
            ['AUD', 'Philippine Standards on Auditing', 'PSA 530 - Audit Sampling', 'PSA 530, Sampling, Projection',
             "PSA 530 governs the use of audit sampling. Project misstatements found in the sample to the whole population. Evaluate whether the use of sampling has provided a reasonable basis for conclusions."],
            ['FAR', 'Cash and Cash Equivalents', 'Cash Flow Statement Overview', 'Operating, Investing, Financing',
             "Three sections: operating, investing, financing. Direct vs indirect method for operating activities. Cash equivalents: highly liquid, original maturity of three months or less."],
            ['RFBT', 'Law on Contracts', 'Regulatory Framework Overview', 'Contracts, Requisites, Obligations',
             "Essential requisites of a contract: consent, object, cause. A contract entered into through fraud is voidable (not void). Obligation to pay money is an obligation 'to give'."],
            ['MS', 'Cost-Volume-Profit Analysis', 'Management Advisory Services', 'CVP, Contribution Margin, Break-even',
             "Contribution margin = Sales - Variable costs. Break-even: total CM = fixed costs. Margin of safety = budgeted sales - break-even sales."],
            ['TAX', 'Income Tax - Corporations', 'Depreciation Methods in Taxation', 'Depreciation, CREATE Law',
             "Straight-line, declining balance, SYD. Under CREATE the regular corporate income tax is 25% (20% for certain small domestic corporations). MCIT is based on gross income."],
        ];

        foreach ($studentIds as $studentId) {
            ReviewNote::where('student_id', $studentId)->delete();

            foreach ($templates as $i => $t) {
                $subject = $subjects[$t[0]] ?? null;
                if (! $subject) {
                    continue;
                }
                $topic = $subject->topics->firstWhere('name', $t[1]);

                $createdDaysAgo  = ($i * 1) + 1;
                $reviewCount     = max(0, 8 - $i);                 // first notes reviewed more
                $lastReviewed    = $reviewCount > 0
                    ? $now->copy()->subHours($i === 0 ? 2 : ($i * 24))
                    : null;

                ReviewNote::create([
                    'student_id'       => $studentId,
                    'subject_id'       => $subject->id,
                    'topic_id'         => $topic->id ?? null,
                    'title'            => $t[2],
                    'tags'             => $t[3],
                    'content'          => $t[4],
                    'is_favorite'      => $i < 2,
                    'review_count'     => $reviewCount,
                    'last_reviewed_at' => $lastReviewed,
                    'created_at'       => $now->copy()->subDays($createdDaysAgo),
                    'updated_at'       => $lastReviewed ?? $now->copy()->subDays($createdDaysAgo),
                ]);
            }
        }
    }
}
