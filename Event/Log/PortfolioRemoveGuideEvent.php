<?php

namespace Icap\PortfolioBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\PortfolioGuide;

class PortfolioRemoveGuideEvent extends LogGenericEvent implements NotifiableInterface
{
    const ACTION = 'portfolio-remove_guide';

    /**
     * @var \Icap\PortfolioBundle\Entity\Portfolio
     */
    protected $portfolio;

    /**
     * @param Portfolio          $portfolio
     * @param PortfolioGuide $portfolioGuide
     */
    public function __construct(Portfolio $portfolio, PortfolioGuide $portfolioGuide)
    {
        $this->portfolio = $portfolio;

        $user = $portfolio->getUser();

        parent::__construct(
            self::ACTION,
            array(
                'owner' => array(
                    'lastName'  => $user->getLastName(),
                    'firstName' => $user->getFirstName()
                ),
                'portfolio' => array(
                    'id'    => $this->portfolio->getId(),
                    'title' => $this->portfolio->getTitle(),
                    'slug'  => $this->portfolio->getSlug()
                )
            ),
            $portfolioGuide->getUser(),
            null,
            null,
            null,
            null,
            $user
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_ADMIN);
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return boolean
     */
    public function getSendToFollowers()
    {
        return true;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return array($this->getReceiver()->getId());
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return array();
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }

    /**
     * Get iconKey string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return "portfolio";
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $receiver = $this->getReceiver();

        $notificationDetails = array(
            'portfolio' => array(
                'id'    => $this->portfolio->getId(),
                'title' => $this->portfolio->getTitle(),
                'slug'  => $this->portfolio->getSlug()
            ),
            'guide'  => array(
                'id'        => $receiver->getId(),
                'publicUrl' => $receiver->getPublicUrl(),
                'lastName'  => $receiver->getLastName(),
                'firstName' => $receiver->getFirstName()
            )
        );

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not
     *
     * @return boolean
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
