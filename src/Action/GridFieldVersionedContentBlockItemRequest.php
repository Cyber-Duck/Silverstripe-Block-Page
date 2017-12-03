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
/*
use SilverStripe\Control\Controller;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Versioned\Versioned;

class CreateBlock_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = [
        'ItemEditForm'
    ];
    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $actions = $form->Actions();

        $fields = $this->record->getCMSFields();
        foreach($fields as $field) {
            $field->setForm($form);
        }
        $form->fields()->removeByName('Title');
        $form->fields()->removeByName('BlockType');
        $form->fields()->removeByName('BlockStage');
        $form->fields()->removeByName('ParentID');
        $form->fields()->removeByName('ParentClass');
        $form->fields()->addFieldsToTab('Root.Main', $fields);

        $this->record->ParentClass = $this->gridField->getList()->getForeignClass();

        $name = TextField::create('Title')
            ->setDescription('Reference title not displayed in the page.');

        $form->fields()->insertAfter($name, 'BlockHeader');
        $form->loadDataFrom($this->record);

        if($this->getAction() == 'new') {
            $actions->removeByName('action_doSave');
            $button = FormAction::create('doCreate');
            $button->setTitle('Create')
                ->setAttribute('data-icon', 'accept')
                ->setAttribute('data-icon-alternate', 'addpage')
                ->setAttribute('data-text-alternate', 'Clean-up now');
            $actions->unshift($button);

            $form->addExtraClass('cms-content-fields');
        } else {
            // add new block button
            $button = FormAction::create('doAddBlock');
            $button->setTitle('New Block')
                ->addExtraClass('btn btn-primary font-icon-rocket')
                ->setAttribute('data-icon', 'accept')
                ->setAttribute('data-icon-alternate', 'addpage')
                ->setAttribute('data-text-alternate', 'Clean-up now');
            $actions->unshift($button);

            // versioned
            if($this->record->has_extension(Versioned::class)) {
                $actions->removeByName('action_doSave');
                $actions->removeByName('action_doDelete');
                // save draft action
                $actions->push(FormAction::create('doSave', 'Saved')
                    ->addExtraClass('btn-outline-primary font-icon-tick')
                    ->setAttribute('data-btn-alternate-add', 'btn-primary font-icon-save')
                    ->setAttribute('data-btn-alternate-remove', 'btn-outline-primary font-icon-tick')
                    ->setUseButtonTag(true)
                    ->setAttribute('data-text-alternate', 'Save draft'));
                // publish action
                if ($this->record->canPublish()) {
                    $publish = FormAction::create('doPublish', 'Publish')
                        ->addExtraClass('btn-outline-primary font-icon-tick')
                        ->setAttribute('data-btn-alternate-add', 'btn-primary font-icon-rocket')
                        ->setAttribute('data-btn-alternate-remove', 'btn-outline-primary font-icon-tick')
                        ->setUseButtonTag(true)
                        ->setAttribute('data-text-alternate', _t(__CLASS__.'.BUTTONSAVEPUBLISH', 'Save & publish'));

                    if ($this->record->stagesDiffer(Versioned::DRAFT, Versioned::LIVE)) {
                        $publish->addExtraClass('btn-primary font-icon-rocket');
                        $publish->setTitle('Save & publish');
                        $publish->removeExtraClass('btn-outline-primary font-icon-tick');
                    }
                    if ($actions->fieldByName('action_doSave')) {
                        $actions->insertAfter('action_doSave', $publish);
                    } else {
                        $actions->push($publish);
                    }
                }
                // unpublish action
                if ($this->record->isPublished() && $this->record->canUnpublish()) {
                    $actions->push(
                        FormAction::create('doUnpublish', 'Unpublish')
                            ->setUseButtonTag(true)
                            ->setDescription('Remove this record from the published site')
                            ->addExtraClass('btn-secondary')
                    );
                }
            }
        }
        return $form;
    }
    public function doCreate($data, Form $form)
    {
        $request = Controller::curr()->getRequest();

        $class = $request->postVar('BlockType');

        $block = new $class();
        $block->ParentID = $request->postVar('ParentID');
        $block->ParentClass = $request->postVar('ParentClass');
        $block->write();
        
        return Controller::curr()->redirect(Controller::join_links($this->gridField->Link('item'), $block->ID, 'edit'));    
    }
    
    public function doPublish($data, Form $form)
    {
        $record = $this->getRecord();

        if (!$this->record->canPublish()) {
            return $this->httpError(403);
        }
        $record = $this->saveFormIntoRecord($data, $form);
        $record->publishRecursive();
        
        $message = sprintf('Published %s Block', $this->record->config()->title);
        $this->setFormMessage($form, $message);

        return $this->redirectAfterSave(false);
    }
    
    public function doUnpublish($data, $form)
    {
        $record = $this->getRecord();

        if (!$this->record->canUnpublish()) {
            return $this->httpError(403);
        }
        $this->record->doUnpublish();

        $message = sprintf('Unpublished %s Block', $this->record->config()->title);
        $this->setFormMessage($form, $message);
        
        return $this->redirectAfterSave(false);
    }
    
    protected function setFormMessage($form, $message)
    {
        $form->sessionMessage($message, 'good', ValidationResult::CAST_HTML);
        $controller = $this->getToplevelController();
        if ($controller->hasMethod('getEditForm')) {
            $backForm = $controller->getEditForm();
            $backForm->sessionMessage($message, 'good', ValidationResult::CAST_HTML);
        }
    }
    
    
    private function getAction()
    {
        $path = explode('/', Controller::curr()->getRequest()->getURL());

        return array_pop($path);
    }
}
*/
