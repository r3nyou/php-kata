<?php

namespace Tests\TicTacToe;

use Exception;
use marcusjian\TicTacToe\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    private Game $game;

    public function setUp(): void
    {
        $this->game = new Game();
    }

    public function testShouldHaveTwoPlayerInTheGame()
    {
        $this->assertTrue($this->game->take('X', 1));
        $this->assertTrue($this->game->take('O', 2));
    }

    public function testShouldNotHaveOtherPlayerInTheGame()
    {
        $this->expectException(Exception::class);
        $this->game->take('Z', 1);
    }

    public function testShouldNotTakeFieldIfAlreadyTaken()
    {
        $this->assertTrue($this->game->take('X', 1));
        $this->assertFalse($this->game->take('O', 1));
    }

    public function testGameOverWhenAllFieldsAreTaken()
    {
        $this->game->take('X', 1);
        $this->game->take('O', 2);
        $this->game->take('X', 3);
        $this->game->take('X', 4);
        $this->game->take('O', 5);
        $this->game->take('X', 6);
        $this->game->take('O', 7);
        $this->game->take('X', 8);
        $this->game->take('O', 9);

        $this->assertEquals(
            'Game Over, no winner!',
            $this->game->winner()
        );
    }

    /**
     * @testWith [{"player": "X", "fields": [1, 2, 3]}]
     *           [{"player": "O", "fields": [4, 5, 6]}]
     *           [{"player": "X", "fields": [7, 8, 9]}]
     */
    public function testShouldWinWhenAllFieldsInARowAreTaken(array $data)
    {
        foreach ($data['fields'] as $field) {
            $this->game->take($data['player'], $field);
        }

        $this->assertEquals(
            'Game Over, winner is ' . $data['player'],
            $this->game->winner()
        );
    }

    /**
     * @testWith [{"player": "X", "fields": [1, 4, 7]}]
     *           [{"player": "O", "fields": [2, 5, 8]}]
     *           [{"player": "X", "fields": [3, 6, 9]}]
     */
    public function testShouldWinWhenAllFieldsInAColumnAreTaken(array $data)
    {
        foreach ($data['fields'] as $field) {
            $this->game->take($data['player'], $field);
        }

        $this->assertEquals(
            'Game Over, winner is ' . $data['player'],
            $this->game->winner()
        );
    }
    /**
     * @testWith [{"player": "X", "fields": [1, 5, 9]}]
     *           [{"player": "O", "fields": [3, 5, 7]}]
     */
    public function testShouldWinWhenAllFieldsInDiagonalAreTaken(array $data)
    {
        foreach ($data['fields'] as $field) {
            $this->game->take($data['player'], $field);
        }

        $this->assertEquals(
            'Game Over, winner is ' . $data['player'],
            $this->game->winner()
        );
    }
}
