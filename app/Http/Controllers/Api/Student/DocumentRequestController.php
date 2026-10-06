<?php

namespace App\Http\Controllers\Api\Student;

use App\Services\DocumentRequestBasket;
use App\Services\DocumentRequestCatalogue;
use App\Services\DocumentRequestOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Document requests in the student app: the catalogue, the basket and the
 * Checkout screen. Every basket call answers with the whole basket, so the app
 * never works out a total itself.
 */
class DocumentRequestController extends StudentApiController
{
    public function __construct(
        private DocumentRequestCatalogue $catalogue,
        private DocumentRequestBasket $basket,
        private DocumentRequestOrders $orders,
    ) {
    }

    public function products(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        return $this->ok($this->catalogue->products());
    }

    public function basket(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        return $this->ok($this->basket->present($student->id));
    }

    /** Tapping a delivery button: adds the option, or raises its quantity. */
    public function addItem(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'option_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:'.DocumentRequestBasket::MAX_PER_LINE,
        ]);
        if($validator->fails()):
            return $this->invalid($validator);
        endif;

        $productId = (int) $request->input('product_id');
        if(!$this->catalogue->isOnOffer($productId)):
            return $this->invalid(['product_id' => ['This item is not available to order.']]);
        endif;

        $option = $this->catalogue->option($productId, $request->input('option_id'));
        if(!$option):
            return $this->invalid(['option_id' => ['This option is not available for the item.']]);
        endif;

        $quantity = (!empty($request->input('quantity')) ? (int) $request->input('quantity') : 1);
        $error = $this->basket->add($student, $productId, $option['kind'], $quantity);
        if($error):
            return $this->invalid(['quantity' => [$error]]);
        endif;

        return $this->ok($this->basket->present($student->id));
    }

    /** The quantity stepper. */
    public function updateItem(Request $request, $itemId)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:'.DocumentRequestBasket::MAX_PER_LINE,
        ]);
        if($validator->fails()):
            return $this->invalid($validator);
        endif;

        if(!$this->basket->setQuantity($student->id, (int) $itemId, (int) $request->input('quantity'))):
            return $this->fail('Basket item not found.', 404);
        endif;

        return $this->ok($this->basket->present($student->id));
    }

    public function removeItem(Request $request, $itemId)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        if(!$this->basket->remove($student->id, (int) $itemId)):
            return $this->fail('Basket item not found.', 404);
        endif;

        return $this->ok($this->basket->present($student->id));
    }

    public function clearBasket(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $this->basket->clear($student->id);

        return $this->ok($this->basket->present($student->id));
    }

    /** Everything the Checkout screen shows. Read-only. */
    public function checkout(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        return $this->ok([
            'student' => $this->orders->studentInfo($student),
            'shipping_address' => $this->orders->shippingAddress($student),
            'basket' => $this->basket->present($student->id),
        ]);
    }
}
