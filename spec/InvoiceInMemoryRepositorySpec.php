<?php

declare(strict_types=1);

namespace spec;

use Invoice;
use Money\Money;
use PhpSpec\ObjectBehavior;

class InvoiceInMemoryRepositorySpec extends ObjectBehavior
{
    public function it_returns_invoices_with_a_due_not_after_date()
    {
        $requestDate = new \DateTime();

        $invoice1 = new Invoice(Money::EUR(0), $requestDate);
        $invoice2 = new Invoice(Money::EUR(0), (clone $requestDate)->modify('+1 day'));
        $invoice3 = new Invoice(Money::EUR(0), (clone $requestDate)->modify('-1 day'));

        $this->beConstructedWith($invoice1, $invoice2, $invoice3);

        $this->findAllWithDueDateBefore($requestDate)
            ->shouldBeLike([
                $invoice1,
                $invoice3
            ]);
    }
}