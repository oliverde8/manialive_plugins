<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Widget_Controls;

use ManiaLivePlugins\oliverde8\ServerNeighborhood\Server;

/**
 *
 * @author oliverde8
 */
class UndefStyle extends \ManiaLive\Gui\Control {
    
    public static $gamemodes = array(
		0 => array('name' => 'SCRIPT',		'icon' => 'RT_Script'),
		1 => array('name' => 'ROUNDS',		'icon' => 'RT_Rounds'),
		2 => array('name' => 'TIME_ATTACK',	'icon' => 'RT_TimeAttack'),
		3 => array('name' => 'TEAM',		'icon' => 'RT_Team'),
		4 => array('name' => 'LAPS',		'icon' => 'RT_Laps'),
		5 => array('name' => 'CUP',         'icon' => 'RT_Cup'),
		6 => array('name' => 'STUNTS',		'icon' => 'RT_Stunts'),
	);
    
    private $bg;
    private $label_name;
    
    private $icon_status, $icon_game, $icon_player, $icon_specs, $icon_ladder;
    private $label_nbPlayers, $label_nbSpecs, $label_ladder;
    
    function __construct($i, Server $server){
        
        $sizeX = $this->getSizeX();
        $sizeY = 8;
        $this->bg = new \ManiaLib\Gui\Elements\BgsPlayerCard($sizeX, $sizeY*0.6+1);
        $this->bg->setPosX(0);
        $this->bg->setSubStyle(\ManiaLib\Gui\Elements\BgsPlayerCard::BgCardSystem);
        $this->bg->setAlign('left', 'top');
        $this->bg->setManialink('maniaplanet://#join='.$server->getServer_data()->server->login.'@'.$server->getServer_data()->server->packmask);
        $this->addComponent($this->bg);
        
        $this->icon_status = new \ManiaLib\Gui\Elements\Quad($sizeY*0.6+0.6, $sizeY*0.6+0.6);
        $this->icon_status->setPosY(-.2);
        $this->icon_status->setPosX($this->getSizeX()-2);
        if($server->getServer_data()->server->login == \ManiaLive\Data\Storage::getInstance()->serverLogin){
            $this->icon_status->setStyle('Icons128x128_1');
            $this->icon_status->setSubStyle('Back');
            $this->addComponent($this->icon_status);
        }else if($server->getServer_data()->server->private == 'true'){
            $this->icon_status->setStyle('Icons128x128_1');
            $this->icon_status->setSubStyle('Padlock');
            $this->addComponent($this->icon_status);
        }
        
        $this->icon_game = new \ManiaLib\Gui\Elements\Quad(3,3);
        $this->icon_game->setPosY(-0.4);
        $this->icon_game->setPosX($this->getSizeX()-4);
        $this->icon_game->setStyle('Icons128x32_1');
        $this->icon_game->setSubStyle(self::$gamemodes[(int)$server->getServer_data()->server->gamemode]['icon']);
        $this->addComponent($this->icon_game);
        
        $this->icon_player = new \ManiaLib\Gui\Elements\Icons64x64_1(2.5,2.5);
        $this->icon_player->setPosY(-4*.6-.4);
        $this->icon_player->setPosX(2.5);
        $this->icon_player->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::Buddy);
        $this->addComponent($this->icon_player);
        
        $this->label_nbPlayers = new \ManiaLib\Gui\Elements\Label(10, $sizeY*0.6+0.6);
        $this->label_nbPlayers->setPosY($this->icon_player->getPosY()-.5);
        $this->label_nbPlayers->setPosX($this->icon_player->getPosX()+2);
        $this->label_nbPlayers->setScale(.5);
        if((int)$server->getServer_data()->server->players->current == (int)$server->getServer_data()->server->players->maximum)
            $this->label_nbPlayers->setText ('$F00'.$server->getServer_data()->server->players->current.'/'.$server->getServer_data()->server->players->maximum);
        else 
            $this->label_nbPlayers->setText ('$FFF'.$server->getServer_data()->server->players->current.'/'.$server->getServer_data()->server->players->maximum);
        $this->addComponent($this->label_nbPlayers);
        
        $this->icon_specs = new \ManiaLib\Gui\Elements\Icons64x64_1(2.5,2.5);
        $this->icon_specs->setPosY(-4*.6-.4);
        $this->icon_specs->setPosX(($this->getSizeX()-2)/2-1);
        $this->icon_specs->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::IconPlayers);
        $this->addComponent($this->icon_specs);
        
        $this->label_nbSpecs = new \ManiaLib\Gui\Elements\Label(10, $sizeY*0.6+0.6);
        $this->label_nbSpecs->setPosY($this->icon_specs->getPosY()-.5);
        $this->label_nbSpecs->setPosX($this->icon_specs->getPosX()+2);
        $this->label_nbSpecs->setScale(.5);
        if((int)$server->getServer_data()->server->players->current == (int)$server->getServer_data()->server->players->maximum)
            $this->label_nbSpecs->setText ('$F00'.$server->getServer_data()->server->spectators->current.'/'.$server->getServer_data()->server->spectators->maximum);
        else
            $this->label_nbSpecs->setText ('$FFF'.$server->getServer_data()->server->spectators->current.'/'.$server->getServer_data()->server->spectators->maximum);
        $this->addComponent($this->label_nbSpecs);
        
        $this->icon_ladder = new \ManiaLib\Gui\Elements\Icons64x64_1(2.5,2.5);
        $this->icon_ladder->setPosY(-4*.6-.4);
        $this->icon_ladder->setPosX($this->getSizeX()-8);
        $this->icon_ladder->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::ToolLeague1);
        $this->addComponent($this->icon_ladder);
        
        $this->label_ladder = new \ManiaLib\Gui\Elements\Label(10, $sizeY*0.6+0.6);
        $this->label_ladder->setPosY($this->icon_ladder->getPosY()-.5);
        $this->label_ladder->setPosX($this->icon_ladder->getPosX()+2);
        $this->label_ladder->setScale(.5);
        $this->label_ladder->setText('$FFF'.$server->getServer_data()->server->ladder->minimum.'/'.$server->getServer_data()->server->ladder->maximum);
        $this->addComponent($this->label_ladder);
        
        $this->label_name = new \ManiaLib\Gui\Elements\Label($sizeX/.6 - 12, $sizeY/2);
        $this->label_name->setPosX(2);
        $this->label_name->setPosY(-1*.6);
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
        $this->label_name->setSizeX($this->getSizeX()/.6 - 12);
        
        $this->icon_status->setPosX($this->getSizeX()-2.2);
        $this->icon_game->setPosX($this->getSizeX()-4);
        
        $this->icon_specs->setPosX(($this->getSizeX()-2)/2-1);
        $this->label_nbSpecs->setPosX($this->icon_specs->getPosX()+2);
        
        $this->icon_ladder->setPosX($this->getSizeX()-8);
        $this->label_ladder->setPosX($this->icon_ladder->getPosX()+2);
    }
    
}

?>
