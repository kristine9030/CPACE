<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a sample CPALE question bank (MCQ + True/False) across all six
 * subjects so the adaptive quiz works out of the box.
 *
 * Idempotent: wipes existing questions/choices and reinserts the sample set.
 */
class QuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = $this->ensureFaculty();

        // Map "Subject code => [topic name => topic_id]" for quick lookup.
        $topicLookup = [];
        foreach (Subject::with('topics')->get() as $subject) {
            foreach ($subject->topics as $topic) {
                $topicLookup[$subject->code][$topic->name] = $topic->id;
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('quiz_answers')->delete();
        DB::table('question_choices')->delete();
        DB::table('questions')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($this->bank() as $code => $items) {
            foreach ($items as $item) {
                $topicId = $topicLookup[$code][$item['topic']] ?? null;
                if (! $topicId) {
                    continue; // topic name mismatch - skip defensively
                }

                $type = $item['type'] ?? 'mcq';
                $question = Question::create([
                    'topic_id'      => $topicId,
                    'created_by'    => $faculty->id,
                    'question_text' => $item['q'],
                    'question_type' => $type,
                    'difficulty'    => $item['diff'] ?? 'moderate',
                    'explanation'   => $item['exp'] ?? null,
                    'is_active'     => true,
                ]);

                if ($type === 'true_false') {
                    $question->choices()->createMany([
                        ['choice_label' => 'A', 'choice_text' => 'True',  'is_correct' => $item['answer'] === true],
                        ['choice_label' => 'B', 'choice_text' => 'False', 'is_correct' => $item['answer'] === false],
                    ]);
                    continue;
                }

                foreach (['A', 'B', 'C', 'D'] as $idx => $label) {
                    $question->choices()->create([
                        'choice_label' => $label,
                        'choice_text'  => $item['choices'][$idx],
                        'is_correct'   => $item['correct'] === $idx,
                    ]);
                }
            }
        }
    }

    private function ensureFaculty(): User
    {
        $faculty = User::where('role_id', Role::FACULTY)->first();
        if ($faculty) {
            return $faculty;
        }

        return User::create([
            'role_id'        => Role::FACULTY,
            'first_name'     => 'Maria',
            'last_name'      => 'Santos',
            'email'          => 'faculty@cpace.test',
            'password'       => Hash::make('password'),
            'is_active'      => true,
            'email_verified' => true,
        ]);
    }

    /**
     * The sample question bank, grouped by subject code.
     * correct = zero-based index into choices.
     */
    private function bank(): array
    {
        return [
            'FAR' => [
                ['topic' => 'Cash and Cash Equivalents', 'diff' => 'easy',
                 'q' => 'Which of the following is NOT considered a cash equivalent?',
                 'choices' => ['Three-month treasury bill', 'Money market fund maturing in 90 days', 'Six-month time deposit', 'Commercial paper acquired 60 days before maturity'],
                 'correct' => 2, 'exp' => 'Cash equivalents must be highly liquid with an original maturity of three months or less. A six-month time deposit exceeds that limit.'],
                ['topic' => 'Cash and Cash Equivalents', 'diff' => 'moderate',
                 'q' => 'A bank reconciliation shows a deposit in transit. How is it treated?',
                 'choices' => ['Added to the bank balance', 'Deducted from the bank balance', 'Added to the book balance', 'Deducted from the book balance'],
                 'correct' => 0, 'exp' => 'Deposits in transit have been recorded in the books but not yet by the bank, so they are added to the bank balance.'],
                ['topic' => 'Receivables', 'diff' => 'moderate',
                 'q' => 'Under the allowance method, the entry to write off an uncollectible account:',
                 'choices' => ['Decreases net income', 'Has no effect on net realizable value of receivables', 'Increases the allowance account', 'Decreases total assets'],
                 'correct' => 1, 'exp' => 'Writing off an account reduces both Accounts Receivable and the Allowance, leaving net realizable value unchanged.'],
                ['topic' => 'Receivables', 'diff' => 'easy', 'type' => 'true_false',
                 'q' => 'The net realizable value of accounts receivable equals the gross receivable less the allowance for doubtful accounts.',
                 'answer' => true, 'exp' => 'Net realizable value = gross accounts receivable minus the allowance for doubtful accounts.'],
                ['topic' => 'Inventories', 'diff' => 'moderate',
                 'q' => 'In a period of rising prices, which inventory method yields the highest net income?',
                 'choices' => ['FIFO', 'LIFO', 'Weighted Average', 'Specific Identification'],
                 'correct' => 0, 'exp' => 'FIFO assigns older, lower costs to cost of goods sold during rising prices, producing higher net income.'],
                ['topic' => 'Inventories', 'diff' => 'moderate',
                 'q' => 'Inventories are measured at:',
                 'choices' => ['Cost', 'Net realizable value', 'Lower of cost and net realizable value', 'Fair value less cost to sell'],
                 'correct' => 2, 'exp' => 'PAS 2 requires inventories to be measured at the lower of cost and net realizable value.'],
                ['topic' => 'Property, Plant and Equipment', 'diff' => 'moderate',
                 'q' => 'Which cost is NOT capitalized as part of an item of PPE?',
                 'choices' => ['Purchase price', 'Import duties', 'Installation and testing costs', 'Routine maintenance after use begins'],
                 'correct' => 3, 'exp' => 'Day-to-day servicing/maintenance is expensed; only costs to bring the asset to working condition are capitalized.'],
                ['topic' => 'Property, Plant and Equipment', 'diff' => 'difficult',
                 'q' => 'An entity uses the revaluation model. A revaluation increase is generally recognized in:',
                 'choices' => ['Profit or loss', 'Other comprehensive income', 'Retained earnings directly', 'Share premium'],
                 'correct' => 1, 'exp' => 'A revaluation surplus is recognized in OCI unless it reverses a previous decrease recognized in profit or loss.'],
                ['topic' => 'Intangible Assets', 'diff' => 'moderate',
                 'q' => 'Which of the following may be recognized as an intangible asset?',
                 'choices' => ['Internally generated goodwill', 'Acquired patent', 'Internally generated brand', 'Staff training costs'],
                 'correct' => 1, 'exp' => 'Internally generated goodwill and brands cannot be recognized; a separately acquired patent qualifies.'],
                ['topic' => 'Intangible Assets', 'diff' => 'easy', 'type' => 'true_false',
                 'q' => 'Intangible assets with indefinite useful lives are not amortized but tested for impairment annually.',
                 'answer' => true, 'exp' => 'Under PAS 38, indefinite-life intangibles are not amortized but are tested for impairment at least annually.'],
            ],

            'AFAR' => [
                ['topic' => 'Business Combinations', 'diff' => 'moderate',
                 'q' => 'Under PFRS 3, goodwill arising from a business combination is:',
                 'choices' => ['Amortized over 10 years', 'Tested for impairment annually', 'Expensed immediately', 'Recognized in equity'],
                 'correct' => 1, 'exp' => 'Goodwill is not amortized; it is tested for impairment at least annually.'],
                ['topic' => 'Business Combinations', 'diff' => 'difficult',
                 'q' => 'Acquisition-related costs (e.g., legal and advisory fees) in a business combination are:',
                 'choices' => ['Added to goodwill', 'Capitalized as part of the investment', 'Expensed as incurred', 'Deducted from share premium'],
                 'correct' => 2, 'exp' => 'PFRS 3 requires acquisition-related costs to be expensed in the period incurred.'],
                ['topic' => 'Consolidated Financial Statements', 'diff' => 'moderate',
                 'q' => 'Non-controlling interest is presented in the consolidated statement of financial position:',
                 'choices' => ['As a liability', 'Within equity, separately from parent equity', 'As a deduction from goodwill', 'In profit or loss'],
                 'correct' => 1, 'exp' => 'NCI is presented within equity but separately from the equity of the owners of the parent.'],
                ['topic' => 'Consolidated Financial Statements', 'diff' => 'easy', 'type' => 'true_false',
                 'q' => 'Intercompany sales between a parent and subsidiary must be eliminated in full on consolidation.',
                 'answer' => true, 'exp' => 'Intra-group transactions and balances are eliminated in full when preparing consolidated statements.'],
                ['topic' => 'Foreign Currency Transactions', 'diff' => 'moderate',
                 'q' => 'A monetary item denominated in foreign currency is translated at year-end using the:',
                 'choices' => ['Historical rate', 'Closing (spot) rate', 'Average rate', 'Forward rate'],
                 'correct' => 1, 'exp' => 'Monetary items are translated using the closing rate at the reporting date under PAS 21.'],
                ['topic' => 'Foreign Currency Transactions', 'diff' => 'difficult',
                 'q' => 'Exchange differences on settlement of monetary items are generally recognized in:',
                 'choices' => ['Other comprehensive income', 'Profit or loss', 'Retained earnings', 'Revaluation surplus'],
                 'correct' => 1, 'exp' => 'Exchange differences on monetary items are recognized in profit or loss in the period they arise.'],
                ['topic' => 'Branch Accounting', 'diff' => 'moderate',
                 'q' => 'In the home office books, the Investment in Branch account is best described as a:',
                 'choices' => ['Liability account', 'Reciprocal account to the Home Office account', 'Revenue account', 'Nominal account closed yearly'],
                 'correct' => 1, 'exp' => 'The Investment in Branch and Home Office accounts are reciprocal and should be equal before adjustments.'],
                ['topic' => 'Branch Accounting', 'diff' => 'moderate',
                 'q' => 'Goods shipped to a branch billed above cost create what at year-end?',
                 'choices' => ['Realized profit only', 'Allowance for overvaluation (unrealized profit)', 'A liability to the branch', 'A prior period error'],
                 'correct' => 1, 'exp' => 'The mark-up on unsold branch inventory is deferred as allowance for overvaluation until the goods are sold.'],
                ['topic' => 'Business Combinations', 'diff' => 'easy',
                 'q' => 'In an acquisition, the acquirer measures identifiable assets acquired at:',
                 'choices' => ['Carrying amount', 'Fair value at acquisition date', 'Historical cost', 'Replacement cost'],
                 'correct' => 1, 'exp' => 'Identifiable assets acquired and liabilities assumed are measured at acquisition-date fair value.'],
                ['topic' => 'Consolidated Financial Statements', 'diff' => 'moderate',
                 'q' => 'Control under PFRS 10 exists when the investor has all of the following EXCEPT:',
                 'choices' => ['Power over the investee', 'Exposure to variable returns', 'Ability to use power to affect returns', 'Ownership of exactly 50% of shares'],
                 'correct' => 3, 'exp' => 'Control is based on power, exposure to variable returns, and the link between them - not a fixed ownership percentage.'],
            ],

            'MS' => [
                ['topic' => 'Cost-Volume-Profit Analysis', 'diff' => 'easy',
                 'q' => 'The contribution margin is computed as:',
                 'choices' => ['Sales minus fixed costs', 'Sales minus variable costs', 'Sales minus total costs', 'Fixed costs minus variable costs'],
                 'correct' => 1, 'exp' => 'Contribution margin = Sales - Variable costs; it contributes toward covering fixed costs and profit.'],
                ['topic' => 'Cost-Volume-Profit Analysis', 'diff' => 'moderate',
                 'q' => 'At the break-even point:',
                 'choices' => ['Total contribution margin equals fixed costs', 'Profit is positive', 'Variable costs equal fixed costs', 'Sales equal variable costs'],
                 'correct' => 0, 'exp' => 'Break-even occurs when total contribution margin exactly covers fixed costs, yielding zero profit.'],
                ['topic' => 'Budgeting and Budgetary Control', 'diff' => 'moderate',
                 'q' => 'A budget prepared for a single level of activity is called a:',
                 'choices' => ['Flexible budget', 'Static budget', 'Rolling budget', 'Zero-based budget'],
                 'correct' => 1, 'exp' => 'A static (fixed) budget is based on one anticipated level of activity.'],
                ['topic' => 'Budgeting and Budgetary Control', 'diff' => 'easy', 'type' => 'true_false',
                 'q' => 'A flexible budget adjusts for changes in the level of activity.',
                 'answer' => true, 'exp' => 'Flexible budgets are restated to the actual activity level to allow meaningful variance analysis.'],
                ['topic' => 'Standard Costing', 'diff' => 'moderate',
                 'q' => 'An unfavorable materials price variance arises when:',
                 'choices' => ['Actual price exceeds standard price', 'Standard price exceeds actual price', 'Actual quantity is less than standard', 'Output exceeds budget'],
                 'correct' => 0, 'exp' => 'A price variance is unfavorable when the actual price paid is higher than the standard price.'],
                ['topic' => 'Standard Costing', 'diff' => 'difficult',
                 'q' => 'The labor efficiency variance measures the difference between:',
                 'choices' => ['Actual and standard wage rates', 'Actual and standard hours, valued at standard rate', 'Budgeted and actual fixed overhead', 'Standard and actual selling price'],
                 'correct' => 1, 'exp' => 'Labor efficiency variance = (Actual hours - Standard hours) x Standard rate.'],
                ['topic' => 'Capital Budgeting', 'diff' => 'moderate',
                 'q' => 'Which capital budgeting method ignores the time value of money?',
                 'choices' => ['Net present value', 'Internal rate of return', 'Payback period', 'Profitability index'],
                 'correct' => 2, 'exp' => 'The traditional payback period does not discount cash flows, ignoring the time value of money.'],
                ['topic' => 'Capital Budgeting', 'diff' => 'moderate',
                 'q' => 'A project should be accepted under the NPV method when:',
                 'choices' => ['NPV is negative', 'NPV is zero', 'NPV is greater than zero', 'IRR is below the cost of capital'],
                 'correct' => 2, 'exp' => 'A positive NPV indicates the project earns more than the required rate of return and adds value.'],
                ['topic' => 'Cost-Volume-Profit Analysis', 'diff' => 'moderate',
                 'q' => 'The margin of safety is:',
                 'choices' => ['Budgeted sales minus break-even sales', 'Fixed costs divided by contribution margin', 'Sales minus variable costs', 'Contribution margin ratio times sales'],
                 'correct' => 0, 'exp' => 'Margin of safety = actual or budgeted sales minus break-even sales; it measures the cushion before losses.'],
                ['topic' => 'Budgeting and Budgetary Control', 'diff' => 'moderate',
                 'q' => 'The starting point in preparing a master budget is usually the:',
                 'choices' => ['Cash budget', 'Production budget', 'Sales budget', 'Capital budget'],
                 'correct' => 2, 'exp' => 'The sales budget drives the rest of the master budget and is normally prepared first.'],
            ],

            'TAX' => [
                ['topic' => 'Income Tax - Individuals', 'diff' => 'easy',
                 'q' => 'Under the TRAIN law, an individual with annual taxable income not exceeding P250,000 is:',
                 'choices' => ['Taxed at 5%', 'Exempt from income tax', 'Taxed at 20%', 'Subject to 8% tax'],
                 'correct' => 1, 'exp' => 'The first P250,000 of annual taxable income of individuals is exempt under the TRAIN law.'],
                ['topic' => 'Income Tax - Individuals', 'diff' => 'moderate',
                 'q' => 'The 8% optional income tax for self-employed individuals is in lieu of:',
                 'choices' => ['Only the graduated income tax', 'Only the percentage tax', 'Both the graduated income tax and percentage tax', 'VAT and excise tax'],
                 'correct' => 2, 'exp' => 'The 8% option replaces both the graduated income tax and the 3% percentage tax for qualified taxpayers.'],
                ['topic' => 'Income Tax - Corporations', 'diff' => 'moderate',
                 'q' => 'Under the CREATE law, the regular corporate income tax rate for most domestic corporations is:',
                 'choices' => ['30%', '25%', '20%', '10%'],
                 'correct' => 1, 'exp' => 'CREATE reduced the regular corporate income tax to 25% (20% for certain small domestic corporations).'],
                ['topic' => 'Income Tax - Corporations', 'diff' => 'difficult',
                 'q' => 'The Minimum Corporate Income Tax (MCIT) is computed as a percentage of:',
                 'choices' => ['Net taxable income', 'Gross income', 'Gross sales', 'Total assets'],
                 'correct' => 1, 'exp' => 'MCIT is imposed on gross income beginning the 4th taxable year of operations.'],
                ['topic' => 'Value Added Tax', 'diff' => 'easy',
                 'q' => 'The standard VAT rate in the Philippines is:',
                 'choices' => ['3%', '10%', '12%', '15%'],
                 'correct' => 2, 'exp' => 'The standard VAT rate is 12% on the gross selling price or gross receipts.'],
                ['topic' => 'Value Added Tax', 'diff' => 'moderate', 'type' => 'true_false',
                 'q' => 'Export sales by a VAT-registered person are subject to VAT at zero percent (0%).',
                 'answer' => true, 'exp' => 'Export sales of VAT-registered taxpayers are zero-rated, allowing input VAT to be claimed or refunded.'],
                ['topic' => 'Percentage Tax', 'diff' => 'moderate',
                 'q' => 'Non-VAT taxpayers whose gross annual sales do not exceed the VAT threshold are generally subject to a percentage tax of:',
                 'choices' => ['1% (or 3%)', '12%', '6%', '8%'],
                 'correct' => 0, 'exp' => 'The general percentage tax is 3%, temporarily reduced to 1% under CREATE for a covered period.'],
                ['topic' => 'Income Tax - Individuals', 'diff' => 'moderate',
                 'q' => 'Which of the following is classified as compensation income?',
                 'choices' => ['Profit from sale of land', 'Salary received as an employee', 'Interest from bank deposits', 'Dividends from shares'],
                 'correct' => 1, 'exp' => 'Compensation income arises from an employer-employee relationship, such as salaries and wages.'],
                ['topic' => 'Value Added Tax', 'diff' => 'moderate',
                 'q' => 'Input VAT refers to VAT:',
                 'choices' => ['Charged on sales to customers', 'Paid on purchases of goods and services', 'Remitted to the BIR', 'Withheld on compensation'],
                 'correct' => 1, 'exp' => 'Input VAT is the VAT paid on purchases, creditable against output VAT on sales.'],
                ['topic' => 'Income Tax - Corporations', 'diff' => 'easy', 'type' => 'true_false',
                 'q' => 'A resident foreign corporation is taxable only on income derived from sources within the Philippines.',
                 'answer' => true, 'exp' => 'Resident foreign corporations are taxed only on Philippine-source income.'],
            ],

            'AUD' => [
                ['topic' => 'Philippine Standards on Auditing', 'diff' => 'easy',
                 'q' => 'The overall objective of an audit of financial statements is to:',
                 'choices' => ['Detect all fraud', 'Express an opinion on whether the statements are fairly presented', 'Prepare the financial statements', 'Guarantee the future viability of the entity'],
                 'correct' => 1, 'exp' => 'The auditor expresses an opinion on whether the financial statements are prepared, in all material respects, in accordance with the framework.'],
                ['topic' => 'Philippine Standards on Auditing', 'diff' => 'moderate', 'type' => 'true_false',
                 'q' => 'Reasonable assurance is an absolute level of assurance.',
                 'answer' => false, 'exp' => 'Reasonable assurance is high but not absolute due to the inherent limitations of an audit.'],
                ['topic' => 'Audit Planning and Risk Assessment', 'diff' => 'moderate',
                 'q' => 'Audit risk is the risk that the auditor:',
                 'choices' => ['Issues an unmodified opinion on materially misstated statements', 'Fails to complete the audit on time', 'Charges an excessive fee', 'Loses an audit client'],
                 'correct' => 0, 'exp' => 'Audit risk is the risk of expressing an inappropriate opinion when the financial statements are materially misstated.'],
                ['topic' => 'Audit Planning and Risk Assessment', 'diff' => 'difficult',
                 'q' => 'The audit risk model is best expressed as:',
                 'choices' => ['AR = IR x CR x DR', 'AR = IR + CR + DR', 'AR = IR / CR', 'AR = CR x DR'],
                 'correct' => 0, 'exp' => 'Audit risk = Inherent risk x Control risk x Detection risk.'],
                ['topic' => 'Audit Evidence and Procedures', 'diff' => 'moderate',
                 'q' => 'Which source of audit evidence is generally considered the most reliable?',
                 'choices' => ['Internally generated documents', 'Oral representations of management', 'External confirmations from third parties', 'Photocopies provided by the client'],
                 'correct' => 2, 'exp' => 'Evidence obtained directly from independent external sources is generally more reliable.'],
                ['topic' => 'Audit Evidence and Procedures', 'diff' => 'easy',
                 'q' => 'Recalculating the depreciation expense is an example of which procedure?',
                 'choices' => ['Inquiry', 'Observation', 'Recalculation', 'Confirmation'],
                 'correct' => 2, 'exp' => 'Recalculation involves checking the mathematical accuracy of documents or records.'],
                ['topic' => 'Audit Reports', 'diff' => 'moderate',
                 'q' => 'A qualified opinion is appropriate when:',
                 'choices' => ['The statements are fairly presented', 'Misstatements are material but not pervasive', 'Misstatements are both material and pervasive', 'The auditor lacks independence'],
                 'correct' => 1, 'exp' => 'A qualified ("except for") opinion is issued when misstatements are material but not pervasive.'],
                ['topic' => 'Audit Reports', 'diff' => 'difficult',
                 'q' => 'When the possible effects of a scope limitation are both material and pervasive, the auditor issues:',
                 'choices' => ['An unmodified opinion', 'A qualified opinion', 'A disclaimer of opinion', 'An adverse opinion'],
                 'correct' => 2, 'exp' => 'A disclaimer is issued when the auditor cannot obtain sufficient evidence and the possible effects are material and pervasive.'],
                ['topic' => 'Audit Planning and Risk Assessment', 'diff' => 'moderate',
                 'q' => 'Materiality is primarily a matter of:',
                 'choices' => ['Professional judgment', 'A fixed percentage set by law', 'Client preference', 'The size of the audit firm'],
                 'correct' => 0, 'exp' => 'Determining materiality involves the exercise of professional judgment.'],
                ['topic' => 'Philippine Standards on Auditing', 'diff' => 'easy',
                 'q' => 'Professional skepticism requires the auditor to:',
                 'choices' => ['Assume management is dishonest', 'Have a questioning mind and critically assess evidence', 'Accept all evidence at face value', 'Avoid testing internal controls'],
                 'correct' => 1, 'exp' => 'Professional skepticism is an attitude that includes a questioning mind and a critical assessment of audit evidence.'],
            ],

            'RFBT' => [
                ['topic' => 'Law on Contracts', 'diff' => 'easy',
                 'q' => 'Which of the following is NOT an essential requisite of a valid contract?',
                 'choices' => ['Consent of the contracting parties', 'Object certain', 'Cause of the obligation', 'Notarization of the agreement'],
                 'correct' => 3, 'exp' => 'The essential requisites are consent, object, and cause. Notarization is generally a matter of form, not validity.'],
                ['topic' => 'Law on Contracts', 'diff' => 'moderate', 'type' => 'true_false',
                 'q' => 'A contract entered into through fraud is void from the beginning.',
                 'answer' => false, 'exp' => 'A contract vitiated by fraud is voidable, not void; it is valid until annulled.'],
                ['topic' => 'Corporation Code', 'diff' => 'moderate',
                 'q' => 'Under the Revised Corporation Code, a One Person Corporation may be formed by:',
                 'choices' => ['A minimum of five incorporators', 'A single stockholder', 'At least two but not more than fifteen persons', 'Only a government agency'],
                 'correct' => 1, 'exp' => 'The Revised Corporation Code allows a One Person Corporation with a single stockholder.'],
                ['topic' => 'Corporation Code', 'diff' => 'easy',
                 'q' => 'The term of existence of a corporation under the Revised Corporation Code is:',
                 'choices' => ['Limited to 50 years', 'Limited to 25 years', 'Perpetual, unless otherwise stated', 'Fixed at 10 years'],
                 'correct' => 2, 'exp' => 'Corporations now have perpetual existence unless the articles of incorporation provide otherwise.'],
                ['topic' => 'Negotiable Instruments Law', 'diff' => 'moderate',
                 'q' => 'Which of the following is NOT a requirement for negotiability?',
                 'choices' => ['It must be in writing and signed by the maker or drawer', 'It must contain an unconditional promise or order to pay a sum certain', 'It must be payable on demand or at a fixed determinable future time', 'It must state the particular transaction giving rise to it'],
                 'correct' => 3, 'exp' => 'Stating the underlying transaction is not required; in fact, conditioning payment on it may destroy negotiability.'],
                ['topic' => 'Negotiable Instruments Law', 'diff' => 'difficult',
                 'q' => 'A holder in due course takes the instrument:',
                 'choices' => ['Subject to all defenses', 'Free from personal defenses', 'Free from real defenses', 'Only if it is overdue'],
                 'correct' => 1, 'exp' => 'A holder in due course takes the instrument free from personal (but not real) defenses.'],
                ['topic' => 'Insurance Code', 'diff' => 'moderate',
                 'q' => 'Insurable interest in property must exist:',
                 'choices' => ['Only at the time the policy is issued', 'Only at the time of loss', 'Both at the time the policy takes effect and at the time of loss', 'At no particular time'],
                 'correct' => 2, 'exp' => 'For property insurance, insurable interest must exist both when the policy takes effect and when the loss occurs.'],
                ['topic' => 'Insurance Code', 'diff' => 'easy', 'type' => 'true_false',
                 'q' => 'A contract of insurance is a contract of indemnity, except in life insurance.',
                 'answer' => true, 'exp' => 'Insurance is generally a contract of indemnity, but life insurance is an exception (an investment/protection contract).'],
                ['topic' => 'Law on Contracts', 'diff' => 'moderate',
                 'q' => 'An obligation to pay a sum of money is an example of an obligation:',
                 'choices' => ['To give', 'To do', 'Not to do', 'Natural obligation'],
                 'correct' => 0, 'exp' => 'Delivering or paying a determinate or generic thing (including money) is an obligation "to give".'],
                ['topic' => 'Corporation Code', 'diff' => 'moderate',
                 'q' => 'The minimum number of directors for an ordinary stock corporation under the Revised Corporation Code is:',
                 'choices' => ['Exactly 5', 'At least 2', 'Not more than 15 with no fixed minimum below that', 'One', ],
                 'correct' => 2, 'exp' => 'The Revised Corporation Code removed the minimum of five; the board may have up to 15 directors.'],
            ],
        ];
    }
}
