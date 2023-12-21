<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AppController extends AbstractController
{
    public function appAction(Request $request)
    {
        return $this->render('app.html.twig');
    }
}
