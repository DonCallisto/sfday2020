<?php

declare(strict_types=1);

namespace tests;

use Invoice;
use InvoiceInMemoryRepository;
use Money\Money;
use PHPUnit\Framework\TestCase;

class InvoiceInMemoryRepositoryTest extends TestCase
{
    public function test_it_returns_invoices_with_a_due_not_after_date()
    {
        $requestDate = new \DateTime();

        $invoice1 = new Invoice(Money::EUR(0), $requestDate);
        $invoice2 = new Invoice(Money::EUR(0), (clone $requestDate)->modify('+1 day'));
        $invoice3 = new Invoice(Money::EUR(0), (clone $requestDate)->modify('-1 day'));

        $repo = new InvoiceInMemoryRepository($invoice1, $invoice2, $invoice3);

        $this->assertEquals([$invoice1, $invoice3], $repo->findAllWithDueDateBefore($requestDate));
    }
}