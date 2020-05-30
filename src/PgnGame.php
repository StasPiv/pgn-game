<?php

namespace StasPiv\PgnGame;

use Exception;
use JsonToPgnParser;
use RuntimeException;
use StasPiv\PgnGame\Exception\FenNotFound;
use StasPiv\PgnGame\Exception\LastMoveInVariation;
use TypeError;

/**
 * Class PgnGame
 *
 * Represents pgn game
 *
 * @package StasPiv\PgnGame
 *
 * @method string getWhite()
 * @method string getBlack()
 * @method string getDate()
 * @method string getEvent()
 * @method string getResult()
 * @method int getRound()
 *
 */
class PgnGame
{
    /** @var array */
    private $pgnGame = [];

    /** @var PgnMoves */
    private $moves = [];

    /** @var PgnFen */
    private $fen;
    /**
     * @var string
     */
    private $pgnContent;

    /** @var string */
    private $header;

    /**
     * PgnGame constructor.
     *
     * @param array  $pgnGame
     * @param string $pgnContent
     */
    public function __construct(array $pgnGame, string $pgnContent)
    {
        $this->pgnGame = $pgnGame;
        $this->moves = new PgnMoves($pgnGame['moves']);
        $this->fen = new PgnFen($pgnGame['fen']);
        $this->pgnContent = $pgnContent;
        $this->header = implode(PHP_EOL, $this->getHeaderByContent($pgnContent));
    }

    /**
     * @return array
     */
    public function getPgnGame(): array
    {
        return $this->pgnGame;
    }

    /**
     * @param string $pgnContent
     *
     * @return PgnGame
     */
    public function setPgnContent(string $pgnContent): PgnGame
    {
        $this->pgnContent = $pgnContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @return PgnFen
     */
    public function getFen(): PgnFen
    {
        return $this->fen;
    }

    /**
     * @return string
     */
    public function getPgnContent(): string
    {
        return $this->pgnContent;
    }

    public function __call($name, $arguments)
    {
        if (!strpos($name, 'get') === 0) {
            throw new RuntimeException('call undefined method: ' . $name);
        }

        $index = strtolower(substr($name, 3));

        if (!isset($this->pgnGame[$index])) {
            return '';
        }

        return $this->pgnGame[$index];
    }

    public function updateMoves()
    {
        foreach ($this->getMoves() as $pgnMove) {
            $pgnMove->updateVariations();
        }

        $this->pgnGame['moves'] = $this->getMoves()->extractMoveArrays();
        $this->updatePgnGameByContent();
        $this->updatePgnContent();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fen'   => $this->getFen(),
            'moves' => $this->getMoves()->extractMoveArrays(),
        ];
    }

    /**
     * @return PgnMoves|PgnMove[]
     */
    public function getMoves(): PgnMoves
    {
        return $this->moves;
    }

    public function __toString()
    {
        return addslashes(implode(
            '-',
            [
                $this->pgnGame['white'],
                $this->pgnGame['black'],
                $this->pgnGame['date'],
                $this->pgnGame['event'],
                $this->pgnGame['round']
            ]
        ));
    }

    /**
     * @param PgnFen $fen
     *
     * @return int
     *
     * @throws RuntimeException
     */
    public function findMoveIndexByFen(PgnFen $fen): int
    {
        if ($this->getFen()->isSameBoard($fen)) {
            return -1;
        }

        return $this->getMoves()->indexOf(
            $this->findMoveByFen($fen)
        );
    }

    /**
     * @param PgnFen            $fen
     * @param PgnVariation|null $variation
     *
     * @return PgnMove
     *
     * @throws RuntimeException
     */
    public function findMoveByFen(PgnFen $fen, PgnVariation &$variation = null): PgnMove
    {
        if (!$this->fenExists($fen)) {
            throw new FenNotFound('fen not found: ' . $fen);
        }

        return $this->getMoves()->findMoveByFen($fen, $variation);
    }

    /**
     * @param PgnFen $fen
     *
     * @return PgnMove
     *
     * @throws RuntimeException
     */
    public function findNextMoveByFen(PgnFen $fen): PgnMove
    {
        if ($this->getFen()->isSameBoard($fen)) {
            return $this->getMoves()->first();
        }

        /** @var PgnMove $currentMove */
        $currentMove = $this->findMoveByFen($fen);

        $moves = $currentMove->getMoves();
        if ($currentMove === $moves->last()) {
            throw new LastMoveInVariation('This is last move in variation or last move in the game');
        }

        $nextMove = $moves[$moves->indexOf($currentMove) + 1];

        if (!$nextMove instanceof PgnMove) {
            throw new LastMoveInVariation('This is last move in variation or last move in the game');
        }

        return $nextMove;
    }

    /**
     * @param string $fen
     */
    public function deleteVariationByFen(string $fen)
    {
        $fenToFound = new PgnFen($fen);
        $move = $this->findMoveByFen($fenToFound);

        if ($move->getMoves()->hasVariation()) {
            $variation = $move->getMoves()->getVariation();
            $variation->getVariations()->removeElement($variation);
            $mainMove = $variation->getVariations()->getMove();
            $mainMove->updateVariations();
        }

        $this->updateMoves();
    }

    public function addComment(string $fen, string $comment)
    {
        $fenObject = new PgnFen($fen);
        $move = $this->findMoveByFen($fenObject);
        $move->setComment($comment);
        $this->updateMoves();
    }

    /**
     * @param string $fen
     */
    public function promoteVariationByFen(string $fen)
    {
        $fenObject = new PgnFen($fen);
        $move = $this->findMoveByFen($fenObject);

        try {
            $mainMove = $move->getMoves()->getVariation()->getVariations()->getMove();
        } catch (TypeError $error) {
            return;
        }

        if ($this->getBlack() !== '' && $this->getMoves()->indexOf($mainMove) !== false) {
            //do not promote to main line if this is not analyze
            return;
        }

        if (!$move->getMoves()->hasVariation()) {
            //already on main line
            return;
        }

        /** @var PgnMove $firstVariationMove */
        $firstVariationMove = $move->getMoves()->getVariation()->getMoves()->first();
        $indexOfMainMove = $mainMove->getMoves()->indexOf($mainMove);
        $variations = $mainMove->getVariations();
        $variations->removeElement($move->getMoves()->getVariation());
        $mainMove->updateVariations();

        $firstVariationMove->getVariations()->add(
            new PgnVariation($mainMove->getMoves()->filter(
                function (PgnMove $mainLineMove) use ($indexOfMainMove) {
                    return $mainLineMove->getMoves()->indexOf($mainLineMove) >= $indexOfMainMove;
                }
            ))
        );
        $firstVariationMove->updateVariations();

        $movesCount = $mainMove->getMoves()->count();
        for ($i = $indexOfMainMove; $i < $movesCount; $i++) {
            $mainMove->getMoves()->remove($i);
        }

        $firstIndex = $indexOfMainMove;
        foreach ($firstVariationMove->getMoves() as $variationMove) {
            /** @var PgnMove $variationMove */
            $mainMove->getMoves()->offsetSet($firstIndex++, $variationMove);
            $variationMove->setMoves($mainMove->getMoves());
        }

        $this->updateMoves();
    }

    /**
     * @param string $fenBefore
     * @param string $fenAfter
     * @param array  $move
     * @param string $moveNotation
     *
     * @return string new pgn
     */
    public function insertNewMove(string $fenBefore, string $fenAfter, array $move, string $moveNotation): string
    {
        $fenBeforeObject = new PgnFen($fenBefore);
        $newMove = $move + [
                'm'          => $moveNotation,
                'fen'        => $fenAfter,
                'variations' => []
            ];
        try {
            $mainMove = $this->findNextMoveByFen($fenBeforeObject);
        } catch (LastMoveInVariation $exception) {
            $currentMove = $this->findMoveByFen($fenBeforeObject);
            /** @var PgnMoves $moves */
            $moves = $currentMove->getMoves();
            $moves->add(new PgnMove($newMove, $moves));

            return $this->generateNewPgn();
        }

        $mainMove->addVariation(new PgnVariation([$newMove], $mainMove->getVariations()));
        return $this->generateNewPgn();
    }

    public function removeRemainingMoves(string $fen): string
    {
        $currentMove = $this->findMoveByFen(new PgnFen($fen));
        $currentIndex = $currentMove->getMoves()->indexOf($currentMove);

        $indexesToRemove = [];
        foreach ($currentMove->getMoves() as $variantMove) {
            $variantMoveIndex = $currentMove->getMoves()->indexOf($variantMove);
            if ($variantMoveIndex > $currentIndex) {
                $indexesToRemove[] = $variantMoveIndex;
            }
        }

        foreach ($indexesToRemove as $indexToRemove) {
            $currentMove->getMoves()->remove($indexToRemove);
        }

        return $this->generateNewPgn();
    }

    /**
     * @param PgnFen $fen
     *
     * @return bool
     */
    public function fenExists(PgnFen $fen): bool
    {
        if ($this->getFen()->isSameBoard($fen)) {
            return true;
        }

        return !is_null($this->getMoves()->findMoveByFen($fen)->getFen());
    }

    /**
     * @param PgnFen $fen
     *
     * @return PgnFen
     */
    public function getGameFen(PgnFen $fen): PgnFen
    {
        if ($this->getFen()->isSameBoard($fen)) {
            return $this->getFen();
        }

        return $this->findMoveByFen($fen)->getFen();
    }

    /**
     * @return string
     */
    private function generateNewPgn(): string
    {
        $this->updateMoves();
        $jsonParser = new JsonToPgnParser();
        $jsonParser->addGameObject($this->toArray());
        $newPgn = $jsonParser->asPgn();
        return $newPgn;
    }

    /**
     * @throws Exception
     */
    public function updatePgnGameByContent()
    {
        $pgnGame = new PgnGame($this->getPgnGame(), $this->getPgnContent());

        $this->pgnGame = $pgnGame->getPgnGame();
        $this->moves = $pgnGame->getMoves();
        $this->fen = $pgnGame->getFen();
        $this->pgnContent = $pgnGame->getPgnContent();
        $this->header = implode(PHP_EOL, $this->getHeaderByContent($this->pgnContent));
    }

    public function updatePgnContent(): void
    {
        $jsonParser = new JsonToPgnParser();
        $jsonParser->addGameObject($this->getPgnGame());
        $newPgn = $this->getHeader() . PHP_EOL . preg_replace("/\[.+\]\n/", '', $jsonParser->asPgn());

        $this->setPgnContent($newPgn);
    }

    /**
     * @param string $pgnContent
     *
     * @return array
     */
    private function getHeaderByContent(string $pgnContent): array
    {
        if (!preg_match_all('/\[\w+ ".+?"]/', $pgnContent, $matches)) {
            return [];
        }

        return $matches[0];
    }
}