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

        $invoice1 = $this->createMock(Invoice::class);
        $invoice1ToPayAmount = Money::EUR(10);
        $invoice1->method('isOverdue')
            ->with($requestDate)
            ->willReturn(true);
        $invoice1->method('getDueDate')
            ->willReturn($requestDate);
        $invoice1->method('getAmountToPay')
            ->willReturn($invoice1ToPayAmount);

        $invoice2 = $this->createStub(Invoice::class);
        $invoice2->method('isOverdue')
            ->with($requestDate)
            ->willReturn(false);
        $invoice2->method('getDueDate')
            ->willReturn($requestDate);
        $invoice2->method('getAmountToPay')
            ->willReturn(Money::EUR(50));

        $invoice3 = $this->createStub(Invoice::class);
        $invoice3ToPayAmount = Money::EUR(25);
        $invoice3->method('isOverdue')
            ->with($requestDate)
            ->willReturn(true);
        $invoice3->method('getDueDate')
            ->willReturn($requestDate);
        $invoice3->method('getAmountToPay')
            ->willReturn($invoice3ToPayAmount);

        $repo = $this->createMock(InvoiceInMemoryRepository::class);
        $repo->method('findAllWithDueDateBefore')
            ->with($requestDate)
            ->willReturn([$invoice1, $invoice2, $invoice3]);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals($invoice1ToPayAmount->add($invoice3ToPayAmount), $calculator->getAmountDue($requestDate));
    }

    public function test_it_applies_ten_percent_interests_if_invoice_overdued_by_more_than_seven_days()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = $this->createMock(Invoice::class);
        $invoice->method('isOverdue')
            ->with($requestDate)
            ->willReturn(true);
        $invoice->method('getDueDate')
            ->willReturn((clone $requestDate)->modify('-8 days'));
        $invoice->method('getAmountToPay')
            ->willReturn($invoice1ToPayAmount);

        $repo = $this->createMock(InvoiceInMemoryRepository::class);
        $repo->method('findAllWithDueDateBefore')
            ->with($requestDate)
            ->willReturn([
                $invoice,
            ]);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals(
            $invoice1ToPayAmount->add(Money::EUR(10)),
            $calculator->getAmountDue($requestDate)
        );
    }

    public function test_it_does_not_apply_ten_percent_interests_if_invoice_overdued_by_more_than_seven_days_but_invoice_with_no_interests()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = $this->createMock(Invoice::class);
        $invoice->method('isOverdue')
            ->with($requestDate)
            ->willReturn(true);
        $invoice->method('getDueDate')
            ->willReturn((clone $requestDate)->modify('-8 days'));
        $invoice->method('canInterestsBeApplied')
            ->willReturn(false);
        $invoice->method('getAmountToPay')
            ->willReturn($invoice1ToPayAmount);

        $repo = $this->createMock(InvoiceInMemoryRepository::class);
        $repo->method('findAllWithDueDateBefore')
            ->with($requestDate)
            ->willReturn([
                $invoice,
            ]);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals($invoice1ToPayAmount, $calculator->getAmountDue($requestDate));
    }
}