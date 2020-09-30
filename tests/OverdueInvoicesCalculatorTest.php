<?php

declare(strict_types=1);


namespace tests;

use Invoice;
use Money\Money;
use NotifierInterface;
use OverdueInvoicesCalculator;
use PHPUnit\Framework\TestCase;
use stub\InvoiceRepositoryDummyInterface;

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

        $repo = new InvoiceRepositoryDummyInterface($invoice1, $invoice2, $invoice3);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals($invoice1ToPayAmount->add($invoice3ToPayAmount), $calculator->getAmountDue($requestDate));
    }

    public function test_it_applies_ten_percent_interests_if_interests_can_be_applied()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));
        $invoice->interestsCanBeApplied();

        $repo = new InvoiceRepositoryDummyInterface($invoice);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals(
            $invoice1ToPayAmount->add(Money::EUR(10)),
            $calculator->getAmountDue($requestDate)
        );
    }

    public function test_it_does_not_apply_ten_percent_interests_if_interests_cannot_be_applied()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));
        $invoice->interestsCantBeApplied();

        $repo = new InvoiceRepositoryDummyInterface($invoice);

        $calculator = new OverdueInvoicesCalculator($repo);

        $this->assertEquals($invoice1ToPayAmount, $calculator->getAmountDue($requestDate));
    }

    public function test_it_notifies_when_overdued_amount_overtakes_two_hundred_euro() {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice1 = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoice2ToPayAmount = Money::EUR(101);
        $invoice2 = new Invoice($invoice2ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $repo = new InvoiceRepositoryDummyInterface($invoice1, $invoice2);

        $notifier1 = $this->createMock(NotifierInterface::class);
        $notifier1
            ->expects($this->once())
            ->method('notify')
            ->with($requestDate, $invoice1, $invoice2);

        $notifier2 = $this->createMock(NotifierInterface::class);
        $notifier2
            ->expects($this->once())
            ->method('notify')
            ->with($requestDate, $invoice1, $invoice2);

        $calculator = new OverdueInvoicesCalculator($repo, $notifier1, $notifier2);
        $calculator->getAmountDue($requestDate);
    }
}