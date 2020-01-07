<?php

namespace App\Twig;

use App\Constants;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

final class AppExtension extends AbstractExtension
{
    private $requestStack;
    private $urlGenerator;

    /**
     * Default constructor
     */
    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('pilea_version', [$this, 'getVersion']),
            new TwigFunction('pilea_repo_git', [$this, 'getGitRepo']),
            new TwigFunction('pilea_documentation', [$this, 'getDocumentation']),
            new TwigFunction('pilea_help_graph', [$this, 'getGraphHelp']),
        ];
    }

    public function getVersion(): string
    {
        return Constants::VERSION;
    }

    public function getGitRepo(): string
    {
        return Constants::REPO_GIT;
    }

    public function getDocumentation(?string $path = ''): string
    {
        return \sprintf("%s%s%s", Constants::DOCUMENTATION, Constants::VERSION, $path);
    }

    public function getGraphHelp(?string $graph): string
    {
        $link =  self::getDocumentation(\sprintf("/utilisateur/graph.html#%s", $graph));
        return \sprintf('<a href="%s" target="_blank" class="help" title="Comment lire ce graphique ?"><i class="fas fa-question-circle"></i></a>', $link);
    }
}
