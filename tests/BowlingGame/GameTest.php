<?php

namespace Tests\BowlingGame;

use marcusjian\BowlingGame\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private $game;

    public function setUp(): void
    {
        $this->game = new Game();
    }

    public function testShouldScoreMissGame()
    {
        $this->roll(20, 0);
        $this->assertEquals(0, $this->game->score());
    }
    
    public function testShouldScoreGameOfOnes()
    {
        $this->roll(20, 1);
        $this->assertEquals(20, $this->game->score());
    }
    
    // TODO 5,5,3,0... -> 13+3
    public function testShouldScoreGameOfSpare()
    {
        $this->game->roll(5);
        $this->game->roll(5);
        $this->game->roll(3);
        $this->roll(17, 0);

        $this->assertEquals(16, $this->game->score());
    }

    // TODO 10,3,5,0...-> 18+8

//    public function testExample()
//    {
//        $game = new Game();
//        $game->roll(0);
//        $this->assertEquals(0, $game->score());
//    }
    protected function roll(int $times, int $pin): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->game->roll($pin);
        }
    }
}