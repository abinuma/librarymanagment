<?php
/**
 * FineService - Business logic for fine calculation
 */

class FineService
{
    private Fine $fineModel;

    public function __construct()
    {
        $this->fineModel = new Fine();
    }

    public function calculateFine(int $overdueDays): float
    {
        $finePerDay = (float) setting('fine_per_day', 1.00);
        return $overdueDays * $finePerDay;
    }

    public function createFine(int $transactionId, int $memberId, float $amount, string $reason = 'Overdue book'): int
    {
        // Check if fine already exists for this transaction
        $existing = $this->fineModel->findByTransaction($transactionId);
        if ($existing) {
            $this->fineModel->markPaid($existing['id']);
            return $existing['id'];
        }

        return $this->fineModel->create([
            'transaction_id' => $transactionId,
            'member_id'      => $memberId,
            'amount'         => $amount,
            'reason'         => $reason,
            'is_paid'        => 1,
        ]);
    }
}
