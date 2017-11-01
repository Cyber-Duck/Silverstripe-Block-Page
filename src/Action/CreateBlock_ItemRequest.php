<?php

namespace CyberDuck\BlockPage\Action;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Versioned\Versioned;

/**
 * CreateBlock_ItemRequest
 *
 * Request handler for creating new blocks in the CMS
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class CreateBlock_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    /**
     * Allowe CMS block request actions 
     *
     * @since version 1.0.0
     *
     * @config array $allowed_actions
     **/
    private static $allowed_actions = [
        'ItemEditForm', 
        'doCreate',
        'doPublish',
        'doUnpublish',
        'doArchive'
    ];

    /**
     * CMS record form method
     *
     * @since version 1.0.0
     *
     * @return object
     **/
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

    /**
     * Handles the block create request and redirects to the new record
     *
     * @since version 1.0.0
     *
     * @param array  $data
     * @param object $form
     *
     * @return void
     **/
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
    
    /**
     * Handles the publish action
     *
     * @since version 4.0.0
     *
     * @param array  $data
     * @param object $form
     *
     * @return void
     **/
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
    
    /**
     * Handles the unpublish action
     *
     * @since version 4.0.0
     *
     * @param array  $data
     * @param object $form
     *
     * @return void
     **/
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
    
    /**
     * Set form action message
     *
     * @since version 4.0.0
     *
     * @param object $form
     * @param string $message
     *
     * @return void
     **/
    protected function setFormMessage($form, $message)
    {
        $form->sessionMessage($message, 'good', ValidationResult::CAST_HTML);
        $controller = $this->getToplevelController();
        if ($controller->hasMethod('getEditForm')) {
            $backForm = $controller->getEditForm();
            $backForm->sessionMessage($message, 'good', ValidationResult::CAST_HTML);
        }
    }
    
    /**
     * Redirects to block selection screen
     *
     * @since version 1.0.0
     *
     * @param array  $data
     * @param object $form
     *
     * @return void
     **/
    public function doAddBlock($data, Form $form)
    {
        return Controller::curr()->redirect(Controller::join_links($this->gridField->Link('item'), 'new'));
    }
    
    /**
     * Get the current record action
     *
     * @since version 1.0.0
     *
     * @return string
     **/
    private function getAction()
    {
        $path = explode('/', Controller::curr()->getRequest()->getURL());

        return array_pop($path);
    }
}
