<?php

declare(strict_types=1);

namespace stub;

use Invoice;
use InvoiceRepositoryInterface;

class InvoiceRepositoryDummyInterface implements InvoiceRepositoryInterface
{
    /**
     * @var Invoice[]
     */
    private array $invoices;

    public function __construct(Invoice ...$invoices)
    {
        $this->invoices = $invoices;
    }

    public function findAll(): array
    {
        return $this->invoices;
    }

    public function findAllWithDueDateBefore(\DateTimeInterface $date): array
    {
        $date = (new \DateTimeImmutable())->setTimestamp($date->getTimestamp())
            ->setTime(0, 0, 0);

        return array_values(
            array_filter($this->invoices, function (Invoice $invoice) use ($date) {
                $invoiceDate = (new \DateTimeImmutable())->setTimestamp($invoice->getDueDate()->getTimestamp())
                    ->setTime(0, 0, 0);

                return $invoiceDate <= $date;
            })
        );
    }
}