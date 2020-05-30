<?php

namespace StasPiv\PgnGame;

use RuntimeException;

/**
 * Class PgnMove
 *
 * represents PgnMove
 *
 * @package StasPiv\PgnGame
 *
 * @method string getM()
 * @method string getFrom()
 * @method string getTo()
 */
class PgnMove
{
    /** @var array */
    private $move;

    /** @var PgnFen */
    private $fen;

    /** @var PgnVariations */
    private $variations;

    /** @var string */
    private $comment;

    /** @var string */
    private $clk;
    /**
     * @var PgnMoves
     */
    private $moves;

    /**
     * PgnMove constructor.
     *
     * @param array    $move
     * @param PgnMoves $moves
     */
    public function __construct(array $move, PgnMoves $moves)
    {
        $this->move = $move;
        $this->fen = new PgnFen($move['fen']);

        if (isset($move['comment'])) {
            $this->comment = $move['comment'];
        }

        if (isset($move['clk'])) {
            $this->clk = $move['clk'];
            $this->comment = $move['clk'] . ' ' . $this->comment;
        }

        $this->variations = new PgnVariations($this, isset($move['variations']) ? $move['variations'] : []);
        $this->moves = $moves;
    }

    /**
     * @param PgnMoves $moves
     *
     * @return PgnMove
     */
    public function setMoves(PgnMoves $moves): PgnMove
    {
        $this->moves = $moves;
        return $this;
    }

    /**
     * @return PgnMoves
     */
    public function getMoves(): PgnMoves
    {
        return $this->moves;
    }

    /**
     * @return PgnVariations|PgnVariation[]
     */
    public function getVariations(): PgnVariations
    {
        return $this->variations;
    }

    /**
     * @return PgnFen
     */
    public function getFen(): PgnFen
    {
        return $this->fen;
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === false) {
            throw new RuntimeException('call undefined method: ' . $name);
        }

        return $this->move[strtolower(substr($name, 3))];
    }

    public function updateVariations()
    {
        $variations = [];

        foreach ($this->getVariations() as $variation) {
            $moves = [];
            /** @var PgnVariation $variation */
            foreach ($variation->getMoves() as $move) {
                /** @var PgnMove $move */
                if (!$move->getVariations()->isEmpty()) {
                    $move->updateVariations();
                }
                $moves[] = $move->getMove();
            }
            $variations[] = $moves;
        }

        $this->move['variations'] = $variations;
    }

    public function setVariations(array $variations)
    {
        $this->move['variations'] = $variations;
        $this->variations = new PgnVariations($this, $variations);

        return $this;
    }

    public function addVariation(PgnVariation $variation)
    {
        $this->variations->add($variation);
        $this->updateVariations();
    }

    public function __toString()
    {
        return $this->getM();
    }

    /**
     * @param array $move
     *
     * @return PgnMove
     */
    public function setMove(array $move): PgnMove
    {
        $this->move = $move;

        return $this;
    }

    /**
     * @return array
     */
    public function getMove(): array
    {
        return $this->move;
    }

    public function matchFen(PgnFen $fen): bool
    {
        return $this->getFen()->isSameBoard($fen);
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->hasComment() ? $this->comment : '';
    }

    public function hasComment(): bool
    {
        return isset($this->comment);
    }

    /**
     * @param string $comment
     *
     * @return PgnMove
     */
    public function setComment(string $comment): PgnMove
    {
        $this->comment = $comment;
        $this->move['comment'] = $comment;
        return $this;
    }
}