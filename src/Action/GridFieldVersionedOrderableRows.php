<?php

namespace CyberDuck\BlockPage\Action;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\Map;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class GridFieldVersionedOrderableRows extends GridFieldOrderableRows
{
    protected function reorderItems($list, array $values, array $sortedIDs)
    {
        parent::reorderItems($list, $values, $sortedIDs);

        $sortField = $this->getSortField();
        
        $map = $list->map('ID', $sortField);
        if ($map instanceof Map) {
            $map = $map->toArray();
        }

        $class = $list->dataClass();
        $isVersioned = $class::has_extension(Versioned::class);

        if($isVersioned && $list instanceof ManyManyList) {
            $sortTable = $this->getSortTable($list);
            foreach ($sortedIDs as $sortValue => $id) {
                if ($map[$id] != $sortValue) {
                    $sql = sprintf(
                        'UPDATE "%s" SET "%s" = %d WHERE %s',
                        $sortTable,
                        $sortField,
                        $sortValue,
                        $this->getSortTableClauseForIds($list, $id)
                    );
                    DB::query($sql);
                }
            }
        }
    }
}