<?php

namespace Icap\PortfolioBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Icap\PortfolioBundle\Entity\Portfolio;

/**
 * @ORM\Table(name="icap__portfolio_abstract_widget")
 * @ORM\Entity(repositoryClass="Icap\PortfolioBundle\Repository\Widget\AbstractWidgetRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="widget_type", type="string")
 */
abstract class AbstractWidget
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $label;

    /**
     * @var \Datetime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \Datetime $updatedAt
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \Icap\PortfolioBundle\Entity\PortfolioWidget[]
     *
     * @ORM\OneToMany(targetEntity="Icap\PortfolioBundle\Entity\PortfolioWidget", mappedBy="widget")
     */
    protected $portfolioWidgets;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     */
    protected $widgetType = null;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return AbstractWidget
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return AbstractWidget
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return AbstractWidget
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * @param string $widgetType
     *
     * @return $this
     */
    public function setWidgetType($widgetType)
    {
        $this->widgetType = $widgetType;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getCommonData()
    {
        return array(
            'id'     => $this->getId(),
            'type'   => $this->getWidgetType(),
            'label'  => $this->getLabel()
        );
    }

    /**
     * @return array
     */
    abstract public function getData();

    /**
     * @return array
     */
    abstract public function getEmpty();
}
