<?php
/**
 * This file is part of the Claroline Connect package
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/13/15
 */

namespace Icap\PortfolioBundle\Listener;

use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class NotificationUserParametersListener
 * @package Claroline\CoreBundle\Listener\Notification
 *
 * @DI\Service()
 */
class NotificationUserParametersListener
{
    /**
     * @param NotificationUserParametersEvent $event
     *
     * @DI\Observe("icap_notification_user_parameters_event")
     */
    public function onGetTypesForParameters(NotificationUserParametersEvent $event)
    {
        $event->addTypes("portfolio");
    }
} 