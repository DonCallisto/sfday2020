<?php

declare(strict_types=1);

class InvoiceInMemoryRepository
{
    /**
     * @var Invoice[]
     */
    private array $invoices;

    public function __construct(Invoice ...$invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * @return Invoice[]
     */
    public function findAll(): array
    {
        return $this->invoices;
    }

    /**
     * @param DateTimeInterface $date
     *
     * @return Invoice[]
     */
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