<?php

namespace StasPiv\PgnGame\Tests;

use JsonToPgnParser;
use PgnParser;
use PHPUnit_Framework_TestCase;
use StasPiv\PgnGame\Exception\FenNotFound;
use StasPiv\PgnGame\Exception\LastMoveInVariation;
use StasPiv\PgnGame\PgnFen;
use StasPiv\PgnGame\PgnGame;

class PgnGameTest extends PHPUnit_Framework_TestCase
{
    /** @var PgnGame */
    private $pgnGame;

    protected function setUp()
    {
        $this->pgnGame = new PgnGame(['moves' => [], 'fen' => ''], '');
    }

    public function testFindMoveIndexByFen()
    {
        $this->setExpectedException(FenNotFound::class, 'fen not found: some fen');
        $this->pgnGame->findMoveIndexByFen(new PgnFen('some fen'));
    }

    public function testFindMoveByFen()
    {
        $pgnGame = new PgnGame(['moves' => [
            [
                'm'          => 'first move',
                'fen'        => 'test0',
                'variations' => []
            ],
            [
                'm'          => 'second move',
                'fen'        => 'test1',
                'variations' => []
            ],
        ], 'fen'                        => ''], '');

        $pgnFen = new PgnFen('test0');
        $move = $pgnGame->findMoveByFen($pgnFen);
        $this->assertEquals('first move', $move->getM());
        $nextMove = $pgnGame->findNextMoveByFen($pgnFen);
        $this->assertEquals('second move', $nextMove->getM());

    }

    public function testFindMoveByFenInVariationDepth1()
    {
        $pgnGame = new PgnGame(['moves' => [
            [
                'm'          => 'first move',
                'fen'        => 'test',
                'variations' => [
                    [
                        [
                            'm'   => 'variation move',
                            'fen' => 'variation fen'
                        ]
                    ]
                ]
            ]
        ], 'fen'                        => ''], '');

        $pgnFen = new PgnFen('variation fen');
        $move = $pgnGame->findMoveByFen($pgnFen);

        $this->assertEquals('variation move', $move->getM());
        $this->assertTrue($pgnGame->fenExists($pgnFen));
        $this->setExpectedException(LastMoveInVariation::class);
        $nextMove = $pgnGame->findNextMoveByFen($pgnFen);
    }

    public function testFindMoveByFenIn2ndVariationDepth1()
    {
        $pgnGame = new PgnGame(['moves' => [
            [
                'm'          => 'first move',
                'fen'        => 'test',
                'variations' => [
                    [
                        [
                            'm'   => 'variation move',
                            'fen' => 'variation fen'
                        ],
                    ],
                    [
                        [
                            'm'   => 'variation move 2',
                            'fen' => 'some fen 2'
                        ],
                    ],
                ]
            ]
        ], 'fen'                        => ''], '');

        $pgnFen = new PgnFen('some fen 2');
        $move = $pgnGame->findMoveByFen($pgnFen);

        $this->assertEquals('variation move 2', $move->getM());
        $this->assertTrue($pgnGame->fenExists($pgnFen));
        $this->setExpectedException(LastMoveInVariation::class);
        $nextMove = $pgnGame->findNextMoveByFen($pgnFen);
    }

    public function testFindMoveByFenInVariationDepth2()
    {
        $pgnGame = new PgnGame(['moves' => [
            [
                'm'          => 'first move',
                'fen'        => 'test',
                'variations' => [
                    [
                        [
                            'm'          => 'variation move',
                            'fen'        => 'variation fen',
                            'variations' => [
                                [
                                    [
                                        'm'   => '',
                                        'fen' => ''
                                    ],
                                    [
                                        'm'   => 'good move',
                                        'fen' => 'good fen'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], 'fen'                        => ''], '');

        $pgnFenObject = new PgnFen('good fen');
        $move = $pgnGame->findMoveByFen($pgnFenObject);

        $this->assertEquals('good move', $move->getM());
        $this->assertTrue($pgnGame->fenExists($pgnFenObject));
    }

    public function testSearchFirstVariation()
    {
        $pgnGame = new PgnGame([
            'metadata'    =>
                [
                    'utcdate'         => '2019.10.30',
                    'utctime'         => '19:56:11',
                    'whiteelo'        => '2387',
                    'blackelo'        => '2325',
                    'whiteratingdiff' => '+6',
                    'blackratingdiff' => '-6',
                    'whitetitle'      => 'FM',
                    'variant'         => 'Standard',
                    'opening'         => 'Sicilian Defense: Lasker-Pelikan Variation, Sveshnikov Variation',
                    'castle'          => 1,
                ],
            'event'       => 'Rated Blitz game',
            'site'        => 'https://lichess.org/oSc35Rda',
            'date'        => '2019.10.30',
            'round'       => '-',
            'white'       => 'Ljucifer',
            'black'       => 'AggressiveSpinach',
            'result'      => '1-0',
            'timecontrol' => '180+0',
            'eco'         => 'B33',
            'termination' => 'Normal',
            'fen'         => 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
            'moves'       =>
                [
                    0  =>
                        [
                            'm'    => 'e4',
                            'from' => 'e2',
                            'to'   => 'e4',
                            'fen'  => 'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1',
                        ],
                    1  =>
                        [
                            'm'    => 'c5',
                            'from' => 'c7',
                            'to'   => 'c5',
                            'fen'  => 'rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2',
                        ],
                    2  =>
                        [
                            'm'    => 'Nf3',
                            'from' => 'g1',
                            'to'   => 'f3',
                            'fen'  => 'rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2',
                        ],
                    3  =>
                        [
                            'm'    => 'Nc6',
                            'from' => 'b8',
                            'to'   => 'c6',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R w KQkq - 2 3',
                        ],
                    4  =>
                        [
                            'm'    => 'd4',
                            'from' => 'd2',
                            'to'   => 'd4',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/2p5/3PP3/5N2/PPP2PPP/RNBQKB1R b KQkq d3 0 3',
                        ],
                    5  =>
                        [
                            'm'    => 'cxd4',
                            'from' => 'c5',
                            'to'   => 'd4',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/8/3pP3/5N2/PPP2PPP/RNBQKB1R w KQkq - 0 4',
                        ],
                    6  =>
                        [
                            'm'    => 'Nxd4',
                            'from' => 'f3',
                            'to'   => 'd4',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/8/3NP3/8/PPP2PPP/RNBQKB1R b KQkq - 0 4',
                        ],
                    7  =>
                        [
                            'm'    => 'Nf6',
                            'from' => 'g8',
                            'to'   => 'f6',
                            'fen'  => 'r1bqkb1r/pp1ppppp/2n2n2/8/3NP3/8/PPP2PPP/RNBQKB1R w KQkq - 1 5',
                        ],
                    8  =>
                        [
                            'm'    => 'Nc3',
                            'from' => 'b1',
                            'to'   => 'c3',
                            'fen'  => 'r1bqkb1r/pp1ppppp/2n2n2/8/3NP3/2N5/PPP2PPP/R1BQKB1R b KQkq - 2 5',
                        ],
                    9  =>
                        [
                            'm'          => 'e5',
                            'variations' =>
                                [
                                    0 =>
                                        [
                                            0 =>
                                                [
                                                    'm'    => 'e6',
                                                    'from' => 'e7',
                                                    'to'   => 'e6',
                                                    'fen'  => 'r1bqkb1r/pp1p1ppp/2n1pn2/8/3NP3/2N5/PPP2PPP/R1BQKB1R w KQkq - 0 6',
                                                ],
                                            1 =>
                                                [
                                                    'm'    => 'e5',
                                                    'from' => 'e4',
                                                    'to'   => 'e5',
                                                    'fen'  => 'r1bqkb1r/pp1p1ppp/2n1pn2/4P3/3N4/2N5/PPP2PPP/R1BQKB1R b KQkq - 0 6',
                                                ],
                                        ],
                                    1 =>
                                        [
                                            0 =>
                                                [
                                                    'm'    => 'd6',
                                                    'from' => 'd7',
                                                    'to'   => 'd6',
                                                    'fen'  => 'r1bqkb1r/pp2pppp/2np1n2/8/3NP3/2N5/PPP2PPP/R1BQKB1R w KQkq - 0 6',
                                                ],
                                        ],
                                ],
                            'from'       => 'e7',
                            'to'         => 'e5',
                            'fen'        => 'r1bqkb1r/pp1p1ppp/2n2n2/4p3/3NP3/2N5/PPP2PPP/R1BQKB1R w KQkq e6 0 6',
                        ],
                    10 =>
                        [
                            'm'    => 'Ndb5',
                            'from' => 'd4',
                            'to'   => 'b5',
                            'fen'  => 'r1bqkb1r/pp1p1ppp/2n2n2/1N2p3/4P3/2N5/PPP2PPP/R1BQKB1R b KQkq - 1 6',
                        ],
                    11 =>
                        [
                            'm'    => 'd6',
                            'from' => 'd7',
                            'to'   => 'd6',
                            'fen'  => 'r1bqkb1r/pp3ppp/2np1n2/1N2p3/4P3/2N5/PPP2PPP/R1BQKB1R w KQkq - 0 7',
                        ],
                    12 =>
                        [
                            'm'    => 'Bg5',
                            'from' => 'c1',
                            'to'   => 'g5',
                            'fen'  => 'r1bqkb1r/pp3ppp/2np1n2/1N2p1B1/4P3/2N5/PPP2PPP/R2QKB1R b KQkq - 1 7',
                        ],
                    13 =>
                        [
                            'm'    => 'a6',
                            'from' => 'a7',
                            'to'   => 'a6',
                            'fen'  => 'r1bqkb1r/1p3ppp/p1np1n2/1N2p1B1/4P3/2N5/PPP2PPP/R2QKB1R w KQkq - 0 8',
                        ],
                    14 =>
                        [
                            'm'    => 'Na3',
                            'from' => 'b5',
                            'to'   => 'a3',
                            'fen'  => 'r1bqkb1r/1p3ppp/p1np1n2/4p1B1/4P3/N1N5/PPP2PPP/R2QKB1R b KQkq - 1 8',
                        ],
                    15 =>
                        [
                            'm'    => 'b5',
                            'from' => 'b7',
                            'to'   => 'b5',
                            'fen'  => 'r1bqkb1r/5ppp/p1np1n2/1p2p1B1/4P3/N1N5/PPP2PPP/R2QKB1R w KQkq b6 0 9',
                        ],
                    16 =>
                        [
                            'm'    => 'Bxf6',
                            'from' => 'g5',
                            'to'   => 'f6',
                            'fen'  => 'r1bqkb1r/5ppp/p1np1B2/1p2p3/4P3/N1N5/PPP2PPP/R2QKB1R b KQkq - 0 9',
                        ],
                    17 =>
                        [
                            'm'    => 'gxf6',
                            'from' => 'g7',
                            'to'   => 'f6',
                            'fen'  => 'r1bqkb1r/5p1p/p1np1p2/1p2p3/4P3/N1N5/PPP2PPP/R2QKB1R w KQkq - 0 10',
                        ],
                    18 =>
                        [
                            'm'    => 'Nd5',
                            'from' => 'c3',
                            'to'   => 'd5',
                            'fen'  => 'r1bqkb1r/5p1p/p1np1p2/1p1Np3/4P3/N7/PPP2PPP/R2QKB1R b KQkq - 1 10',
                        ],
                    19 =>
                        [
                            'm'    => 'f5',
                            'from' => 'f6',
                            'to'   => 'f5',
                            'fen'  => 'r1bqkb1r/5p1p/p1np4/1p1Npp2/4P3/N7/PPP2PPP/R2QKB1R w KQkq - 0 11',
                        ],
                    20 =>
                        [
                            'm'    => 'Bd3',
                            'from' => 'f1',
                            'to'   => 'd3',
                            'fen'  => 'r1bqkb1r/5p1p/p1np4/1p1Npp2/4P3/N2B4/PPP2PPP/R2QK2R b KQkq - 1 11',
                        ],
                    21 =>
                        [
                            'm'    => 'Be6',
                            'from' => 'c8',
                            'to'   => 'e6',
                            'fen'  => 'r2qkb1r/5p1p/p1npb3/1p1Npp2/4P3/N2B4/PPP2PPP/R2QK2R w KQkq - 2 12',
                        ],
                    22 =>
                        [
                            'm'    => 'Qh5',
                            'from' => 'd1',
                            'to'   => 'h5',
                            'fen'  => 'r2qkb1r/5p1p/p1npb3/1p1Npp1Q/4P3/N2B4/PPP2PPP/R3K2R b KQkq - 3 12',
                        ],
                    23 =>
                        [
                            'm'    => 'Bg7',
                            'from' => 'f8',
                            'to'   => 'g7',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Npp1Q/4P3/N2B4/PPP2PPP/R3K2R w KQkq - 4 13',
                        ],
                    24 =>
                        [
                            'm'    => 'O-O',
                            'from' => 'e1',
                            'to'   => 'g1',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Npp1Q/4P3/N2B4/PPP2PPP/R4RK1 b kq - 5 13',
                        ],
                    25 =>
                        [
                            'm'    => 'f4',
                            'from' => 'f5',
                            'to'   => 'f4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Np2Q/4Pp2/N2B4/PPP2PPP/R4RK1 w kq - 0 14',
                        ],
                    26 =>
                        [
                            'm'    => 'c4',
                            'from' => 'c2',
                            'to'   => 'c4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Np2Q/2P1Pp2/N2B4/PP3PPP/R4RK1 b kq c3 0 14',
                        ],
                    27 =>
                        [
                            'm'    => 'bxc4',
                            'from' => 'b5',
                            'to'   => 'c4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/3Np2Q/2p1Pp2/N2B4/PP3PPP/R4RK1 w kq - 0 15',
                        ],
                    28 =>
                        [
                            'm'    => 'Nxc4',
                            'from' => 'a3',
                            'to'   => 'c4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/3Np2Q/2N1Pp2/3B4/PP3PPP/R4RK1 b kq - 0 15',
                        ],
                    29 =>
                        [
                            'm'    => 'O-O',
                            'from' => 'e8',
                            'to'   => 'g8',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1Pp2/3B4/PP3PPP/R4RK1 w - - 1 16',
                        ],
                    30 =>
                        [
                            'm'    => 'g3',
                            'from' => 'g2',
                            'to'   => 'g3',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1Pp2/3B2P1/PP3P1P/R4RK1 b - - 0 16',
                        ],
                    31 =>
                        [
                            'm'    => 'fxg3',
                            'from' => 'f4',
                            'to'   => 'g3',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1P3/3B2p1/PP3P1P/R4RK1 w - - 0 17',
                        ],
                    32 =>
                        [
                            'm'    => 'hxg3',
                            'from' => 'h2',
                            'to'   => 'g3',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1P3/3B2P1/PP3P2/R4RK1 b - - 0 17',
                        ],
                    33 =>
                        [
                            'm'    => 'f5',
                            'from' => 'f7',
                            'to'   => 'f5',
                            'fen'  => 'r2q1rk1/6bp/p1npb3/3Npp1Q/2N1P3/3B2P1/PP3P2/R4RK1 w - f6 0 18',
                        ],
                    34 =>
                        [
                            'm'    => 'Ncb6',
                            'from' => 'c4',
                            'to'   => 'b6',
                            'fen'  => 'r2q1rk1/6bp/pNnpb3/3Npp1Q/4P3/3B2P1/PP3P2/R4RK1 b - - 1 18',
                        ],
                    35 =>
                        [
                            'm'    => 'Bf7',
                            'from' => 'e6',
                            'to'   => 'f7',
                            'fen'  => 'r2q1rk1/5bbp/pNnp4/3Npp1Q/4P3/3B2P1/PP3P2/R4RK1 w - - 2 19',
                        ],
                    36 =>
                        [
                            'm'    => 'Qh3',
                            'from' => 'h5',
                            'to'   => 'h3',
                            'fen'  => 'r2q1rk1/5bbp/pNnp4/3Npp2/4P3/3B2PQ/PP3P2/R4RK1 b - - 3 19',
                        ],
                    37 =>
                        [
                            'm'    => 'Rb8',
                            'from' => 'a8',
                            'to'   => 'b8',
                            'fen'  => '1r1q1rk1/5bbp/pNnp4/3Npp2/4P3/3B2PQ/PP3P2/R4RK1 w - - 4 20',
                        ],
                    38 =>
                        [
                            'm'    => 'exf5',
                            'from' => 'e4',
                            'to'   => 'f5',
                            'fen'  => '1r1q1rk1/5bbp/pNnp4/3NpP2/8/3B2PQ/PP3P2/R4RK1 b - - 0 20',
                        ],
                    39 =>
                        [
                            'm'    => 'Nb4',
                            'from' => 'c6',
                            'to'   => 'b4',
                            'fen'  => '1r1q1rk1/5bbp/pN1p4/3NpP2/1n6/3B2PQ/PP3P2/R4RK1 w - - 1 21',
                        ],
                    40 =>
                        [
                            'm'    => 'Nxb4',
                            'from' => 'd5',
                            'to'   => 'b4',
                            'fen'  => '1r1q1rk1/5bbp/pN1p4/4pP2/1N6/3B2PQ/PP3P2/R4RK1 b - - 0 21',
                        ],
                    41 =>
                        [
                            'm'    => 'Rxb6',
                            'from' => 'b8',
                            'to'   => 'b6',
                            'fen'  => '3q1rk1/5bbp/pr1p4/4pP2/1N6/3B2PQ/PP3P2/R4RK1 w - - 0 22',
                        ],
                    42 =>
                        [
                            'm'    => 'f6',
                            'from' => 'f5',
                            'to'   => 'f6',
                            'fen'  => '3q1rk1/5bbp/pr1p1P2/4p3/1N6/3B2PQ/PP3P2/R4RK1 b - - 0 22',
                        ],
                    43 =>
                        [
                            'm'    => 'h5',
                            'from' => 'h7',
                            'to'   => 'h5',
                            'fen'  => '3q1rk1/5bb1/pr1p1P2/4p2p/1N6/3B2PQ/PP3P2/R4RK1 w - h6 0 23',
                        ],
                    44 =>
                        [
                            'm'    => 'fxg7',
                            'from' => 'f6',
                            'to'   => 'g7',
                            'fen'  => '3q1rk1/5bP1/pr1p4/4p2p/1N6/3B2PQ/PP3P2/R4RK1 b - - 0 23',
                        ],
                    45 =>
                        [
                            'm'    => 'Kxg7',
                            'from' => 'g8',
                            'to'   => 'g7',
                            'fen'  => '3q1r2/5bk1/pr1p4/4p2p/1N6/3B2PQ/PP3P2/R4RK1 w - - 0 24',
                        ],
                    46 =>
                        [
                            'm'    => 'Nc2',
                            'from' => 'b4',
                            'to'   => 'c2',
                            'fen'  => '3q1r2/5bk1/pr1p4/4p2p/8/3B2PQ/PPN2P2/R4RK1 b - - 1 24',
                        ],
                    47 =>
                        [
                            'm'    => 'Rxb2',
                            'from' => 'b6',
                            'to'   => 'b2',
                            'fen'  => '3q1r2/5bk1/p2p4/4p2p/8/3B2PQ/PrN2P2/R4RK1 w - - 0 25',
                        ],
                    48 =>
                        [
                            'm'    => 'Ne3',
                            'from' => 'c2',
                            'to'   => 'e3',
                            'fen'  => '3q1r2/5bk1/p2p4/4p2p/8/3BN1PQ/Pr3P2/R4RK1 b - - 1 25',
                        ],
                ],
        ], '');

        $pgnFenObject = new PgnFen('r1bqkb1r/pp1p1ppp/2n1pn2/8/3NP3/2N5/PPP2PPP/R1BQKB1R');
        $move = $pgnGame->findMoveByFen($pgnFenObject);

        $this->assertEquals('e6', $move->getM());
        $this->assertTrue($pgnGame->fenExists($pgnFenObject));
    }

    public function testSearchSecondVariation()
    {
        $pgnGame = new PgnGame([
            'metadata'    =>
                [
                    'utcdate'         => '2019.10.30',
                    'utctime'         => '19:56:11',
                    'whiteelo'        => '2387',
                    'blackelo'        => '2325',
                    'whiteratingdiff' => '+6',
                    'blackratingdiff' => '-6',
                    'whitetitle'      => 'FM',
                    'variant'         => 'Standard',
                    'opening'         => 'Sicilian Defense: Lasker-Pelikan Variation, Sveshnikov Variation',
                    'castle'          => 1,
                ],
            'event'       => 'Rated Blitz game',
            'site'        => 'https://lichess.org/oSc35Rda',
            'date'        => '2019.10.30',
            'round'       => '-',
            'white'       => 'Ljucifer',
            'black'       => 'AggressiveSpinach',
            'result'      => '1-0',
            'timecontrol' => '180+0',
            'eco'         => 'B33',
            'termination' => 'Normal',
            'fen'         => 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
            'moves'       =>
                [
                    0  =>
                        [
                            'm'    => 'e4',
                            'from' => 'e2',
                            'to'   => 'e4',
                            'fen'  => 'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1',
                        ],
                    1  =>
                        [
                            'm'    => 'c5',
                            'from' => 'c7',
                            'to'   => 'c5',
                            'fen'  => 'rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2',
                        ],
                    2  =>
                        [
                            'm'    => 'Nf3',
                            'from' => 'g1',
                            'to'   => 'f3',
                            'fen'  => 'rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2',
                        ],
                    3  =>
                        [
                            'm'    => 'Nc6',
                            'from' => 'b8',
                            'to'   => 'c6',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R w KQkq - 2 3',
                        ],
                    4  =>
                        [
                            'm'    => 'd4',
                            'from' => 'd2',
                            'to'   => 'd4',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/2p5/3PP3/5N2/PPP2PPP/RNBQKB1R b KQkq d3 0 3',
                        ],
                    5  =>
                        [
                            'm'    => 'cxd4',
                            'from' => 'c5',
                            'to'   => 'd4',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/8/3pP3/5N2/PPP2PPP/RNBQKB1R w KQkq - 0 4',
                        ],
                    6  =>
                        [
                            'm'    => 'Nxd4',
                            'from' => 'f3',
                            'to'   => 'd4',
                            'fen'  => 'r1bqkbnr/pp1ppppp/2n5/8/3NP3/8/PPP2PPP/RNBQKB1R b KQkq - 0 4',
                        ],
                    7  =>
                        [
                            'm'    => 'Nf6',
                            'from' => 'g8',
                            'to'   => 'f6',
                            'fen'  => 'r1bqkb1r/pp1ppppp/2n2n2/8/3NP3/8/PPP2PPP/RNBQKB1R w KQkq - 1 5',
                        ],
                    8  =>
                        [
                            'm'    => 'Nc3',
                            'from' => 'b1',
                            'to'   => 'c3',
                            'fen'  => 'r1bqkb1r/pp1ppppp/2n2n2/8/3NP3/2N5/PPP2PPP/R1BQKB1R b KQkq - 2 5',
                        ],
                    9  =>
                        [
                            'm'          => 'e5',
                            'variations' =>
                                [
                                    0 =>
                                        [
                                            0 =>
                                                [
                                                    'm'    => 'e6',
                                                    'from' => 'e7',
                                                    'to'   => 'e6',
                                                    'fen'  => 'r1bqkb1r/pp1p1ppp/2n1pn2/8/3NP3/2N5/PPP2PPP/R1BQKB1R w KQkq - 0 6',
                                                ],
                                            1 =>
                                                [
                                                    'm'    => 'e5',
                                                    'from' => 'e4',
                                                    'to'   => 'e5',
                                                    'fen'  => 'r1bqkb1r/pp1p1ppp/2n1pn2/4P3/3N4/2N5/PPP2PPP/R1BQKB1R b KQkq - 0 6',
                                                ],
                                        ],
                                    1 =>
                                        [
                                            0 =>
                                                [
                                                    'm'    => 'd6',
                                                    'from' => 'd7',
                                                    'to'   => 'd6',
                                                    'fen'  => 'r1bqkb1r/pp2pppp/2np1n2/8/3NP3/2N5/PPP2PPP/R1BQKB1R w KQkq - 0 6',
                                                ],
                                        ],
                                ],
                            'from'       => 'e7',
                            'to'         => 'e5',
                            'fen'        => 'r1bqkb1r/pp1p1ppp/2n2n2/4p3/3NP3/2N5/PPP2PPP/R1BQKB1R w KQkq e6 0 6',
                        ],
                    10 =>
                        [
                            'm'    => 'Ndb5',
                            'from' => 'd4',
                            'to'   => 'b5',
                            'fen'  => 'r1bqkb1r/pp1p1ppp/2n2n2/1N2p3/4P3/2N5/PPP2PPP/R1BQKB1R b KQkq - 1 6',
                        ],
                    11 =>
                        [
                            'm'    => 'd6',
                            'from' => 'd7',
                            'to'   => 'd6',
                            'fen'  => 'r1bqkb1r/pp3ppp/2np1n2/1N2p3/4P3/2N5/PPP2PPP/R1BQKB1R w KQkq - 0 7',
                        ],
                    12 =>
                        [
                            'm'    => 'Bg5',
                            'from' => 'c1',
                            'to'   => 'g5',
                            'fen'  => 'r1bqkb1r/pp3ppp/2np1n2/1N2p1B1/4P3/2N5/PPP2PPP/R2QKB1R b KQkq - 1 7',
                        ],
                    13 =>
                        [
                            'm'    => 'a6',
                            'from' => 'a7',
                            'to'   => 'a6',
                            'fen'  => 'r1bqkb1r/1p3ppp/p1np1n2/1N2p1B1/4P3/2N5/PPP2PPP/R2QKB1R w KQkq - 0 8',
                        ],
                    14 =>
                        [
                            'm'    => 'Na3',
                            'from' => 'b5',
                            'to'   => 'a3',
                            'fen'  => 'r1bqkb1r/1p3ppp/p1np1n2/4p1B1/4P3/N1N5/PPP2PPP/R2QKB1R b KQkq - 1 8',
                        ],
                    15 =>
                        [
                            'm'    => 'b5',
                            'from' => 'b7',
                            'to'   => 'b5',
                            'fen'  => 'r1bqkb1r/5ppp/p1np1n2/1p2p1B1/4P3/N1N5/PPP2PPP/R2QKB1R w KQkq b6 0 9',
                        ],
                    16 =>
                        [
                            'm'    => 'Bxf6',
                            'from' => 'g5',
                            'to'   => 'f6',
                            'fen'  => 'r1bqkb1r/5ppp/p1np1B2/1p2p3/4P3/N1N5/PPP2PPP/R2QKB1R b KQkq - 0 9',
                        ],
                    17 =>
                        [
                            'm'    => 'gxf6',
                            'from' => 'g7',
                            'to'   => 'f6',
                            'fen'  => 'r1bqkb1r/5p1p/p1np1p2/1p2p3/4P3/N1N5/PPP2PPP/R2QKB1R w KQkq - 0 10',
                        ],
                    18 =>
                        [
                            'm'    => 'Nd5',
                            'from' => 'c3',
                            'to'   => 'd5',
                            'fen'  => 'r1bqkb1r/5p1p/p1np1p2/1p1Np3/4P3/N7/PPP2PPP/R2QKB1R b KQkq - 1 10',
                        ],
                    19 =>
                        [
                            'm'    => 'f5',
                            'from' => 'f6',
                            'to'   => 'f5',
                            'fen'  => 'r1bqkb1r/5p1p/p1np4/1p1Npp2/4P3/N7/PPP2PPP/R2QKB1R w KQkq - 0 11',
                        ],
                    20 =>
                        [
                            'm'    => 'Bd3',
                            'from' => 'f1',
                            'to'   => 'd3',
                            'fen'  => 'r1bqkb1r/5p1p/p1np4/1p1Npp2/4P3/N2B4/PPP2PPP/R2QK2R b KQkq - 1 11',
                        ],
                    21 =>
                        [
                            'm'    => 'Be6',
                            'from' => 'c8',
                            'to'   => 'e6',
                            'fen'  => 'r2qkb1r/5p1p/p1npb3/1p1Npp2/4P3/N2B4/PPP2PPP/R2QK2R w KQkq - 2 12',
                        ],
                    22 =>
                        [
                            'm'    => 'Qh5',
                            'from' => 'd1',
                            'to'   => 'h5',
                            'fen'  => 'r2qkb1r/5p1p/p1npb3/1p1Npp1Q/4P3/N2B4/PPP2PPP/R3K2R b KQkq - 3 12',
                        ],
                    23 =>
                        [
                            'm'    => 'Bg7',
                            'from' => 'f8',
                            'to'   => 'g7',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Npp1Q/4P3/N2B4/PPP2PPP/R3K2R w KQkq - 4 13',
                        ],
                    24 =>
                        [
                            'm'    => 'O-O',
                            'from' => 'e1',
                            'to'   => 'g1',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Npp1Q/4P3/N2B4/PPP2PPP/R4RK1 b kq - 5 13',
                        ],
                    25 =>
                        [
                            'm'    => 'f4',
                            'from' => 'f5',
                            'to'   => 'f4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Np2Q/4Pp2/N2B4/PPP2PPP/R4RK1 w kq - 0 14',
                        ],
                    26 =>
                        [
                            'm'    => 'c4',
                            'from' => 'c2',
                            'to'   => 'c4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/1p1Np2Q/2P1Pp2/N2B4/PP3PPP/R4RK1 b kq c3 0 14',
                        ],
                    27 =>
                        [
                            'm'    => 'bxc4',
                            'from' => 'b5',
                            'to'   => 'c4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/3Np2Q/2p1Pp2/N2B4/PP3PPP/R4RK1 w kq - 0 15',
                        ],
                    28 =>
                        [
                            'm'    => 'Nxc4',
                            'from' => 'a3',
                            'to'   => 'c4',
                            'fen'  => 'r2qk2r/5pbp/p1npb3/3Np2Q/2N1Pp2/3B4/PP3PPP/R4RK1 b kq - 0 15',
                        ],
                    29 =>
                        [
                            'm'    => 'O-O',
                            'from' => 'e8',
                            'to'   => 'g8',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1Pp2/3B4/PP3PPP/R4RK1 w - - 1 16',
                        ],
                    30 =>
                        [
                            'm'    => 'g3',
                            'from' => 'g2',
                            'to'   => 'g3',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1Pp2/3B2P1/PP3P1P/R4RK1 b - - 0 16',
                        ],
                    31 =>
                        [
                            'm'    => 'fxg3',
                            'from' => 'f4',
                            'to'   => 'g3',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1P3/3B2p1/PP3P1P/R4RK1 w - - 0 17',
                        ],
                    32 =>
                        [
                            'm'    => 'hxg3',
                            'from' => 'h2',
                            'to'   => 'g3',
                            'fen'  => 'r2q1rk1/5pbp/p1npb3/3Np2Q/2N1P3/3B2P1/PP3P2/R4RK1 b - - 0 17',
                        ],
                    33 =>
                        [
                            'm'    => 'f5',
                            'from' => 'f7',
                            'to'   => 'f5',
                            'fen'  => 'r2q1rk1/6bp/p1npb3/3Npp1Q/2N1P3/3B2P1/PP3P2/R4RK1 w - f6 0 18',
                        ],
                    34 =>
                        [
                            'm'    => 'Ncb6',
                            'from' => 'c4',
                            'to'   => 'b6',
                            'fen'  => 'r2q1rk1/6bp/pNnpb3/3Npp1Q/4P3/3B2P1/PP3P2/R4RK1 b - - 1 18',
                        ],
                    35 =>
                        [
                            'm'    => 'Bf7',
                            'from' => 'e6',
                            'to'   => 'f7',
                            'fen'  => 'r2q1rk1/5bbp/pNnp4/3Npp1Q/4P3/3B2P1/PP3P2/R4RK1 w - - 2 19',
                        ],
                    36 =>
                        [
                            'm'    => 'Qh3',
                            'from' => 'h5',
                            'to'   => 'h3',
                            'fen'  => 'r2q1rk1/5bbp/pNnp4/3Npp2/4P3/3B2PQ/PP3P2/R4RK1 b - - 3 19',
                        ],
                    37 =>
                        [
                            'm'    => 'Rb8',
                            'from' => 'a8',
                            'to'   => 'b8',
                            'fen'  => '1r1q1rk1/5bbp/pNnp4/3Npp2/4P3/3B2PQ/PP3P2/R4RK1 w - - 4 20',
                        ],
                    38 =>
                        [
                            'm'    => 'exf5',
                            'from' => 'e4',
                            'to'   => 'f5',
                            'fen'  => '1r1q1rk1/5bbp/pNnp4/3NpP2/8/3B2PQ/PP3P2/R4RK1 b - - 0 20',
                        ],
                    39 =>
                        [
                            'm'    => 'Nb4',
                            'from' => 'c6',
                            'to'   => 'b4',
                            'fen'  => '1r1q1rk1/5bbp/pN1p4/3NpP2/1n6/3B2PQ/PP3P2/R4RK1 w - - 1 21',
                        ],
                    40 =>
                        [
                            'm'    => 'Nxb4',
                            'from' => 'd5',
                            'to'   => 'b4',
                            'fen'  => '1r1q1rk1/5bbp/pN1p4/4pP2/1N6/3B2PQ/PP3P2/R4RK1 b - - 0 21',
                        ],
                    41 =>
                        [
                            'm'    => 'Rxb6',
                            'from' => 'b8',
                            'to'   => 'b6',
                            'fen'  => '3q1rk1/5bbp/pr1p4/4pP2/1N6/3B2PQ/PP3P2/R4RK1 w - - 0 22',
                        ],
                    42 =>
                        [
                            'm'    => 'f6',
                            'from' => 'f5',
                            'to'   => 'f6',
                            'fen'  => '3q1rk1/5bbp/pr1p1P2/4p3/1N6/3B2PQ/PP3P2/R4RK1 b - - 0 22',
                        ],
                    43 =>
                        [
                            'm'    => 'h5',
                            'from' => 'h7',
                            'to'   => 'h5',
                            'fen'  => '3q1rk1/5bb1/pr1p1P2/4p2p/1N6/3B2PQ/PP3P2/R4RK1 w - h6 0 23',
                        ],
                    44 =>
                        [
                            'm'    => 'fxg7',
                            'from' => 'f6',
                            'to'   => 'g7',
                            'fen'  => '3q1rk1/5bP1/pr1p4/4p2p/1N6/3B2PQ/PP3P2/R4RK1 b - - 0 23',
                        ],
                    45 =>
                        [
                            'm'    => 'Kxg7',
                            'from' => 'g8',
                            'to'   => 'g7',
                            'fen'  => '3q1r2/5bk1/pr1p4/4p2p/1N6/3B2PQ/PP3P2/R4RK1 w - - 0 24',
                        ],
                    46 =>
                        [
                            'm'    => 'Nc2',
                            'from' => 'b4',
                            'to'   => 'c2',
                            'fen'  => '3q1r2/5bk1/pr1p4/4p2p/8/3B2PQ/PPN2P2/R4RK1 b - - 1 24',
                        ],
                    47 =>
                        [
                            'm'    => 'Rxb2',
                            'from' => 'b6',
                            'to'   => 'b2',
                            'fen'  => '3q1r2/5bk1/p2p4/4p2p/8/3B2PQ/PrN2P2/R4RK1 w - - 0 25',
                        ],
                    48 =>
                        [
                            'm'    => 'Ne3',
                            'from' => 'c2',
                            'to'   => 'e3',
                            'fen'  => '3q1r2/5bk1/p2p4/4p2p/8/3BN1PQ/Pr3P2/R4RK1 b - - 1 25',
                        ],
                ],
        ], '');

        $pgnFenObject = new PgnFen('r1bqkb1r/pp2pppp/2np1n2/8/3NP3/2N5/PPP2PPP/R1BQKB1R');
        $move = $pgnGame->findMoveByFen($pgnFenObject);

        $this->assertEquals('d6', $move->getM());
        $this->assertTrue($pgnGame->fenExists($pgnFenObject));
    }

    public function testPromoteVariation()
    {
        $pgnGame = new PgnGame(['moves' => [
            [
                'm'          => 'first move',
                'fen'        => 'test',
                'variations' => [
                    [
                        [
                            'm'          => 'variation move',
                            'fen'        => 'variation fen',
                            'variations' => [
                                [
                                    [
                                        'm'   => 'first variation move',
                                        'fen' => 'fen to promote'
                                    ],
                                    [
                                        'm'   => 'good move',
                                        'fen' => 'good fen'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'm'   => 'old second variation move',
                            'fen' => 'old second variation fen',
                        ]
                    ]
                ]
            ]
        ], 'fen'                        => ''], '');

        $pgnGame->promoteVariationByFen('good fen');

        $moves = $pgnGame->getPgnGame()["moves"];
        $this->assertEquals(2, count($moves[0]["variations"][0]));
        $this->assertEquals('first variation move', $moves[0]['variations'][0][0]['m']);
        $this->assertEquals(2, count($moves[0]["variations"][0]));

    }

    public function testInsertNewMoveAtTheEndOfVariation()
    {
        $pgnGame = new PgnGame([
            'fen'   => 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
            'moves' =>
                [
                    0 =>
                        [
                            'm'          => 'Rd1',
                            'variations' =>
                                [
                                    0 =>
                                        [
                                            0 =>
                                                [
                                                    'm'    => 'e4',
                                                    'from' => 'e3',
                                                    'to'   => 'e4',
                                                    'fen'  => 'r2q1rk1/pp1nbppp/2p1pn2/5bB1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 b - - 0 12',
                                                ],
                                            1 =>
                                                [
                                                    'm'          => 'Bg4',
                                                    'variations' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    0 =>
                                                                        [
                                                                            'm'    => 'Bg6',
                                                                            'from' => 'f5',
                                                                            'to'   => 'g6',
                                                                            'fen'  => 'r2q1rk1/pp1nbppp/2p1pnb1/6B1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 w - - 1 13',
                                                                        ],
                                                                ],
                                                        ],
                                                    'from'       => 'f5',
                                                    'to'         => 'g4',
                                                    'fen'        => 'r2q1rk1/pp1nbppp/2p1pn2/6B1/1PBPP1b1/P1N2N2/5PPP/R1Q2RK1 w - - 1 13',
                                                ],
                                        ],
                                ],
                            'from'       => 'f1',
                            'to'         => 'd1',
                            'fen'        => 'r2q1rk1/pp1nbppp/2p1pn2/5bB1/1PBP4/P1N1PN2/5PPP/R1QR2K1 b - - 2 12',
                        ],
                ],
        ], '');

        $arguments = [
            0 => 'r2q1rk1/pp1nbppp/2p1pnb1/6B1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 w - - 1 13',
            1 => 'r2q1rk1/pp1nbppp/2p1pnb1/4P1B1/1PBP4/P1N2N2/5PPP/R1Q2RK1 b - - 0 13',
            2 =>
                [
                    'from' => 'e4',
                    'to'   => 'e5',
                ],
            3 => 'e5',
        ];

        $pgnGame->insertNewMove($arguments[0], $arguments[1], $arguments[2], $arguments[3]);

        $expectedVariation = [
            0 =>
                [
                    'm'    => 'Bg6',
                    'from' => 'f5',
                    'to'   => 'g6',
                    'fen'  => 'r2q1rk1/pp1nbppp/2p1pnb1/6B1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 w - - 1 13',
                ],
            1 =>
                [
                    'm'          => 'e5',
                    'from'       => 'e4',
                    'to'         => 'e5',
                    'fen'        => 'r2q1rk1/pp1nbppp/2p1pnb1/4P1B1/1PBP4/P1N2N2/5PPP/R1Q2RK1 b - - 0 13',
                    'variations' => []
                ],
        ];

        $this->assertEquals($expectedVariation, $pgnGame->getPgnGame()["moves"][0]["variations"][0][1]["variations"][0]);
    }

    public function testDeleteVariation()
    {
        $pgnGame = new PgnGame([
            'moves' =>
                [
                    0 =>
                        [
                            'm'          => 'Rd1',
                            'variations' =>
                                [
                                    0 =>
                                        [
                                            0 =>
                                                [
                                                    'm'    => 'e4',
                                                    'from' => 'e3',
                                                    'to'   => 'e4',
                                                    'fen'  => 'r2q1rk1/pp1nbppp/2p1pn2/5bB1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 b - - 0 12',
                                                ],
                                            1 =>
                                                [
                                                    'm'          => 'Bg4',
                                                    'variations' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    0 =>
                                                                        [
                                                                            'm'    => 'e5',
                                                                            'from' => 'e6',
                                                                            'to'   => 'e5',
                                                                            'fen'  => 'r2q1rk1/pp1nbppp/2p2n2/4pbB1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 w - - 0 13',
                                                                        ],
                                                                ],
                                                        ],
                                                    'from'       => 'f5',
                                                    'to'         => 'g4',
                                                    'fen'        => 'r2q1rk1/pp1nbppp/2p1pn2/6B1/1PBPP1b1/P1N2N2/5PPP/R1Q2RK1 w - - 1 13',
                                                ],
                                        ],
                                ],
                            'from'       => 'f1',
                            'to'         => 'd1',
                            'fen'        => 'r2q1rk1/pp1nbppp/2p1pn2/5bB1/1PBP4/P1N1PN2/5PPP/R1QR2K1 b - - 2 12',
                        ],
                ],
            'fen'   => ''
        ], '');

        $pgnGame->deleteVariationByFen('r2q1rk1/pp1nbppp/2p2n2/4pbB1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 w - - 0 13');

        $expectedResult = [
            'moves' =>
                [
                    0 =>
                        [
                            'm'          => 'Rd1',
                            'variations' =>
                                [
                                    0 =>
                                        [
                                            0 =>
                                                [
                                                    'm'    => 'e4',
                                                    'from' => 'e3',
                                                    'to'   => 'e4',
                                                    'fen'  => 'r2q1rk1/pp1nbppp/2p1pn2/5bB1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 b - - 0 12',
                                                ],
                                            1 =>
                                                [
                                                    'm'          => 'Bg4',
                                                    'variations' =>
                                                        [
//                                                            0 =>
//                                                                [
//                                                                    0 =>
//                                                                        [
//                                                                            'm' => 'e5',
//                                                                            'from' => 'e6',
//                                                                            'to' => 'e5',
//                                                                            'fen' => 'r2q1rk1/pp1nbppp/2p2n2/4pbB1/1PBPP3/P1N2N2/5PPP/R1Q2RK1 w - - 0 13',
//                                                                        ],
//                                                                ],
                                                        ],
                                                    'from'       => 'f5',
                                                    'to'         => 'g4',
                                                    'fen'        => 'r2q1rk1/pp1nbppp/2p1pn2/6B1/1PBPP1b1/P1N2N2/5PPP/R1Q2RK1 w - - 1 13',
                                                ],
                                        ],
                                ],
                            'from'       => 'f1',
                            'to'         => 'd1',
                            'fen'        => 'r2q1rk1/pp1nbppp/2p1pn2/5bB1/1PBP4/P1N1PN2/5PPP/R1QR2K1 b - - 2 12',
                        ],
                ],
            'fen'   => ''
        ];

        $this->assertEquals($expectedResult, $pgnGame->getPgnGame());

    }

    public function testPromoteVariationOnlyVariationMoves()
    {
        $pgnGame = new PgnGame([
            'moves' => [
                [
                    'm'          => 'main move',
                    'fen'        => 'main fen',
                    'variations' => [
                        [
                            [
                                'm'   => 'variation move 1',
                                'fen' => 'variation fen 1',
                            ],
                            [
                                'm'          => 'variation move 2',
                                'fen'        => 'variation fen 2',
                                'variations' => [
                                    [
                                        [
                                            'm'   => '1st move depth 2',
                                            'fen' => '1st fen depth 2'
                                        ],
                                        [
                                            'm'   => '2nd move depth 2',
                                            'fen' => '2nd fen depth 2'
                                        ],
                                    ]
                                ]
                            ],
                            [
                                'm'   => 'variation move 3',
                                'fen' => 'variation fen 3',
                            ],
                        ]
                    ]
                ]
            ],
            'fen'   => ''
        ], '');

        $pgnGame->promoteVariationByFen('1st fen depth 2');
        $expectedResult = [
            'moves' =>
                [
                    0 =>
                        [
                            'm'          => 'main move',
                            'fen'        => 'main fen',
                            'variations' =>
                                [
                                    0 =>
                                        [
                                            0 =>
                                                [
                                                    'm'   => 'variation move 1',
                                                    'fen' => 'variation fen 1',
                                                ],
                                            1 =>
                                                [
                                                    'm'          => '1st move depth 2',
                                                    'fen'        => '1st fen depth 2',
                                                    'variations' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    0 =>
                                                                        [
                                                                            'm'          => 'variation move 2',
                                                                            'fen'        => 'variation fen 2',
                                                                            'variations' =>
                                                                                [
                                                                                ],
                                                                        ],
                                                                    1 =>
                                                                        [
                                                                            'm'   => 'variation move 3',
                                                                            'fen' => 'variation fen 3',
                                                                        ],
                                                                ],
                                                        ],
                                                ],
                                            2 =>
                                                [
                                                    'm'   => '2nd move depth 2',
                                                    'fen' => '2nd fen depth 2',
                                                ],
                                        ],
                                ],
                        ],
                ],
            'fen'   => '',
        ];
        $this->assertEquals($expectedResult, $pgnGame->getPgnGame());

    }

    public function testPromoteVariationMainVariationLonger()
    {
        $pgnGame = new PgnGame([
            'moves' => [
                [
                    'm'          => 'main move',
                    'fen'        => 'main fen',
                    'variations' => [
                        [
                            [
                                'm'   => 'variation move 1',
                                'fen' => 'variation fen 1',
                            ],
                            [
                                'm'          => 'variation move 2',
                                'fen'        => 'variation fen 2',
                                'variations' => [
                                    [
                                        [
                                            'm'   => '1st move depth 2',
                                            'fen' => '1st fen depth 2'
                                        ],
                                        [
                                            'm'   => '2nd move depth 2',
                                            'fen' => '2nd fen depth 2'
                                        ],
                                    ]
                                ]
                            ],
                            [
                                'm'   => 'variation move 3',
                                'fen' => 'variation fen 3',
                            ],
                            [
                                'm'   => 'variation move 4',
                                'fen' => 'variation fen 4',
                            ],
                            [
                                'm'   => 'variation move 5',
                                'fen' => 'variation fen 5',
                            ],
                            [
                                'm'   => 'variation move 6',
                                'fen' => 'variation fen 6',
                            ],
                        ]
                    ]
                ]
            ],
            'fen'   => ''
        ], '');

        $pgnGame->promoteVariationByFen('1st fen depth 2');

        $expectedResult = [
            'moves' =>
                [
                    0 =>
                        [
                            'm'          => 'main move',
                            'fen'        => 'main fen',
                            'variations' =>
                                [
                                    0 =>
                                        [
                                            0 =>
                                                [
                                                    'm'   => 'variation move 1',
                                                    'fen' => 'variation fen 1',
                                                ],
                                            1 =>
                                                [
                                                    'm'          => '1st move depth 2',
                                                    'fen'        => '1st fen depth 2',
                                                    'variations' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    0 =>
                                                                        [
                                                                            'm'          => 'variation move 2',
                                                                            'fen'        => 'variation fen 2',
                                                                            'variations' =>
                                                                                [
                                                                                ],
                                                                        ],
                                                                    1 =>
                                                                        [
                                                                            'm'   => 'variation move 3',
                                                                            'fen' => 'variation fen 3',
                                                                        ],
                                                                    2 =>
                                                                        [
                                                                            'm'   => 'variation move 4',
                                                                            'fen' => 'variation fen 4',
                                                                        ],
                                                                    3 =>
                                                                        [
                                                                            'm'   => 'variation move 5',
                                                                            'fen' => 'variation fen 5',
                                                                        ],
                                                                    4 =>
                                                                        [
                                                                            'm'   => 'variation move 6',
                                                                            'fen' => 'variation fen 6',
                                                                        ],
                                                                ],
                                                        ],
                                                ],
                                            2 =>
                                                [
                                                    'm'   => '2nd move depth 2',
                                                    'fen' => '2nd fen depth 2',
                                                ],
                                        ],
                                ],
                        ],
                ],
            'fen'   => '',
        ];
//        var_export($pgnGame->getPgnGame());

        $this->assertEquals($expectedResult, $pgnGame->getPgnGame());

    }

    public function testPromoteInsteadOfRemoving()
    {
        $pgnContent = '[Event "Rated Blitz game"]
[Site "https://lichess.org/gEeAxvoC"]
[Date "2019.11.01"]
[Round "-"]
[White "AggressiveSpinach"]
[Black "parciful"]
[Result "0-1"]
[UTCDate "2019.11.01"]
[UTCTime "16:56:12"]
[WhiteElo "2361"]
[BlackElo "2366"]
[WhiteRatingDiff "-7"]
[BlackRatingDiff "+7"]
[Variant "Standard"]
[TimeControl "180+0"]
[ECO "B12"]
[Opening "Caro-Kann Defense: Advance Variation, Short Variation"]
[Termination "Time forfeit"]

1. e4 c6 (1... c5 2. Nf3 Nc6 (2... d6 3. d3 (3. d4)) 3. d4 cxd4 4. Nxd4) 2. d4 d5 3. e5 Bf5 4. Nf3 e6 5. Be2 Ne7 6. O-O h6 7. Nbd2 g5 8. Nb3 Ng6 9. Bd2 g4 10. Ne1 h5 11. c4 Be7 12. c5 Nd7 13. Bd3 Nh4 14. Qc1 f6 15. Bxf5 Nxf5 16. Nd3 Qc7 17. Bf4 Qd8 18. Re1 Kf7 19. exf6 Bxf6 20. Ne5+ Nxe5 21. Bxe5 Bxe5 22. Rxe5 Qf6 23. Qf4 Rae8 24. Rae1 Rhg8 25. Na5 Re7 26. Qd2 h4 27. Qb4 g3 28. hxg3 hxg3 29. f3 Qh4 30. Nxb7 Qh2+ 31. Kf1 Qh1+ 32. Ke2 Qxg2+ 33. Kd3 Qxf3+ 34. Kc2 Qf2+ 35. Kb1 g2 36. Nd6+ Kf6 37. Nxf5 g1=Q 38. Nxe7 Qgxe1+ 39. Rxe1 Rg1';
        $pgnGame = @$this->createPgnGameByContent($pgnContent);

        $pgnGame->promoteVariationByFen('rnbqkbnr/pp2pppp/3p4/2p5/3PP3/5N2/PPP2PPP/RNBQKB1R b KQkq d3 0 3');

        $this->updatePgnContent($pgnGame);

        $expectedResult = '[Event "Rated Blitz game"]
[Site "https://lichess.org/gEeAxvoC"]
[Date "2019.11.01"]
[Round "-"]
[White "AggressiveSpinach"]
[Black "parciful"]
[Result "0-1"]
[UTCDate "2019.11.01"]
[UTCTime "16:56:12"]
[WhiteElo "2361"]
[BlackElo "2366"]
[WhiteRatingDiff "-7"]
[BlackRatingDiff "+7"]
[Variant "Standard"]
[TimeControl "180+0"]
[ECO "B12"]
[Opening "Caro-Kann Defense: Advance Variation, Short Variation"]
[Termination "Time forfeit"]

1. e4 c6 (1... c5 2. Nf3 Nc6 (2... d6 3. d4 (3. d3)) 3. d4 cxd4 4. Nxd4) 2. d4 d5 3. e5 Bf5 4. Nf3 e6 5. Be2 Ne7 6. O-O h6 7. Nbd2 g5 8. Nb3 Ng6 9. Bd2 g4 10. Ne1 h5 11. c4 Be7 12. c5 Nd7 13. Bd3 Nh4 14. Qc1 f6 15. Bxf5 Nxf5 16. Nd3 Qc7 17. Bf4 Qd8 18. Re1 Kf7 19. exf6 Bxf6 20. Ne5+ Nxe5 21. Bxe5 Bxe5 22. Rxe5 Qf6 23. Qf4 Rae8 24. Rae1 Rhg8 25. Na5 Re7 26. Qd2 h4 27. Qb4 g3 28. hxg3 hxg3 29. f3 Qh4 30. Nxb7 Qh2+ 31. Kf1 Qh1+ 32. Ke2 Qxg2+ 33. Kd3 Qxf3+ 34. Kc2 Qf2+ 35. Kb1 g2 36. Nd6+ Kf6 37. Nxf5 g1=Q 38. Nxe7 Qgxe1+ 39. Rxe1 Rg1';
        $this->assertEquals($expectedResult, $pgnGame->getPgnContent());
    }

    public function testPromoteAndInsert()
    {
        $pgnContent = '[Event "Rated Blitz game"]
        
1. e4 c6 (1... c5 2. Nf3 Nc6 (2... d6 3. d4 (3. d3)) 3. d4 cxd4 4. Nxd4) 2. d4 d5 3. e5 Bf5 ';

        $pgnGame = $this->createPgnGameByContent($pgnContent);

        $fenAfterD3 = 'rnbqkbnr/pp2pppp/3p4/2p5/4P3/3P1N2/PPP2PPP/RNBQKB1R b KQkq - 0 3';
        $pgnGame->promoteVariationByFen($fenAfterD3);
        $this->updatePgnContent($pgnGame);

        $this->assertEquals('[Event "Rated Blitz game"]

1. e4 c6 (1... c5 2. Nf3 Nc6 (2... d6 3. d3 (3. d4)) 3. d4 cxd4 4. Nxd4) 2. d4 d5 3. e5 Bf5', $pgnGame->getPgnContent());

        $fenBefore = $fenAfterD3;
        $fenAfterH6 = 'rnbqkbnr/pp2ppp1/3p3p/2p5/4P3/3P1N2/PPP2PPP/RNBQKB1R w KQkq - 0 4';
        $move = [
            'from' => 'h7',
            'to'   => 'h6'
        ];

        $moveNotation = 'h6';

        $pgnGame->insertNewMove($fenBefore, $fenAfterH6, $move, $moveNotation);
        $pgnGame->updateMoves();

        $this->updatePgnContent($pgnGame);

        $this->assertEquals('[Event "Rated Blitz game"]

1. e4 c6 (1... c5 2. Nf3 Nc6 (2... d6 3. d3 (3. d4) h6) 3. d4 cxd4 4. Nxd4) 2. d4 d5 3. e5 Bf5', $pgnGame->getPgnContent());

    }

    public function testInsertMoveToBigDepth()
    {
        $pgnContentBefore = '[Event "Rated Blitz game"]
[Site "https://lichess.org/rWr2H04s"]
[Date "2019.11.07"]
[Round "-"]
[White "AggressiveSpinach"]
[Black "Fishok"]
[Result "1-0"]
[UTCDate "2019.11.07"]
[UTCTime "20:38:54"]
[WhiteElo "2328"]
[BlackElo "2258"]
[WhiteRatingDiff "+6"]
[BlackRatingDiff "-6"]
[Variant "Standard"]
[TimeControl "180+0"]
[ECO "B33"]
[Opening "Sicilian Defense: Lasker-Pelikan Variation, Sveshnikov Variation, Novosibirsk Variation"]
[Termination "Normal"]

1. e4 e5 (1... c5 2. Nf3 (2. Nc3 d6 (2... e6 3. a4 (3. b4 a5 (3... a6)))))';

        $pgnGame = $this->createPgnGameByContent($pgnContentBefore);

        $allFens = [
            'e4'  => 'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1',
            'e5'  => 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2',
            'c5'  => 'rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2',
            'Nf3' => 'rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2',
            'Nc3' => 'rnbqkbnr/pp1ppppp/8/2p5/4P3/2N5/PPPP1PPP/R1BQKBNR b KQkq - 1 2',
            'd6'  => 'rnbqkbnr/pp2pppp/3p4/2p5/4P3/2N5/PPPP1PPP/R1BQKBNR w KQkq - 0 3',
            'e6'  => 'rnbqkbnr/pp1p1ppp/4p3/2p5/4P3/2N5/PPPP1PPP/R1BQKBNR w KQkq - 0 3',
            'a4'  => 'rnbqkbnr/pp1p1ppp/4p3/2p5/P3P3/2N5/1PPP1PPP/R1BQKBNR b KQkq a3 0 3',
            'b4'  => 'rnbqkbnr/pp1p1ppp/4p3/2p5/1P2P3/2N5/P1PP1PPP/R1BQKBNR b KQkq b3 0 3',
            'a5'  => 'rnbqkbnr/1p1p1ppp/4p3/p1p5/1P2P3/2N5/P1PP1PPP/R1BQKBNR w KQkq a6 0 4',
            'a6'  => 'rnbqkbnr/1p1p1ppp/p3p3/2p5/1P2P3/2N5/P1PP1PPP/R1BQKBNR w KQkq - 0 4',
        ];

        $arguments = [
            0 => $allFens['a6'],
            1 => 'new fen',
            2 =>
                [
                    'from' => 'e1',
                    'to'   => 'e2',
                ],
            3 => 'Ke2',
        ];

        call_user_func_array([$pgnGame, 'insertNewMove'], $arguments);

        $this->assertNotEquals($allFens, $pgnGame->getMoves()->getFenArray());
        $this->assertNotEquals($pgnContentBefore, $pgnGame->getPgnContent());
    }

    public function testInsertKh1AfterQg5()
    {
        $arguments = [
            0 => 'r4k1r/5pb1/P2p4/1B1Np1qp/Q2np1b1/N7/PP3PPP/R3R1K1 w - - 4 18',
            1 => 'r4k1r/5pb1/P2p4/1B1Np1qp/Q2np1b1/N7/PP3PPP/R3R2K b - - 5 18',
            2 =>
                [
                    'from' => 'g1',
                    'to'   => 'h1',
                ],
            3 => 'Kh1',
        ];

        $pgnContentBefore = '[Event "Rated Blitz game"]
[Site "https://lichess.org/rWr2H04s"]
[Date "2019.11.07"]
[Round "-"]
[White "AggressiveSpinach"]
[Black "Fishok"]
[Result "1-0"]
[UTCDate "2019.11.07"]
[UTCTime "20:38:54"]
[WhiteElo "2328"]
[BlackElo "2258"]
[WhiteRatingDiff "+6"]
[BlackRatingDiff "-6"]
[Variant "Standard"]
[TimeControl "180+0"]
[ECO "B33"]
[Opening "Sicilian Defense: Lasker-Pelikan Variation, Sveshnikov Variation, Novosibirsk Variation"]
[Termination "Normal"]

1. e4 c5 2. Nf3 Nc6 3. d4 cxd4 4. Nxd4 Nf6 5. Nc3 e5 6. Ndb5 d6 7. Bg5 a6 8. Na3 b5 9. Bxf6 gxf6 10. Nd5 Bg7 11. c4 Ne7 (11... Nd4 12. cxb5 f5 13. bxa6 fxe4 14. Bb5+ Kf8 15. O-O h5 16. Rc1 (16. Re1 Bg4 17. Qa4 Nf3+ (17... Qg5) 18. gxf3 Bxf3 19. Re3 Qg5+ 20. Kf1) Bg4 17. f3 exf3) 12. cxb5 Nxd5 13. Qxd5 Be6 14. Qc6+ Kf8 15. Rd1 d5 16. exd5 Rc8 17. Qxa6 Bxd5 18. Bc4 Qe7 19. Qxc8+';

        $pgnGame = @$this->createPgnGameByContent($pgnContentBefore);
        call_user_func_array([$pgnGame, 'insertNewMove'], $arguments);

        $this->updatePgnContent($pgnGame);

        $expectedPgnContent = '[Event "Rated Blitz game"]
[Site "https://lichess.org/rWr2H04s"]
[Date "2019.11.07"]
[Round "-"]
[White "AggressiveSpinach"]
[Black "Fishok"]
[Result "1-0"]
[UTCDate "2019.11.07"]
[UTCTime "20:38:54"]
[WhiteElo "2328"]
[BlackElo "2258"]
[WhiteRatingDiff "+6"]
[BlackRatingDiff "-6"]
[Variant "Standard"]
[TimeControl "180+0"]
[ECO "B33"]
[Opening "Sicilian Defense: Lasker-Pelikan Variation, Sveshnikov Variation, Novosibirsk Variation"]
[Termination "Normal"]

1. e4 c5 2. Nf3 Nc6 3. d4 cxd4 4. Nxd4 Nf6 5. Nc3 e5 6. Ndb5 d6 7. Bg5 a6 8. Na3 b5 9. Bxf6 gxf6 10. Nd5 Bg7 11. c4 Ne7 (11... Nd4 12. cxb5 f5 13. bxa6 fxe4 14. Bb5+ Kf8 15. O-O h5 16. Rc1 (16. Re1 Bg4 17. Qa4 Nf3+ (17... Qg5 18. Kh1) 18. gxf3 Bxf3 19. Re3 Qg5+ 20. Kf1) Bg4 17. f3 exf3) 12. cxb5 Nxd5 13. Qxd5 Be6 14. Qc6+ Kf8 15. Rd1 d5 16. exd5 Rc8 17. Qxa6 Bxd5 18. Bc4 Qe7 19. Qxc8+';

        $this->assertEquals($expectedPgnContent, $pgnGame->getPgnContent());
    }

    public function testPromoteCastle()
    {
        $pgnContent = '[Event "Rated Blitz game"]
[Site "https://lichess.org/rWr2H04s"]
[Date "2019.11.07"]
[Round "-"]
[White "AggressiveSpinach"]
[Black "Fishok"]
[Result "1-0"]
[UTCDate "2019.11.07"]
[UTCTime "20:38:54"]
[WhiteElo "2328"]
[BlackElo "2258"]
[WhiteRatingDiff "+6"]
[BlackRatingDiff "-6"]
[Variant "Standard"]
[TimeControl "180+0"]
[ECO "B33"]
[Opening "Sicilian Defense: Lasker-Pelikan Variation, Sveshnikov Variation, Novosibirsk Variation"]
[Termination "Normal"]

1. e4 c5 2. Nf3 Nc6 3. d4 cxd4 4. Nxd4 Nf6 5. Nc3 e5 6. Ndb5 d6 7. Bg5 a6 8. Na3 b5 9. Bxf6 gxf6 10. Nd5 Bg7 11. c4 (11. Bd3 Ne7 12. Nxe7 Qxe7 13. c4 f5 14. O-O (14. cxb5 d5) Bb7 15. exf5 e4 16. Re1 Bxb2 (16... O-O) 17. cxb5 axb5 18. Bxb5+ Kf8 19. Nc4 Bxa1 20. Qxa1 Rg8 21. a4 e3 22. Nxe3 Qg5 23. Qd4 Rd8 24. g3) Ne7 (11... Nd4 12. cxb5 f5 13. bxa6 fxe4 14. Bb5+ Kf8 15. O-O h5 16. Rc1 (16. Re1 Bg4 17. Qa4 Rh6 (17... Nf3+ (17... Qg5 18. Kh1 h4 (18... Nf3 19. Re3) 19. h3 Bxh3 20. gxh3 Qf5 21. Bd7 Qf3+ 22. Kg1 Bh6 23. Bg4 Rg8 24. Qd7 Rg6 25. Nc4) 18. gxf3 Bxf3 19. Re3 Qg5+ 20. Kf1)) Bg4 17. f3 exf3) 12. cxb5 Nxd5 13. Qxd5 Be6 14. Qc6+ Kf8 15. Rd1 d5 16. exd5 Rc8 17. Qxa6 Bxd5 18. Bc4 Qe7 19. Qxc8+';

        $pgnGame = @$this->createPgnGameByContent($pgnContent);
        $pgnGame->promoteVariationByFen('r4rk1/1b2qpbp/p2p4/1p3P2/2P1p3/N2B4/PP3PPP/R2QR1K1 w - - 2 17');
    }

    public function testPromoteAndThenPromoteAgain()
    {
        $pgnContent = '[Event "Rated Blitz game"]
[Site "https://lichess.org/eJSHd0Iv"]
[Date "2019.11.08"]
[Round "-"]
[White "Paulewski"]
[Black "AggressiveSpinach"]
[Result "1-0"]
[UTCDate "2019.11.08"]
[UTCTime "21:01:10"]
[WhiteElo "2314"]
[BlackElo "2323"]
[WhiteRatingDiff "+8"]
[BlackRatingDiff "-7"]
[Variant "Standard"]
[TimeControl "180+0"]
[ECO "D45"]
[Opening "Semi-Slav Defense: Stoltz Variation"]
[Termination "Normal"]

1. d4 d5 2. c4 c6 3. Nc3 Nf6 4. e3 e6 5. Nf3 Nbd7 6. Qc2 Bd6 7. Bd3 dxc4 8. Bxc4 b5 (8... e5 9. O-O O-O 10. b3 Qe7 11. h3 (11. Nh4 Nb6 12. Bd3) e4 12. Ng5 b5 13. Be2 Re8 14. f3 exf3 15. Bxf3 Bb7 16. Nxb5 Bb8 17. e4 cxb5 18. e5 Bxf3 19. exf6 (19. Nxf3) Be4) 9. Be2 Bb7 10. O-O O-O 11. e4 e5 12. dxe5 Nxe5 13. Nxe5 Bxe5 14. Be3 b4 15. Na4 Qa5 16. Nc5 Bb8';

        $pgnGame = @$this->createPgnGameByContent($pgnContent);
        $fenArray = [
            'd4'        => 'rnbqkbnr/pppppppp/8/8/3P4/8/PPP1PPPP/RNBQKBNR b KQkq d3 0 1',
            'd5'        => 'rnbqkbnr/ppp1pppp/8/3p4/3P4/8/PPP1PPPP/RNBQKBNR w KQkq d6 0 2',
            'c4'        => 'rnbqkbnr/ppp1pppp/8/3p4/2PP4/8/PP2PPPP/RNBQKBNR b KQkq c3 0 2',
            'c6'        => 'rnbqkbnr/pp2pppp/2p5/3p4/2PP4/8/PP2PPPP/RNBQKBNR w KQkq - 0 3',
            'Nc3'       => 'rnbqkbnr/pp2pppp/2p5/3p4/2PP4/2N5/PP2PPPP/R1BQKBNR b KQkq - 1 3',
            'Nf6'       => 'rnbqkb1r/pp2pppp/2p2n2/3p4/2PP4/2N5/PP2PPPP/R1BQKBNR w KQkq - 2 4',
            'e3'        => 'rnbqkb1r/pp2pppp/2p2n2/3p4/2PP4/2N1P3/PP3PPP/R1BQKBNR b KQkq - 0 4',
            'e6'        => 'rnbqkb1r/pp3ppp/2p1pn2/3p4/2PP4/2N1P3/PP3PPP/R1BQKBNR w KQkq - 0 5',
            'Nf3'       => 'rnbqkb1r/pp3ppp/2p1pn2/3p4/2PP4/2N1PN2/PP3PPP/R1BQKB1R b KQkq - 1 5',
            'Nbd7'      => 'r1bqkb1r/pp1n1ppp/2p1pn2/3p4/2PP4/2N1PN2/PP3PPP/R1BQKB1R w KQkq - 2 6',
            'Qc2'       => 'r1bqkb1r/pp1n1ppp/2p1pn2/3p4/2PP4/2N1PN2/PPQ2PPP/R1B1KB1R b KQkq - 3 6',
            'Bd6'       => 'r1bqk2r/pp1n1ppp/2pbpn2/3p4/2PP4/2N1PN2/PPQ2PPP/R1B1KB1R w KQkq - 4 7',
            'Bd3'       => 'r1bqk2r/pp1n1ppp/2pbpn2/3p4/2PP4/2NBPN2/PPQ2PPP/R1B1K2R b KQkq - 5 7',
            'dxc4'      => 'r1bqk2r/pp1n1ppp/2pbpn2/8/2pP4/2NBPN2/PPQ2PPP/R1B1K2R w KQkq - 0 8',
            'Bxc4'      => 'r1bqk2r/pp1n1ppp/2pbpn2/8/2BP4/2N1PN2/PPQ2PPP/R1B1K2R b KQkq - 0 8',
            'b5'        => 'r1bqk2r/p2n1ppp/2pbpn2/1p6/2BP4/2N1PN2/PPQ2PPP/R1B1K2R w KQkq b6 0 9',
            'b5 e5'     => 'rb2r1k1/pb1nqppp/5n2/1p2P1N1/3P4/1P3B1P/P1Q3P1/R1B2RK1 b - - 0 18',
            'b5 O-O'    => 'r1bq1rk1/pp1n1ppp/2pb1n2/4p3/2BP4/2N1PN2/PPQ2PPP/R1B2RK1 w - - 2 10',
            'b5 b3'     => 'r1bq1rk1/pp1n1ppp/2pb1n2/4p3/2BP4/1PN1PN2/P1Q2PPP/R1B2RK1 b - - 0 10',
            'b5 Qe7'    => 'r1b2rk1/pp1nqppp/2pb1n2/4p3/2BP4/1PN1PN2/P1Q2PPP/R1B2RK1 w - - 1 11',
            'b5 h3'     => 'r1b2rk1/pp1nqppp/2pb1n2/4p3/2BP4/1PN1PN1P/P1Q2PP1/R1B2RK1 b - - 0 11',
            'h3 Nh4'    => 'r1b2rk1/pp1nqppp/2pb1n2/4p3/2BP3N/1PN1P3/P1Q2PPP/R1B2RK1 b - - 2 11',
            'h3 Nb6'    => 'r1b2rk1/pp2qppp/1npb1n2/4p3/2BP3N/1PN1P3/P1Q2PPP/R1B2RK1 w - - 3 12',
            'h3 Bd3'    => 'r1b2rk1/pp2qppp/1npb1n2/4p3/3P3N/1PNBP3/P1Q2PPP/R1B2RK1 b - - 4 12',
            'b5 e4'     => 'rb2r1k1/pb1nqppp/2p2n2/1N4N1/3PP3/1P3B1P/P1Q3P1/R1B2RK1 b - - 0 17',
            'b5 Ng5'    => 'r1b2rk1/pp1nqppp/2pb1n2/6N1/2BPp3/1PN1P2P/P1Q2PP1/R1B2RK1 b - - 1 12',
            'b5 b5'     => 'r1b2rk1/p2nqppp/2pb1n2/1p4N1/2BPp3/1PN1P2P/P1Q2PP1/R1B2RK1 w - b6 0 13',
            'b5 Be2'    => 'r1b2rk1/p2nqppp/2pb1n2/1p4N1/3Pp3/1PN1P2P/P1Q1BPP1/R1B2RK1 b - - 1 13',
            'b5 Re8'    => 'r1b1r1k1/p2nqppp/2pb1n2/1p4N1/3Pp3/1PN1P2P/P1Q1BPP1/R1B2RK1 w - - 2 14',
            'b5 f3'     => 'r1b1r1k1/p2nqppp/2pb1n2/1p4N1/3Pp3/1PN1PP1P/P1Q1B1P1/R1B2RK1 b - - 0 14',
            'b5 exf3'   => 'r1b1r1k1/p2nqppp/2pb1n2/1p4N1/3P4/1PN1Pp1P/P1Q1B1P1/R1B2RK1 w - - 0 15',
            'b5 Bxf3'   => 'rb2r1k1/p2nqppp/5n2/1p2P1N1/3P4/1P3b1P/P1Q3P1/R1B2RK1 w - - 0 19',
            'b5 Bb7'    => 'r3r1k1/pb1nqppp/2pb1n2/1p4N1/3P4/1PN1PB1P/P1Q3P1/R1B2RK1 w - - 1 16',
            'b5 Nxb5'   => 'r3r1k1/pb1nqppp/2pb1n2/1N4N1/3P4/1P2PB1P/P1Q3P1/R1B2RK1 b - - 0 16',
            'b5 Bb8'    => 'rb2r1k1/pb1nqppp/2p2n2/1N4N1/3P4/1P2PB1P/P1Q3P1/R1B2RK1 w - - 1 17',
            'b5 cxb5'   => 'rb2r1k1/pb1nqppp/5n2/1p4N1/3PP3/1P3B1P/P1Q3P1/R1B2RK1 w - - 0 18',
            'b5 exf6'   => 'rb2r1k1/p2nqppp/5P2/1p4N1/3P4/1P3b1P/P1Q3P1/R1B2RK1 b - - 0 19',
            'exf6 Nxf3' => 'rb2r1k1/p2nqppp/5n2/1p2P3/3P4/1P3N1P/P1Q3P1/R1B2RK1 b - - 0 19',
            'b5 Be4'    => 'rb2r1k1/p2nqppp/5P2/1p4N1/3Pb3/1P5P/P1Q3P1/R1B2RK1 w - - 1 20',
            'Be2'       => 'r1bqk2r/p2n1ppp/2pbpn2/1p6/3P4/2N1PN2/PPQ1BPPP/R1B1K2R b KQkq - 1 9',
            'Bb7'       => 'r2qk2r/pb1n1ppp/2pbpn2/1p6/3P4/2N1PN2/PPQ1BPPP/R1B1K2R w KQkq - 2 10',
            'O-O'       => 'r2q1rk1/pb1n1ppp/2pbpn2/1p6/3P4/2N1PN2/PPQ1BPPP/R1B2RK1 w - - 4 11',
            'e4'        => 'r2q1rk1/pb1n1ppp/2pbpn2/1p6/3PP3/2N2N2/PPQ1BPPP/R1B2RK1 b - - 0 11',
            'e5'        => 'r2q1rk1/pb1n1ppp/2pb1n2/1p2p3/3PP3/2N2N2/PPQ1BPPP/R1B2RK1 w - - 0 12',
            'dxe5'      => 'r2q1rk1/pb1n1ppp/2pb1n2/1p2P3/4P3/2N2N2/PPQ1BPPP/R1B2RK1 b - - 0 12',
            'Nxe5'      => 'r2q1rk1/pb3ppp/2pb1n2/1p2N3/4P3/2N5/PPQ1BPPP/R1B2RK1 b - - 0 13',
            'Bxe5'      => 'r2q1rk1/pb3ppp/2p2n2/1p2b3/4P3/2N5/PPQ1BPPP/R1B2RK1 w - - 0 14',
            'Be3'       => 'r2q1rk1/pb3ppp/2p2n2/1p2b3/4P3/2N1B3/PPQ1BPPP/R4RK1 b - - 1 14',
            'b4'        => 'r2q1rk1/pb3ppp/2p2n2/4b3/1p2P3/2N1B3/PPQ1BPPP/R4RK1 w - - 0 15',
            'Na4'       => 'r2q1rk1/pb3ppp/2p2n2/4b3/Np2P3/4B3/PPQ1BPPP/R4RK1 b - - 1 15',
            'Qa5'       => 'r4rk1/pb3ppp/2p2n2/q3b3/Np2P3/4B3/PPQ1BPPP/R4RK1 w - - 2 16',
            'Nc5'       => 'r4rk1/pb3ppp/2p2n2/q1N1b3/1p2P3/4B3/PPQ1BPPP/R4RK1 b - - 3 16',
            'Bb8'       => 'rb3rk1/pb3ppp/2p2n2/q1N5/1p2P3/4B3/PPQ1BPPP/R4RK1 w - - 4 17',
        ];

        $pgnGame->promoteVariationByFen($fenArray['exf6 Nxf3']);
        $fenAfter1stPromotion = $pgnGame->getPgnContent();

        $pgnGame->promoteVariationByFen($fenArray['b5 exf6']);
        $fenAfter2ndPromotion = $pgnGame->getPgnContent();

        $this->assertNotEquals($fenAfter2ndPromotion, $fenAfter1stPromotion);
    }

    /**
     * @param string $pgnContent
     *
     * @return PgnGame
     * @throws \Exception
     */
    private function createPgnGameByContent(string $pgnContent): PgnGame
    {
        $parser = new PgnParser();
        $parser->setPgnContent($pgnContent);

        $pgnGame = new PgnGame($parser->getFirstGame(), $pgnContent);
        return $pgnGame;
    }

    /**
     * @param PgnGame $pgnGame
     */
    private function updatePgnContent(PgnGame $pgnGame): void
    {
        $jsonParser = new JsonToPgnParser();
        $jsonParser->addGameObject($pgnGame->toArray());
        $newPgn = $pgnGame->getHeader() . $jsonParser->asPgn();
        $pgnGame->setPgnContent($newPgn);
    }

}
