<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    /** @var ContainerBagInterface */
    private $parameters;

    /**
     * Default constructor
     */
    public function __construct(ContainerBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('aeneria_demo_mode', [$this, 'isDemoMode']),
            new TwigFunction('aeneria_welcome_message', [$this, 'getWelcomeMessage']),
            new TwigFunction('aeneria_matomo', [$this, 'getMatomo']),
        ];
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
