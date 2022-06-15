<?php

namespace marcusjian\BowlingGame;

class Game
{
    private int $roll = 0;

    private array $rolls = [];

    public function bulkRoll(int ...$pins): void
    {
        foreach ($pins as $pin) {
            $this->roll($pin);
        }
    }

    public function roll(int $pin): void
    {
        $this->rolls[] = $pin;
        $this->roll++;
    }

    public function score(): int
    {
        $score = 0;
        $cursor = 0;
        for ($frame = 0; $frame < 10; $frame++) {
            if ($this->isSpare($cursor)) {
                $score += (10 + $this->rolls[$cursor+2]);
                $cursor += 2;
            } else {
                $score += $this->rolls[$cursor] + $this->rolls[$cursor+1];
                $cursor += 2;
            }
        }
        return $score;
    }

    protected function isSpare(int $cursor): bool
    {
        return 10 === $this->rolls[$cursor] + $this->rolls[$cursor + 1];
    }
}