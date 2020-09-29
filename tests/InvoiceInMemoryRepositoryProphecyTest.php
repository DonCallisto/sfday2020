<?php

declare(strict_types=1);

namespace tests;

use Invoice;
use InvoiceInMemoryRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class InvoiceInMemoryRepositoryProphecyTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_returns_invoices_with_a_due_not_after_date()
    {
        $requestDate = new \DateTime();

        $invoice1 = $this->prophesize(Invoice::class);
        $invoice1->getDueDate()
            ->willReturn($requestDate);
        $invoice2 = $this->prophesize(Invoice::class);
        $invoice2->getDueDate()
            ->willReturn((clone $requestDate)->modify('+1 day'));
        $invoice3 = $this->prophesize(Invoice::class);
        $invoice3->getDueDate()
            ->willReturn((clone $requestDate)->modify('-1 day'));

        $repo = new InvoiceInMemoryRepository($invoice1->reveal(), $invoice2->reveal(), $invoice3->reveal());

        $this->assertEquals([$invoice1->reveal(), $invoice3->reveal()], $repo->findAllWithDueDateBefore($requestDate));
    }
}