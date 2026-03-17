<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class ErrorController extends BaseController
{
    public function notFound(): void
    {
        $this->render('error/404', ['title' => 'Page introuvable']);
    }

    public function forbidden(): void
    {
        $this->render('error/403', ['title' => 'Accès refusé']);
    }
}
