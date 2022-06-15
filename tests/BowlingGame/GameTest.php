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
        $this->game->bulkRoll(0,0, 0,0, 0,0, 0,0, 0,0, 0,0, 0,0, 0,0, 0,0, 0,0);
        $this->assertEquals(0, $this->game->score());
    }
    
    public function testShouldScoreGameOfOnes()
    {
        $this->game->bulkRoll(1,1, 1,1, 1,1, 1,1, 1,1, 1,1, 1,1, 1,1, 1,1, 1,1);
        $this->assertEquals(20, $this->game->score());
    }
    
    // TODO 5,5,3,0... -> 13+3
    public function testShouldScoreGameOfSpare()
    {
        $this->game->bulkRoll(5,5, 3,0, 0,0, 0,0, 0,0, 0,0, 0,0, 0,0, 0,0, 0,0);
        $this->assertEquals(16, $this->game->score());
    }

    // TODO 10,3,5,0...-> 18+8

//    public function testExample()
//    {
//        $game = new Game();
//        $game->roll(0);
//        $this->assertEquals(0, $game->score());
//    }
}