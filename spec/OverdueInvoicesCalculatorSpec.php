<?php

declare(strict_types=1);

namespace spec;

use Invoice;
use InvoiceInMemoryRepository;
use Money\Money;
use PhpSpec\ObjectBehavior;

class OverdueInvoicesCalculatorSpec extends ObjectBehavior
{
    public function let(InvoiceInMemoryRepository $invoiceRepo)
    {
        $this->beConstructedWith($invoiceRepo);
    }

    public function it_sums_all_overdue_invoices(InvoiceInMemoryRepository $invoiceRepo)
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(10);
        $invoice1 = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoice2 = new Invoice(Money::EUR(50), $requestDate);

        $invoice3ToPayAmount = Money::EUR(25);
        $invoice3 = new Invoice($invoice3ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoiceRepo->findAll()
            ->willReturn([
                $invoice1,
                $invoice2,
                $invoice3
            ]);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount->add($invoice3ToPayAmount));
    }

    public function it_applies_ten_percent_interests_if_invoice_overdued_by_more_than_seven_days(
        InvoiceInMemoryRepository $invoiceRepo
    ) {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-8 days'));

        $invoiceRepo->findAll()
            ->willReturn([
                $invoice,
            ]);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount->add(Money::EUR(10)));
    }
}
