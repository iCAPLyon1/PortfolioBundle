<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Entity\Widget\FormationsWidget;
use Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidget;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill;
use Icap\PortfolioBundle\Entity\Widget\TextWidget;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Entity\Widget\UserInformationWidget;
use Icap\PortfolioBundle\Transformer\XmlToArray;

class Leap2aImporter implements  ImporterInterface
{
    const IMPORT_FORMAT = 'leap2a';
    const IMPORT_FORMAT_LABEL = 'Leap2a';

    protected $xmlToArrayTransformer;

    /**
     * @param XmlToArray $xmlToArrayTransformer
     */
    public function __construct(XmlToArray $xmlToArrayTransformer = null)
    {
        if (null === $xmlToArrayTransformer) {
            $xmlToArrayTransformer = new XmlToArray();
        }
        $this->xmlToArrayTransformer = $xmlToArrayTransformer;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return self::IMPORT_FORMAT;
    }

    /**
     * @return string
     */
    public function getFormatLabel()
    {
        return self::IMPORT_FORMAT_LABEL;
    }

    /**
     * @param string $content
     * @param User   $user
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio
     * @throws \InvalidArgumentException
     */
    public function import($content, User $user)
    {
        $arrayContent = $this->transformContent($content);

        $portfolio = $this->arrayToPortfolio($arrayContent, $user);

        return $portfolio;
    }

    /**
     * @param string $content
     *
     * @return array
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function transformContent($content)
    {
        return $this->xmlToArrayTransformer->transform($content);
    }

    /**
     * @param array $arrayContent
     * @param User  $user
     *
     * @return Portfolio
     * @throws \Exception
     */
    public function arrayToPortfolio(array $arrayContent, User $user)
    {
        $portfolio = new Portfolio();
        $portfolio->setUser($user);

        if (!isset($arrayContent['title'])) {
            throw new \Exception("Missing portfolio's title");
        }
        $titleWidget = new TitleWidget();
        $titleWidget->setTitle($arrayContent['title']['$']);

        $widgets = array();

        if (isset($arrayContent['entry'])) {
            $widgets = $this->retrieveWidgets($arrayContent['entry']);
        }

        $widgets[] = $titleWidget;

        $portfolio->setWidgets($widgets);

        return $portfolio;
    }

    /**
     * @param array $entries
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]
     * @throws \Exception
     */
    public function retrieveWidgets(array $entries)
    {
        $widgets = array();

        foreach ($entries as $entry) {
            $this->validateEntry($entry);

            $entryType = $entry['rdf:type']['@rdf:resource'];
            $entryCategory = isset($entry['category']) ? $entry['category']['@term'] : null;

            $widgetType = $this->getWidgetType($entryType, $entryCategory);

            if (null !== $widgetType) {
                switch($widgetType) {
                    case 'skills':
                            $widgets[] = $this->extractSkillsWidget($entries, $entry);
                        break;
                    case 'userInformation':
                            $widgets[] = $this->extractUserInformationWidget($entry);
                        break;
                    case 'formations':
                            $widgets[] = $this->extractFormationsWidget($entries, $entry);
                        break;
                    case 'text':
                            $widgets[] = $this->extractTextWidget($entry);
                        break;
                    case 'badges':
                        break;
                    default:
                        throw new \Exception(sprintf("Unknown widget type '%s'.", $widgetType));
                }
            }
        }

        return $widgets;
    }

    /**
     * @param array $entry
     *
     * @throws \Exception
     */
    protected function validateEntry(array $entry)
    {
        if (!isset($entry['rdf:type'])) {
            throw new \Exception('Entry type missing.');
        }
        if (!isset($entry['rdf:type']['@rdf:resource'])) {
            throw new \Exception('Entry type missing.');
        }
    }

    /**
     * @param string      $entryType
     * @param null|string $entryCategory
     *
     * @return null|string
     */
    protected function getWidgetType($entryType, $entryCategory)
    {
        return ('leap2:selection' === $entryType && null !== $entryCategory && 'Abilities' === $entryCategory) ? 'skills' : (
                    ('leap2:person' === $entryType) ? 'userInformation' : (
                        ('leap2:activity' && null !== $entryCategory && 'Education' === $entryCategory) ? 'formations' : (
                            ('leap2:entry' === $entryType) ? 'text' : (
                                ('leap2:selection' === $entryType && null !== $entryCategory && 'Grouping' === $entryCategory) ? 'badges' : null
                            )
                        )
                    )
                );
    }

    /**
     * @param array $entries
     * @param array $entry
     *
     * @return SkillsWidget
     * @throws \Exception
     */
    protected function extractSkillsWidget(array $entries, array $entry)
    {
        $skillsWidgetSkills = array();

        foreach ($entries as $subEntry) {
            $this->validateEntry($subEntry);

            if ('leap2:ability' === $subEntry['rdf:type']['@rdf:resource']) {
                $skillsWidgetSkill = new SkillsWidgetSkill();
                $skillsWidgetSkill->setName($subEntry['title']);

                $skillsWidgetSkills[] = $skillsWidgetSkill;
            }
        }

        $skillsWidget = new SkillsWidget();
        $skillsWidget
            ->setLabel($entry['title']['$'])
            ->setSkills($skillsWidgetSkills);

        return $skillsWidget;
    }

    /**
     * @param array $entries
     * @param array $entry
     *
     * @return FormationsWidget
     */
    protected function extractFormationsWidget(array $entries, array $entry)
    {
        $formationsWidgetResources = array();

        foreach ($entries as $subEntry) {
            $this->validateEntry($subEntry);

            if ('leap2:resource' === $subEntry['rdf:type']['@rdf:resource']) {
                $formationsWidgetResource = new FormationsWidgetResource();
                $formationsWidgetResource
                    ->setUriLabel($subEntry['title']['$'])
                    ->setUri($subEntry['uri']['$']);

                $formationsWidgetResources[] = $formationsWidgetResource;
            }
        }

        $formationsWidget = new FormationsWidget();
        $formationsWidget
            ->setLabel($entry['title']['$'])
            ->setResources($formationsWidgetResources);

        return $formationsWidget;
    }

    /**
     * @param array $entry
     *
     * @return UserInformationWidget
     */
    private function extractUserInformationWidget(array $entry)
    {
        $userInformationWidget = new UserInformationWidget();
        $userInformationWidget->setLabel($entry['title']['$']);

        foreach ($entry['leap2:persondata'] as $personData) {
            switch($personData['@leap2:field']) {
                case 'dob':
                    $userInformationWidget->setBirthDate(new \DateTime($personData['$']));
                    break;
                case 'other':
                    switch($personData['@leap2:label']) {
                        case 'city':
                            $userInformationWidget->setCity($personData['$']);
                            break;
                    }
                    break;
            }
        }

        return $userInformationWidget;
    }

    /**
     * @param array $entry
     *
     * @return UserInformationWidget
     */
    private function extractTextWidget(array $entry)
    {
        $TextWidget = new TextWidget();
        $TextWidget
            ->setLabel($entry['title']['$'])
            ->setText($entry['content']['$']);

        return $TextWidget;
    }
}
