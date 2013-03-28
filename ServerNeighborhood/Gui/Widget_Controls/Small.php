<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Widget_Controls;

use ManiaLivePlugins\oliverde8\ServerNeighborhood\Server;

/**
 *
 * @author oliverde8
 */
class Small extends \ManiaLive\Gui\Control {
    
    private $bg;
    private $label_name;
    
    function __construct($i, $ctr, Server $server){
        
        $sizeX = 20;
        $sizeY = 4;
        $this->bg = new \ManiaLib\Gui\Elements\BgsPlayerCard($sizeX, $sizeY*0.6+1);
        $this->bg->setPosX(0);
        $this->bg->setSubStyle(\ManiaLib\Gui\Elements\BgsPlayerCard::BgCardSystem);
        $this->bg->setAlign('left', 'top');
        $this->bg->setManialink('maniaplanet://#join='.$server->getServer_data()->server->login.'@'.$server->getServer_data()->server->packmask);
        $this->addComponent($this->bg);
        
        $this->label_name = new \ManiaLib\Gui\Elements\Label($sizeX-6, $sizeY);
        $this->label_name->setPosX(1);
        $this->label_name->setPosY(0);
        $this->label_name->setAlign('left', 'top');
        $this->label_name->setScale(0.6);
        $this->label_name->setText('$AAA'.$server->getServer_data()->server->name);
        $this->addComponent($this->label_name);
        
        $this->sizeY = $sizeY*0.6+1;

        $this->label_name->setManialink('maniaplanet://#join='.$server->getServer_data()->server->login.'@'.$server->getServer_data()->server->packmask);
    }
    
    public function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->bg->setSizeX($this->getSizeX());
        $this->label_name->setSizeX($this->getSizeX()/.6 - 2);
    }
    
}

?>
