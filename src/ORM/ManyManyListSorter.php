<?php

namespace CyberDuck\BlockPage\ORM;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

class ManyManyListSorter
{
    use Injectable;

    /**
     * Many many list instance
     *
     * @var ManyManyList
     */
    protected $list;

    /**
     * Passed many item
     *
     * @var int|object
     */
    protected $item;

    /**
     * Passed many item ID
     *
     * @var int
     */
    protected $itemID;

    /**
     * Parent table name
     *
     * @var string
     */
    protected $parentTable;

    /**
     * Parent class namespace
     *
     * @var string
     */
    protected $parentNamespace;

    /**
     * Many relation name
     *
     * @var string
     */
    protected $relationName;

    /**
     * Many sorting column name
     *
     * @var string
     */
    protected $column;

    /**
     * Sets the required properties
     *
     * @param ManyManyList $list
     * @param int|object $item
     */
    public function __construct(ManyManyList $list, $item)
    {
        $this->list = $list;
        $this->item = $item;
        $this->itemID = is_object($item) ? $item->ID : $item;
    }

    /**
     * Returns the sorting column and value in array format [column => value]
     *
     * @return void
     */
    public function getColumn()
    {
        $table = explode('_', $this->list->getJoinTable());

        // checks table exists
        $this->parentTable = $table[0];
        $this->parentNamespace = array_search(
            $this->parentTable,
            DataObject::getSchema()->getTableNames()
        );
        if ($this->parentNamespace === false) {
            return [];
        }
        $this->relationName = $table[1];
        // checks if many_many_sorting is set
        $config = $this->getSortingConfig();
        if (!array_key_exists($this->relationName, $config)) {
            return [];
        }
        // checks the many_many_sorting column is in many_many_extraFields
        $this->column = $config[$this->relationName];
        if (!array_key_exists($this->column, $this->getExtraFields())) {
            return [];
        }
        // check current sorting not already applied and > 0
        if ($this->hasSortingApplied()) {
            return [];
        }
        // checks whether we are writing a belongs many many
        $id = $this->item instanceof $this->parentNamespace
        ? $this->itemID
        : $this->list->getForeignID();

        // checks the parent exists
        $parent = DataObject::get_by_id($this->parentNamespace, $id);
        if (!$parent) {
            return [];
        }
        // return the sorting value
        $max = (int) $parent->{$this->relationName}()->max($this->column);
        return [$this->column => $max + 1];
    }

    /**
     * Returns the parent many_many_sorting config data
     *
     * @return void
     */
    protected function getSortingConfig()
    {
        return (array) $this->parentNamespace::config()->get('many_many_sorting');
        ;
    }

    /**
     * Returns the parent many_many_extraFields config data
     *
     * @return void
     */
    protected function getExtraFields()
    {
        return (array) DataObject::getSchema()
            ->manyManyExtraFieldsForComponent(
                $this->parentNamespace,
                $this->relationName
            );
    }

    /**
     * Checks if the passed many many item has sorting applied
     *
     * @return boolean
     */
    protected function hasSortingApplied()
    {
        $query = sprintf(
            "SELECT %s FROM %s WHERE %s = ? AND %s = ?",
            $this->column,
            $this->list->getJoinTable(),
            $this->list->getForeignKey(),
            $this->list->getLocalKey()
        );
        $current = DB::prepared_query($query, [
            $this->list->getForeignID(), $this->itemID
        ])->record();

        return $current && $current[$this->column] > 0;
    }
}
