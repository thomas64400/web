<?php declare(strict_types = 1);
namespace LibertAPI\Authentification;

use LibertAPI\Tools\Libraries\ARepository;
use \Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de l'authentification
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 * @see \Tests\Units\Authentification\AuthentificationController
 */
final class AuthentificationController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable
{
    public function __construct(ARepository $repository, IRouter $router)
    {
        $this->repository = $repository;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    protected function ensureAccessUser(string $order, \LibertAPI\Utilisateur\UtilisateurEntite $utilisateur)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        $authentificationType = 'Basic';
        $authentification = $request->getHeaderLine('Authorization');
        if (0 !== stripos($authentification, $authentificationType)) {
            return $this->getResponseBadRequest($response, 'Authorization mechanism is not set to « ' . $authentificationType . ' »');
        }

        $authentification = substr($authentification, strlen($authentificationType) + 1);
        list($login, $password) = explode(':', base64_decode($authentification));

        try {
            $utilisateur = $this->repository->find([
                'login' => $login,
                'isActif' => true,
            ]);
            if (!$utilisateur->isPasswordMatching($password)) {
                throw new \UnexpectedValueException('Wrong password');
            }
            $utilisateurUpdated = $this->repository->regenerateToken($utilisateur);
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNotFound($response, 'No user matches these criteria');
        }

        return $this->getResponseSuccess(
            $response,
            $utilisateurUpdated->getToken(),
            200
        );
    }
}
