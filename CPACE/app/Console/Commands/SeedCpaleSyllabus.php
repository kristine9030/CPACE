<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

/**
 * Seeds the full CPALE outline (PRC Board of Accountancy Resolution No. 274,
 * s. 2015) as nested topics/subtopics under each subject's existing
 * top-level topics. Safe to re-run: matches on (subject_id, parent_id, name)
 * and only ever creates missing nodes — existing rows (which may already
 * have questions/materials attached) are never modified or deleted.
 */
class SeedCpaleSyllabus extends Command
{
    protected $signature = 'cpace:seed-syllabus';

    protected $description = 'Seed the full CPALE syllabus topic/subtopic outline into the subjects table';

    public function handle(): int
    {
        $before = Topic::count();

        $this->seedSubject(1, $this->farTree());
        $this->seedSubject(2, $this->afarTree());
        $this->seedSubject(3, $this->msTree());
        $this->seedSubject(4, $this->taxTree());
        $this->seedSubject(5, $this->audTree());
        $this->seedSubject(6, $this->rfbtTree());

        $after = Topic::count();
        $this->info("Topics before: {$before}, after: {$after} (added " . ($after - $before) . ').');

        return self::SUCCESS;
    }

    private function seedSubject(int $subjectId, array $tree): void
    {
        $order = 100;
        foreach ($tree as $name => $children) {
            $this->upsert($subjectId, null, $name, $order, is_array($children) ? $children : []);
            $order += 10;
        }
    }

    private function upsert(int $subjectId, ?int $parentId, string $name, int $order, array $children): void
    {
        $topic = Topic::firstOrCreate(
            ['subject_id' => $subjectId, 'parent_id' => $parentId, 'name' => $name],
            ['sort_order' => $order, 'is_active' => true]
        );

        $childOrder = 0;
        foreach ($children as $childName => $grandchildren) {
            $this->upsert($subjectId, $topic->id, $childName, $childOrder, is_array($grandchildren) ? $grandchildren : []);
            $childOrder++;
        }
    }

    /** FAR — matches existing topics 1-5, 26-35 (subject_id 1). */
    private function farTree(): array
    {
        return [
            'Development of Financial Reporting Framework and Standard-Setting Bodies' => [],
            'Accounting Process' => [
                'Adjusting Entries' => [],
                'Accounting Cycle' => [],
            ],
            'Conceptual Framework and Accounting Standards' => [
                'Basic Objectives of Financial Statements' => [],
                'Qualitative Characteristics of Financial Statements' => [],
                'Elements of Financial Statements' => [],
                'Financial Capital and Physical Capital' => [],
            ],
            'Presentation of Financial Statements' => [
                'Statement of Financial Position' => [],
                'Statement of Comprehensive Income' => [],
                'Statement of Cash Flows' => [],
                'Statement of Changes in Equity' => [],
                'Notes to the Financial Statements' => [],
            ],
            'Investments and Financial Assets' => [
                'Investments in Debt Instruments' => [
                    'Financial Assets at Fair Value through Profit or Loss (Except Derivatives)' => [],
                    'Financial Assets at Fair Value through Other Comprehensive Income' => [],
                    'Financial Assets at Amortized Cost' => [],
                ],
                'Investments in Associates (equity method of accounting)' => [],
                'Basic Derivatives (excluding Hedge Accounting)' => [
                    'Forwards' => [],
                    'Futures' => [],
                    'Interest Rate Swap' => [],
                    'Call and Put Options' => [],
                ],
            ],
            'Inventories' => [
                'Cost, Lower of cost or Net realizable value' => [],
                'Estimating procedures' => [],
            ],
            'Property, Plant and Equipment' => [
                'Nature' => [],
                'Recognition principle' => [],
                'Initial recognition basis' => [],
                'Depreciation methods' => [],
                'Impairment' => [],
            ],
            'Investment Property' => [
                'Nature and measurement principle' => [],
            ],
            'Intangible Assets' => [
                'Nature and recognition principle' => [],
                'Research and development expenditures' => [],
                'Subsequent expenditures' => [],
                'Amortization' => [],
            ],
            'Biological Assets and Agriculture' => [
                'Nature and recognition principle' => [],
                'After initial recognition' => [],
            ],
            'Non-Current Assets Held For Sale' => [
                'Classification criteria' => [],
                'Initial and subsequent measurement principles' => [],
            ],
            'Liabilities and Provisions' => [
                'Financial Liabilities' => [
                    'Accounts Payable and Other Trade Payables' => [
                        'Initial recognition' => [],
                        'Subsequent measurement' => [],
                    ],
                    'Debt Restructuring' => [
                        'Nature and forms' => [],
                        'Principles of derecognition' => [],
                    ],
                ],
                'Non-Financial Liabilities' => [
                    'Premiums and warranties' => [],
                    'Unearned revenues for gift certificates and subscriptions' => [],
                ],
                'Provisions and Contingencies' => [
                    'Recognition and measurement criteria' => [],
                ],
            ],
            "Shareholders' Equity" => [
                'Share Capital Transactions' => [
                    'Share Capital (nature, recognition, and measurement)' => [],
                    'Issuance and retirement of preference and ordinary shares' => [],
                    'Share split, treasury shares and other equity transactions' => [],
                    'Recapitalization and quasi-reorganization' => [],
                ],
                'Dividends (IFRIC 17)' => [],
                'Retained Earnings' => [],
                'Other Comprehensive Income' => [],
                'Book Value per share and Earnings per Share' => [],
                'Share-based payments (IFRS 2)' => [],
            ],
            'Borrowing Costs (IAS 23)' => [
                'Nature' => [],
                'Criteria for capitalizing borrowing costs' => [],
            ],
            'Leases' => [
                'Operating lease' => [],
                'Finance lease' => [],
            ],
            'Income Taxes (PAS 12)' => [
                'Accounting profit' => [],
                'Taxable profit' => [],
            ],
            'Employee Benefits' => [
                'Defined benefit plan' => [],
                'Defined benefit liability (asset)' => [],
            ],
            'Interim Reporting (IAS 34)' => [
                'Purpose' => [],
                'Principles for Recognition' => [],
            ],
            'IFRS for Small and Medium Sized Entities' => [
                'Reporting requirements' => [],
                'Peculiarities' => [],
                'Principles for reporting investments in equity and debt securities' => [],
            ],
            'Cash to Accrual' => [
                'Purpose' => [],
                'Single-entry computation of profit' => [],
                'Reconciling profit using the transaction approach' => [],
                'Convert cash basis revenues and expenses to accrual basis revenues and expenses' => [],
            ],
        ];
    }

    /** AFAR — matches existing topics for subject_id 2. */
    private function afarTree(): array
    {
        return [
            'Partnership Accounting' => [
                'Formation' => [],
                'Operations' => [],
                'Dissolution / changes in ownership interest' => [
                    'Admission of a new partner' => [
                        'By purchase of interest' => [],
                        'By investment' => [],
                        'Withdrawal, retirement or death of a partner' => [],
                        'Incorporation of a partnership' => [],
                    ],
                ],
                'Liquidation' => [
                    'Lump-sum method' => [],
                    'Installment method' => [],
                ],
            ],
            'Corporate Liquidation' => [
                'Statement of Affairs' => [],
                'Deficiency Statement' => [],
                'Statement of realization and Liquidation' => [],
            ],
            'Joint Arrangements' => [
                'Joint Operations' => [],
                'Joint Venture (Equity method)' => [],
                'Accounting for SME' => [],
            ],
            'Revenue Recognition (PFRS 15)' => [
                'Installment Sales' => [
                    'Recognition of gross profit – regular sales versus installment sales' => [],
                    'Default and Repossession and Trade-in merchandise' => [],
                    'Financial Statement Presentation' => [],
                ],
                "Franchise Operations – Franchisor's point of view" => [
                    'Initial Franchise Fee (accrual method, installment sales method, cost recovery method)' => [],
                    'Continuing Franchise Fee, Bargain Purchase (Option), and Commingled Revenue' => [],
                    'Repossessed Franchise' => [],
                    'Option to Purchase the Franchise Outlet' => [],
                    'Financial Statement Presentation' => [],
                    'Accounting for SME' => [],
                ],
                'Consignment Sales' => [],
            ],
            'Long-term Construction Contracts' => [
                'Journal entries and determination of revenue, costs and gross profit' => [
                    'Percentage of completion' => [
                        'Proportion of contract costs incurred to estimated total contract costs' => [],
                        'Surveys of work performed' => [],
                        'Completion of a physical proportion of the contract work' => [],
                    ],
                    'Cost Recovery Method' => [],
                ],
                'Computation of gross amount due from/to customers' => [],
                'Financial Statement Presentation' => [],
                'Accounting for SME' => [],
            ],
            'Home Office and Branch Accounting' => [
                'Transactions on the books of the home office and the branch' => [],
                'Reconciliation of reciprocal accounts' => [],
                'Preparation of individual and combined financial statements' => [],
                'Special procedures in home office and branch transactions (inter-branch transfer of cash and merchandise at cost or at billed price)' => [],
                'Accounting for Agency transactions' => [],
            ],
            'Business Combinations' => [
                'Acquisition of assets and liabilities (acquisition method)' => [
                    'Determination of Consideration Transferred' => [],
                    'Recognition of Acquired Assets and Liabilities' => [],
                    'Recognition and Measurement of Goodwill and Gain from a Bargain Purchase' => [],
                    'Journal Entries' => [],
                ],
                'Financial Statement Presentation' => [],
                'Accounting for SME' => [],
            ],
            'Separate Financial Statements' => [
                'Accounting for Investment in Subsidiary' => [
                    'Cost' => [],
                    'In accordance with PAS 39/PFRS 9 (effective January 1, 2018)' => [],
                    'Equity method' => [],
                ],
                'Accounting for SME' => [],
            ],
            'Consolidated Financial Statements' => [
                'Date of acquisition' => [],
                'Subsequent to date of acquisition (At cost, in accordance with PAS 39, and equity method)' => [
                    'Net income, dividends, amortization and impairment of goodwill' => [],
                    'With intercompany transactions (inventories, land and depreciable assets)' => [],
                ],
                'Determination of Net Income, Total Comprehensive Income, Equity and Retained Earnings' => [
                    "Attributable to Equity Holders of Parent/Controlling or Parent's Interest" => [],
                    'Non-controlling Interest' => [],
                    'Consolidated/Group' => [],
                ],
                'Accounting for SME' => [],
            ],
            'Foreign Currency Transactions' => [
                'Without hedging activities (import, export, lending, and borrowing transactions)' => [],
                'Hedging Activities: Hedging Foreign Currency Exposures' => [
                    'Foreign Currency Forward Contracts' => [
                        'Hedges where hedge accounting is Not Required (Undesignated Hedges)' => [
                            'Exposed Asset (Import) or Liability (Export) Position' => [],
                            'Speculation' => [],
                        ],
                        'Hedge that requires Hedge Accounting' => [
                            'Fair value hedge — Hedge of a Firm Commitment (Purchase or Sale Transaction)' => [],
                            'Cash flow hedge — Hedge of a Firm Commitment (Purchase or Sale Transaction)' => [],
                            'Cash flow hedge — Hedge of a Forecasted Transaction (Purchase or Sale Transaction)' => [],
                        ],
                        'Hedge of a net investment in foreign entity' => [],
                    ],
                ],
                'Accounting for SME' => [],
            ],
            'Translation of Foreign Currency Financial Statements' => [
                'Translation from the Functional Currency to the Presentation Currency (Closing/Current Rate Method)' => [],
                'Remeasurement from a Foreign Currency to the Functional Currency (Temporal Method)' => [],
                'Restatement of financial statements (functional currency of a hyperinflationary economy)' => [],
            ],
            'Not-for-Profit Organizations' => [
                'Voluntary health and welfare organizations (VHWO)' => [],
                'Hospitals and other health care organizations' => [],
                'Colleges and Universities' => [],
                'Other not-for-profit organizations (churches, museums, fraternity associations, etc.)' => [],
            ],
            'Government Accounting' => [
                'Basic Concepts in Government Accounting' => [],
                'Budget Process' => [],
                'Journal Entries – Books of National Government Agency' => [],
            ],
            'Other Special Topics' => [
                'Accounting for insurance contracts by insurers (PFRS 4)' => [],
                'Accounting for build, operate & transfer (IFRIC 12)' => [],
            ],
            'Cost Accounting' => [
                'System of cost accumulation or costing system' => [
                    'Comparison between Actual Costing, Normal Costing and Standard Costing' => [],
                ],
                'Job-order costing system' => [
                    'Cost accumulation procedures – materials, labor and overhead' => [],
                    'Journal entries' => [],
                    'Preparation of statement of goods manufactured and sold' => [],
                    'Accounting for scrap, waste, spoilage and rework' => [],
                ],
                'Process costing system' => [
                    'Cost accumulation procedures – materials, labor and overhead' => [],
                    'Journal entries' => [],
                    'Preparation of cost of production report' => [
                        'First-in, first-out (FIFO) method' => [],
                        'Average method' => [],
                    ],
                    'Accounting for lost units' => [
                        'Normal lost units' => [],
                        'Abnormal lost unit' => [],
                    ],
                ],
                'Backflush costing system (JIT system)' => [
                    'Cost accumulation procedures – materials, labor and overhead' => [],
                    'Journal entries' => [],
                ],
                'Service Cost Allocation' => [
                    'Direct method' => [],
                    'Step-down' => [],
                    'Reciprocal method' => [],
                ],
                'Activity-based costing system (ABC costing)' => [
                    'Allocation of costs: Traditional Costing versus ABC Costing' => [],
                    'Determination of Total Product Costs: Traditional Costing versus ABC Costing' => [],
                ],
                'Accounting for joint and by-products' => [
                    'Methods of allocating joint cost to products' => [
                        'Market (sales) value method' => [
                            'Market value at split-off point approach' => [],
                            'Hypothetical Market Value / Net Realizable Value Approach' => [],
                            'Average unit (production output) method' => [],
                            'Weighted average method' => [],
                        ],
                        'Methods of allocating joint cost to by-products' => [
                            'No joint cost allocated to by-product' => [],
                            'With joint costs allocated to by-product' => [],
                        ],
                        'Treatment of by-products' => [],
                    ],
                ],
                'Standard Costing (two-way variance excluding mix and yield variances)' => [
                    'Computation of Variances' => [],
                    'Journal Entries and reporting' => [],
                ],
            ],
        ];
    }

    /** MS (Management Advisory Services) — matches existing topics for subject_id 3. */
    private function msTree(): array
    {
        return [
            'Objectives, Role and Scope of Management Accounting' => [
                'Basic management functions and concepts' => [],
                'Distinction among management accounting, cost accounting and financial accounting' => [],
                'Role and activities of controller and treasurer' => [],
                'International certifications in management accounting' => [],
            ],
            'Cost Concepts and Behavior' => [
                'Nature and classification of costs' => [],
                'Analysis of cost behavior (variable, fixed, semi-variable/mixed, step-cost)' => [],
                'Splitting mixed cost (high-low, scatter graph, least-squares regressions)' => [],
            ],
            'Cost-Volume-Profit Analysis' => [
                'Uses, assumptions and limitations of CVP analysis' => [],
                'Factors affecting profit' => [],
                'Breakeven point in unit sales and peso sales' => [],
                'Required selling price, unit sales and peso sales to achieve a target profit' => [],
                'Sensitivity analysis (including indifference point in unit sales and peso sales)' => [],
                'Use of sales mix in multi-product companies' => [],
                'Concepts of margin of safety and degree of operating leverage' => [],
            ],
            'Standard Costing and Variance Analysis' => [
                'Direct material variance (quantity, price usage, purchase price, mix and yield)' => [],
                'Direct labor variance (efficiency, rate, mix and yield)' => [],
                'Factory overhead variance (two-way, three-way, four-way methods)' => [],
            ],
            'Variable and Absorption Costing' => [
                'Nature and treatment of fixed factory overhead costs' => [],
                'Distinction between product cost and period cost' => [],
                'Inventory costs between variable costing and absorption costing' => [],
                'Reconciliation of operating income under variable costing and absorption costing' => [],
            ],
            'Budgeting and Budgetary Control' => [
                'Definition and coverage of the budgeting process' => [],
                'Master budget and its components (operating and financial budgets)' => [],
                'Types of budgets (static, flexible, zero-based, continuous)' => [],
                'Budget variance analysis (static and flexible)' => [],
            ],
            'Activity-Based Costing' => [
                'Activity levels (unit-level, batch-level, product-level, facility-level), cost pools and activity drivers' => [],
                'Determination of cost pool rates and application of overhead costs' => [],
                'Traditional costing vs. activity-based costing' => [],
                'Process value analysis (value-added and non-value-added activities)' => [],
            ],
            'Strategic Cost Management' => [
                'Total quality management' => [],
                'Just-in-time production system' => [],
                'Continuous improvement' => [],
                'Business Process Reengineering' => [],
                'Kaizen costing' => [],
                'Product life cycle costing' => [],
                'Target costing' => [],
            ],
            'Responsibility Accounting and Transfer Pricing' => [
                'Type of responsibility centers (cost, revenue, profit and investment centers)' => [],
                'Concepts of decentralization and segment reporting' => [],
                'Controllable and non-controllable costs, direct and common costs' => [],
                'Performance margin (manager vs. segment performance)' => [],
                "Preparation of 'segmented' income statement" => [],
                'Return on investment (ROI), residual income and economic value added (EVA)' => [],
                'Rationale and need for transfer price' => [],
                'Transfer pricing schemes (minimum, market-based, cost-based, negotiated price)' => [],
            ],
            'Balanced Scorecard' => [
                'Nature and perspectives of balanced scorecard' => [],
                'Financial and non-financial performance measures' => [],
            ],
            'Quantitative Techniques' => [
                'Regression and correlation analysis' => [],
                'Gantt chart' => [],
                'Program evaluation review technique (PERT) – Critical Path Method (CPM)' => [],
                'Probability analysis (expected value concept)' => [],
                'Decision tree diagram' => [],
                'Learning curve' => [],
                'Inventory models (carrying and ordering costs, EOQ model, safety stock, reorder point)' => [],
                'Linear programming (graphic method; algebraic method)' => [],
            ],
            'Relevant Costing and Decision Making' => [
                'Definition and identification of relevant costs' => [],
                'Concept of opportunity costs' => [],
                'Approaches in analyzing alternatives in non-routine decisions (total and differential)' => [],
                'Types of decisions (make or buy, accept/reject special order, continue or drop, sell or process further, best product combination, pricing decisions)' => [],
            ],
            'Objectives and Scope of Financial Management' => [
                'Nature, purpose and scope of financial management' => [],
                'Role of financial managers in investment, operating and financing decisions' => [],
            ],
            'Financial Statement Analysis' => [
                'Vertical analysis (common-size financial statements)' => [],
                'Horizontal analysis (trend percentages and index analysis)' => [],
                'Cash flow analysis (interpretation of cash flows including free cash flow concept)' => [],
                'Gross profit variance analysis (price, cost and volume factors)' => [],
                'Financial ratios (liquidity, solvency, activity, profitability, growth and other ratios; Du Pont model)' => [],
                'Financial forecasting using additional funds needed (AFN)' => [],
            ],
            'Working Capital Management' => [
                'Concepts and significance of working capital management' => [],
                'Working capital investment and financing policies (conservative vs. aggressive)' => [],
                'Cash and marketable securities management (cash conversion cycle, optimal cash balance, collection and disbursement float, cash management system)' => [],
                'Receivables management (average balance and investment in receivables, incremental analysis, discount/collection/credit policy evaluation)' => [],
                'Inventory management (carrying, ordering and stock-out costs, EOQ model, safety stock, reorder point)' => [],
                'Sources of short-term funds (trade credit, bank loans, commercial papers, receivable factoring)' => [],
                'Estimating cost of short-term funds (annual cost of trade credit, effective and nominal annual rate)' => [],
            ],
            'Capital Budgeting' => [
                'Capital investment decision factors (net investment, cost of capital, cash and accrual net returns)' => [],
                'Non-discounted capital budgeting techniques (payback period, ARR, bail-out payback, payback reciprocal)' => [],
                'Discounted capital budgeting techniques (NPV, IRR, profitability index, equivalent annual annuity, Fisher rate/NPV point of indifference)' => [],
                'Project screening, project ranking and capital rationing (independent and mutually exclusive projects)' => [],
                'Sensitivity analysis (effects of changes in project cash flow, tax rates and other assumptions)' => [],
            ],
            'Risks and Rates of Return' => [
                'Types of risks (business/operating, financing)' => [],
                'Measures of risks (coefficient of variation and standard deviation)' => [],
                'Degree of operating, financial and total leverage' => [],
            ],
            'Cost of Capital and Capital Structure' => [
                'Basic concepts and tools of capital structure management' => [],
                'Sources of intermediate and long-term financing (including hybrid financing)' => [],
                'Cost of capital (cost of long-term debt, preferred shares, equity, WACC, marginal cost of capital)' => [],
            ],
            'Management Consultancy' => [
                'Management Consultancy Practice by CPAs' => [
                    'Nature of management consultancy engagements' => [],
                    'Professional attributes of management consultants' => [],
                    'Areas, stages and management of management consultancy engagements' => [],
                ],
                'Project Feasibility Studies' => [
                    'Nature, purpose and components (economic/marketing, technical and financial)' => [],
                    'Analysis of project revenue and costs under specific assumptions' => [],
                    'Preparation of projected financial statements' => [],
                    'Analysis of financial projections' => [],
                ],
            ],
            'Economic Concepts' => [
                'Macroeconomics (GDP, unemployment, inflation, fiscal and monetary policies, international trade and foreign exchange rates)' => [],
                'Microeconomics (supply, demand, market equilibrium, price elasticity of demand, market structure, production and cost functions)' => [],
            ],
        ];
    }

    /** Taxation — matches existing topics for subject_id 4. */
    private function taxTree(): array
    {
        return [
            'Principles of Taxation' => [
                'Nature, scope, classification, and essential characteristics' => [],
                'Principles of sound tax system' => [],
                'Limitations on the power of taxation' => [],
                'Tax evasion vs. tax avoidance' => [],
                'Situs/place of taxation' => [],
                'Double taxation' => [],
                'Legislation of tax laws' => [],
                'Impact of taxes in nation building' => [],
                'Ethical tax compliance and administration' => [],
                'Organization of the BIR, BOC, Local Government Tax Collecting Units, Board of Investments, PEZA' => [],
            ],
            'Tax Remedies' => [
                'Remedies of the government' => [
                    'Definition, scope, prescriptive period' => [],
                    'Administrative remedies' => [],
                    'Judicial actions' => [],
                    'Additions to Tax (Surcharge, Interest, Compromise penalty)' => [],
                    'Other sanctions (Criminal penalties, Closure of Business, Collection of Delinquent Taxes)' => [],
                    'Powers of the Bureau of Internal Revenue' => [],
                ],
                'Remedies of the taxpayer' => [
                    'Definition, scope, prescriptive period' => [],
                    'Taxpayers rights' => [],
                    'Administrative remedies (protesting assessment, recovery of erroneously paid taxes, compromise/abatement/refund/credit requests, requests for rulings)' => [],
                    'Judicial remedies' => [],
                ],
                'Expanded jurisdiction of the Court of Tax Appeals' => [],
            ],
            'Gross Income and Deductions' => [
                'Taxpayer and tax base' => [
                    'Individuals' => [],
                    'Corporations' => [],
                    'Partnerships' => [],
                    'Joint ventures' => [],
                    'Estates and trusts' => [],
                    'Co-ownerships' => [],
                    'Tax exempt individuals and organizations' => [],
                ],
                'Gross income' => [
                    'Inclusions in the gross income' => [],
                    'Exclusions/exemptions from gross income' => [],
                    'Income from compensation' => [],
                    'Income from business' => [],
                    'Passive income subject to final withholding tax' => [],
                    'Capital gains' => [],
                ],
                'Deductions from gross income' => [
                    'Itemized deductions' => [],
                    'Items not deductible' => [],
                    'Optional standard deduction' => [],
                    'Deductions allowed under special laws' => [],
                ],
                'Accounting periods' => [],
                'Accounting methods' => [
                    'Reconciliation of income under PFRS and income under tax accounting' => [],
                ],
                'Tax return preparation and filing and tax payments' => [
                    'Manual filing' => [],
                    'Electronic filing and E-submission' => [],
                    'Large taxpayers and non-large taxpayers' => [],
                    'Income tax credits' => [],
                    'Venue and time of filing of tax returns' => [],
                    'Venue and time of payment' => [],
                    'Modes of payment' => [],
                    'Use of tax tables' => [],
                    'Accomplishing of various income tax returns and forms' => [],
                ],
                'Compliance Requirements' => [
                    'Administrative requirements (registration, issuance of receipts, printing of receipts)' => [],
                    'Attachments to the income tax return, including CPA certificate, per NIRC requirement' => [],
                    'Keeping of books of accounts and records, including report of inventories' => [],
                    'Prescriptive period to maintain books of accounts and other accounting records' => [],
                ],
            ],
            'Income Tax - Individuals' => [],
            'Income Tax - Corporations' => [],
            'Income Tax - Partnerships, Estates and Trusts' => [],
            'Withholding Taxes' => [
                'Time of withholding' => [],
                'Income payments subject to withholding' => [],
                'Year-end withholding of tax and requirements' => [],
                'Venue and time of filing of withholding tax returns' => [],
                'Venue and time of payment' => [],
                'Modes of payment' => [],
                'Use of tax tables and rates' => [],
                'Use of various withholding tax returns and forms' => [],
            ],
            'Estate Tax' => [
                'Gross estate' => [],
                'Deductions allowed to estate' => [],
                'Tax credit' => [],
                'Venue and time of filing of tax returns' => [],
                'Venue and time of payment' => [],
                'Modes of payment' => [],
                'Use of tax tables' => [],
                'Accomplishing of tax returns and forms' => [],
                'Attachments to the tax return' => [],
                'Administrative requirements' => [],
            ],
            "Donor's Tax" => [
                'Gross gift' => [],
                'Exemptions' => [],
                'Tax rates in general and when the donee is a stranger' => [],
                'Venue and time of filing of tax returns' => [],
                'Venue and time of payment' => [],
                'Modes of payment' => [],
                'Use of tax tables' => [],
                'Accomplishing of tax returns and forms' => [],
                'Attachments to the tax return' => [],
                'Administrative requirements' => [],
            ],
            'Value Added Tax' => [
                'Output VAT' => [],
                'Input VAT' => [],
                'VAT tax credits' => [],
                'Refund of excess input VAT' => [],
                'Venue and time of filing of VAT returns' => [],
                'Venue and time of payment' => [],
                'Modes of payment' => [],
                'Accomplishing of tax returns and forms' => [],
                'Attachments to the tax return' => [],
                'Invoicing and Accounting requirements' => [],
            ],
            'Percentage Tax' => [
                'Tax base and tax rates' => [],
                'Venue and time of filing of tax returns' => [],
                'Venue and time of payment' => [],
                'Modes of payment' => [],
                'Use of tax rates' => [],
                'Accomplishing of tax returns and forms' => [],
            ],
            'Taxation Under the Local Government Code' => [
                'Scope and different types of local taxes (real property tax, local business tax)' => [],
                'Tax base and tax rates' => [],
                'Venue and time of filing of tax returns' => [],
                'Venue and time of payment' => [],
            ],
            'Preferential Taxation and Incentives' => [
                'Senior Citizens Law' => [
                    'Exemption from income tax of qualified senior citizens' => [],
                    'Tax incentives for qualified establishments selling goods and services to senior citizens' => [],
                ],
                'Magna Carta for Disabled Persons' => [
                    'Tax incentives for qualified establishments selling goods and services to persons with disability' => [],
                ],
                'Special Economic Zone Act' => [
                    'Policy and the Philippine Economic Zone Authority (PEZA)' => [],
                    'Registration of investments' => [],
                    'Fiscal incentives to PEZA-registered economic zone enterprises' => [],
                ],
                'Omnibus Investments Code (Book 1 of Executive Order 226)' => [
                    'Policy and the Board of Investments (BOI)' => [],
                    'Preferred areas of investment' => [],
                    'Investments Priority Plan' => [],
                    'Registration of investments' => [],
                    'Fiscal incentives to BOI-registered enterprises' => [],
                ],
                'Barangay Micro Business Enterprises (BMBEs) Act' => [
                    'Registration of BMBEs' => [],
                    'Fiscal incentives to BMBEs' => [],
                ],
                'Double Taxation Agreements (DTA)' => [
                    'Nature and purpose of DTAs' => [],
                    'Manner of giving relief from double taxation' => [],
                    'Procedure for availment of tax treaty benefits' => [],
                ],
            ],
            'Tariff and Customs' => [
                'Functions of the Bureau of Customs' => [],
                'Functions of the Tariff Commission' => [],
                'Nature of tariff and customs duties' => [],
                'Basis of assessment of duty' => [],
                'Documents required for importation of goods' => [],
                'Documents required for export of goods' => [],
            ],
            'Effective Communication to Stakeholders' => [],
        ];
    }

    /** Auditing — matches existing topics for subject_id 5. */
    private function audTree(): array
    {
        return [
            'Fundamentals of Auditing and Assurance' => [
                'Introduction to assurance engagements' => [
                    'Nature, objective and elements' => [],
                    'Types of assurance engagements (audits, reviews, other assurance engagements)' => [],
                    'Assurance service vis-à-vis attestation services' => [],
                ],
                'Introduction to auditing' => [
                    'Nature, philosophy, and objectives' => [],
                    'Types of audit' => [
                        'According to nature of assertion/data (financial statements, operational, compliance audit)' => [],
                        'According to types of auditor (external, internal, government audit)' => [],
                    ],
                ],
            ],
            'Audit Planning and Risk Assessment' => [
                'Overview of the audit process' => [],
                'Pre-engagement procedures' => [],
                'Scope and purposes of audit planning' => [
                    'Essential planning requirements' => [
                        'Knowledge of the business' => [],
                        'Preliminary analytical procedures' => [],
                        'Materiality' => [],
                        'Assessing and managing audit risks' => [],
                        'Overall audit plan and audit program (experts, internal auditor, other independent auditors)' => [],
                    ],
                ],
                'Direction, supervision and review' => [],
                'Understanding the Entity and its Environment' => [
                    'Industry, regulatory and other external factors' => [
                        'Nature of the entity' => [],
                        'Objectives and strategies and related business risks' => [],
                        "Measurement and review of the entity's financial performance" => [],
                    ],
                ],
                'Assessing the risks of material misstatement' => [
                    'Fraud and errors' => [],
                    'Risk assessment procedures' => [],
                    'Discussion among the engagement team' => [],
                    'Significant risks that require special audit consideration' => [],
                    'Risks for which substantive procedures alone do not provide sufficient appropriate audit evidence' => [],
                    'Revision of risk assessment' => [],
                ],
                'Communicating with those charged with governance and management' => [],
            ],
            'Internal Control' => [
                'Basic concepts and elements of internal control' => [],
                'Consideration of accounting and internal control systems' => [
                    'Understanding and documentation' => [],
                    'Assessment of control risks' => [
                        'Test of controls' => [],
                        'Documentation' => [],
                    ],
                ],
            ],
            'Audit Evidence and Procedures' => [
                'Nature and significance' => [],
                'Evidential matters' => [],
                'Audit procedures/techniques' => [],
                'Audit working papers' => [],
            ],
            'Auditing in a CIS Environment' => [
                'Internal control in a CIS environment' => [
                    'Introduction' => [],
                    'Impact of computers on accounting and internal control systems' => [
                        'General controls' => [],
                        'Application controls' => [],
                    ],
                    'Unique characteristics of specific CIS' => [
                        'Stand alone' => [],
                        'On-line' => [],
                        'Database system' => [],
                    ],
                ],
                'Basic approach to the audit of CIS environment' => [
                    'Introduction' => [],
                    'Effects of computers on the audit process' => [],
                    'Computer assisted audit techniques' => [],
                ],
            ],
            'Completing the Audit' => [
                'Completing the audit and audit report preparation' => [
                    'Analytical procedures for overall review' => [],
                    'Related party transactions' => [],
                    'Subsequent events review' => [],
                    'Assessment of going concern assumption' => [],
                    "Obtaining client's representation letter" => [],
                    'Evaluating findings, formulating an opinion and drafting the audit report' => [],
                ],
                'Post-audit responsibilities' => [
                    'Subsequent discovery of facts' => [],
                    'Subsequent discovery of omitted procedures' => [],
                ],
            ],
            'Audit Reports' => [
                "The unqualified auditor's report" => [],
                "Basic elements of the unqualified auditor's report" => [],
                "Modified auditor's report" => [
                    "Matters that do not affect the auditor's opinion" => [],
                    "Matters that do affect the auditor's opinion" => [],
                ],
                'Report on comparatives' => [],
            ],
            'Other Assurance and Non-assurance Services' => [
                'Procedures and reports on special purpose audit engagements' => [
                    'General considerations' => [],
                    'Audit of financial statements prepared under a comprehensive basis of accounting other than GAAP in the Philippines' => [],
                    'Audit of a component of financial statements' => [],
                    'Reports on compliance with contractual agreements' => [],
                    'Reports on summarized financial statements' => [],
                ],
                'Nonaudit engagements: procedures and reports' => [
                    'Examination of prospective financial information' => [],
                    'Engagements to review financial statements' => [],
                ],
                'Nonassurance engagements' => [
                    'Engagements to perform agreed-upon procedures regarding financial information' => [],
                    'Engagements to compile financial information' => [],
                ],
            ],
            'Audit of Financial Statement Cycles' => [
                'Audit of the revenue and receipt cycle' => [
                    'Audit of sales and revenue transactions' => [],
                    'Audit of receivable balances' => [],
                    'Audit of cash receipt transactions / cash balance' => [],
                ],
                'Audit of expenditure and disbursement cycle' => [
                    'Audit of acquisitions and purchases' => [],
                    'Audit of payroll transactions' => [],
                    'Audit of cash disbursement transactions / cash balance' => [],
                    'Audit of inventory balances' => [],
                    'Audit of trade payable balances' => [],
                    'Audit of prepaid expenses and accrued liabilities' => [],
                ],
                'Audit of production cycle' => [
                    'Audit of conversion activities' => [],
                    'Audit of inventory balances: work-in-process and finished goods' => [],
                    'Audit of cost of goods sold balance' => [],
                ],
                'Audit of the financing cycle' => [
                    'Audit of financing cycle transactions' => [],
                    'Audit of non-trade liability balances' => [],
                    'Audit of interest expense and finance cost balances' => [],
                    'Audit of equity accounts' => [],
                ],
                'Audit of investing cycle' => [
                    'Audit of investing transactions' => [],
                    'Audit of investment account balances' => [],
                    'Audit of property, plant and equipment account balances' => [],
                    'Audit of intangible account balances' => [],
                ],
                'Audit of cash balances' => [],
            ],
        ];
    }

    /** RFBT — matches existing topics for subject_id 6. */
    private function rfbtTree(): array
    {
        return [
            'Obligations' => [
                'Sources of obligations and their concepts' => [
                    'Law' => [],
                    'Contracts' => [],
                    'Quasi-contracts' => [],
                    'Delicts' => [],
                    'Quasi-delicts' => [],
                ],
                'Kinds of obligations in general under the Civil Code' => [],
                'Specific circumstances affecting obligations in general' => [
                    'Fortuitous events' => [],
                    'Fraud' => [],
                    'Negligence' => [],
                    'Delay' => [],
                    'Breach of contract' => [],
                ],
                'Duties of obligor in obligation to do or not to do' => [],
                'Extinguishment of obligation' => [
                    'Payment of debts of money' => [],
                    'Mercantile documents as means of payment' => [],
                    'Special forms or modes of payment' => [],
                    'Remission or condonation, confusion, compensation and novation' => [],
                    'Effect of insolvency and bankruptcy on extinguishment of obligation' => [],
                ],
            ],
            'Law on Contracts' => [
                'Concepts and classification' => [],
                'Elements and stages' => [],
                'Freedom from contract and limitation' => [],
                'Persons bound' => [],
                'Consent' => [
                    'Capacitated persons' => [],
                    'Requisites' => [],
                    'Vices of consent' => [],
                ],
                'Objects of contracts' => [],
                'Considerations of contracts' => [],
                'Formalities of contracts' => [],
                'Interpretation and reformation of contract' => [],
                'Defective contracts' => [
                    'Rescissible' => [],
                    'Voidable' => [],
                    'Unenforceable' => [],
                    'Void' => [],
                ],
            ],
            'Sales' => [
                'Nature, forms and requisites' => [],
                'Earnest money as distinguished from option money' => [],
                'Rights/obligations of vendor and vendee' => [],
                'Warranties (in relation to consumer laws)' => [],
                'Installment sales' => [
                    'Personal property – Recto Law' => [],
                    'Real Property – Maceda Law' => [],
                    'PD 957 / Condominium Act' => [],
                ],
            ],
            'Credit Transactions (Pledge, Mortgage)' => [
                'Pledge, Real Mortgage and Chattel Mortgage' => [
                    'Nature and requisites' => [],
                    'Requirements to bind the parties and third persons' => [],
                    'Obligations and rights of pledgor and pledgee' => [],
                    'Obligations and rights of mortgagor and mortgagee' => [],
                    'Effect of pactum commissorium' => [],
                    'Modes of extinguishment' => [],
                ],
                'Insolvency Law' => [
                    'Definition of insolvency' => [],
                    'Suspension of payments' => [],
                    'Voluntary insolvency' => [],
                    'Involuntary insolvency' => [],
                ],
                'Corporate Rehabilitation' => [
                    'Definition of Terms' => [],
                    'Stay Order' => [],
                    'Receiver' => [],
                    'Rehabilitation Plan' => [],
                    'Contents of Petition and other types of Rehabilitations' => [],
                ],
            ],
            'Negotiable Instruments Law' => [
                'Negotiability of instrument' => [],
                'Abnormal negotiable instruments' => [],
                'Incomplete but delivered instruments' => [],
                'Incomplete and undelivered instruments' => [],
                'Complete but undelivered instruments' => [],
                'Instruments with forged signature' => [],
            ],
            'Bouncing Checks Law (BP 22)' => [
                'Checks without sufficient funds' => [],
                'Evidence of knowledge of insufficient funds' => [],
                'Duty of Drawee' => [],
                'Credit Construed' => [],
            ],
            'Partnership Law' => [
                'Nature and as distinguished from corporation' => [],
                'Elements and kinds' => [],
                'Formalities required' => [],
                'Rules of management' => [],
                'Distribution of profits and losses' => [],
                'Sharing of losses and liabilities' => [],
                'Modes and retirement requirements' => [],
                'Limited partnership' => [],
            ],
            'Corporation Code (Revised Corporation Code)' => [
                'Nature and classes of corporation' => [],
                'Incorporation and organization of Private Corporation' => [],
                'Powers of a corporation' => [
                    'Expressed' => [],
                    'Implied' => [],
                    'Incidental' => [],
                ],
                'Board of Directors/Corporate Officers' => [
                    'Qualifications' => [],
                    'Election and removal' => [],
                    'Powers and fiduciary duties' => [],
                ],
                'Classes of stocks' => [
                    'Concepts' => [],
                    'Subscriptions' => [],
                ],
                'Powers, duties, rights and obligations of stockholders' => [],
                'Majority and minority control' => [],
                'By-Laws' => [],
                'Meetings' => [],
                'Corporate reorganization' => [
                    'Mergers' => [],
                    'Consolidations' => [],
                    'Other business combinations' => [],
                ],
                'Non-stock corporation' => [],
                'Modes of dissolution and liquidation' => [
                    'Retirement Requirements' => [],
                ],
                'Foreign corporations' => [
                    'License to do business' => [
                        'Purpose of the license' => [],
                    ],
                    'Requirements for application/issuance of license' => [],
                    'Consequence of doing business without a license' => [],
                    'Definition and rights of foreign corporations' => [],
                    'Definition of doing business and its relation to foreign investments' => [],
                    'Purpose and qualifications of Resident agent' => [],
                    'Suits against foreign corporations' => [],
                    'Suspension or revocation of license' => [],
                    'Withdrawal from business' => [],
                ],
                'Kinds and availability of corporate books' => [],
                'Securities Regulation Code' => [
                    'Kinds of securities' => [],
                    'Protection of investors, private tender offer and Insider Trading' => [],
                    'SEC Circulars and Issuances' => [],
                    'Code of Corporate Governance' => [],
                    'Filing of General Information Sheet' => [],
                    'Filing of Annual Audited Financial Statements' => [],
                ],
            ],
            'Cooperatives' => [
                'Organization and Registration of Cooperatives' => [],
                'Administration' => [],
                'Responsibilities, Rights and Privileges of Cooperatives' => [],
                'Capital, Property of Funds' => [],
                "Audit, Inquiry and Members' Right to Examine" => [],
                'Allocation and Distribution of Funds' => [],
                'Types and Categories of Cooperatives' => [],
                'Merger and Consolidation of Cooperatives' => [],
                'Dissolution of Cooperatives' => [],
            ],
            'Banking Laws and AMLA' => [
                'PDIC Law' => [
                    'Insurable deposits' => [],
                    'Maximum liability' => [],
                    'Requirements for Claims' => [],
                ],
                'Secrecy of Bank Deposits and Unclaimed Balances Law' => [],
                'General Banking Law' => [
                    'Definition of Banks' => [],
                    'Loans' => [
                        'SBL' => [],
                        'DOSRI' => [],
                    ],
                ],
                'AMLA Law' => [
                    'Covered transactions' => [],
                    'Suspicious transactions' => [],
                    'Reportorial Requirement' => [],
                ],
                'The New Central Bank Act' => [
                    'Legal tender power over coins and notes' => [],
                    'Conservatorship' => [],
                    'Receivership and Closures' => [],
                ],
            ],
            'Intellectual Property Law' => [
                'The Law on Patents' => [],
                'The Law on Trademark, Service Marks and Trade Names' => [],
                'The Law on Copyright' => [],
            ],
        ];
    }
}
