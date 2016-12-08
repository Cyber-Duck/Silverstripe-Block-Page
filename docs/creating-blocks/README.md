# Creating Blocks

A block consists of 2 parts; a DataObject and a .ss template. Both these should have the same name.

e.g: 
  - EditorBlock.php
  - EditorBlock.ss

## Block YML Configuration

Define all your blocks in the YML configuration with the "blocks" option.

```yml
BlockPage:
  blocks:
    - EditorBlock
    - FeaturedImageBlock
    - FeaturedQuoteBlock
```

## Block Page Configuration

To loop out the page content blocks add the following to the .ss template file for the page that has the block page extension attached.

```
<% loop ContentBlocks %>
$Top.IncludeBlock($ClassName, $ID)
<% end_loop %>
```

## Block DataObject

This should extends ContentBlock and should have title and description configuration properties as a minimum.

```php
class EditorBlock extends ContentBlock
{
    private static $title = 'Editor';

    private static $description = 'Simple WYSIWYG editor block';

    private static $db = [
        'Text' => 'HTMLText'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }
}
```

## Block Template

All the DataObject properties will be available within your block template. 

The block templates should reside in /themes/YOUR-THEME/templates/Blocks/

```
<div>
    $Text
</div>
```
