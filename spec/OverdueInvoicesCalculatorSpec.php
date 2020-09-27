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
}
