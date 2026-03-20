<?php

return [
  'frontend' => [
    'priorist/edm-typo3/add-to-user-list' => [
      'target' => \Priorist\EdmTypo3\Middleware\UserListMiddleware::class,
      'before' => [
        'typo3/cms-frontend/static-route-resolver',
      ],
      'after' => [
        'typo3/cms-core/normalized-params-attribute',
      ],
    ],
  ],
];
