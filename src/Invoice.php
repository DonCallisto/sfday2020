<?php

declare(strict_types=1);

use Money\Money;

class Invoice
{
    private const STATUS_NEW = 'new';
    private const STATUS_PAID = 'paid';
    private const STATUS_PARTIALLY_PAID = 'partially_paid';

    private Money $amount;

    private \DateTimeInterface $dueDate;

    private Money $paidAmount;

    private string $status;

    private ?bool $interestsCanBeApplied = null;

    public function __construct(Money $amount, \DateTimeInterface $dueDate)
    {
        $this->amount = $amount;
        $this->dueDate = (clone $dueDate)->setTime(0, 0, 0);
        $this->paidAmount = Money::EUR(0);
        $this->status = self::STATUS_NEW;
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function pay(Money $amount): Money
    {
        $this->paidAmount = $this->paidAmount->add($amount);
        if ($this->paidAmount->greaterThanOrEqual($this->amount)) {
            $this->paidAmount = $this->amount;
            $this->status = self::STATUS_PAID;

            return Money::EUR(0);
        }

        $this->status = self::STATUS_PARTIALLY_PAID;

        return $this->amount->subtract($this->paidAmount);
    }

    public function getAmountToPay(): Money
    {
        return $this->amount->subtract($this->paidAmount);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isOverdue(\DateTimeInterface $date): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        $date = (clone $date)
            ->setTime(0, 0, 0);

        return $date > $this->dueDate;
    }

    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    public function interestsCantBeApplied(): void
    {
        $this->interestsCanBeApplied = false;
    }

    public function interestsCanBeApplied(): void
    {
        $this->interestsCanBeApplied = true;
    }

    public function canInterestsBeApplied(): ?bool
    {
        return $this->interestsCanBeApplied;
    }
}
