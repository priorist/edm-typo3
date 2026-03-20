<?php

declare(strict_types=1);

namespace Priorist\EdmTypo3\Service;

use Exception;
use Priorist\EDM\Client\Client;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class EdmClientService
{
  const REGISTRY_NAMESPACE = 'tx_edmtypo3';
  const REGISTRY_KEY = 'cache_access-token';
  const REGISTRY_EXPIRATION = 12;

  public function __construct(
    private readonly ExtensionConfiguration $extensionConfiguration,
    private readonly Registry $registry,
  ) {}

  public function getClient(): Client
  {
    $config = $this->extensionConfiguration->get('edm-typo3');

    $url = $config['edm']['url'] ?? null;
    $clientId = $config['edm']['auth']['anonymous']['clientId'] ?? null;
    $clientSecret = $config['edm']['auth']['anonymous']['clientSecret'] ?? null;

    if (!$url || !$clientId || !$clientSecret) {
      throw new Exception('EDM extension configuration is incomplete.');
    }

    $client = new Client($url, $clientId, $clientSecret);

    $storedAccessToken = $this->getStoredAccessToken();

    if ($storedAccessToken !== null) {
      $client->setAccessToken($storedAccessToken);
    } else {
      $this->storeAccessToken($client->getAccessToken());
    }

    return $client;
  }

  private function storeAccessToken($accessToken): void
  {
    $this->registry->set(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY, [
      'access_token' => $accessToken,
      'timestamp' => time(),
    ]);
  }

  private function getStoredAccessToken()
  {
    $cached = $this->registry->get(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY);

    if ($cached === false) {
      return null;
    }

    $expirationInSeconds = self::REGISTRY_EXPIRATION * 60 * 60;

    if ((time() - $expirationInSeconds) >= $cached['timestamp']) {
      $this->registry->remove(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY);
      return null;
    }

    return $cached['access_token'];
  }
}
