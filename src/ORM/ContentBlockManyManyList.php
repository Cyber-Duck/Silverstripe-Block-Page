<?php

namespace CyberDuck\BlockPage\ORM;

use SilverStripe\ORM\ManyManyList;

class ContentBlockManyManyList extends ManyManyList
{
    public function add($item, $extraFields = [])
    {
        if (!is_array($extraFields)) {
            $extraFields = [];
        }
        $sorter = ManyManyListSorter::create($this, $item);
        $extraFields = array_merge(
            $extraFields,
            $sorter->getColumn()
        );
        parent::add($item, $extraFields);
    }
}
