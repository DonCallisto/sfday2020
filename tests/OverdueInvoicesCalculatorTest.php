<?php

declare(strict_types=1);


namespace tests;

use Invoice;
use InvoiceInMemoryRepository;
use Money\Money;
use OverdueInvoicesCalculator;
use PHPUnit\Framework\TestCase;

class OverdueInvoicesCalculatorTest extends TestCase
{
    public function test_it_sums_all_overdue_invoices()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(10);
        $invoice1 = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoice2 = new Invoice(Money::EUR(50), $requestDate);

        $invoice3ToPayAmount = Money::EUR(25);
        $invoice3 = new Invoice($invoice3ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $repo = $this->createStub(InvoiceInMemoryRepository::class);
        $repo->method('findAll')
            ->willReturn([$invoice1, $invoice2, $invoice3]);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals($invoice1ToPayAmount->add($invoice3ToPayAmount), $calculator->getAmountDue($requestDate));
    }

    public function test_it_applies_ten_percent_interests_if_invoice_overdued_by_more_than_seven_days()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-8 days'));

        $repo = $this->createStub(InvoiceInMemoryRepository::class);
        $repo->method('findAll')
            ->willReturn([
                $invoice,
            ]);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals(
            $invoice1ToPayAmount->add(Money::EUR(10)),
            $calculator->getAmountDue($requestDate)
        );
    }
}