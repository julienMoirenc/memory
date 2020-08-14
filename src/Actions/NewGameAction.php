<?php

namespace App\Actions;

use App\Models\Game;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Action used to create a new game and return it as a json array
 */
final class NewGameAction
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

    /**
     * create a new game, return a game identifier and a shuffled array of cards
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    function __invoke(Request $request, Response $response): Response
    {
        //get the new game from the model
        $game = $this->gameModel->new();

        //prepare json data
        $data = new \StdClass();
        $data->key = $game['key'];
        $data->problem = json_decode($game['problem']);
        $data->timeout = Game::TIMEOUT;

        //send response
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
