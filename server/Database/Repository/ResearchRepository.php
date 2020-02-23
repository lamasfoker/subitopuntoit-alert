<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database\Repository;

use SubitoPuntoItAlert\Database\AbstractRepository;
use SubitoPuntoItAlert\Database\AbstractModel;
use SubitoPuntoItAlert\Database\Model\Research;

class ResearchRepository extends AbstractRepository
{
    const TABLE_NAME = 'Research';
    const COLUMNS_NAME = ['endpoint', 'location', 'locationParameters', 'onlyInTitle', 'query', 'lastCheck'];

    /**
     * @param $data
     * @return AbstractModel
     */
    protected function hydrateModel($data): AbstractModel
    {
        $research = new Research((int) $data[static::ID_NAME]);
        $research->setEndpoint($data['endpoint'])
            ->setLocation($data['location'])
            ->setLocationParameters($data['locationParameters'])
            ->setOnlyInTitle($data['onlyInTitle']===1)
            ->setQuery($data['query'])
            ->setLastCheck($data['lastCheck']);
        return $research;
    }

    /**
     * @param AbstractModel $model
     * @return array
     */
    protected function dryModel(AbstractModel $model): array
    {
        /** @var Research $model */
        return [
            $model->getEndpoint(),
            $model->getLocation(),
            $model->getLocationParameters(),
            $model->isOnlyInTitle()?1:0,
            $model->getQuery(),
            $model->getLastCheck()
        ];
    }
}
