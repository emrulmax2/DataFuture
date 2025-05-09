<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\StudentShoppingCart;
use App\Models\Student;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveShoppingCartSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated as a student
        if (auth('student')->check()) {
            $studentUserId = auth('student')->user()->id;

            // Retrieve the student and their shopping cart
            $student = Student::where('student_user_id', $studentUserId)->first();
            if ($student) {
                $shoppingCart = StudentShoppingCart::with(['student','letterSet'])->where('student_id', $student->id)->get();

                // Remove expired items from the cart
                foreach ($shoppingCart as $item) {
                    if ($item->expire_at < now()) {
                        $item->delete();
                    }
                }

                // Save the shopping cart to the session
                session(['shopping_cart' => $shoppingCart]);
            }
        }

        return $next($request);
    }
}