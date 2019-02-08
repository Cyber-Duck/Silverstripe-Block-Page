<?php

namespace CyberDuck\BlockPage\ORM;

use SilverStripe\ORM\ManyManyList;

class ContentBlockManyManyList extends ManyManyList
{
    /**
     * Adds a many many relation item
     *
     * The custom ManyManyListSorter intecepts the action here and sets sorting
     * if required
     *
     * @param int|object $item
     * @param array $extraFields
     * @return void
     */
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
