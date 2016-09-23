 <?php

class BlockPageAdmin extends ModelAdmin {

    //private static $menu_priority = 100;

    private static $managed_models = array('ContentBlock');

    private static $url_segment = 'blockpage-admin';

    private static $menu_title = 'Page Blocks';
    
    private static $menu_icon = 'framework/admin/images/menu-icons/16x16/community.png';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $types = Config::inst()->get('BlockPage', 'blocks');

        Config::inst()->update('BlockPageAdmin', 'managed_models', $types);

        $form
            ->Fields()
            ->fieldByName($this->sanitiseClassName($this->modelClass))
            ->getConfig()
            ->getComponentByType('GridFieldDetailForm')
            ->setItemRequestClass('CreateBlock_ItemRequest');

        return $form;
    }
}