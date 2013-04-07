<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Windows;

use ManiaLivePlugins\oliverde8\ServerNeighborhood\Server;

use ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Controls\ServerItem;
use \ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Controls\PlayerItem;

/**
 * Description of ServerList
 *
 * @author oliverde8
 */
class PlayerList extends \ManiaLive\Gui\ManagedWindow{
    
    private $pager;
    private $items = array();
    private $serverItem;
    
    protected function onConstruct(){
        parent::onConstruct();
        $this->pager = new \ManiaLive\Gui\Controls\Pager($this->getSizeX()-2, $this->getSizeY()-30);
        $this->pager->setPosY(-30);
        $this->pager->setPosX(1);
        $this->addComponent($this->pager);
        $this->setMaximizable(true);
    }
    
    public function setServer(Server $server){
        
        $this->serverItem = new ServerItem(0, null, $server);
        $this->serverItem->setPosY(-15);
        $this->serverItem->setSizeX($this->getSizeX()-2);
        $this->addComponent($this->serverItem);
        
        
        $this->pager->clearItems();
        foreach ($this->items as $item) {
            $item->destroy();            
        }        
        $this->items = array();
        
        $i = 1;
        foreach($server->getServer_data()->current->players->player as $player){

            $pitem = new PlayerItem($i, $this, $player);
            $pitem->setSizeX($this->getSizeX()-2);
            $this->items[] = $pitem;
            $this->pager->addItem($pitem);
        }      

    }
    
    public function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->pager->setSize($this->getSizeX()-2, $this->getSizeY()-35);
         foreach ($this->items as $item) {
            $item->setSizeX($this->getSizeX()-2);            
        } 
        if($this->serverItem != null)
            $this->serverItem->setSizeX($this->getSizeX()-2);
    }
    
    public function destroy() {
        foreach ($this->items as $item) {
            $item->destroy();            
        }        
        $this->items = null;
        $this->pager->destroy();
        $this->serverItem->destroy();
        parent::destroy();
    }
    
}

?>
