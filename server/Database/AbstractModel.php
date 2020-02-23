<?php
declare(strict_types=1);

namespace SubitoPuntoItAlert\Database;

abstract class AbstractModel
{
    /**
     * @var int|null
     */
    private $id = null;

    /**
     * @param int $id
     */
    public function __construct(int $id = null)
    {
        if ($id) {
            $this->setId($id);
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AbstractModel
     */
    private function setId(int $id): AbstractModel
    {
        $this->id = $id;
        return $this;
    }
}
