<?php

namespace marcusjian\MultiCurrency;

class Pair
{
    private $from;

    private $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function __toString(): string
    {
        return $this->from . '-' . $this->to;
    }
}