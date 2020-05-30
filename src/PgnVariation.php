<?php

namespace StasPiv\PgnGame;

/**
 * Class PgnVariation
 *
 * Represents variation in pgn game
 *
 * @package StasPiv\PgnGame
 */
class PgnVariation
{
    /** @var PgnMoves */
    private $moves = [];
    /**
     * @var PgnVariations
     */
    private $variations;

    /**
     * PgnVariation constructor.
     *
     * @param PgnMoves|array $moves
     * @param PgnVariations  $variations
     */
    public function __construct($moves, PgnVariations $variations = null)
    {
        if ($moves instanceof PgnMoves) {
            $moves->setVariation($this);
            $this->moves = $moves;
        } else {
            $this->moves = new PgnMoves($moves, $this);
        }
        $this->variations = $variations;
    }

    /**
     * @param PgnVariations $variations
     *
     * @return PgnVariation
     */
    public function setVariations(PgnVariations $variations): PgnVariation
    {
        $this->variations = $variations;
        return $this;
    }

    /**
     * @return PgnMoves|PgnMove[]
     */
    public function getMoves(): PgnMoves
    {
        return $this->moves;
    }

    /**
     * @return PgnVariations
     */
    public function getVariations(): PgnVariations
    {
        return $this->variations;
    }
}
