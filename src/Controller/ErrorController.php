<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

/**
 * @see \Symfony\Component\HttpKernel\Controller\ErrorController
 */
final class ErrorController
{
    /**
     * Show the error.
     */
    public function show(\Throwable $exception, Environment $twig): Response
    {
        if ($exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
        } else {
            $code = $exception->getCode();
        }

        switch ($code) {
            case 403:
                $httpStatusCode = 403;
                $template = 'error/error403.html.twig';
                break;
            case 404:
                $httpStatusCode = 404;
                $template = 'error/error404.html.twig';
                break;
            default:
                $httpStatusCode = $code ? $code : 500;
                $template = 'error/error.html.twig';
                break;
        }

        return new Response($twig->render($template, ['exception' => $exception]), $httpStatusCode);
    }
}
