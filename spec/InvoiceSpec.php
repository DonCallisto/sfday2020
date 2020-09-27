<?php

declare(strict_types=1);

namespace spec;

use Money\Money;
use PhpSpec\ObjectBehavior;

class InvoiceSpec extends ObjectBehavior
{
    public function it_is_new()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime());

        $this->isNew()
            ->shouldBe(true);
    }

    public function it_is_paid()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime());

        $this->pay(Money::EUR(500));

        $this->isPaid()
            ->shouldBe(true);
    }

    public function it_wont_pay_more_than_total()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime());

        $change = $this->pay(Money::EUR(501));

        $change->shouldBeLike(Money::EUR(0));
    }

    public function it_is_partially_paid()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime());

        $this->pay(Money::EUR(499));

        $this->isPartiallyPaid()
            ->shouldBe(true);
    }

    public function it_pays_it_partially()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime());

        $this->pay(Money::EUR(499));

        $this->getAmountToPay()
            ->shouldBeLike(Money::EUR(1));
    }

    public function it_is_overdue()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime('2020-09-26'));

        $this->isOverdue(new \DateTime('2020-09-27'))
            ->shouldBe(true);
    }

    public function it_is_not_overdue_if_paid()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime('2020-09-26'));
        $this->pay(Money::EUR(500));

        $this->isOverdue(new \DateTime('2020-09-27'))
            ->shouldBe(false);
    }

    public function it_is_overdue_if_partially_paid()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime('2020-09-26'));
        $this->pay(Money::EUR(499));

        $this->isOverdue(new \DateTime('2020-09-27'))
            ->shouldBe(true);
    }

    public function it_is_not_overdue_if_requested_on_due_date()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime('2020-09-26'));

        $this->isOverdue(new \DateTime('2020-09-26'))
            ->shouldBe(false);
    }

    public function it_is_not_overdue_if_requested_on_due_date_but_after_due_time()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime('2020-09-26 04:00:00'));

        $this->isOverdue(new \DateTime('2020-09-26 04:00:01'))
            ->shouldBe(false);
    }

    public function it_is_not_overdue_if_requested_before_due_date()
    {
        $this->beConstructedWith(Money::EUR(500), new \DateTime('2020-09-26'));

        $this->isOverdue(new \DateTime('2020-09-25'))
            ->shouldBe(false);
    }

    public function it_returns_due_date()
    {
        $overdueDate = new \DateTime('2020-09-26');
        $this->beConstructedWith(Money::EUR(500), $overdueDate);

        $this->getDueDate()
            ->shouldBeLike($overdueDate);
    }
}
