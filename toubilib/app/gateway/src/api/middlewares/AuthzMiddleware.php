<?php
declare(strict_types=1);

namespace toubilib\gateway\api\middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

final class AuthzMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('authenticated_user');

        if ($user === null) {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'User not authenticated']));
            return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $role = (int)($user['role'] ?? 0);
        $userId = (string)($user['id'] ?? ($user['ID'] ?? ''));

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeName = $route ? $route->getName() : null;
        $args = $route ? $route->getArguments() : [];

        $allowed = $this->isAllowed($role, $userId, $routeName, $args);

        if (!$allowed) {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'Forbidden']));
            return $res->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }

    private function isAllowed(int $role, string $userId, ?string $routeName, array $args): bool
    {
        return match ($routeName) {
            'praticiens.creneaux', 'agenda' => $role === 10 && ($args['praticienId'] ?? '') === $userId,

            'rdvs.detail', 'rdv.get' => in_array($role, [1,10,100], true),

            'rdvs.create', 'rdv.create' => in_array($role, [1,10,100], true),

            'rdvs.update', 'rdv.update' => $role === 10 || $role === 100,

            'rdvs.delete', 'rdv.delete' => $role === 1 || $role === 100,

            'praticiens.list', 'praticiens.detail' => true,

            default => true,
        };
    }
}