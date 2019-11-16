<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database;

abstract class AbstractModel
{
    /**
     * @var mixed
     */
    private $id;

    /**
     * @param mixed $id
     */
    public function __construct($id = null)
    {
        $this->setId($id);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return AbstractModel
     */
    protected function setId($id): AbstractModel
    {
        $this->id = $id;
        return $this;
    }
}
