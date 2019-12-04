<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Repository;

use SubitoPuntoItAlert\Database\AbstractRepository;
use SubitoPuntoItAlert\Database\AbstractModel;
use SubitoPuntoItAlert\Database\Model\Subscription;

class SubscriptionRepository extends AbstractRepository
{
    const TABLE_NAME = 'Subscription';
    const COLUMNS_NAME = ['endpoint', 'contentEncoding', 'authToken', 'publicKey'];

    /**
     * @param $data
     * @return AbstractModel
     */
    protected function hydrateModel($data): AbstractModel
    {
        $subscription = new Subscription($data[static::ID_NAME]);
        $subscription->setEndpoint($data['endpoint'])
            ->setContentEncoding($data['contentEncoding'])
            ->setAuthToken($data['authToken'])
            ->setPublicKey($data['publicKey']);
        return $subscription;
    }

    /**
     * @param AbstractModel $model
     * @return array
     */
    protected function dryModel(AbstractModel $model): array
    {
        /** @var Subscription $model */
        return [
            $model->getEndpoint(),
            $model->getContentEncoding(),
            $model->getAuthToken(),
            $model->getPublicKey()
        ];
    }
}
