<?php

namespace CyberDuck\BlockPage\Extension;

/**
 * BlockPageExtension
 *
 * Extension to turn a Page object into a Block Page
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class BlockPageExtension extends DataExtension
{    
    /**
     * Has many object relations
     *
     * @since version 1.0.0
     *
     * @config array $has_many
     **/
    private static $has_many = [
        'ContentBlocks' => 'ContentBlock'
    ];

    /**
     * Method to include content blocks in the page template
     *
     * @since version 1.0.0
     *
     * @param string $class
     * @param int    $id
     *
     * @return string
     **/
    public function IncludeBlock($class, $id)
    {   
        return DataObject::get_by_id($class, $id)->renderWith($class);
    }

    /**
     * Update the page CMS fields with the content block grid field
     *
     * @since version 1.0.0
     *
     * @param object $fields
     *
     * @return object
     **/
    public function updateCMSFields(FieldList $fields) 
    {   
        $blocks = $this->owner->ContentBlocks();
        $editor = GridFieldConfig_RelationEditor::create()->addComponent(new GridFieldSortableRows('BlockSort'));
        $grid = new GridField('ContentBlocks', 'Content Blocks', $blocks, $editor);

        $grid->getConfig()
            ->removeComponentsByType('GridFieldAddExistingAutocompleter')
            ->getComponentByType('GridFieldDetailForm')
            ->setItemRequestClass('CreateBlock_ItemRequest');

        $detail = $grid->getConfig()->getComponentByType('GridFieldDetailForm');

        $content = new ContentBlock();
        $content->ParentID = $this->owner->ID;
        $content->ParentClass = $this->owner->ClassName;
        $detail->setFields($content->getCMSFields());

        $fields->addFieldToTab('Root.ContentBlocks', $grid);

        return $fields;
    }

    public function onAfterDuplicate($new, $dowrite = null)
    {
        /*
         * SiteTree duplicate will call this twice. Only the second call
         * will have the ID set. See source of SiteTree:duplicate and
         * DataObject::duplicate to see why.
         *
         * The simplest way to identify that we're in the second call is to
         * use the above default value.
         *
         * It is possible in the future we will have to identify in another
         * way by e.g. keeping a counter of calls or walking the backtrace.
         *
         */


        if ($dowrite === null) {

            $blocks = $this->owner->ContentBlocks();
            foreach ($blocks
                     as
                     $block)
            {
                /** @var ContentBlock $block */
                $block = $block->duplicate(true);

                $block->ParentID = $new->ID;

                $block->write(false, false, true);
            }
        }
    }
}
