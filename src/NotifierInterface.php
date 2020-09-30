<?php

declare(strict_types=1);

interface NotifierInterface
{
    public function notify(\DateTimeInterface $data, Invoice ...$invoices): void;
}
