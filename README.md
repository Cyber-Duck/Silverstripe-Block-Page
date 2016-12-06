# SilverStripe Block Page

[![Latest Stable Version](https://poser.pugx.org/cyber-duck/silverstripe-block-page/v/stable)](https://packagist.org/packages/cyber-duck/silverstripe-block-page)
[![Total Downloads](https://poser.pugx.org/cyber-duck/silverstripe-block-page/downloads)](https://packagist.org/packages/cyber-duck/silverstripe-block-page)
[![License](https://poser.pugx.org/cyber-duck/silverstripe-block-page/license)](https://packagist.org/packages/cyber-duck/silverstripe-block-page)

A modular approach to building pages in SilverStripe. Allows creation of pages in blocks allowing maximum flexibility for developers and CMS admins.
  - Customize block fields easily like you would any other DataObject
  - Use repeating block components and unlimited block variations to create infinite layout variations
  - Use drag and drop GridField functionality to change and re-order blocks easily
  - Tie in things like forms to blocks

Author: [Andrew Mc Cormack](https://github.com/Andrew-Mc-Cormack)

# Installation

## Composer

Add the following to your composer.json file

```json
{  
    "require": {  
        "cyber-duck/silverstripe-block-page": "1.0.*"
    }
}
```

## Extension

In your config.yml file add the block page extension to a Page object

```yml
BlogPage:
  extensions:
    - BlockPageExtension
```

# Creating Blocks

## Block makeup

A block consists of 2 parts; a DataObject and a .ss template. Both these should have the same name.

e.g: 
  - EditorBlock.php
  - EditorBlock.ss

### The DataObject 

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

### The .SS Template

All the DataObject properties will be available within your block template.

```
<div>
    $Text
</div>
```

## Defining a block

Define all your blocks in the YML configuration with the "blocks" option.

```yml
BlockPage:
  blocks:
    - EditorBlock
    - FeaturedImageBlock
    - FeaturedQuoteBlock
```

# Loading Blocks

To loop out the page content blocks add the following to the .ss template file for the page that has the block page extension attached.

```
<% loop ContentBlocks %>
$Top.IncludeBlock($ClassName, $ID)
<% end_loop %>
```

## License

```
Copyright (c) 2016, Andrew Mc Cormack <andrewm@cyber-duck.co.uk>.
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in
      the documentation and/or other materials provided with the
      distribution.

Neither the name of Andrew Mc Cormack nor the names of his
contributors may be used to endorse or promote products derived
from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
```