<?php

namespace Tests\BowlingGame;

use marcusjian\BowlingGame\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    public function testShouldScoreMissGame()
    {
        $game = new Game();
        for ($i = 0; $i < 20; $i++) {
            $game->roll(0);
        }
        $this->assertEquals(0, $game->score());
    }
    
    public function testShouldScoreGameOfOnes()
    {
        $game = new Game();
        for ($i = 0; $i < 20; $i++) {
            $game->roll(1);
        }
        $this->assertEquals(20, $game->score());
    }
    
    // TODO 5,5,3,0... -> 13+3
    // TODO 10,3,5,0...-> 18+8

//    public function testExample()
//    {
//        $game = new Game();
//        $game->roll(0);
//        $this->assertEquals(0, $game->score());
//    }
}