<?php

declare(strict_types=1);

namespace spec;

use Invoice;
use Money\Money;
use NotifierInterface;
use PhpSpec\ObjectBehavior;
use stub\InvoiceRepositoryDummyInterface;

class OverdueInvoicesCalculatorSpec extends ObjectBehavior
{
    public function it_sums_all_overdue_invoices()
    {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(10);
        $invoice1 = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoice2 = new Invoice(Money::EUR(50), $requestDate);

        $invoice3ToPayAmount = Money::EUR(25);
        $invoice3 = new Invoice($invoice3ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoiceRepo = new InvoiceRepositoryDummyInterface($invoice1, $invoice2, $invoice3);
        $this->beConstructedWith($invoiceRepo);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount->add($invoice3ToPayAmount));
    }

    public function it_applies_ten_percent_interests_if_interests_can_be_applied() {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));
        $invoice->interestsCanBeApplied();

        $invoiceRepo = new InvoiceRepositoryDummyInterface($invoice);
        $this->beConstructedWith($invoiceRepo);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount->add(Money::EUR(10)));
    }

    public function it_does_not_apply_ten_percent_interests_if_interests_cannot_be_applied() {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));
        $invoice->interestsCantBeApplied();

        $invoiceRepo = new InvoiceRepositoryDummyInterface($invoice);
        $this->beConstructedWith($invoiceRepo);

        $this->getAmountDue($requestDate)
            ->shouldBeLike($invoice1ToPayAmount);
    }

    /*
     * I've should've tested also for = 200 and <200 (shouldNotBeCalled).
     * That's left out intentionally
     */
    public function it_notifies_when_overdued_amount_overtakes_two_hundred_euro(
        NotifierInterface $notifier1,
        NotifierInterface $notifier2
    ) {
        $requestDate = new \DateTime();

        $invoice1ToPayAmount = Money::EUR(100);
        $invoice1 = new Invoice($invoice1ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoice2ToPayAmount = Money::EUR(101);
        $invoice2 = new Invoice($invoice2ToPayAmount, (clone $requestDate)->modify('-1 day'));

        $invoiceRepo = new InvoiceRepositoryDummyInterface($invoice1, $invoice2);
        $this->beConstructedWith($invoiceRepo, $notifier1, $notifier2);

        $notifier1->notify($requestDate, $invoice1, $invoice2)
            ->shouldBeCalledTimes(1);
        $notifier2->notify($requestDate, $invoice1, $invoice2)
            ->shouldBeCalledTimes(1);

        $this->getAmountDue($requestDate);
    }
}
