<?php

declare(strict_types=1);

namespace spec;

use Invoice;
use PhpSpec\ObjectBehavior;

class InvoiceInMemoryRepositorySpec extends ObjectBehavior
{
    public function it_returns_invoices_with_a_due_not_after_date(
        Invoice $invoice1,
        Invoice $invoice2,
        Invoice $invoice3
    ) {
        $requestDate = new \DateTime();
        $requestDateImmutable = (new \DateTimeImmutable())
            ->setTimestamp($requestDate->getTimestamp());

        $invoice1->getDueDate()
            ->willReturn($requestDateImmutable);
        $invoice2->getDueDate()
            ->willReturn($requestDateImmutable->modify('+1 day'));
        $invoice3->getDueDate()
            ->willReturn($requestDateImmutable->modify('-1 day'));

        $this->beConstructedWith($invoice1, $invoice2, $invoice3);

        $this->findAllWithDueDateBefore($requestDate)
            ->shouldBeLike([
                $invoice1,
                $invoice3
            ]);
    }
}