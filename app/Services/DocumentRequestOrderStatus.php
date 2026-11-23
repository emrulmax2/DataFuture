<?php

namespace App\Services;

use App\Models\StudentDocumentRequestForm;
use App\Models\StudentOrder;
use App\Models\StudentTask;

/**
 * A student portal order follows its document-request tasks (Task Manager "Document Requests
 * from Student portal", user 23 Nov 2026): once none of the order's tasks is still open
 * (Pending / In Progress) and at least one was completed, the order shows Completed.
 *
 * - Every request form on the order must have a task; an order with a form still waiting
 *   for one is left alone.
 * - Cancelled tasks don't hold an order up, but an order whose every task was cancelled is
 *   left to the existing "Rejected" rule (updateStudentDocumentRequst).
 * - Nothing is moved backwards: a Completed order stays Completed.
 */
class DocumentRequestOrderStatus
{
    const OPEN = ['Pending', 'In Progress'];

    /**
     * The orders behind these student tasks, completed where the rule allows.
     *
     * @param  int[]  $studentTaskIds
     * @return int[]  the order ids moved to Completed
     */
    public static function syncForTasks(array $studentTaskIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $studentTaskIds))));
        if (empty($ids)) {
            return [];
        }

        $formIds = StudentTask::whereIn('id', $ids)->whereNotNull('student_document_request_form_id')
            ->pluck('student_document_request_form_id')->unique();
        if ($formIds->isEmpty()) {
            return [];
        }

        $orderIds = StudentDocumentRequestForm::whereIn('id', $formIds)->whereNotNull('student_order_id')
            ->pluck('student_order_id')->unique();

        $completed = [];
        foreach ($orderIds as $orderId) {
            if (self::syncOrder((int) $orderId)) {
                $completed[] = (int) $orderId;
            }
        }

        return $completed;
    }

    /** True when the order is due to be Completed (read-only). */
    public static function isDue(int $orderId): bool
    {
        $order = StudentOrder::find($orderId);
        if (!$order || $order->status === 'Completed') {
            return false;
        }

        $formIds = StudentDocumentRequestForm::where('student_order_id', $orderId)->pluck('id');
        if ($formIds->isEmpty()) {
            return false;
        }

        $tasks = StudentTask::whereIn('student_document_request_form_id', $formIds)->get(['student_document_request_form_id', 'status']);
        if ($tasks->pluck('student_document_request_form_id')->unique()->count() < $formIds->count()) {
            return false;
        }
        if ($tasks->contains(fn ($task) => in_array($task->status, self::OPEN, true))) {
            return false;
        }

        return $tasks->contains('status', 'Completed');
    }

    /** Marks the order Completed when it is due; true when it changed. */
    public static function syncOrder(int $orderId): bool
    {
        if (!self::isDue($orderId)) {
            return false;
        }

        return StudentOrder::where('id', $orderId)->where('status', '!=', 'Completed')
            ->update(['status' => 'Completed']) > 0;
    }
}
