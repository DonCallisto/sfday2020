<?php

declare(strict_types=1);

namespace tests;

use Invoice;
use Money\Money;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    public function test_it_is_new()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime());

        $this->assertTrue($invoice->isNew());
    }

    public function test_it_is_paid()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime());
        $invoice->pay(Money::EUR(500));

        $this->assertTrue($invoice->isPaid());
    }

    public function test_it_wont_pay_more_than_total()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime());
        $change = $invoice->pay(Money::EUR(501));

        $this->assertEquals(Money::EUR(0), $change);
    }

    public function test_it_is_partially_paid()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime());
        $invoice->pay(Money::EUR(499));

        $this->assertTrue($invoice->isPartiallyPaid());
    }

    public function test_it_pays_it_partially()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime());
        $change = $invoice->pay(Money::EUR(499));

        $this->assertEquals(Money::EUR(1), $change);
    }

    public function test_it_is_overdue()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime('2020-09-26'));

        $this->assertTrue($invoice->isOverdue(new \DateTime('2020-09-27')));
    }

    public function test_it_is_not_overdue_if_paid()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime('2020-09-26'));
        $invoice->pay(Money::EUR(500));

        $this->assertFalse($invoice->isOverdue(new \DateTime('2020-09-27')));
    }

    public function test_it_is_overdue_if_partially_paid()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime('2020-09-26'));
        $invoice->pay(Money::EUR(499));

        $this->assertTrue($invoice->isOverdue(new \DateTime('2020-09-27')));
    }

    public function test_it_is_not_overdue_if_requested_on_due_date()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime('2020-09-26'));

        $this->assertFalse($invoice->isOverdue(new \DateTime('2020-09-26')));
    }

    public function test_it_is_not_overdue_if_requested_on_due_date_but_after_due_time()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime('2020-09-26 04:00:00'));

        $this->assertFalse($invoice->isOverdue(new \DateTime('2020-09-26 04:00:01')));
    }

    public function test_it_is_not_overdue_if_requested_before_due_date()
    {
        $invoice = new Invoice(Money::EUR(500), new \DateTime('2020-09-26'));

        $this->assertFalse($invoice->isOverdue(new \DateTime('2020-09-25')));
    }

    public function test_it_returns_due_date()
    {
        $overdueDate = new \DateTime('2020-09-26');
        $invoice = new Invoice(Money::EUR(500), $overdueDate);

        $this->assertEquals($overdueDate, $invoice->getDueDate());
    }
}