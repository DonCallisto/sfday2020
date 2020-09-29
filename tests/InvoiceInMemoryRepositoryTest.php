<?php

declare(strict_types=1);

namespace tests;

use Invoice;
use InvoiceInMemoryRepository;
use PHPUnit\Framework\TestCase;

class InvoiceInMemoryRepositoryTest extends TestCase
{
    public function test_it_returns_invoices_with_a_due_not_after_date()
    {
        $requestDate = new \DateTime();

        $invoice1 = $this->createStub(Invoice::class);
        $invoice1->method('getDueDate')
            ->willReturn($requestDate);
        $invoice2 = $this->createStub(Invoice::class);
        $invoice2->method('getDueDate')
            ->willReturn((clone $requestDate)->modify('+1 day'));
        $invoice3 = $this->createStub(Invoice::class);
        $invoice3->method('getDueDate')
            ->willReturn((clone $requestDate)->modify('-1 day'));

        $repo = new InvoiceInMemoryRepository($invoice1, $invoice2, $invoice3);

        $this->assertEquals([$invoice1, $invoice3], $repo->findAllWithDueDateBefore($requestDate));
    }
}