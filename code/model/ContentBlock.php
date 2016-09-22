<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class ContentBlock extends DataObject
{
	private static $db = array(
		'Name'        => 'Varchar(512)',
        'CssSelector' => 'Varchar(512)'
	);

	private static $belongs_any_many = array(
		'Pages' => 'Page'
	);
}