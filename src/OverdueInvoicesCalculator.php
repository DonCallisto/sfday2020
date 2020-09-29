<?php

declare(strict_types=1);

use Money\Money;

class OverdueInvoicesCalculator
{
    private const INTEREST_APPLIES_AFTER_DAYS = 7;
    private const INTEREST_PERCENTAGE = 10;

    private InvoiceInMemoryRepository $invoiceRepo;

    public function __construct(InvoiceInMemoryRepository $invoiceRepo)
    {
        $this->invoiceRepo = $invoiceRepo;
    }

    public function getAmountDue(\DateTimeInterface $date): Money
    {
        $invoices = $this->invoiceRepo->findAll();
        $overdueInvoices = array_filter($invoices, function (Invoice $invoice) use ($date) {
            return $invoice->isOverdue($date);
        });

        return array_reduce($overdueInvoices, function (Money $amountDue, Invoice $invoice) use ($date) {
            $amountToPay = $invoice->getAmountToPay();
            
            if (!$this->haveToApplyInterests($invoice, $date)) {
                return $amountDue->add($amountToPay);
            }

            $amountToPayWithInterests = ($amountToPay->getAmount() / 100) * self::INTEREST_PERCENTAGE;

            return $amountDue->add($amountToPay, Money::EUR($amountToPayWithInterests));

        }, Money::EUR(0));
    }

    private function haveToApplyInterests(Invoice $invoice, \DateTimeInterface $date): bool
    {
        if ($invoice->getDueDate() >= $date) {
            return false;
        }

        return $invoice->getDueDate()->diff($date)->days >= self::INTEREST_APPLIES_AFTER_DAYS;
    }
}
