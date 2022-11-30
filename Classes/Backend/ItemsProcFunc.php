<?php

namespace Priorist\EdmTypo3\Backend;

use Priorist\EDM\Client\Client;

class ItemsProcFunc
{
    protected $settings;
    /**
     * Get EDM Parent Categories
     */
    public function getParentCategories(array $config)
    {
        $categories = $this->getClient()->getRestClient()->fetchCollection('categories', []);
        $categoriesArray = $categories->toArray();
        $returnCategories = [];

        foreach ($categoriesArray['results'] as $category) {
            if (count($category['child_categories']) > 0) {
                $returnCategories[] = [
                    $category['name'],
                    $category['id']
                ];
            }
        }

        $config['items'] = $returnCategories;
    }

    /**
     * Get EDM Categories
     */
    public function getCategories(array $config)
    {
        $categories = $this->getClient()->getRestClient()->fetchCollection('categories', []);
        $categoriesArray = $categories->toArray();
        $returnCategories = [];

        foreach ($categoriesArray['results'] as $category) {
            if ($category['parent_category'] !== null) {
                $returnCategories[] = [
                    $category['parent_category_name'] . ' / ' . $category['name'],
                    $category['id']
                ];
            } else if (count($category['child_categories']) > 0) {
                $returnCategories[] = [
                    $category['name'] . ' (inkl. Unterkategorien)',
                    $category['id']
                ];
            } else {
                $returnCategories[] = [
                    $category['name'],
                    $category['id']
                ];
            }
        }

        $config['items'] = $returnCategories;
    }

    /**
     * Get EDM Staff Members
     */
    public function getStaffMembers(array $config)
    {
        $query = $this->getClient()->getRestClient()->fetchCollection('staff_members', []);
        $queryArray = $query->toArray();
        $returnData = [];

        foreach ($queryArray['results'] as $queryData) {
            $name = $queryData['first_name'] . ' ' . $queryData['last_name'];
            $returnData[] = [
                $name,
                $queryData['id']
            ];
        }

        $config['items'] = $returnData;
    }

    /**
     * Get EDM Partners
     */
    public function getContexts(array $config)
    {
        $this->getAndFormatData($config, 'contexts');
    }


    /**
     * Get EDM Event Types
     */
    public function getEventTypes(array $config)
    {
        $this->getAndFormatData($config, 'event_types');
    }

    /**
     * Get EDM Locations
     */
    public function getLocations(array $config)
    {
        $this->getAndFormatData($config, 'event_locations');
    }

    private function getAndFormatData(array $config, String $apiPath)
    {
        $query = $this->getClient()->getRestClient()->fetchCollection($apiPath, []);
        $queryArray = $query->toArray();
        $returnData[] = [
            'alle',
            0
        ];

        foreach ($queryArray['results'] as $queryData) {
            $returnData[] = [
                $queryData['name'],
                $queryData['id']
            ];
        }

        $config['items'] = $returnData;
    }

    private function getClient()
    {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $fullTypoScript = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $settings = $fullTypoScript['plugin.']['tx_edm.']['settings.'];
        $url = $settings['edm.']['url'];
        $clientId = $settings['edm.']['auth.']['anonymous.']['clientId'];
        $clientSecret = $settings['edm.']['auth.']['anonymous.']['clientSecret'];
        $this->client = new Client($url, $clientId, $clientSecret);

        return $this->client;
    }
}
