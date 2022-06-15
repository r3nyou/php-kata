<?php

namespace marcusjian\BowlingGame;

class Game
{
    private $score = 0;

    public function roll(int $pin): void
    {
        $this->score += $pin;
    }

    public function score(): int
    {
        return $this->score;
    }
}