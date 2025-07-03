<?php

namespace Priorist\EdmTypo3\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

class EventSlugMapper implements StaticMappableAspectInterface
{
  public function generate(string $value): ?string
  {
    return $this->isValidSlug($value) ? $value : null;
  }

  public function resolve(string $value): ?string
  {
    return $this->isValidSlug($value) ? $value : null;
  }

  protected function isValidSlug(string $slug): bool
  {
    // TODO: actually implement check via API if event slug is existant in EDM
    // Minimal validation for testing
    return preg_match('/^[a-zA-Z0-9\-]+$/', $slug) === 1;
  }
}
