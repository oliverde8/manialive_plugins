<?php

namespace ManiaLivePlugins\oliverde8\HudMenu;


class onOliverde8HudMenuReady extends \ManiaLive\Event\Event{

	
    private $menu;
    
    function __construct($menu){
        $this->menu = $menu;
        $this->onWhat = self::ALL;
    }

    function fireDo($listener){
		call_user_func_array(array($listener, 'onOliverde8HudMenuReady'), array($this->menu));
    }


}
?>
