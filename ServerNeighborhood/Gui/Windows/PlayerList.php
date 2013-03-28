<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Windows;

use ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Controls\ServerItem;

/**
 * Description of ServerList
 *
 * @author oliverde8
 */
class PlayerList extends \ManiaLive\Gui\ManagedWindow{
    
    private $pager;
    private $items = array();
    
    protected function onConstruct(){
        parent::onConstruct();
        $this->pager = new \ManiaLive\Gui\Controls\Pager($this->getSizeX()-2, $this->getSizeY()-18);
        $this->pager->setPosY(-15);
        $this->pager->setPosX(1);
        $this->addComponent($this->pager);      
    }
    
    public function setServer($server){
        
        $this->pager->clearItems();
        foreach ($this->items as $item) {
            $item->destroy();            
        }        
        $this->items = array();
        
        $i = 1;
        foreach($servers as $player){
            
        }

    }
    
    public function onDraw(){
        
    }
    
    public function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->pager->setSize($this->getSizeX()-2, $this->getSizeY()-18);
         foreach ($this->items as $item) {
            $item->setSizeX($this->getSizeX()-2);            
        }  
    }
    
    public function destroy() {
        foreach ($this->items as $item) {
            $item->destroy();            
        }        
        $this->items = null;
        $this->pager->destroy();
        parent::destroy();
    }
    
    public function showServerPlayers($login, $server){
        echo"Show ...\n";
    }
}

?>
