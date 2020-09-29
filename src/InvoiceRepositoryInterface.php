<?php

declare(strict_types=1);

interface InvoiceRepositoryInterface
{
    /**
     * @return Invoice[]
     */
    public function findAll(): array;

    /**
     * @param DateTimeInterface $date
     *
     * @return Invoice[]
     */
    public function findAllWithDueDateBefore(\DateTimeInterface $date): array;
}