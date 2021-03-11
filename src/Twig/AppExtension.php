<?php

namespace App\Twig;

use App\Entity\Feed;
use App\Entity\User;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    /** @var ContainerBagInterface */
    private $parameters;

    /** @var EnedisDataConnectProvider */
    private $enedisDataConnectProvider;

    /**
     * Default constructor
     */
    public function __construct(
        ContainerBagInterface $parameters,
        EnedisDataConnectProvider $enedisDataConnectProvider
    ) {
        $this->parameters = $parameters;
        $this->enedisDataConnectProvider = $enedisDataConnectProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('aeneria_version', [$this, 'getVersion']),
            new TwigFunction('aeneria_repo_git', [$this, 'getGitRepo']),
            new TwigFunction('aeneria_documentation', [$this, 'getDocumentation']),
            new TwigFunction('aeneria_help_icon_link', [$this, 'getHelpIconLink']),
            new TwigFunction('aeneria_help_graph', [$this, 'getGraphHelp']),
            new TwigFunction('aeneria_user_max_places', [$this, 'getUserMaxPlaces']),
            new TwigFunction('aeneria_user_can_share_place', [$this, 'canUserSharePlace']),
            new TwigFunction('aeneria_user_can_fetch', [$this, 'canUserFetchData']),
            new TwigFunction('aeneria_user_can_export', [$this, 'canUserExportData']),
            new TwigFunction('aeneria_user_can_import', [$this, 'canUserImportData']),
            new TwigFunction('aeneria_place_can_be_public', [$this, 'canPlaceBePublic']),
            new TwigFunction('aeneria_user_can_add_place', [$this, 'canUserAddPlace']),
            new TwigFunction('aeneria_linky_get_description', [$this, 'getLinkyDescription']),
            new TwigFunction('aeneria_demo_mode', [$this, 'isDemoMode']),
            new TwigFunction('aeneria_welcome_message', [$this, 'getWelcomeMessage']),
            new TwigFunction('aeneria_matomo', [$this, 'getMatomo']),
        ];
    }

    public function getVersion(): string
    {
        return $this->parameters->get('aeneria.version');
    }

    public function getGitRepo(): string
    {
        return $this->parameters->get('aeneria.repo_git');
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

    public function getUserMaxPlaces(): ?int
    {
        $userMaxPlaces = (int) $this->parameters->get('aeneria.user.max_places');

        return -1 === $userMaxPlaces ? null : $userMaxPlaces;
    }

    public function canUserSharePlace(): bool
    {
        return (bool) $this->parameters->get('aeneria.user.can_share_place');
    }

    public function canUserFetchData(): bool
    {
        return (bool) $this->parameters->get('aeneria.user.can_fetch');
    }

    public function canUserExportData(): bool
    {
        return (bool) $this->parameters->get('aeneria.user.can_export');
    }

    public function canUserImportData(): bool
    {
        return (bool) $this->parameters->get('aeneria.user.can_import');
    }

    public function canPlaceBePublic(): bool
    {
        return (bool) $this->parameters->get('aeneria.place_can_be_public');
    }

    public function canUserAddPlace(User $user): bool
    {
        if ($userMaxPlaces = $this->getUserMaxPlaces()) {
            return \count($user->getPlaces()) < $userMaxPlaces;
        }

        return true;
    }

    public function isDemoMode(): bool
    {
        return $this->parameters->get('aeneria.demo_mode');
    }

    public function getWelcomeMessage(): string
    {
        return $this->parameters->get('aeneria.welcome_message');
    }

    public function getLinkyDescription(Feed $feed): ?string
    {
        if (Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT === $feed->getFeedDataProviderType()) {
            $address = $this->enedisDataConnectProvider->getAddressFrom($feed);

            return $address . ' (PDL : ' . $address->getUsagePointId() . ')';
        }

        return null;
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
