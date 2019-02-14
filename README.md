# SilverStripe 4 Block Page

[![Latest Stable Version](https://poser.pugx.org/cyber-duck/silverstripe-block-page/v/stable)](https://packagist.org/packages/cyber-duck/silverstripe-block-page)
[![Latest Unstable Version](https://poser.pugx.org/cyber-duck/silverstripe-block-page/v/unstable)](https://packagist.org/packages/cyber-duck/silverstripe-block-page)
[![Total Downloads](https://poser.pugx.org/cyber-duck/silverstripe-block-page/downloads)](https://packagist.org/packages/cyber-duck/silverstripe-block-page)
[![License](https://poser.pugx.org/cyber-duck/silverstripe-block-page/license)](https://packagist.org/packages/cyber-duck/silverstripe-block-page)

Author: [Andrew Mc Cormack](https://github.com/Andrew-Mc-Cormack)

## Features

A modular approach to building pages in SilverStripe which allows model based page components.
  - Custom model based blocks
  - No limit to number of blocks
  - Easily block selection and editing
  - Use drag and drop GridField functionality to change and re-order blocks easily
  - Apply complex logic like forms to blocks
  - Versioning across blocks

## Installation

Add the following to your composer.json file and run /dev/buid?flush=all

```json
{  
    "require": {  
        "cyber-duck/silverstripe-block-page": "4.3.*"
    }
}
```

***

## Setup

### Add Extension 

The first step to adding block functionality is to apply the block page extension to your DataObject. This can be a normal DataObject or a Page.
This will add a new tab to the CMS called content blocks.

```yml
Page:
  extensions:
    - BlockPageExtension
```

### Add Block Model

The next step is to create a block. A block consists of 2 parts; a DataObject and a .ss template. Both these should have the same name.

  - EditorBlock.php
  - EditorBlock.ss

The model file can reside anywhere inside your code folder and should extend ContentBlock
The base template for a block DataObject is as follows:

```php
use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

class EditorBlock extends ContentBlock
{
    private static $title = 'Editor';

    private static $description = 'Simple WYSIWYG editor block';
    
    private static $preview = '/resources/themes/{YourTheme}/img/block/EditorBlock.png';

    private static $db = [
        'Content' => 'HTMLText'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        # FIELDS - YOUR FIELDS HERE
        $fields->addFieldToTab('Root.Main', HTMLEditorField::create('Content')); // example field

        return $fields;
    }
}
```

In the example above 1 custom block field is created called Content. You can replace this / add any other fields you want.
There are 3 config properties used for a block used in the block selection screen:

  - $title - Block title
  - $description - Block description
  - $preview - Preview image for the block. You can point this to an image folder in your theme or similar. 360w x 150h.

### Add Block template

Next in your theme folder create a folder at themes/{YourTheme}/templates/Block/ and add the EditorBlock.ss template within with the following content:

```
<div>
    $Content
</div>
```

### Add Block YML Config

The final step to configuring your blocks is to set up the block YML config and visit /dev/build?flush=all

```yml
---
Name: block config
---
CyberDuck\BlockPage\Model\ContentBlock:
  blocks:
    - EditorBlock
  restrict:
```

You can restrict certain block selections to a particular page type by passing a restrict option

```yml
CyberDuck\BlockPage\Model\ContentBlock:
  blocks:
    - EditorBlock
    - HomeFeaturedBlock
  restrict:
    HomePage:
      - HomeFeaturedBlock
```

### Add Block Loop

Add the loop within your page template for the blocks:

```html
<% loop ContentBlocks.Sort('SortBlock') %>
$Template
<% end_loop %>
```

If you wish to wrap all blocks within a certain template you can create a ContentBlock_holder.ss template within the /Block/ folder.

```html
<div id="block-$ID">
    $Template
</div>
```

The loop within your page needs to change slightly and call $TemplateHolder instead of template.

```html
<% loop ContentBlocks.Sort('SortBlock') %>
$TemplateHolder
<% end_loop %>
```

### Add Blocks in the CMS

Go the the CMS and visit your Page / Object editing screen and you will see a new tab called Content Blocks.
Here you can create new blocks, edit blocks, and re-order blocks.

### Sorting 

The module includes a feature to append the last created item to the bottom of a sortable gridifeld list. 

Add many_many_sorting to your class config specifying the relationship and many many sort column.

```php
private static $many_many = [
    'ContentBlocks' => ContentBlock::class
];

private static $many_many_extraFields = [
    'ContentBlocks' => [
        'SortBlock' => 'Int'
    ]
];

private static $many_many_sorting = [
    'ContentBlocks' => 'SortBlock'
];
```

***

## Todo