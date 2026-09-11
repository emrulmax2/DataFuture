<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;

/**
 * Library Management settings — the three rule sets the library module reads
 * when it takes a deposit, lends a book, or charges for a late return.
 *
 * Values live in `options` under the LIBRARY_SETTINGS category, the same store
 * the rest of Site Settings uses, so no new table is needed for what is a
 * handful of single-value rules.
 *
 * The three screens share one shell and one save action and differ only by the
 * field list below, so they cannot drift apart in layout or in how they store.
 */
class LibrarySettingController extends Controller
{
    public const CATEGORY = 'LIBRARY_SETTINGS';

    private const PAGES = [
        'deposit' => [
            'route'    => 'library.settings.deposit.rule',
            'title'    => 'Deposit Rule',
            'icon'     => 'wallet',
            'lead'     => 'What a borrower must leave on account before a book can go out.',
            'card'     => 'Deposit Rule',
            'cardLead' => 'Amount held against a loan',
            'fields'   => [
                [
                    'name'  => 'library_deposit_amount',
                    'label' => 'Deposit amount',
                    'icon'  => 'pound-sterling',
                    'step'  => '0.01',
                    'hint'  => 'Held while a book is on loan and released when it is returned.',
                ],
                [
                    'name'    => 'library_require_deposit',
                    'label'   => 'Require deposit before borrowing',
                    'type'    => 'toggle',
                    'icon'    => 'shield-check',
                    'hint'    => 'On: a student cannot reserve a book until the deposit is paid. Turn off only to let borrowing continue while card payments are unavailable.',
                    'options' => ['1' => 'Yes — block borrowing until paid', '0' => 'No — allow borrowing without a deposit'],
                ],
            ],
        ],
        'loan' => [
            'route'    => 'library.settings.loan.rule',
            'title'    => 'Loan Rule',
            'icon'     => 'book-open',
            'lead'     => 'How many books a borrower may hold, for how long, and how often a loan can be renewed.',
            'card'     => 'Loan Rules',
            'cardLead' => 'Borrowing limits, loan length and renewals',
            'fields'   => [
                [
                    'name'   => 'library_max_take_home_books',
                    'label'  => 'Max take-home books',
                    'icon'   => 'book-copy',
                    'step'   => '1',
                    'suffix' => 'books',
                    'hint'   => 'Most a single borrower may have on loan at one time.',
                ],
                [
                    'name'   => 'library_loan_period',
                    'label'  => 'Loan period',
                    'icon'   => 'calendar-days',
                    'step'   => '1',
                    'suffix' => 'days',
                    'hint'   => 'Days a book may be kept before it counts as overdue.',
                ],
                [
                    'name'   => 'library_hold_days',
                    'label'  => 'Collection window',
                    'icon'   => 'alarm-clock',
                    'step'   => '1',
                    'suffix' => 'days',
                    'hint'   => 'A booked copy is held this long before the request is cancelled and the book goes back on the shelf.',
                ],
                [
                    'name'   => 'library_max_renewals',
                    'label'  => 'Max renewals',
                    'icon'   => 'refresh-cw',
                    'step'   => '1',
                    'suffix' => 'times',
                    'hint'   => 'How many times one loan may be extended.',
                ],
            ],
        ],
        'fine' => [
            'route'    => 'library.settings.fine.charges',
            'title'    => 'Fine & Charges',
            'icon'     => 'receipt',
            'lead'     => 'What a borrower is charged once a book is overdue.',
            'card'     => 'Fines & Charges',
            'cardLead' => 'Overdue penalties',
            'fields'   => [
                [
                    'name'   => 'library_overdue_penalty',
                    'label'  => 'Overdue penalty',
                    'icon'   => 'pound-sterling',
                    'step'   => '0.01',
                    'suffix' => 'per day, per book',
                    'hint'   => 'Charged for each day a book is late, for each book.',
                ],
                [
                    'name'   => 'library_grace_period',
                    'label'  => 'Grace period',
                    'icon'   => 'clock',
                    'step'   => '1',
                    'suffix' => 'days',
                    'hint'   => 'Days after the due date before the penalty starts.',
                ],
            ],
        ],
    ];

    public function depositRule()
    {
        return $this->page('deposit');
    }

    public function loanRule()
    {
        return $this->page('loan');
    }

    public function fineCharges()
    {
        return $this->page('fine');
    }

    public function save(Request $request, $group)
    {
        abort_unless(isset(self::PAGES[$group]), 404);

        $page = self::PAGES[$group];

        /* Only the fields this screen owns are written, so a stray input in the
           request cannot create an option row nobody reads. */
        $rules = [];
        foreach ($page['fields'] as $field):
            $rules[$field['name']] = ($field['type'] ?? 'number') === 'toggle'
                ? ['required', 'in:0,1']
                : ['required', 'numeric', 'min:0'];
        endforeach;

        $validated = $request->validate($rules, [], $this->attributeNames($page));

        foreach ($validated as $name => $value):
            Option::updateOrCreate(
                ['category' => self::CATEGORY, 'name' => $name],
                [
                    'category' => self::CATEGORY,
                    'name' => $name,
                    'value' => $value,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]
            );
        endforeach;

        return redirect()->route($page['route'])->with('success', $page['title'].' saved.');
    }

    private function page(string $key)
    {
        $page = self::PAGES[$key];

        return view('pages.settings.library.rules', [
            'title' => $page['title'].' - London Churchill College',
            'subtitle' => $page['title'],
            'slug' => 'library_'.$key.'_rule',
            'breadcrumbs' => [
                ['label' => 'Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Library Management', 'href' => 'javascript:void(0);'],
                ['label' => $page['title'], 'href' => 'javascript:void(0);'],
            ],
            'group' => $key,
            'page' => $page,
            'values' => Option::where('category', self::CATEGORY)->pluck('value', 'name')->toArray(),
        ]);
    }

    /** Field labels read better than `library_max_take_home_books` in an error. */
    private function attributeNames(array $page): array
    {
        $names = [];
        foreach ($page['fields'] as $field):
            $names[$field['name']] = strtolower($field['label']);
        endforeach;

        return $names;
    }
}
