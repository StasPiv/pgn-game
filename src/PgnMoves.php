<?php

namespace StasPiv\PgnGame;

use Doctrine\Common\Collections\ArrayCollection;
use StasPiv\PgnGame\Exception\FenNotFound;

/**
 * Class PgnMoves
 *
 * Represents PgnMoves
 *
 * @package StasPiv\PgnGame
 */
class PgnMoves extends ArrayCollection
{
    /**
     * @var PgnVariation
     */
    private $variation;

    /**
     * PgnMoves constructor.
     *
     * @param array        $elements
     * @param PgnVariation $variation
     */
    public function __construct(array $elements = [], PgnVariation $variation = null)
    {
        parent::__construct(array_map(
            function ($move) {
                return is_array($move) ? new PgnMove($move, $this) : $move;
            },
            $elements
        ));
        $this->variation = $variation;
    }

    /**
     * @param PgnVariation $variation
     *
     * @return PgnMoves
     */
    public function setVariation(PgnVariation $variation): PgnMoves
    {
        $this->variation = $variation;
        return $this;
    }

    public function hasVariation(): bool
    {
        return isset($this->variation);
    }

    /**
     * @return PgnVariation
     */
    public function getVariation(): PgnVariation
    {
        return $this->variation;
    }

    public function findMoveByFen(PgnFen $fen, PgnVariation &$variation = null)
    {
        $matchedMoves = new PgnMoves();
        foreach ($this as $move) {
            /** @var PgnMove $move */
            if ($move->matchFen($fen)) {
                $matchedMoves->add($move);
            }

            try {
                return $move->getVariations()->findMoveByFen($fen, $variation);
            } catch (FenNotFound $exception) {
                continue 1;
            }
        }

        if ($matchedMoves->isEmpty()) {
            throw new FenNotFound('fen not found: ' . $fen);
        }

        return $matchedMoves->last();
    }

    /**
     * @return array
     */
    public function extractMoveArrays(): array
    {
        $moves = [];

        foreach ($this as $pgnMove) {
            /** @var PgnMove $pgnMove */
            $pgnMove->updateVariations();
            $moves[] = $pgnMove->getMove();
        }

        return $moves;
    }

    public function getFenArray(PgnMoves $pgnMoves = null): array
    {
        $fenArray = [];

        if (is_null($pgnMoves)) {
            $pgnMoves = $this;
        }

        foreach ($pgnMoves as $pgnMove) {
            /** @var PgnMove $pgnMove */
            $index = $pgnMove->getM();

            if ($pgnMove->getMoves()->hasVariation()) {
                $index = $pgnMove->getMoves()->getVariation()->getVariations()->getMove()->getM() . ' ' . $index;
            }

            $fenArray[$index] = (string)$pgnMove->getFen();
            foreach ($pgnMove->getVariations() as $pgnVariation) {
                $fenArray = array_merge($fenArray, $this->getFenArray($pgnVariation->getMoves()));
            }
        }

        return $fenArray;
    }
}
