<?php

declare(strict_types=1);

namespace Priorist\EdmTypo3\Middleware;

use Priorist\EdmTypo3\Service\EdmClientService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class UserListMiddleware implements MiddlewareInterface
{
  private const MIN_ELAPSED_SECONDS = 2.0;

  public function __construct(
    private readonly ResponseFactoryInterface $responseFactory,
    private readonly EdmClientService $edmClientService,
  ) {}

  public function process(
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
  ): ResponseInterface {

    $path = $request->getUri()->getPath();

    try {
      if ($path === '/edm/add-to-user-list') {
        return $this->handleAddToUserList($request);
      }

      if ($path === '/edm/fetch-user-lists') {
        return $this->handleFetchUserLists($request);
      }

      return $handler->handle($request);
    } catch (\Throwable $e) {
      return $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  private function handleAddToUserList(ServerRequestInterface $request): ResponseInterface
  {
    if ($request->getMethod() !== 'POST') {
      return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }

    $body = json_decode((string)$request->getBody(), true);

    if (!is_array($body)) {
      return $this->jsonResponse(['error' => 'Invalid JSON payload'], 400);
    }

    /*
     * EDM bot-checks user_list_data/bulk_create but not users, so the check has to be repeated
     * here - otherwise a bot submission creates the user and only loses the list. Answer like a
     * success so the caller learns nothing.
     */
    if ($this->isBotSubmission($body['website'] ?? null, $body['form_loaded_at'] ?? null)) {
      return $this->jsonResponse(['status' => 'ok']);
    }

    if (!is_array($body['lists'] ?? null) || empty($body['lists'])) {
      return $this->jsonResponse(['error' => 'lists must be a non-empty array'], 400);
    }

    try {
      $success = $this->handlePayload($body);
    } catch (\Throwable $e) {
      return $this->jsonResponse(['error' => $e->getMessage()], 500);
    }

    if ($success) {
      return $this->jsonResponse(['status' => 'ok']);
    }

    return $this->jsonResponse(['error' => 'Request failed, check logs for details'], 500);
  }

  private function handleFetchUserLists(ServerRequestInterface $request): ResponseInterface
  {
    if ($request->getMethod() !== 'GET') {
      return $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }

    $queryParams = $request->getQueryParams();
    $listIds = array_filter(array_map('intval', explode(',', $queryParams['list_ids'] ?? '')));

    if (empty($listIds)) {
      return $this->jsonResponse(['error' => 'list_ids is required'], 400);
    }

    try {
      $client = $this->edmClientService->getClient();
      $userLists = $client->userLists->findByIds($listIds);
    } catch (\Throwable $e) {
      return $this->jsonResponse(['error' => $e->getMessage()], 500);
    }

    return $this->jsonResponse($userLists->toArray());
  }

  private function isBotSubmission(mixed $honeypotValue, mixed $formLoadedAt): bool
  {
    if (!empty($honeypotValue)) {
      return true;
    }

    if (!is_numeric($formLoadedAt)) {
      return true;
    }

    return (microtime(true) - (float)$formLoadedAt) < self::MIN_ELAPSED_SECONDS;
  }

  private function handlePayload(array $body): bool
  {
    $userId = null;

    try {
      $user = $body['user'] ?? null;
      $lists = $body['lists'] ?? null;
      $website = $body['website'] ?? null;
      $formLoadedAt = $body['form_loaded_at'] ?? null;

      $client = $this->edmClientService->getClient();

      if (is_int($user)) {
        $userId = $user;
      } else if (is_array($user)) {
        $response = $client->user->create($user);
        $userId = $response['id'] ?? null;
      } else {
        return false;
      }

      if ($userId === null) {
        return false;
      }

      if (is_array($lists) && !empty($lists)) {
        $userListData = [
          'data' => [
            'list_data' => array_map(function ($listId) use ($userId) {
              return [
                'user_list' => $listId,
                'user' => $userId,
              ];
            }, $lists),
            'consent_method' => 'trigger_confirmation',
            'website' => $website,
            'form_loaded_at' => $formLoadedAt,
          ],
        ];
        $response = $client->userList->bulkCreate($userListData);
      } else {
        return false;
      }

      return true;
    } catch (\Throwable $e) {
      GeneralUtility::makeInstance(LogManager::class)
        ->getLogger(__CLASS__)
        ->error(sprintf(
          'UserListMiddleware::handlePayload failed (user %s): %s',
          $userId ?? 'not created',
          $e->getMessage()
        ));

      return false;
    }
  }

  private function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
  {
    $response = $this->responseFactory->createResponse($statusCode)
      ->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($data));
    return $response;
  }
}
