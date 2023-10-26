<?php

namespace CyberDuck\BlockPage\Extension;

use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldFooter;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\ORM\DataExtension;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class BlockPageExtension extends DataExtension
{
    private static $db = [];

    private static $many_many = [
        'ContentBlocks' => ContentBlock::class
    ];

    private static $many_many_extraFields = [
        'ContentBlocks' => [
            'SortBlock' => 'Int'
        ]
    ];

    private static $owns = [
        'ContentBlocks'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->ID > 0) {
            $editor = GridFieldConfig_RelationEditor::create();
            $grid = new GridField('ContentBlocks', 'Content Blocks', $this->owner->ContentBlocks(), $editor);

            $paginator = $grid->getConfig()->getComponentByType(GridFieldPaginator::class);

            if($paginator instanceof GridFieldPaginator) {
                $paginator->setItemsPerPage(100);
            }

            $grid->getConfig()
                ->removeComponentsByType([
                    GridFieldPageCount::class,
                    GridFieldAddExistingAutocompleter::class
                ])
                ->addComponents([
                    new GridFieldOrderableRows('SortBlock'),
                    new GridFieldAddExistingSearchButton()
                ]);

            $session = Controller::curr()->getRequest()->getSession();
            $session->set('BlockRelationID', $this->owner->ID);
            $session->set('BlockRelationClass', $this->owner->ClassName);

            $fields->addFieldToTab('Root.ContentBlocks', $grid);
        } else {
            $fields->addFieldToTab('Root.ContentBlocks', LiteralField::create(false, 'Please save this block to start adding items<br><br>'));
        }
    }

    public function updateAnchorsOnPage(&$anchors) {
        $block_anchors = [];

        if($this->owner->hasMethod('ContentBlocks')) foreach($this->owner->ContentBlocks() as $block) {
            $block_anchors = array_merge($block->getAnchorsInBlock(), $block_anchors);
        }

        $anchors = array_unique(array_merge($anchors, $block_anchors));
    }
}
