<?php
namespace Dittto\FilenameExtensionBundle\Model;

/**
 * Class Repository
 * A repository for retrieving all of the filenames in a given path
 *
 * @package Dittto\FilenameExtensionBundle\Model
 */
class Repository
{
    /**
     * The filename query builder
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Class constructor
     *
     * @param QueryBuilder $queryBuilder The filename query builder to use
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Gets only the filename data that matches the name provided
     *
     * @param string $name The name of the filename to retrieve
     * @return array|null
     */
    public function getOneByName($name)
    {
        return $this->queryBuilder->getOneByName($name);
    }

    /**
     * Get all of the filenames
     *
     * @return array
     */
    public function getAll()
    {
        return $this->queryBuilder->getAll();
    }
}