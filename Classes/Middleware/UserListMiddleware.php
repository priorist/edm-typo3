<?php

declare(strict_types=1);

namespace Priorist\EdmTypo3\Middleware;

use Priorist\EdmTypo3\Service\EdmClientService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

final class UserListMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
  use LoggerAwareTrait;

  public function __construct(
    private readonly ResponseFactoryInterface $responseFactory,
    private readonly EdmClientService $edmClientService,
  ) {}

  public function process(
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
  ): ResponseInterface {
    $this->logger->debug('Process called', [
      'method' => $request->getMethod(),
      'path' => $request->getUri()->getPath(),
    ]);

    try {
      if ($request->getUri()->getPath() !== '/edm/add-to-user-list') {
        return $handler->handle($request);
      }

      if ($request->getMethod() !== 'POST') {
        return $this->jsonResponse(['error' => 'Method not allowed'], 405);
      }

      $body = json_decode((string)$request->getBody(), true);

      if (!is_array($body)) {
        return $this->jsonResponse(['error' => 'Invalid JSON payload'], 400);
      }

      $this->logger->debug('Payload received', ['body' => $body]);

      try {
        $success = $this->handlePayload($body);
      } catch (\Throwable $e) {
        $this->logger->error('Process error', ['message' => $e->getMessage()]);
        return $this->jsonResponse(['error' => $e->getMessage()], 500);
      }

      if ($success) {
        return $this->jsonResponse(['status' => 'ok']);
      }

      return $this->jsonResponse(['error' => 'Request failed, check logs for details'], 500);
    } catch (\Throwable $e) {
      $this->logger->error('UserListMiddleware fatal error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  private function handlePayload(array $body): bool
  {
    $this->logger->debug('handlePayload called');

    try {
      $user = $body['user'] ?? null;
      $lists = $body['lists'] ?? null;
      $userId = null;

      $client = $this->edmClientService->getClient();

      if (is_int($user)) {
        $userId = $user;
      } else if (is_array($user)) {
        $response = $client->user->create($user);
        $this->logger->debug('Create user response', ['response' => $response]);
        $userId = $response['id'] ?? null;
      } else {
        $this->logger->debug('Invalid user type', ['type' => gettype($user)]);
        return false;
      }

      if ($userId === null) {
        $this->logger->debug('No user ID after create', ['response' => $response ?? null]);
        return false;
      }

      if (is_array($lists)) {
        $userListData = [
          'data' => [
            'list_data' => array_map(function ($listId) use ($userId) {
              return [
                'user_list' => $listId,
                'user' => $userId,
              ];
            }, $lists),
            'consent_method' => 'trigger_confirmation',
          ],
        ];
        $response = $client->userList->bulkCreate($userListData);
        $this->logger->debug('Bulk create list response', ['response' => $response]);
      } else {
        $this->logger->debug('Invalid lists type', ['type' => gettype($lists)]);
        return false;
      }

      return true;
    } catch (\Throwable $e) {
      $this->logger->error('handlePayload error', ['message' => $e->getMessage()]);
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
