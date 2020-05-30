<?php

namespace StasPiv\PgnGame;

use Exception;
use PgnParser;

/**
 * Class PgnGameFactory
 *
 * Creates PgnGame from content
 *
 * @package StasPiv\PgnGame
 */
class PgnGameFactory
{
    /**
     * @param string $pgnContent
     *
     * @return PgnGame
     * @throws Exception
     */
    public static function getPgnGameByContent(string $pgnContent): PgnGame
    {
        $parser = new PgnParser();
        $parser->setPgnContent($pgnContent);

        $pgnGame = new PgnGame($parser->getFirstGame(), $pgnContent);
        return $pgnGame;
    }
}