<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Pager\PagerFactory;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioUser;
use Icap\PortfolioBundle\Entity\PortfolioGuide;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Entity\Widget\WidgetNode;
use Icap\PortfolioBundle\Event\Log\PortfolioAddGuideEvent;
use Icap\PortfolioBundle\Event\Log\PortfolioAddViewerEvent;
use Icap\PortfolioBundle\Event\Log\PortfolioRemoveGuideEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service("icap_portfolio.manager.portfolio")
 */
class PortfolioManager
{
    const PORTFOLIO_OPENING_MODE_VIEW     = 'view';
    const PORTFOLIO_OPENING_MODE_EVALUATE = 'evaluate';
    const PORTFOLIO_OPENING_MODE_EDIT     = 'edit';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    /**
     * @var  \Icap\PortfolioBundle\Manager\WidgetsManager
     */
    protected $widgetsManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "widgetsManager"  = @DI\Inject("icap_portfolio.manager.widgets"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     * })
     */
    public function __construct(EntityManager $entityManager, FormFactory $formFactory, WidgetsManager $widgetsManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager   = $entityManager;
        $this->formFactory     = $formFactory;
        $this->widgetsManager  = $widgetsManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Portfolio   $portfolio
     * @param TitleWidget $titleWidget
     *
     * @throws \InvalidArgumentException
     */
    public function addPortfolio(Portfolio $portfolio, TitleWidget $titleWidget)
    {
        $titleWidget->setPortfolio($portfolio);

        $this->entityManager->persist($titleWidget);

        $this->persistPortfolio($portfolio);
    }

    /**
     * @param TitleWidget $titleWidget
     * @param bool      $refreshUrl
     */
    public function renamePortfolio(TitleWidget $titleWidget, $refreshUrl = false)
    {
        if ($refreshUrl) {
            $titleWidget->setSlug(null);
        }

        $this->entityManager->persist($titleWidget);
        $this->entityManager->flush();
    }

    /**
     * @param Portfolio                               $portfolio
     * @param Collection|PortfolioUser[]              $originalPortfolioUsers
     * @param \Doctrine\Common\Collections\Collection $originalPortfolioGroups
     */
    public function updateVisibility(Portfolio $portfolio, Collection $originalPortfolioUsers, Collection $originalPortfolioGroups)
    {
        $portfolioUsers                = $portfolio->getPortfolioUsers();
        $addedPortfolioViewersToNotify = array();

        foreach ($portfolioUsers as $portfolioUser) {
            if ($originalPortfolioUsers->contains($portfolioUser)) {
                $originalPortfolioUsers->removeElement($portfolioUser);
            }
            else {
                $addedPortfolioViewersToNotify[] = $portfolioUser;
            }
        }

        foreach ($originalPortfolioUsers as $originalPortfolioUser) {
            $this->entityManager->remove($originalPortfolioUser);
        }

        $portfolioGroups = $portfolio->getPortfolioGroups();

        foreach ($portfolioGroups as $portfolioGroup) {
            if ($originalPortfolioGroups->contains($portfolioGroup)) {
                $originalPortfolioGroups->removeElement($portfolioGroup);
            }
        }

        foreach ($originalPortfolioGroups as $originalPortfolioGroup) {
            $this->entityManager->remove($originalPortfolioGroup);
        }

        $this->persistPortfolio($portfolio);

        foreach ($addedPortfolioViewersToNotify as $addedPortfolioViewer) {
            $portfolioAddViewerEvent = new PortfolioAddViewerEvent($portfolio, $addedPortfolioViewer);
            $this->dispatch($portfolioAddViewerEvent);
        }
    }

    /**
     * @param Portfolio                               $portfolio
     * @param Collection|PortfolioGuide[]         $originalPortfolioGuides
     */
    public function updateGuides(Portfolio $portfolio, Collection $originalPortfolioGuides)
    {
        $portfolioGuides                = $portfolio->getPortfolioGuides();
        /** @var PortfolioGuide[] $addedPortfolioGuidesToNotify */
        $addedPortfolioGuidesToNotify   = array();
        /** @var PortfolioGuide[] $removedPortfolioGuidesToNotify */
        $removedPortfolioGuidesToNotify = array();

        foreach ($portfolioGuides as $portfolioGuide) {
            if ($originalPortfolioGuides->contains($portfolioGuide)) {
                $originalPortfolioGuides->removeElement($portfolioGuide);
            }
            else {
                $addedPortfolioGuidesToNotify[] = $portfolioGuide;
            }
        }

        foreach ($originalPortfolioGuides as $originalPortfolioGuide) {
            $this->entityManager->remove($originalPortfolioGuide);
            $removedPortfolioGuidesToNotify[] = $originalPortfolioGuide;
        }

        $this->persistPortfolio($portfolio);

        foreach ($addedPortfolioGuidesToNotify as $addedPortfolioGuide) {
            $portfolioAddGuideEvent = new PortfolioAddGuideEvent($portfolio, $addedPortfolioGuide);
            $this->dispatch($portfolioAddGuideEvent);
        }
        foreach ($removedPortfolioGuidesToNotify as $removedPortfolioGuide) {
            $portfolioAddGuideEvent = new PortfolioRemoveGuideEvent($portfolio, $removedPortfolioGuide);
            $this->dispatch($portfolioAddGuideEvent);
        }
    }

    /**
     * @param LogGenericEvent $event
     */
    protected function dispatch(LogGenericEvent $event)
    {
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @param Portfolio $portfolio
     */
    public function deletePortfolio(Portfolio $portfolio)
    {
        $this->entityManager->remove($portfolio);
        $this->entityManager->flush();
    }

    /**
     * @param Portfolio $portfolio
     */
    private function persistPortfolio(Portfolio $portfolio)
    {
        $this->entityManager->persist($portfolio);
        $this->entityManager->flush();
    }

    /**
     * @param Portfolio $portfolio
     *
     * @return array
     */
    public function getPortfolioData(Portfolio $portfolio)
    {
        /** @var \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[] $widgets */
        $widgets  = $portfolio->getWidgets();
        /** @var \Icap\PortfolioBundle\Entity\PortfolioComment[] $comments */
        $comments = $portfolio->getComments();

        $data = array(
            'id'          => $portfolio->getId(),
            'disposition' => $portfolio->getDisposition()
        );

        foreach ($widgets as $widget) {
            $data['widgets'][] = $this->widgetsManager->getWidgetData($widget);
        }

        $commentsDatas = array();

        foreach ($comments as $comment) {
            $commentsDatas[] = $comment->getData();
        }
        $data['comments'] = $commentsDatas;

        return $data;
    }

    /**
     * @param object $data
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function getForm($data)
    {
        return $this->formFactory->create('icap_portfolio_portfolio_form', $data);
    }

    /**
     * @param Portfolio $portfolio
     * @param array     $parameters
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function handle(Portfolio $portfolio, array $parameters)
    {
        $data           = array();
        $oldDisposition = $portfolio->getDisposition();

        $form = $this->getForm($portfolio);
        $form->submit($parameters);

        if ($form->isValid()) {
            $newDisposition = $portfolio->getDisposition();

            $this->entityManager->persist($portfolio);
            $this->entityManager->flush();

            if ($oldDisposition !== $newDisposition) {
                $this->dispositionUpdated($portfolio);
            }

            $data = array(
                'id'          => $portfolio->getId(),
                'disposition' => $portfolio->getDisposition(),
            );

            $data = $this->getPortfolioData($portfolio);

            return $data;
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param Portfolio $portfolio
     */
    public function dispositionUpdated(Portfolio $portfolio)
    {
        $widgets   = $portfolio->getWidgets();
        $maxColumn = $portfolio->getDisposition() + 1;

        foreach ($widgets as $widget) {
            if ($maxColumn < $widget->getColumn()) {
                $widget->setColumn($maxColumn);
            }
            $this->entityManager->persist($widget);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Portfolio $portfolio
     * @param User|null $user
     * @param bool      $isAdmin
     *
     * @return string|null
     */
    public function getOpeningMode(Portfolio $portfolio, $user, $isAdmin = false)
    {
        $openingMode = null;

        if (null !== $user) {
            if ($user === $portfolio->getUser() || $isAdmin) {
                $openingMode = self::PORTFOLIO_OPENING_MODE_EDIT;
            }
            elseif ($portfolio->hasGuide($user)) {
                $openingMode = self::PORTFOLIO_OPENING_MODE_EVALUATE;
            }
            elseif ($portfolio->visibleToUser($user)) {
                $openingMode = self::PORTFOLIO_OPENING_MODE_VIEW;
            }
        }
        elseif (Portfolio::VISIBILITY_EVERYBODY === $portfolio->getVisibility()) {
            $openingMode = self::PORTFOLIO_OPENING_MODE_VIEW;
        }

        return $openingMode;
    }
}