<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentShoppingCart;
use App\Models\TermDeclaration;
use Illuminate\Support\Facades\DB;

/**
 * The student's Document requests basket, for the student app.
 *
 * It is the same student_shopping_carts table the web portal fills, in the
 * same shape - one row per letter set, holding a quantity and how many of
 * those are free - so a basket started in one can be finished in the other.
 *
 * The app thinks in lines (a product and a delivery option), which is one of
 * the two counts on a row. DocumentRequestCatalogue::lines() does that split.
 */
class DocumentRequestBasket
{
    const MAX_PER_LINE = 9;

    /* How long a row waits in the basket before it is dropped, as on the web. */
    const HOLD_DAYS = 30;

    public function __construct(private DocumentRequestCatalogue $catalogue)
    {
    }

    /** The rows still in the basket. Expired ones are dropped on the way, as the web does on every page. */
    public function rows($studentId, $lock = false)
    {
        $this->dropExpired($studentId);

        $query = StudentShoppingCart::with(['letterSet' => function($q){
            $q->withTrashed();
        }])->where('student_id', $studentId)->orderBy('id', 'ASC');

        if($lock):
            $query->lockForUpdate();
        endif;

        return $query->get();
    }

    /** The whole basket as the app shows it, totals included. */
    public function present($studentId): array
    {
        $items = [];
        foreach($this->rows($studentId) as $row):
            foreach($this->catalogue->lines($row) as $line):
                $items[] = $line;
            endforeach;
        endforeach;

        $subtotal = array_sum(array_column($items, 'line_total'));

        return [
            'items' => $items,
            'item_count' => array_sum(array_column($items, 'quantity')),
            'subtotal' => $subtotal,
            'tax' => 0,
            'total' => $subtotal,
            'currency' => DocumentRequestCatalogue::CURRENCY,
        ];
    }

    /**
     * Add an option to the basket, or raise its quantity if it is there already.
     *
     * @return string|null what stopped it, or null once the basket is changed
     */
    public function add(Student $student, $letterSetId, $kind, $quantity): ?string
    {
        return DB::transaction(function () use ($student, $letterSetId, $kind, $quantity) {
            $this->dropExpired($student->id);

            $row = StudentShoppingCart::where('student_id', $student->id)->where('letter_set_id', $letterSetId)
                    ->orderBy('id', 'ASC')->lockForUpdate()->first();

            [$free, $paid] = $this->counts($row);
            if($kind == DocumentRequestCatalogue::OPTION_FREE):
                $free += $quantity;
            else:
                $paid += $quantity;
            endif;

            if(($kind == DocumentRequestCatalogue::OPTION_FREE ? $free : $paid) > self::MAX_PER_LINE):
                return 'You can have up to '.self::MAX_PER_LINE.' of each item in your basket.';
            endif;

            if(!$row):
                $row = new StudentShoppingCart();
                $row->student_id = $student->id;
                $row->letter_set_id = $letterSetId;
                $row->term_declaration_id = $this->termId($student);
                $row->expire_at = now()->addDays(self::HOLD_DAYS);
            endif;

            $this->save($row, $free, $paid);

            return null;
        });
    }

    /** Set how many of a line the student wants. False when the line is not in their basket. */
    public function setQuantity($studentId, $lineId, $quantity): bool
    {
        return $this->change($studentId, $lineId, $quantity);
    }

    /** Take a line out of the basket. False when the line is not in their basket. */
    public function remove($studentId, $lineId): bool
    {
        return $this->change($studentId, $lineId, 0);
    }

    public function clear($studentId): void
    {
        StudentShoppingCart::where('student_id', $studentId)->delete();
    }

    private function change($studentId, $lineId, $quantity): bool
    {
        [$rowId, $kind] = $this->catalogue->parseLineId($lineId);
        if($kind != DocumentRequestCatalogue::OPTION_FREE && $kind != DocumentRequestCatalogue::OPTION_PAID):
            return false;
        endif;

        return DB::transaction(function () use ($studentId, $rowId, $kind, $quantity) {
            $this->dropExpired($studentId);

            /* The id comes off the request, so the row has to be this student's. */
            $row = StudentShoppingCart::where('id', $rowId)->where('student_id', $studentId)->lockForUpdate()->first();
            if(!$row):
                return false;
            endif;

            [$free, $paid] = $this->counts($row);
            if(($kind == DocumentRequestCatalogue::OPTION_FREE ? $free : $paid) < 1):
                return false;
            endif;

            if($kind == DocumentRequestCatalogue::OPTION_FREE):
                $free = $quantity;
            else:
                $paid = $quantity;
            endif;

            if(($free + $paid) < 1):
                $row->delete();
            else:
                $this->save($row, $free, $paid);
            endif;

            return true;
        });
    }

    private function dropExpired($studentId): void
    {
        StudentShoppingCart::where('student_id', $studentId)->where('expire_at', '<', now())->delete();
    }

    /** @return array{0:int,1:int} how many of the row are free, and how many are paid for */
    private function counts($row): array
    {
        if(!$row):
            return [0, 0];
        endif;

        $free = max(0, (int) $row->number_of_free);

        return [$free, max(0, (int) $row->quantity - $free)];
    }

    /** Write the two counts back in the shape the web checkout reads. */
    private function save(StudentShoppingCart $row, $free, $paid): void
    {
        $amount = ($paid * $this->catalogue->paidOption($row->letter_set_id)['price']) / 100;

        $row->quantity = $free + $paid;
        $row->number_of_free = $free;
        $row->product_type = ($paid > 0 ? 'Paid' : 'Free');
        $row->sub_amount = $amount;
        $row->tax_amount = 0;
        $row->total_amount = $amount;
        $row->save();
    }

    /** The term a request is filed under: the student's current one, else the newest. */
    private function termId(Student $student)
    {
        $term = $student->current_term;
        if(isset($term->id) && $term->id > 0):
            return $term->id;
        endif;

        $latest = TermDeclaration::orderBy('id', 'DESC')->first();

        return (isset($latest->id) ? $latest->id : null);
    }
}
