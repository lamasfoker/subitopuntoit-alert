<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Repository;

use SubitoPuntoItAlert\Database\AbstractRepository;
use SubitoPuntoItAlert\Database\AbstractModel;
use SubitoPuntoItAlert\Database\Model\Announcement;

class AnnouncementRepository extends AbstractRepository
{
    const TABLE_NAME = 'Announcement';
    const COLUMNS_NAME = ['endpoint', 'details'];

    /**
     * @param $data
     * @return AbstractModel
     */
    protected function hydrateModel($data): AbstractModel
    {
       $announcement = new Announcement($data[static::ID_NAME]);
       $announcement->setEndpoint($data['endpoint'])
           ->setDetails($data['details']);
       return $announcement;
    }

    /**
     * @param AbstractModel $model
     * @return array
     */
    protected function dryModel(AbstractModel $model): array
    {
        /** @var Announcement $model */
        return [
            $model->getEndpoint(),
            $model->getDetails()
        ];
    }
}
