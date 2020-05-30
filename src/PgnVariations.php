<?php

namespace StasPiv\PgnGame;

use Doctrine\Common\Collections\ArrayCollection;
use StasPiv\PgnGame\Exception\FenNotFound;

/**
 * Class PgnVariations
 *
 * Represents variation collection
 *
 * @package StasPiv\PgnGame
 */
class PgnVariations extends ArrayCollection
{
    /**
     * @var PgnMove
     */
    private $move;

    /**
     * PgnMoves constructor.
     *
     * @param PgnMove $move
     * @param array   $elements
     */
    public function __construct(PgnMove $move, array $elements = [])
    {
        parent::__construct(array_map(
            function ($variation) {
                return new PgnVariation($variation, $this);
            },
            $elements
        ));
        $this->move = $move;
    }

    /**
     * @return PgnMove
     */
    public function getMove(): PgnMove
    {
        return $this->move;
    }

    public function add($element)
    {
        /** @var PgnVariation $element */
        $element->setVariations($this);
        return parent::add($element);
    }

    public function findMoveByFen(PgnFen $fen, PgnVariation &$returnVariation = null): PgnMove
    {
        foreach ($this as $variation) {
            try {
                /** @var PgnVariation $variation */
                $move = $variation->getMoves()->findMoveByFen($fen, $variation);
                $returnVariation = $variation;
                return $move;
            } catch (FenNotFound $exception) {
                continue;
            }
        }

        throw new FenNotFound('fen not found: ' . $fen);
    }
}
