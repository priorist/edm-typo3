<?php

declare(strict_types=1);

namespace Priorist\EdmTypo3\ViewHelpers;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class ImageViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'img';

    /**
     * @var \TYPO3\CMS\Extbase\Service\ImageService
     */
    protected $imageService;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory, ImageService $imageService)
    {
        parent::__construct(); // Call the parent constructor to initialize $tag
        $this->resourceFactory = $resourceFactory;
        $this->imageService = $imageService;
    }

    const UPLOAD_DIRECTORY = 'edmImages';
    const TEMP_PREFIX = 'edm';

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', false);
        $this->registerArgument('filename', 'string', 'Override filename for local file.', false, '');
        $this->registerArgument('src', 'string', 'a path to a file, a combined FAL identifier or an uid (int). If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record. If you already got a FAL object, consider using the $image parameter instead', true, '');
        $this->registerArgument('width', 'string', 'width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('height', 'string', 'height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('minWidth', 'int', 'minimum width of the image');
        $this->registerArgument('minHeight', 'int', 'minimum height of the image');
        $this->registerArgument('maxWidth', 'int', 'maximum width of the image');
        $this->registerArgument('maxHeight', 'int', 'maximum height of the image');
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
    }

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @return string Rendered tag
     * @throws \Exception
     */
    public function render()
    {
        $src = (string)$this->arguments['src'];
        $filename = (string)$this->arguments['filename'];

        if ($src === '') {
            throw new Exception('You must either specify a string src', 1382284106);
        }

        $edmUrl = $this->getEdmBaseUrl();
        $fullUrl = $edmUrl . $src;

        // A URL was given as src, this is kept as is, and we can only scale
        if (preg_match('/^(https?:)?\/\//', $fullUrl)) {
            if (filter_var($fullUrl, FILTER_VALIDATE_URL)) {
                $storage = $this->resourceFactory->getDefaultStorage();
                if (!$storage) {
                    throw new Exception('No default storage found.', 1625585161);
                }
                if (!$storage->hasFolder(self::UPLOAD_DIRECTORY)) {
                    $storage->createFolder(self::UPLOAD_DIRECTORY);
                }

                $externalFile = GeneralUtility::getUrl($fullUrl);
                if ($externalFile === false) {
                    throw new Exception(sprintf('Failed to download external URL %s.', $fullUrl), 1473233519);
                }

                $tempFileName = tempnam(sys_get_temp_dir(), self::TEMP_PREFIX);
                $handle = fopen($tempFileName, 'w');
                fwrite($handle, $externalFile);
                fclose($handle);

                // Validate the temporary file
                if (!file_exists($tempFileName) || filesize($tempFileName) === 0) {
                    unlink($tempFileName);
                    throw new Exception(sprintf('Temporary file for %s is invalid or empty.', $fullUrl), 1625585162);
                }

                // Check MIME type and determine correct extension
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($tempFileName);
                $validMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($mimeType, $validMimeTypes, true)) {
                    unlink($tempFileName);
                    throw new Exception(sprintf('Downloaded file %s is not a valid image (MIME type: %s).', $fullUrl, $mimeType), 1625585163);
                }

                // Map MIME type to extension
                $mimeToExtension = [
                    'image/jpeg' => '.jpg',
                    'image/png' => '.png',
                    'image/gif' => '.gif',
                ];
                $fileExtension = $mimeToExtension[$mimeType] ?? '';

                // Decode URL to handle special characters like umlauts
                $decodedUrl = urldecode($fullUrl);
                $parsedUrl = parse_url($decodedUrl);
                $pathInfo = pathinfo($parsedUrl['path'] ?? '');

                if ($filename !== '') {
                    // Use provided filename, but remove any existing extension
                    $filenameBase = preg_replace('/\.[^.]+$/', '', $filename);
                    $newFileName = $filenameBase . $fileExtension;
                } else {
                    // Use URL basename, but replace extension with correct one
                    $basename = $pathInfo['basename'] ?? basename($decodedUrl);
                    $basenameWithoutExtension = preg_replace('/\.[^.]+$/', '', $basename);
                    $newFileName = $basenameWithoutExtension . $fileExtension;
                }

                // Sanitize filename to avoid invalid characters
                $newFileName = $storage->sanitizeFileName($newFileName);

                // Check if file already exists to avoid redundant downloads
                $uploadFolder = $storage->getFolder(self::UPLOAD_DIRECTORY);
                if ($uploadFolder->hasFile($newFileName)) {
                    $file = $uploadFolder->getFile($newFileName);
                } else {
                    try {
                        $file = $uploadFolder->addFile($tempFileName, $newFileName, 'replace');
                    } catch (\Exception $e) {
                        unlink($tempFileName);
                        throw new Exception(sprintf('Failed to add file %s to FAL: %s', $newFileName, $e->getMessage()), 1625585160);
                    }
                }
                unlink($tempFileName);

                // Use the File object directly
                $image = $this->imageService->getImage($file->getCombinedIdentifier(), null, false);

                $processingInstructions = [
                    'width' => $this->arguments['width'],
                    'height' => $this->arguments['height'],
                    'minWidth' => $this->arguments['minWidth'],
                    'minHeight' => $this->arguments['minHeight'],
                    'maxWidth' => $this->arguments['maxWidth'],
                    'maxHeight' => $this->arguments['maxHeight'],
                    'crop' => null,
                ];

                $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
                $imageUri = $this->imageService->getImageUri($processedImage, $this->arguments['absolute']);

                $this->tag->addAttribute('src', $imageUri);
                $this->tag->addAttribute('width', $processedImage->getProperty('width'));
                $this->tag->addAttribute('height', $processedImage->getProperty('height'));

                // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
                if (empty($this->arguments['alt'])) {
                    $this->tag->addAttribute('alt', $image->hasProperty('alternative') ? $image->getProperty('alternative') : '');
                }
                // Add title-attribute from property if not already set and the property is not an empty string
                $title = (string)($image->hasProperty('title') ? $image->getProperty('title') : '');
                if (empty($this->arguments['title']) && $title !== '') {
                    $this->tag->addAttribute('title', $title);
                }

                return $this->tag->render();
            } else {
                throw new Exception(sprintf('Invalid URL: %s', $fullUrl), 1473233520);
            }
        }

        // Fallback in case the src is not a valid URL
        throw new Exception(sprintf('The provided src "%s" is not a valid URL.', $fullUrl), 1473233521);
    }

    protected function getEdmBaseUrl(): string
    {
        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $fullTypoScript = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $settings = $fullTypoScript['plugin.']['tx_edmtypo3.']['settings.'] ?? [];
        return $settings['edm.']['url'] ?? '';
    }
}
