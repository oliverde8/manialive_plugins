<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Controls;

/**
 * Description of PlayerItem
 *
 * @author oliverde8
 */
class PlayerItem extends \ManiaLive\Gui\Control{
    
    private static $bgStyle = 'Bgs1';
    private static $bgStyles = array('BgList', 'BgListLine');
    
    private $bg_nick, $bg_login, $bg_nation, $bg_ladder, $bg_spec;
    private $label_nick, $label_login, $label_nation, $label_ladder;
    private $icon_spec;
    
                //nickname, login, nation, ladder, spectator
    function __construct($indexNumber, $ctr, $player) {
        $sizeY = 4;
        
        $this->bg_nick = new \ManiaLib\Gui\Elements\Quad(0, $sizeY);
        $this->bg_nick->setStyle(self::$bgStyle);
        $this->bg_nick->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_nick);
        
        $this->label_nick = new \ManiaLib\Gui\Elements\Label(0,$sizeY);
        $this->label_nick->setScale(.8);
        $this->label_nick->setPosition(1, -1);
        $this->label_nick->setText('$FFF'.$player->nickname);
        $this->addComponent($this->label_nick);
        
        $this->bg_login = new \ManiaLib\Gui\Elements\Quad(0, $sizeY);
        $this->bg_login->setStyle(self::$bgStyle);
        $this->bg_login->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_login);
        
        $this->label_login = new \ManiaLib\Gui\Elements\Label(0,$sizeY);
        $this->label_login->setScale(.8);
        $this->label_login->setPosition(1, $this->bg_login->getPosY()-1);
        $this->label_login->setText('$FFF'.$player->login);
        $this->addComponent($this->label_login);
        
        $this->bg_nation = new \ManiaLib\Gui\Elements\Quad(0, $sizeY);
        $this->bg_nation->setStyle(self::$bgStyle);
        $this->bg_nation->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_nation);
        
        $this->label_nation = new \ManiaLib\Gui\Elements\Label(0,$sizeY);
        $this->label_nation->setScale(.8);
        $this->label_nation->setPosition(1, $this->bg_nation->getPosY()-1);
        $this->label_nation->setText('$FFF'.$player->nation);
        $this->addComponent($this->label_nation);
        
        $this->bg_ladder = new \ManiaLib\Gui\Elements\Quad(0, $sizeY);
        $this->bg_ladder->setStyle(self::$bgStyle);
        $this->bg_ladder->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_ladder);
        
        $this->label_ladder = new \ManiaLib\Gui\Elements\Label(0,$sizeY);
        $this->label_ladder->setScale(.8);
        $this->label_ladder->setPosition(1, $this->bg_ladder->getPosY()-1);
        $this->label_ladder->setText('$FFF'.$player->ladder);
        $this->addComponent($this->label_ladder);
        

        if($player->spectator == 'true'){
            $this->icon_spec =  new \ManiaLib\Gui\Elements\Icons64x64_1(6,4);
            $this->icon_spec->setSubStyle (\ManiaLib\Gui\Elements\Icons64x64_1::CameraLocal);
            $this->icon_spec->setPosY(1);
        }else{
            $this->icon_spec =  new \ManiaLib\Gui\Elements\UIConstructionSimple_Buttons(10,10);
            $this->icon_spec->setSubStyle (\ManiaLib\Gui\Elements\UIConstructionSimple_Buttons::Drive);
            $this->icon_spec->setPosY(3.5);
        }
        $this->addComponent($this->icon_spec);
        
        $this->sizeY = 4;
    }
    
    public function onResize($oldX, $oldY) {
        $this->bg_nick->setSize($this->getSizeX()*.3, $this->getSizeY());
        $this->label_nick->setSize( ($this->getSizeX()*.3)/.8, $this->getSizeY());
        
        $this->bg_login->setSize($this->getSizeX()*.2, $this->getSizeY());
        $this->bg_login->setPosX($this->getSizeX()*.3 + 1);
        $this->label_login->setSizeX( ($this->bg_login->getSizeX()-2)/.8);
        $this->label_login->setPosX($this->bg_login->getPosX()+1);
        
        $this->bg_nation->setSize($this->getSizeX()*.3, $this->getSizeY());
        $this->bg_nation->setPosX($this->getSizeX()*.5 + 1);
        $this->label_nation->setPosX($this->bg_nation->getPosX()+1);
        
        $this->bg_ladder->setSize($this->getSizeX()*.1, $this->getSizeY());
        $this->bg_ladder->setPosX($this->getSizeX()*.8 + 1);
        $this->label_ladder->setPosX($this->bg_ladder->getPosX()+1);
        
        $sizeX = $this->getSizeX()*.08;
        $posX = $this->getSizeX()*.9 + 1;
        
        $this->icon_spec->setPosX($posX + $sizeX/2 - $this->icon_spec->getSizeX()/2);
    }
}

?>
