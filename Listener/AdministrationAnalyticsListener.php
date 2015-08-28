<?php

namespace Icap\PortfolioBundle\Listener;

use Claroline\CoreBundle\Event\Analytics\PlatformContentItemDetailsEvent;
use Claroline\CoreBundle\Event\Analytics\PlatformContentItemEvent;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Manager\PortfolioManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class AdministrationAnalyticsListener
{
    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PortfolioManager
     */
    private $portfolioManager;

    /**
     * @DI\InjectParams({
     *     "twig" = @DI\Inject("templating"),
     *     "translator" = @DI\Inject("translator"),
     *     "portfolioManager" = @DI\Inject("icap_portfolio.manager.portfolio")
     * })
     */
    public function __construct(TwigEngine $twig, TranslatorInterface $translator, PortfolioManager $portfolioManager)
    {
        $this->twig = $twig;
        $this->translator = $translator;
        $this->portfolioManager = $portfolioManager;
    }

    /**
     * @param PlatformContentItemEvent $event
     *
     * @DI\Observe("administration_analytics_platform_content_item_add")
     */
    public function onPlatformContentItemAdd(PlatformContentItemEvent $event)
    {
        $event->addItem([
            'item' => 'portfolio',
            'label' => $this->translator->trans('portfolio', [], 'icap_portfolio'),
            'value' => $this->portfolioManager->countAll()
        ]);
    }

    /**
     * @param PlatformContentItemDetailsEvent $event
     *
     * @DI\Observe("administration_analytics_platform_content_item_details_portfolio")
     */
    public function onPlatformContentItemDetailsPortfolio(PlatformContentItemDetailsEvent $event)
    {
        $countPortfolio = $this->portfolioManager->countAll();
        $countDeletedPortfolio = $this->portfolioManager->countAllDeleted();

        $countClosedPortfolio = 0;
        $countOpenPortfolio = 0;
        $countPrivatePortfolio = 0;
        $countPlatformPortfolio = 0;

        $countPortfolioByVisibilityStatuss = $this->portfolioManager->countAllByVisibilityStatus();
        foreach ($countPortfolioByVisibilityStatuss as $countPortfolioByVisibilityStatus) {
            switch($countPortfolioByVisibilityStatus['visibility']) {
                case Portfolio::VISIBILITY_NOBODY:
                    $countClosedPortfolio = $countPortfolioByVisibilityStatus['number'];
                    break;
                case Portfolio::VISIBILITY_EVERYBODY:
                    $countOpenPortfolio = $countPortfolioByVisibilityStatus['number'];
                    break;
                case Portfolio::VISIBILITY_USER:
                    $countPrivatePortfolio = $countPortfolioByVisibilityStatus['number'];
                    break;
                case Portfolio::VISIBILITY_PLATFORM_USER:
                    $countPlatformPortfolio = $countPortfolioByVisibilityStatus['number'];
                    break;
                default:
                    throw new \Exception(); //not supposed to happen, but hey who knows ;-)
            }
        }

        $event->setContent($this->twig->render('IcapPortfolioBundle:analytics:platform_content_item_details.html.twig', [
            'countPortfolio' => $countPortfolio,
            'countClosedPortfolio' => $countClosedPortfolio,
            'countOpenPortfolio' => $countOpenPortfolio,
            'countPrivatePortfolio' => $countPrivatePortfolio,
            'countPlatformPortfolio' => $countPlatformPortfolio,
            'countDeletedPortfolio' => $countDeletedPortfolio
        ]));
        $event->stopPropagation();
    }
}
