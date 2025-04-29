<?php

namespace Priorist\EdmTypo3\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ImageViewHelper extends AbstractViewHelper
{
    const DESTINATION_DIRECTORY = 'edm/images';

    public function initializeArguments()
    {
        $this->registerArgument('image', 'array', 'The EDM image object.', true);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $image = $arguments['image'];

        if (!isset($image)) {
            return '';
        }

        $imageFilename = $image['original_filename'];
        $imageUrl = $image['url'];
        $imageViewHelper = new ImageViewHelper();

        return $imageViewHelper->saveImage($imageFilename, $imageUrl);
    }

    protected function saveImage($name, $url)
    {
        $edmUrl = self::getEdmBaseUrl();
        $externalFile = file_get_contents($edmUrl . $url);
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storage = $storageRepository->getDefaultStorage();

        if (!$storage->hasFolder(self::DESTINATION_DIRECTORY)) {
            $storage->createFolder(self::DESTINATION_DIRECTORY);
        }

        // check if file was already downloaded
        if ($externalFile) {
            $publicPath = Environment::getPublicPath();
            $filename = crc32($externalFile) . '-' . urlencode($name);

            $fileadminPath = '/fileadmin/' . self::DESTINATION_DIRECTORY;

            $relativePath = $fileadminPath . '/' . $filename;
            $imageFolder = $publicPath . $fileadminPath;
            $fullPath = $imageFolder . '/' . $filename;

            if (file_put_contents($fullPath, $externalFile)) {
                return $relativePath;
            }
        }

        return null;
    }

    protected function getEdmBaseUrl()
    {
        $configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $fullTypoScript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $settings = $fullTypoScript['plugin.']['tx_edmtypo3.']['settings.'];
        $url = $settings['edm.']['url'];

        return $url;
    }
}
