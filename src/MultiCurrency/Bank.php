<?php

namespace marcusjian\MultiCurrency;

class Bank
{
    private $rates = [];

    public function reduce(Expression $source, string $to): Money
    {
        return $source->reduce($this, $to);
    }

    public function addRate(string $from, string $to, int $rate)
    {
        $this->rates[(string) new Pair($from, $to)] = $rate;
    }

    public function rate(string $from, string $to)
    {
        if ($from === $to) {
            return 1;
        }
        return $this->rates[(string) new Pair($from, $to)];
    }
}