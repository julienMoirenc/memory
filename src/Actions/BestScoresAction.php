<?php

namespace App\Actions;

use App\Models\Game;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Action used to get best scores as a json array
 */
final class BestScoresAction
{
    /**
     * The Game model
     *
     * @var Game
     */
    private $gameModel;

    public function __construct(Game $gameModel)
    {
        $this->gameModel = $gameModel;
    }

    public function  __invoke(Request $request, Response $response)
    {
        //get best score from model
        $scores = $this->gameModel->getBests();

        //format scoress
        $formattedScore = [];
        foreach ($scores as $score) {
            $formattedScore[] =  $score['solvingtime'] . ' s';
        }

        //return json response
        $response->getBody()->write(json_encode($formattedScore));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
