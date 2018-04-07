<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends Controller
{    
    /**
     * @Route("/configuration/install", name="config_install")
     */
    public function installAction(Request $request)
    {
        $initializer = $this->getInitializer;
        
        $initializer->test();
        return new Response('<body>ok<body>', 200);
    }
}