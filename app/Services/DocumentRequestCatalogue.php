<?php

namespace App\Services;

use App\Models\LetterSet;

/**
 * What a student can order from Document requests, and what each choice costs.
 *
 * The web catalogue decides this inline in its blade
 * (pages/students/frontend/document_requests/products.blade.php) and posts the
 * price with the form. The student app cannot be trusted with a price, so the
 * same rules live here and every amount the API uses is worked out from them:
 *
 *   - a letter         3 working days, free   or   same day, £10
 *   - ID card (159)    3 working days, £10
 *   - printer (165)    top up, £5
 *
 * Amounts are whole pence throughout.
 */
class DocumentRequestCatalogue
{
    /* The two letter sets the portal prices differently - the ids the web checks. */
    const ID_CARD_REPLACEMENT = 159;
    const PRINTER_TOP_UP = 165;

    const OPTION_FREE = 1;
    const OPTION_PAID = 2;

    const CURRENCY = 'GBP';

    /** Letter sets currently on offer, in the order the web catalogue lists them. */
    public function letterSets()
    {
        return LetterSet::where('status', 1)->where('document_request', 1)->orderBy('id', 'ASC')->get();
    }

    public function isOnOffer($letterSetId): bool
    {
        return LetterSet::where('id', $letterSetId)->where('status', 1)->where('document_request', 1)->exists();
    }

    /**
     * An option id carries its product: letter set 45 has options 451 (free)
     * and 452 (paid). The basket and the order tables keep one row per letter
     * set with a free count and a paid count, so there is no option row to
     * point at - the id is how the app names which of the two it means.
     */
    public function optionId($letterSetId, $kind): int
    {
        return ((int) $letterSetId * 10) + (int) $kind;
    }

    public function freeOption($letterSetId): array
    {
        return [
            'id' => $this->optionId($letterSetId, self::OPTION_FREE),
            'kind' => self::OPTION_FREE,
            'label' => '3 working days',
            'price' => 0,
            'service_type' => '3 Working Days (Free)',
        ];
    }

    public function paidOption($letterSetId): array
    {
        if($letterSetId == self::PRINTER_TOP_UP):
            $label = 'Printer top up';
            $price = 500;
            $serviceType = 'Printer Top Up (cost £5.00)';
        elseif($letterSetId == self::ID_CARD_REPLACEMENT):
            $label = '3 working days';
            $price = 1000;
            $serviceType = '3 Working Days (cost £10.00)';
        else:
            $label = 'Same day';
            $price = 1000;
            $serviceType = 'Same Day (cost £10.00)';
        endif;

        return [
            'id' => $this->optionId($letterSetId, self::OPTION_PAID),
            'kind' => self::OPTION_PAID,
            'label' => $label,
            'price' => $price,
            'service_type' => $serviceType,
        ];
    }

    /** The options a student may pick for this letter set, keyed by kind. */
    public function options($letterSetId): array
    {
        $options = [];
        if($letterSetId != self::PRINTER_TOP_UP && $letterSetId != self::ID_CARD_REPLACEMENT):
            $options[self::OPTION_FREE] = $this->freeOption($letterSetId);
        endif;
        $options[self::OPTION_PAID] = $this->paidOption($letterSetId);

        return $options;
    }

    /** The option on offer with this id, or null when it is not one of this product's. */
    public function option($letterSetId, $optionId): ?array
    {
        foreach($this->options($letterSetId) as $option):
            if($option['id'] == (int) $optionId):
                return $option;
            endif;
        endforeach;

        return null;
    }

    public function category($letterSetId): string
    {
        return ($letterSetId == self::PRINTER_TOP_UP || $letterSetId == self::ID_CARD_REPLACEMENT ? 'ID card and printing' : 'Letters');
    }

    public function product(LetterSet $letterSet): array
    {
        $options = [];
        foreach($this->options($letterSet->id) as $option):
            $options[] = [
                'id' => $option['id'],
                'label' => $option['label'],
                'price' => $option['price'],
                'currency' => self::CURRENCY,
            ];
        endforeach;

        return [
            'id' => $letterSet->id,
            'name' => $letterSet->letter_title,
            'category' => $this->category($letterSet->id),
            /* letter_sets.description is the body of the letter itself, not a
               blurb for the catalogue, so there is nothing to show here. */
            'description' => null,
            'options' => $options,
        ];
    }

    public function products(): array
    {
        $products = [];
        foreach($this->letterSets() as $letterSet):
            $products[] = $this->product($letterSet);
        endforeach;

        return $products;
    }

    /**
     * Split one basket row or order item into the lines the app shows.
     *
     * A row holds a quantity and how many of them are free, so a letter asked
     * for once free and twice same-day is one row here and two lines there.
     * Basket lines are priced from the catalogue; an order keeps the price it
     * was placed at, which is read back from the amount stored on the item.
     */
    public function lines($row, $forBasket = true): array
    {
        $free = max(0, (int) $row->number_of_free);
        $paid = max(0, (int) $row->quantity - $free);
        $name = (isset($row->letterSet->letter_title) ? $row->letterSet->letter_title : '');
        $lines = [];

        if($free > 0):
            $lines[] = $this->line($row, $name, $this->freeOption($row->letter_set_id), $free, 0, $forBasket);
        endif;
        if($paid > 0):
            $option = $this->paidOption($row->letter_set_id);
            $unitPrice = ($forBasket ? $option['price'] : (int) round(((float) $row->total_amount * 100) / $paid));
            $lines[] = $this->line($row, $name, $option, $paid, $unitPrice, $forBasket);
        endif;

        return $lines;
    }

    private function line($row, $name, array $option, $quantity, $unitPrice, $forBasket): array
    {
        $line = [];
        if($forBasket):
            $line['id'] = $this->lineId($row->id, $option['kind']);
        endif;

        return $line + [
            'product_id' => (int) $row->letter_set_id,
            'product_name' => $name,
            'option_id' => $option['id'],
            'option_label' => $option['label'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
        ];
    }

    /** A basket line id is its basket row plus which of the two counts it is. */
    public function lineId($rowId, $kind): int
    {
        return ((int) $rowId * 10) + (int) $kind;
    }

    /** @return array{0:int,1:int} the basket row id and the kind */
    public function parseLineId($lineId): array
    {
        return [intdiv((int) $lineId, 10), (int) $lineId % 10];
    }

    public static function pence($amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
