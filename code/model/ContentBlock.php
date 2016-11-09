<?php

class ContentBlock extends DataObject
{
	private static $db = [
		'Name'        => 'Varchar(512)',
        'CssSelector' => 'Varchar(512)',
        'BlockType'   => 'Varchar(30)',
        'BlockSort'   => 'Int'
	];

	private static $summary_fields = [
		'ID'          => 'ID',
		'Name'        => 'Name',
		'ClassName'   => 'Type',
		'CssSelector' => 'CSS Style'
	];

	private static $has_one = [
		'Page' => 'Page'
	];

	public function getCMSActions()
	{
    	$fields = parent::getCMSActions();

	    $fields->fieldByName('MajorActions')->push(
	        $cleanupAction = FormAction::create('cleanup', 'Cleaned')
	            ->setAttribute('data-icon', 'accept')
	            ->setAttribute('data-icon-alternate', 'addpage')
	            ->setAttribute('data-text-alternate', 'Clean-up now')
	    );
	    return $fields;
	}

    public function getCMSValidator()
    {
    	return new RequiredFields([]);
    }

    public function getCMSFields()
    {
    	$fields = parent::getCMSFields();

        if($this->getAction() == 'new') {
            return $this->getBlockSelectionFields($fields);
        }
        return $fields;
    }

    private function getAction()
    {
        $path = explode('/', Controller::curr()->getRequest()->getURL());

        return array_pop($path);
    }

    public function getBlockSelectionFields(FieldList $fields)
    {
    	$remove = $fields->dataFields();

        foreach($remove as $field) {
        	if($field->Name != 'PageID') {
            	$fields->removeByName($field->Name);
        	}
        }
        $tabs = TabSet::create('Root',
			TabSet::create('Main', 'Main')
		);
		$fields->push($tabs);

		$fields->addFieldsToTab('Root.Main', [
			LiteralField::create(false, '<div id="PageType">'),
			HeaderField::create('Blocks'),
			OptionsetField::create('BlockType', $this->getBlockSelectionLabel(), $this->getBlockSelectionOptions())
				->setCustomValidationMessage('Please select a block type'),
			LiteralField::create(false, '</div">'),
			HiddenField::create('BlockStage')->setValue('choose')
		]);
        return $fields;
    }

    private function getBlockSelectionLabel()
    {
    	$label = '<span class="step-label"><span class="flyout">%d</span><span class="arrow"></span><span class="title">%s</span></span>';
        
        return sprintf($label, 1, 'Add content block');
    }

    private function getBlockSelectionOptions()
    {
    	$types = Config::inst()->get('BlockPage', 'blocks');

    	$options = [];

        foreach($types as $type) {
			$html = sprintf('<span class="page-icon class-%s"></span><strong class="title">%s</strong><span class="description">%s</span>',
				$type,
				Config::inst()->get($type, 'title'),
				Config::inst()->get($type, 'description')
			);
			$options[$type] = DBField::create_field('HTMLText', $html);
        }
        return $options;
    }
}