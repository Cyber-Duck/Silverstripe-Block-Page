<?php

namespace CyberDuck\BlockPage\Action;

use Page;
use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\VersionedGridFieldItemRequest;

class GridFieldVersionedContentBlockItemRequest extends VersionedGridFieldItemRequest
{
    private static $allowed_actions = [
        'doSelect',
        'doSelection',
        'ItemEditForm'
    ];

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $actions = $form->Actions();

        if($this->record->getAction() == 'new') {
            // remove all actions and add the create action
            foreach($actions as $action) {
                $actions->remove($action);
            }
            $button = FormAction::create('doSelect');
            $button->setTitle('Create')
                ->setUseButtonTag(true)
                ->setAttribute('data-icon', 'accept')
                ->addExtraClass('btn action btn-primary font-icon-plus');
            $actions->unshift($button);
        } else {
            $button = FormAction::create('doSelection');
            $button->setTitle('New Block')
                ->setAttribute('data-icon', 'accept');
            $actions->unshift($button);
            // set fields
            $fields = $this->record->getCMSFields();
            $fields->setForm($form);

            $form->fields()->removeByName('Root');
            $form->fields()->push(TabSet::create('Root', Tab::create('Main')));
            $form->fields()->addFieldsToTab('Root.Main', $fields);
            
            $form->loadDataFrom($this->record);
        }
        return $form;
    }

    public function doSelect()
    {
        $request = Controller::curr()->getRequest();
        
        $class = $request->postVar('ContentBlock');
        $block = $class::create();
        $block->write();

        $page = DataObject::get_by_id(Page::class, $request->postVar('BlockRelationID'));
        $page->ContentBlocks()->add($block);
        
        return Controller::curr()->redirect(Controller::join_links($this->gridField->Link('item'), $block->ID, 'edit'));    
    }

    public function doSelection($data, Form $form)
    {
        return Controller::curr()->redirect(Controller::join_links($this->gridField->Link('item'), 'new'));
    }
}