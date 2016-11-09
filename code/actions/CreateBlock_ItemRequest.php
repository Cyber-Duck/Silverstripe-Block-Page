<?php

class CreateBlock_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = ['ItemEditForm', 'doCreateBlock'];

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $actions = $form->Actions();

        $form = new Form(
            $this,
            'ItemEditForm',
            $this->record->getCMSFields(),
            $actions,
            $this->component->getValidator()
        );
        $form->loadDataFrom($this->record);

        if($this->getAction() == 'edit') {
            $form->addExtraClass('cms-content cms-edit-form center cms-content-fields');
        }
        if($this->getAction() == 'new') {
            $actions->removeByName('action_doSave');
            $button = FormAction::create('doCreateBlock');
            $button->setTitle('Create')
                ->setAttribute('data-icon', 'accept')
                ->setAttribute('data-icon-alternate', 'addpage')
                ->setAttribute('data-text-alternate', 'Clean-up now');
            $actions->unshift($button);

            $form->addExtraClass('cms-content-fields');
        }
        return $form;
    }

    public function doCreateBlock($data, $form)
    {
        $request = Controller::curr()->getRequest();

        $class = $request->postVar('BlockType');

        $block = new $class();
        $block->PageID = $request->postVar('PageID');
        $block->write();

        return Controller::curr()->redirect(sprintf('/admin/pages/edit/EditForm/field/ContentBlocks/item/%s/edit', $block->ID));
    }

    private function getAction()
    {
        $path = explode('/', Controller::curr()->getRequest()->getURL());

        return array_pop($path);
    }
}