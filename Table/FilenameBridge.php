<?php
namespace Dittto\FilenameExtensionBundle\Table;

use Dittto\TableBundle\Table\Bridge;
use Dittto\FilenameExtensionBundle\Model\Repository;
use Dittto\FilenameExtensionBundle\Model\QueryBuilder;

/**
 * Class FilenameBridge
 * This allows a folder to be used as the data-source of a bridge
 *
 * @package Dittto\FilenameExtensionBundle\Table
 */
class FilenameBridge extends Bridge
{
    /**
     * The repository for the filenames
     * @var Repository
     */
    private $repository;

    /**
     * The query builder used to retrieve the filenames
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * The class constructor
     *
     * @param Repository $repository The repository for the filenames
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This retrieves and stores a query builder that takes the options supplied and builds a search and
     * ordering around it
     *
     * @return void
     */
    public function createQueryBuilder()
    {
        // store the query builder locally
        $this->queryBuilder = $this->repository->getQueryBuilder();
    }

    /**
     * Sets any additional changes specified by an extended bridge
     *
     * @return void
     */
    public function setAdditionalChanges()
    {
        // init vars
        $fields = $this->getFields();

        // update any fields values that are missing
        foreach ($fields as $field => $options) {
            $fields[$field]['order'] = isset($options['order']) ? $options['order'] : false;
            $fields[$field]['name'] = isset($options['name']) ? $options['name'] : ucwords(strtolower($field));
            $fields[$field]['fieldAlias'] = isset($options['fieldAlias']) && $options['fieldAlias'] ? $options['fieldAlias'] : $field;
        }

        // update the fields values with missing values
        $this->setFields($fields);

        // update the search with any extra regexes
        $this->setExtraQueryChanges($this->queryBuilder);
    }

    /**
     * Changes the ordering of the query
     *
     * @param string $order The name of the field to order by
     * @param string $direction Either asc or desc
     * @return void
     */
    public function setOrderingChanges($order, $direction)
    {
        // store any ordering changes
        $this->queryBuilder->addOrderField($order, strtoupper($direction));
    }

    /**
     * Adds the pagination to the query. This first calculates which page should be shown and then returns this
     * information to be stored
     *
     * @param int $page The page number requested by the browser
     * @param int $perPage The number of items per page to return by the browser
     */
    public function setPaginationChanges($page, $perPage)
    {
        // store any pagination changes
        $this->queryBuilder->addOffset($perPage * ($page - 1));
        $this->queryBuilder->addLimit($perPage);
    }

    /**
     * Gets the array of data from the bridge. Includes any last-minute changes
     *
     * @return array
     */
    public function getData()
    {
        // use the query builder to get the data
        return $this->queryBuilder->getAll();
    }

    /**
     * Gets the count of the data from the bridge
     *
     * @return int
     */
    public function getCount()
    {
        // use the query builder to get the total count
        return $this->queryBuilder->getCount();
    }

    /**
     * Sets any extra query builder options from extended functions and adds them to the main search
     *
     * @param QueryBuilder $queryBuilder The query builder to update
     */
    protected function setExtraQueryChanges(QueryBuilder $queryBuilder)
    {
    }
}