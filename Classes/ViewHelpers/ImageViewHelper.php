<?php

namespace Priorist\EdmTypo3\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ImageViewHelper extends AbstractViewHelper
{
    const DESTINATION_DIRECTORY = 'edm/images';

    public function initializeArguments()
    {
        $this->registerArgument('image', 'array', 'The EDM image object.', true);
        $this->registerArgument('returnFile', 'bool', 'Return the File object instead of the URL.', false, false);
    }

    public function render()
    {
        $image = $this->arguments['image'] ?? null;
        $returnFile = $this->arguments['returnFile'] ?? false;

        if (!$image || !isset($image['original_filename'], $image['url'])) {
            return $returnFile ? null : '';
        }

        $file = $this->saveImage($image['original_filename'], $image['url']);
        if ($returnFile) {
            return $file;
        }
        return $file ? $file->getPublicUrl() : '';
    }

    protected function saveImage(string $name, string $url): ?\TYPO3\CMS\Core\Resource\File
    {
        // Fetch the external file content
        $edmUrl = $this->getEdmBaseUrl();
        $externalFile = @file_get_contents($edmUrl . $url);

        if ($externalFile === false) {
            return null;
        }

        // Initialize storage
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storage = $storageRepository->getDefaultStorage();

        if (!$storage) {
            return null;
        }

        // Ensure destination folder exists
        $targetFolderIdentifier = 'fileadmin/' . self::DESTINATION_DIRECTORY;
        if (!$storage->hasFolder(self::DESTINATION_DIRECTORY)) {
            $storage->createFolder(self::DESTINATION_DIRECTORY);
        }

        // Get the target folder object
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            $targetFolder = $resourceFactory->getFolderObjectFromCombinedIdentifier($targetFolderIdentifier);
        } catch (Exception $e) {
            return null;
        }

        // Sanitize filename
        $sanitizedFilename = $storage->sanitizeFileName($name);

        // Check if file already exists
        try {
            if ($targetFolder->hasFile($sanitizedFilename)) {
                return $targetFolder->getFile($sanitizedFilename);
            }
        } catch (Exception $e) {
            // File does not exist or error occurred, proceed to save
        }

        // Save the file using FAL
        try {
            $tempFile = GeneralUtility::tempnam('edm_image_');
            file_put_contents($tempFile, $externalFile);

            // Add file to FAL
            $file = $storage->addFile(
                $tempFile,
                $targetFolder,
                $sanitizedFilename
            );

            // Clean up temporary file
            GeneralUtility::unlink_tempfile($tempFile);

            return $file;
        } catch (Exception $e) {
            return null;
        }
    }

    protected function getEdmBaseUrl(): string
    {
        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $fullTypoScript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $settings = $fullTypoScript['plugin.']['tx_edmtypo3.']['settings.'] ?? [];
        return $settings['edm.']['url'] ?? '';
    }
}
