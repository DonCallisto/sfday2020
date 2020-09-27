<?php

declare(strict_types=1);

use Money\Money;

class OverdueInvoicesCalculator
{
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

        return array_reduce($overdueInvoices, function (Money $amountDue, Invoice $invoice) {
            return $amountDue->add($invoice->getAmountToPay());
        }, Money::EUR(0));
    }
}
