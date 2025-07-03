<?php

namespace Priorist\EdmTypo3\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

class EventIdMapper implements StaticMappableAspectInterface
{
  public function generate(string $value): ?string
  {
    return $this->isValidId($value) ? $value : null;
  }

  public function resolve(string $value): ?string
  {
    return $this->isValidId($value) ? $value : null;
  }

  protected function isValidId(string $id): bool
  {
    // TODO: actually implement check via API if event id is existant in EDM
    // Minimal validation for testing
    return preg_match('/^[a-zA-Z0-9\-]+$/', $id) === 1;
  }
}
