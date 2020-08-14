<?php

use Slim\App;
use App\Actions\IndexAction;
use App\Actions\BestScoresAction;
use App\Actions\NewGameAction;
use App\Actions\VerifyGameAction;

return function (App $app) {

    //Main application route (UI)
    $app->get('/', IndexAction::class);

    //API routes
    $app->get('/api/score/best', BestScoresAction::class);
    $app->get('/api/game/new', NewGameAction::class);
    $app->post('/api/game/verify', VerifyGameAction::class);
};
