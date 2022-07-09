<?php

namespace marcusjian\TicTacToe;

use Exception;

class Game
{
    private array $fields;

    public function __construct()
    {
        $this->fields = array_fill(0, 10, null);
    }

    /**
     * @param string $string X|Y
     * @param int $index 1~9
     *
     * @return bool
     */
    public function take(string $string, int $index): bool
    {
        if (!in_array($string, ['X', 'O'])) {
            throw new Exception('Invalid Player');
        }

        if (null !== $this->fields[$index]) {
            return false;
        }
        $this->fields[$index] = $string;

        return true;
    }

    public function winner(): string
    {
        if ($winner = $this->checkWinner()) {
            return $winner;
        }

        if (9 === count(array_filter($this->fields))) {
            return 'Game Over, no winner!';
        }

        return '';
    }

    protected function checkWinner(): string
    {
        $wins = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
            [1, 4, 7],
            [2, 5, 8],
            [3, 6, 9],
            [1, 5, 9],
            [3, 5, 7],
        ];

        foreach ($wins as $win) {
            if (
                null !== $this->fields[$win[0]] &&
                $this->fields[$win[0]] === $this->fields[$win[1]] &&
                $this->fields[$win[1]] === $this->fields[$win[2]]
            ) {
                return 'Game Over, winner is ' . $this->fields[$win[0]];
            }
        }

        return '';
    }
}
