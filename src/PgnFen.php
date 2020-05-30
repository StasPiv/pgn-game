<?php

namespace StasPiv\PgnGame;

/**
 * Class PgnFen
 *
 * Representates fen inside pgn
 *
 * @package StasPiv\PgnGame
 */
class PgnFen
{
    /**
     * @var string
     */
    private $fen;

    /**
     * PgnFen constructor.
     *
     * @param string $fen
     */
    public function __construct(string $fen)
    {
        $this->fen = $fen;
    }

    /**
     * @return string
     */
    public function getOnlyBoard()
    {
        return explode(' ', $this->fen)[0];
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return explode(' ', $this->fen)[1] === 'w' ? 'white' : 'black';
    }

    /**
     * @param PgnFen $fen
     *
     * @return bool
     */
    public function isSameBoard(PgnFen $fen): bool
    {
        return $fen->getOnlyBoard() === $this->getOnlyBoard();
    }

    public function __toString()
    {
        return $this->fen;
    }
}