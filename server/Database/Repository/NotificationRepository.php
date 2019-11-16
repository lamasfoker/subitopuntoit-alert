<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Repository;

use SubitoPuntoItAlert\Database\AbstractRepository;
use SubitoPuntoItAlert\Database\AbstractModel;
use SubitoPuntoItAlert\Database\Model\Notification;

class NotificationRepository extends AbstractRepository
{
    const TABLE_NAME = 'Notification';
    const ID_NAME = 'endpoint';
    const COLUMNS_NAME = ['endpoint', 'message'];

    /**
     * @param $data
     * @return AbstractModel
     */
    protected function hydrateModel($data): AbstractModel
    {
        $notification = new Notification($data[static::ID_NAME]);
        $notification->setMessage($data['message']);
        return $notification;
    }

    /**
     * @param AbstractModel $model
     * @return array
     */
    protected function dryModel(AbstractModel $model): array
    {
        /** @var Notification $model */
        return [
            $model->getEndpoint(),
            $model->getMessage()
        ];
    }
}
