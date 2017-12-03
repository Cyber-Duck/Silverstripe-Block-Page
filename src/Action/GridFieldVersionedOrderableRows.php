<?php

namespace CyberDuck\BlockPage\Action;

use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class GridFieldVersionedOrderableRows extends GridFieldOrderableRows
{
    protected function reorderItems($list, array $values, array $sortedIDs)
    {
        $sortField = $this->getSortField();
        $map = $list->map('ID', $sortField);
        if ($map instanceof SS_Map) {
            $map = $map->toArray();
        }

        $isVersioned = true;
        $class = $list->dataClass();
        //if ($class == $this->getSortTable($list)) {
        //    $isVersioned = $class::has_extension('SilverStripe\\ORM\\Versioning\\Versioned');
        //}

        if (!$isVersioned) {
            $sortTable = $this->getSortTable($list);
            $additionalSQL = (!$list instanceof ManyManyList) ? ', "LastEdited" = NOW()' : '';
            foreach ($sortedIDs as $sortValue => $id) {
                if ($map[$id] != $sortValue) {
                    DB::query(sprintf(
                        'UPDATE "%s" SET "%s" = %d%s WHERE %s',
                        $sortTable,
                        $sortField,
                        $sortValue,
                        $additionalSQL,
                        $this->getSortTableClauseForIds($list, $id)
                    ));
                }
            }
        } else {
            foreach ($sortedIDs as $sortValue => $id) {
                if ($map[$id] != $sortValue) {
                    $record = $class::get()->byID($id);
                    $record->$sortField = $sortValue;
                    $record->write();
                }
            }
        }

        $this->extend('onAfterReorderItems', $list);
    }
}