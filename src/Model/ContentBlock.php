<?php

namespace CyberDuck\BlockPage\Model;

use Page;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;

class ContentBlock extends DataObject implements PermissionProvider
{
	private static $table_name = 'ContentBlock';

	private static $db = [];

	private static $belongs_many_many = [
		'Pages' => Page::class,
	];

	private static $owned_by = [
		'Pages'
	];

	private static $extensions = [
		Versioned::class
	];

	private static $versioned_gridfield_extensions = true;

	private static $singular_name = 'Content Block';

	private static $plural_name = 'Content Blocks';

	private static $summary_fields = [
		'Thumbnail' => '',
		'i18n_singular_name' => 'Content type',
		'ID' => 'ID',
		'Title' => 'Title',
		'Pages.Count' => 'Pages'
	];

	private static $searchable_fields = [
		'ID',
		'ClassName'
	];

	public function searchableFields()
	{
		$fields = parent::searchableFields();

		$block_types = ClassInfo::subclassesFor(ContentBlock::class);

		$block_options = [];

		foreach($block_types as $block_type) {
			$block_inst = Injector::inst()->create($block_type);
			$block_options[$block_type] = $block_inst->i18n_singular_name();
		}

		asort($block_options);

		$fields['ID'] = [
			'filter' => 'ExactMatchFilter',
			'title' => 'ID',
            'field' => NumericField::create('ID')
		];

		$fields['ClassName'] = [
			'filter' => 'ExactMatchFilter',
			'title' => 'Content type',
			'field' => DropdownField::create('ClassName')->setSource($block_options)->setEmptyString('- Any -')
		];

		return $fields;
	}

	/**
	 * @return DBField
	 */
	public function getThumbnail()
	{
		return DBField::create_field('HTMLText', sprintf('<img src="%s" height="20">', $this->getPreviewImagePath()));
	}

	/**
	 * @return string
	 */
	protected function getPreviewImagePath()
	{
		$previewImagePath = $this->config()->get('preview');

		$this->extend('updatePreviewImagePath', $previewImagePath);

		return $previewImagePath;
	}

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->removeByName('Pages');

		if($this->getAction() == 'new' && $this->ClassName === ContentBlock::class) {
			return $this->getCMSSelectionFields($fields);
		} else {
			$editor = GridFieldConfig_RelationEditor::create();
            $editor->removeComponentsByType([
                GridFieldAddExistingAutocompleter::class
            ])->addComponents([
                new GridFieldAddExistingSearchButton()
            ]);

			$grid = new GridField('Pages', 'Pages', $this->Pages(), $editor);
			$grid->getConfig()
				->removeComponentsByType(GridFieldAddNewButton::class);
			$fields->addFieldToTab('Root.Pages', $grid);
		}
		return $fields;
	}

	public function getTemplateHolder()
	{
		return $this->renderWith(['Block/ContentBlock_holder']);
	}

	public function getTemplate()
	{
		if($this->ClassName != ContentBlock::class) {
			return $this->renderWith('Block/'.$this->ClassName);
		}
	}

	public function getAction()
	{
		$path = explode('/', Controller::curr()->getRequest()->getURL());
		return array_pop($path);
	}

	public function getBlockType()
	{
		$className = $this->owner->ClassName;
		return $className::config()->get('title');
	}

	private function getCMSSelectionFields(FieldList $fields)
	{
		$fields->removeByName('Root');
		// fields used in the inital selection request
		$session = Controller::curr()->getRequest()->getSession();
		$fields->push(HiddenField::create('BlockRelationID')->setValue($session->get('BlockRelationID')));
		$fields->push(HiddenField::create('BlockRelationClass')->setValue($session->get('BlockRelationClass')));

		// create the selection tab and options
		$fields->push(TabSet::create('Root', Tab::create('Main')));

		$rules = (array) Config::inst()->get(ContentBlock::class, 'restrict');

		if(array_key_exists($session->get('BlockRelationClass'), $rules)) {
			$classes = $rules[$session->get('BlockRelationClass')];
		} else {
			$classes = (array) Config::inst()->get(ContentBlock::class, 'blocks');
		}
		$options = [];
		foreach($classes as $class) {
			$block_inst = Injector::inst()->create($class);

			$options[$class] = DBField::create_field('HTMLText', Controller::curr()
				->customise([
					'Preview'     => singleton($class)->getPreviewImagePath(),
					'Title'       => $block_inst->i18n_singular_name(),
					'Description' => $class::config()->get('description')
				])
				->renderWith('/Includes/ContentBlockOption')
			);
		}
		$checked = key(array_slice($options, 0, 1, true));

		$fields->addFieldToTab('Root.Main', OptionsetField::create('ContentBlock', false, $options, $checked));

		$this->extend('updateCMSSelectionFields', $fields);

		return $fields;
	}

	public function providePermissions()
	{
		return [
			'VIEW_CONTENT_BLOCKS' => [
				'name' => 'View content blocks',
				'help' => 'Allow viewing page content blocks',
				'category' => 'Page - Content Blocks',
				'sort' => 100
			],
			'CREATE_CONTENT_BLOCKS' => [
				'name' => 'Create content blocks',
				'help' => 'Allow creating page content blocks',
				'category' => 'Page - Content Blocks',
				'sort' => 100
			],
			'EDIT_CONTENT_BLOCKS' => [
				'name' => 'Edit content blocks',
				'help' => 'Allow editing page content blocks',
				'category' => 'Page - Content Blocks',
				'sort' => 100
			],
			'DELETE_CONTENT_BLOCKS' => [
				'name' => 'Delete content blocks',
				'help' => 'Allow deleting page content blocks',
				'category' => 'Page - Content Blocks',
				'sort' => 100
			]
		];
	}

	public function canView($member = null, $context = [])
	{
		return Permission::check('VIEW_CONTENT_BLOCKS', 'any', $member);
	}

	public function canCreate($member = null, $context = [])
	{
		return Permission::check('CREATE_CONTENT_BLOCKS', 'any', $member);
	}

	public function canEdit($member = null, $context = [])
	{
		return Permission::check('EDIT_CONTENT_BLOCKS', 'any', $member);
	}

	public function canDelete($member = null, $context = [])
	{
		return Permission::check('DELETE_CONTENT_BLOCKS', 'any', $member);
	}
}
