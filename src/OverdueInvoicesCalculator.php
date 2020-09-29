<?php

declare(strict_types=1);

use Money\Money;

class OverdueInvoicesCalculator
{
    private const INTEREST_PERCENTAGE = 10;

    private InvoiceRepositoryInterface $invoiceRepo;

    public function __construct(InvoiceRepositoryInterface $invoiceRepo)
    {
        $this->invoiceRepo = $invoiceRepo;
    }

    public function getAmountDue(\DateTimeInterface $date): Money
    {
        $invoices = $this->invoiceRepo->findAllWithDueDateBefore($date);
        $overdueInvoices = array_filter($invoices, function (Invoice $invoice) use ($date) {
            return $invoice->isOverdue($date);
        });

        return array_reduce($overdueInvoices, function (Money $amountDue, Invoice $invoice) use ($date) {
            $amountToPay = $invoice->getAmountToPay();
            
            if (!$invoice->canInterestsBeApplied()) {
                return $amountDue->add($amountToPay);
            }

            $amountToPayWithInterests = (int) ($amountToPay->getAmount() / 100) * self::INTEREST_PERCENTAGE;

            return $amountDue->add($amountToPay, Money::EUR($amountToPayWithInterests));

        }, Money::EUR(0));
    }
}
