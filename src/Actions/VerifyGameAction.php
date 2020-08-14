<?php

namespace App\Actions;

use App\Models\Game;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Action used to verify a solution to a memory problem and to store it and resolving time into the database
 */
final class VerifyGameAction
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
     * verify if a game solution is valid ans store it
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    function __invoke(Request $request, Response $response): Response
    {
        //get post param from request
        $parsedBody = $request->getParsedBody();

        //call the game model to verify solution, store it and calculate resolvingtime
        $verified = $this->gameModel->verify($parsedBody['key'], $parsedBody['solution']);

        //prepare json data
        $data = [];
        $data['status'] = $verified ? 1 : 0;

        //send response
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
