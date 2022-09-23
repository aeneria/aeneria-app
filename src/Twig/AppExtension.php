<?php

namespace App\Twig;

use App\Entity\Feed;
use App\Entity\User;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Services\FeedDataProvider\GrdfAdictProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    /** @var ContainerBagInterface */
    private $parameters;

    /** @var EnedisDataConnectProvider */
    private $enedisDataConnectProvider;

    /** @var GrdfAdictProvider */
    private $grdfAdictProvider;

    /**
     * Default constructor
     */
    public function __construct(
        ContainerBagInterface $parameters,
        EnedisDataConnectProvider $enedisDataConnectProvider,
        GrdfAdictProvider $grdfAdictProvider
    ) {
        $this->parameters = $parameters;
        $this->enedisDataConnectProvider = $enedisDataConnectProvider;
        $this->grdfAdictProvider = $grdfAdictProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('aeneria_documentation', [$this, 'getDocumentation']),
            new TwigFunction('aeneria_help_icon_link', [$this, 'getHelpIconLink']),
            new TwigFunction('aeneria_help_graph', [$this, 'getGraphHelp']),
            new TwigFunction('aeneria_demo_mode', [$this, 'isDemoMode']),
            new TwigFunction('aeneria_welcome_message', [$this, 'getWelcomeMessage']),
            new TwigFunction('aeneria_matomo', [$this, 'getMatomo']),
        ];
    }

    public function getDocumentation(?string $path = ''): string
    {
        $documentationBaseUri = $this->parameters->get('aeneria.documentation');
        $version = $this->parameters->get('aeneria.version');

        return \sprintf("%s%s/%s", $documentationBaseUri, $version, $path);
    }

    public function getHelpIconLink(?string $path, ?string $title = null, ?string $class = null): string
    {
        $link = $this->getDocumentation(\sprintf("%s", $path));

        return \sprintf(
            '<a href="%s" target="_blank" class="%s" title="%s"><i class="fas fa-question-circle"></i></a>',
            $link,
            $class,
            $title
        );
    }

    public function getGraphHelp(?string $graph): string
    {
        $path = \sprintf("utilisateur/graph.html#%s", $graph);

        return $this->getHelpIconLink($path, "Comment lire ce graphique ?", "help");
    }

    public function isDemoMode(): bool
    {
        return $this->parameters->get('aeneria.demo_mode');
    }

    public function getWelcomeMessage(): string
    {
        return $this->parameters->get('aeneria.welcome_message');
    }

    public function getMatomo(): ?string
    {
        if (
            ($matomoUrl = $this->parameters->get('aeneria.matomo.url')) &&
            ($matomoSiteId = $this->parameters->get('aeneria.matomo.site_id'))
        ) {
            return <<<EOL
            <!-- Matomo -->
            <script type="text/javascript">
            var _paq = window._paq = window._paq || [];
            /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
                var u="//$matomoUrl/";
                _paq.push(['setTrackerUrl', u+'matomo.php']);
                _paq.push(['setSiteId', '$matomoSiteId']);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
            })();
            </script>
            <!-- End Matomo Code -->
            EOL;
        }

        return null;
    }
}
