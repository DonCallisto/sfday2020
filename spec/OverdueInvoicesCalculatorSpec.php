<?php

declare(strict_types=1);

namespace spec;

use Invoice;
use InvoiceInMemoryRepository;
use Money\Money;
use PhpSpec\ObjectBehavior;

class OverdueInvoicesCalculatorSpec extends ObjectBehavior
{
    public function let(InvoiceInMemoryRepository $invoiceRepo)
    {
        $this->beConstructedWith($invoiceRepo);
    }

    public function it_sums_all_overdue_invoices(
        InvoiceInMemoryRepository $invoiceRepo,
        Invoice $invoice1,
        Invoice $invoice2,
        Invoice $invoice3
    ) {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(10);
        $invoice1->isOverdue($requestDate)
            ->willReturn(true);
        $invoice1->getDueDate()
            ->willReturn($requestDate);
        $invoice1->canInterestsBeApplied()
            ->willReturn(true);
        $invoice1->getAmountToPay()
            ->willReturn($invoice1ToPayAmount);

        $invoice2->isOverdue($requestDate)
            ->willReturn(false);
        $invoice2->getDueDate()
            ->willReturn($requestDate);
        $invoice2->canInterestsBeApplied()
            ->willReturn(true);
        $invoice2->getAmountToPay()
            ->willReturn(Money::EUR(50));

        $invoice3ToPayAmount = Money::EUR(25);
        $invoice3->isOverdue($requestDate)
            ->willReturn(true);
        $invoice3->getDueDate()
            ->willReturn($requestDate);
        $invoice3->canInterestsBeApplied()
            ->willReturn(true);
        $invoice3->getAmountToPay()
            ->willReturn($invoice3ToPayAmount);

        $invoiceRepo->findAllWithDueDateBefore($requestDate)
            ->willReturn([
                $invoice1,
                $invoice2,
                $invoice3
            ]);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount->add($invoice3ToPayAmount));
    }

    public function it_applies_ten_percent_interests_if_invoice_overdued_by_more_than_seven_days(
        InvoiceInMemoryRepository $invoiceRepo,
        Invoice $invoice
    ) {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice->isOverdue($requestDate)
            ->willReturn(true);
        $invoice->getDueDate()
            ->willReturn((clone $requestDate)->modify('-8 days'));
        $invoice->canInterestsBeApplied()
            ->willReturn(true);
        $invoice->getAmountToPay()
            ->willReturn($invoice1ToPayAmount);

        $invoiceRepo->findAllWithDueDateBefore($requestDate)
            ->willReturn([
                $invoice,
            ]);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount->add(Money::EUR(10)));
    }

    public function it_does_not_apply_ten_percent_interests_if_invoice_overdued_by_more_than_seven_days_but_invoice_with_no_interests(
        InvoiceInMemoryRepository $invoiceRepo,
        Invoice $invoice
    ) {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice->isOverdue($requestDate)
            ->willReturn(true);
        $invoice->getDueDate()
            ->willReturn((clone $requestDate)->modify('-8 days'));
        $invoice->canInterestsBeApplied()
            ->willReturn(false);
        $invoice->getAmountToPay()
            ->willReturn($invoice1ToPayAmount);

        $invoiceRepo->findAllWithDueDateBefore($requestDate)
            ->willReturn([
                $invoice,
            ]);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount);
    }
}
