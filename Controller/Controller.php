<?php

namespace Icap\PortfolioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->get('translator');
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    public function getSessionFlashbag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \Icap\PortfolioBundle\Form\Handler\PortfolioHandler
     */
    public function getPortfolioFormHandler()
    {
        return $this->get('icap_portfolio.form_handler.portfolio');
    }

    /**
     * @return \Icap\PortfolioBundle\Manager\PortfolioManager
     */
    public function getPortfolioManager()
    {
        return $this->get('icap_portfolio.manager.portfolio');
    }

    /**
     * @return \Icap\PortfolioBundle\Manager\PortfolioDispositionManager
     */
    public function getPortfolioDispositionManager()
    {
        return $this->get('icap_portfolio.manager.portfolio_disposition');
    }

    /**
     * @return \Icap\PortfolioBundle\Manager\WidgetsManager
     */
    public function getWidgetsManager()
    {
        return $this->get('icap_portfolio.manager.widgets');
    }

    /**
     * @return \Icap\PortfolioBundle\Manager\CommentsManager
     */
    public function getCommentsManager()
    {
        return $this->get('icap_portfolio.manager.comments');
    }

    /**
     * @return \Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler
     */
    public function getPlatformConfigHandler()
    {
        return $this->get('claroline.config.platform_config_handler');
    }

    /**
     * @return bool
     */
    public function getPortfolioToolAccess()
    {
        return $this->getPlatformConfigHandler()->getParameter('portfolio_url') === null;
    }

    public function checkPortfolioToolAccess()
    {
        if (!$this->getPortfolioToolAccess()) {
            throw $this->createNotFoundException();
        }
    }
}
