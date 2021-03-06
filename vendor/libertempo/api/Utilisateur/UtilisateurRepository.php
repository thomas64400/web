<?php declare(strict_types = 1);
namespace LibertAPI\Utilisateur;

use LibertAPI\Tools\Libraries\AEntite;
use LibertAPI\Tools\Libraries\Application;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 * @see \LibertAPI\Tests\Units\Utilisateur\UtilisateurRepository
 */
class UtilisateurRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /**
     * @var Application Bibliothèque d'accès aux données de l'application
     */
    private $application;

    /**
     * Ajoute la bibliothèque d'accès aux données, pour la génération du token
     *
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        if ($this->application instanceof Application) {
            throw new \LogicException('Application can\'t be set twice');
        }
        $this->application = $application;
    }

    final protected function getEntiteClass() : string
    {
        return UtilisateurEntite::class;
    }

    public function getOne(int $id) : AEntite
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * Retourne une ressource correspondant à des critères
     *
     * @param array $parametres
     * @example [offset => 4, start-after => 23, filter => 'name::chapo|status::1,3']
     *
     * @return AEntite
     */
    public function find(array $parametres)
    {
        $list = $this->getList($parametres);
        return reset($list);
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres) : array
    {
        $this->queryBuilder->select('*, u_login AS id');
        $this->setWhere($this->getParamsConsumer2Storage($parametres));
        $res = $this->queryBuilder->execute();
        $data = $res->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entites = array_map(function ($value) {
            return new UtilisateurEntite($this->getStorage2Entite($value));
        }, $data);

        return $entites;
    }

    /**
     * @inheritDoc
     */
    final protected function getStorage2Entite(array $dataStorage) : array
    {
        return [
            'id' => $dataStorage['id'],
            'login' => $dataStorage['u_login'],
            'nom' => $dataStorage['u_nom'],
            'prenom' => $dataStorage['u_prenom'],
            'isResp' => $dataStorage['u_is_resp'] === 'Y',
            'isAdmin' => $dataStorage['u_is_admin'] === 'Y',
            'isHr' => $dataStorage['u_is_hr'] === 'Y',
            'isActif' => $dataStorage['u_is_active'] === 'Y',
            'password' => $dataStorage['u_passwd'],
            'quotite' => $dataStorage['u_quotite'],
            'email' => $dataStorage['u_email'],
            'numeroExercice' => $dataStorage['u_num_exercice'],
            'planningId' => $dataStorage['planning_id'],
            'heureSolde' => $dataStorage['u_heure_solde'],
            'dateInscription' => $dataStorage['date_inscription'],
            'token' => $dataStorage['token'],
            'dateLastAccess' => $dataStorage['date_last_access'],
        ];
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Storage(array $paramsConsumer) : array
    {
        $results = [];
        if (!empty($paramsConsumer['login'])) {
            $results['u_login'] = (string) $paramsConsumer['login'];
        }
        if (!empty($paramsConsumer['token'])) {
            $results['token'] = (string) $paramsConsumer['token'];
        }
        if (!empty($paramsConsumer['gt_date_last_access'])) {
            $results['gt_date_last_access'] = (string) $paramsConsumer['gt_date_last_access'];
        }
        if (!empty($paramsConsumer['isActif'])) {
            $results['is_active'] = $paramsConsumer['isActif'];
        }
        return $results;
    }

    public function postOne(array $data, AEntite $entite) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    public function putOne(AEntite $entite)
    {
        $this->queryBuilder->update($this->getTableName());
        $this->setSet($this->getEntite2Storage($entite));
        $this->queryBuilder->where('u_login = :id');
        $this->queryBuilder->setParameter(':id', $entite->getId());

        $this->queryBuilder->execute();
    }

    /**
     * @inheritDoc
     */
    final protected function setValues(array $values)
    {
        unset($values);
    }

    final protected function setSet(array $parametres)
    {
        if (!empty($parametres['token'])) {
            $this->queryBuilder->set('token', ':token');
            $this->queryBuilder->setParameter(':token', $parametres['token']);
        }
        if (!empty($parametres['date_last_access'])) {
            $this->queryBuilder->set('date_last_access', ':date_last_access');
            $this->queryBuilder->setParameter(':date_last_access', $parametres['date_last_access']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function setWhere(array $parametres)
    {
        $whereCriteria = [];
        if (!empty($parametres['u_login'])) {
            $this->queryBuilder->andWhere('u_login = :id');
            $whereCriteria[':id'] = $parametres['u_login'];
        }
        if (!empty($parametres['token'])) {
            $this->queryBuilder->andWhere('token = :token');
            $whereCriteria[':token'] = $parametres['token'];
        }
        if (!empty($parametres['gt_date_last_access'])) {
            $this->queryBuilder->andWhere('date_last_access >= :gt_date_last_access');
            $whereCriteria[':gt_date_last_access'] = $parametres['gt_date_last_access'];
        }
        if (!empty($parametres['is_active'])) {
            $this->queryBuilder->andWhere('u_is_active = :actif');
            $whereCriteria[':actif'] = ($parametres['is_active']) ? 'Y' : 'N';
        }
        if (!empty($whereCriteria)) {
            $this->queryBuilder->setParameters($whereCriteria);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite) : array
    {
        return [
            //'u_login' => $entite->getLogin(), // PK ne doit pas être vu par le stockage
            /*'u_nom' => $entite->getJourId(),
            'u_prenom' => $entite->getTypeSemaine(),
            'u_is_resp' => $entite->getTypePeriode(),
            'u_is_admin' => $entite->getDebut(),
            'u_is_hr' => $entite->getFin(),
            'u_is_active' => $entite->getFin(),
            'u_passwd' => $entite->getFin(),
            'u_quotite' => $entite->getFin(),
            'u_email' => $entite->getFin(),
            'u_num_exercice' => $entite->getFin(),
            'planning_id' => $entite->getFin(),
            'u_heure_solde' => $entite->getFin(),
            'date_inscription' => $entite->getFin(),*/
            'token' => $entite->getToken(),
            'date_last_access' => $entite->getDateLastAccess(),
        ];
    }

    /**
     * « Ping » la date de dernier accès de l'utilisateur
     *
     * @param UtilisateurEntite $entite Entité utilisateur
     *
     * @since 0.3
     */
    public function updateDateLastAccess(UtilisateurEntite $entite)
    {
        $entite->updateDateLastAccess();
        $this->putOne($entite);
    }

    /**
     * Regénère le token de l'utilisateur pour une nouvelle session
     *
     * @param AEntite $entite Entité utilisateur
     *
     * @return AEntite L'entité hydratée du nouveau token
     * @throws \RuntimeException Si le token instance n'est pas posé
     */
    public function regenerateToken(AEntite $entite)
    {
        $instanceToken = $this->application->getTokenInstance();
        if (empty($instanceToken)) {
            throw new \RuntimeException('Instance token is not set');
        }

        try {
            $entite->populateToken($this->buildToken($instanceToken));
            $entite->updateDateLastAccess();
            $this->putOne($entite);

            return $entite;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Génère le token
     *
     * @param string $instanceToken Clé API de l'instance
     *
     * @return string
     */
    private function buildToken($instanceToken)
    {
        return password_hash($instanceToken, \PASSWORD_BCRYPT);
    }

    public function deleteOne(AEntite $entite) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_users';
    }
}
