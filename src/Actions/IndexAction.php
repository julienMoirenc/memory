<?php

namespace App\Actions;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Views\PhpRenderer;

/**
 * Action to get the UI
 */
final class IndexAction
{
    private $renderer;

    public function __construct(\Slim\Views\PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    protected function getRenderer()
    {
        return $this->renderer;
    }

    public function  __invoke(Request $request, Response $response)
    {
        //simply return the view
        return $this->getRenderer()->render($response, 'main/index.html');
    }
}
