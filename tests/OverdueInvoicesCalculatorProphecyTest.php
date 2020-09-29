<?php

declare(strict_types=1);


namespace tests;

use Invoice;
use InvoiceRepositoryInterface;
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
        $requestDataImmutable = (new \DateTimeImmutable())
            ->setTimestamp($requestDate->getTimestamp());

        $invoice1 = $this->prophesize(Invoice::class);
        $invoice1ToPayAmount = Money::EUR(10);
        $invoice1->isOverdue($requestDate)
            ->willReturn(true);
        $invoice1->getDueDate()
            ->willReturn($requestDataImmutable); // DEAD CODE!
        $invoice1->canInterestsBeApplied()
            ->willReturn(true);
        $invoice1->getAmountToPay()
            ->willReturn($invoice1ToPayAmount);

        $invoice2 = $this->prophesize(Invoice::class);
        $invoice2->isOverdue($requestDate)
            ->willReturn(false);
        $invoice2->getDueDate()
            ->willReturn($requestDataImmutable); // DEAD CODE!
        $invoice2->canInterestsBeApplied()
            ->willReturn(true);
        $invoice2->getAmountToPay()
            ->willReturn(Money::EUR(50));

        $invoice3 = $this->prophesize(Invoice::class);
        $invoice3ToPayAmount = Money::EUR(25);
        $invoice3->isOverdue($requestDate)
            ->willReturn(true);
        $invoice3->getDueDate()
            ->willReturn($requestDataImmutable); // DEAD CODE!
        $invoice3->canInterestsBeApplied()
            ->willReturn(true);
        $invoice3->getAmountToPay()
            ->willReturn($invoice3ToPayAmount);

        $repo = $this->prophesize(InvoiceRepositoryInterface::class);
        $repo->findAllWithDueDateBefore($requestDate)
            ->willReturn([$invoice1, $invoice2, $invoice3]);

        $calculator = new OverdueInvoicesCalculator($repo->reveal());

        $this->assertEquals($invoice1ToPayAmount->add($invoice3ToPayAmount), $calculator->getAmountDue($requestDate));
    }

    public function test_it_applies_ten_percent_interests_if_interests_can_be_applied()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = $this->prophesize(Invoice::class);
        $invoice->isOverdue($requestDate)
            ->willReturn(true);
        $invoice->canInterestsBeApplied()
            ->willReturn(true);
        $invoice->getAmountToPay()
            ->willReturn($invoice1ToPayAmount);

        $repo = $this->prophesize(InvoiceRepositoryInterface::class);
        $repo->findAllWithDueDateBefore($requestDate)
            ->willReturn([
                $invoice,
            ]);

        $calculator = new OverdueInvoicesCalculator($repo->reveal());

        $this->assertEquals(
            $invoice1ToPayAmount->add(Money::EUR(10)),
            $calculator->getAmountDue($requestDate)
        );
    }

    public function test_it_does_not_apply_ten_percent_interests_if_interests_cannot_be_applied()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = $this->prophesize(Invoice::class);
        $invoice->isOverdue($requestDate)
            ->willReturn(true);
        $invoice->canInterestsBeApplied()
            ->willReturn(false);
        $invoice->getAmountToPay()
            ->willReturn($invoice1ToPayAmount);

        $repo = $this->prophesize(InvoiceRepositoryInterface::class);
        $repo->findAllWithDueDateBefore($requestDate)
            ->willReturn([
                $invoice,
            ]);

        $calculator = new OverdueInvoicesCalculator($repo->reveal());

        $this->assertEquals($invoice1ToPayAmount, $calculator->getAmountDue($requestDate));
    }
}