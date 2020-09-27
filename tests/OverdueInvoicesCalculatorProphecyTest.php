<?php

declare(strict_types=1);


namespace tests;

use Invoice;
use InvoiceInMemoryRepository;
use Money\Money;
use OverdueInvoicesCalculator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class OverdueInvoicesCalculatorProphecyTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_sums_all_overdue_invoices()
    {
        $requestDate = new \DateTime();

        $invoice1 = $this->prophesize(Invoice::class);
        $invoice1ToPayAmount = Money::EUR(10);
        $invoice1->isOverdue($requestDate)
            ->willReturn(true);
        $invoice1->getAmountToPay()
            ->willReturn($invoice1ToPayAmount);

        $invoice2 = $this->prophesize(Invoice::class);
        $invoice2->isOverdue($requestDate)
            ->willReturn(false);
        $invoice2->getAmountToPay()
            ->willReturn(Money::EUR(50));

        $invoice3 = $this->prophesize(Invoice::class);
        $invoice3ToPayAmount = Money::EUR(25);
        $invoice3->isOverdue($requestDate)
            ->willReturn(true);
        $invoice3->getAmountToPay()
            ->willReturn($invoice3ToPayAmount);

        $repo = $this->prophesize(InvoiceInMemoryRepository::class);
        $repo->findAll()
            ->willReturn([$invoice1, $invoice2, $invoice3]);

        $calculator = new OverdueInvoicesCalculator($repo->reveal());

        $this->assertEquals($invoice1ToPayAmount->add($invoice3ToPayAmount), $calculator->getAmountDue($requestDate));
    }
}