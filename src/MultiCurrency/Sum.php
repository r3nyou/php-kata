<?php

namespace marcusjian\MultiCurrency;

class Sum implements Expression
{
    public Expression $augend;

    public Expression $addend;

    public function __construct(Expression $augend, Expression $addend)
    {
        $this->augend = $augend;
        $this->addend = $addend;
    }

    public function reduce(Bank $bank, string $to): Money
    {
        $amount = $this->augend->reduce($bank, $to)->amount +
            $this->addend->reduce($bank, $to)->amount;
        return new Money($amount, $to);
    }

    public function plus(Expression $addend): Expression
    {
        return new Sum($this, $addend);
    }

    public function times(int $multiplier): Expression
    {
        return new Sum(
            $this->augend->times($multiplier),
            $this->addend->times($multiplier)
        );
    }
}