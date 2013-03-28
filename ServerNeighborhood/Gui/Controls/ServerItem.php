<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Controls;

/**
 * Description of ServerItem
 *
 * @author oliverde8
 */
class ServerItem extends \ManiaLive\Gui\Control{
    
    private static $bgStyle = 'Bgs1';
    private static $bgStyles = array('BgList', 'BgListLine');
    
    private $bg_num;
    private $label_num;
    
    private $bg_main;
    private $label_sname;
    
    //Server information
    private $icons_frame;
    private $icon_status, $icon_game, $icon_player, $icon_specs, $icon_ladder;
    private $label_nbPlayers, $label_nbSpecs, $label_ladder;
    
    //Server Map information
    private $map_frame;
    private $icon_map, $icon_author, $icon_envi, $icon_gtime;
    private $label_map, $label_author, $label_envi, $label_gtime;
    
    
    private $bg_jspec;
    private $bg_jplayer;
    private $bg_fav;
    private $bg_info;
    
    function __construct($indexNumber, \ManiaLivePlugins\oliverde8\ServerNeighborhood\Server $server) {
        
        $sizeY = 10;
        $YSpace = 0.2;
        $bsize = $sizeY/2;
        
        $this->bg_num = new \ManiaLib\Gui\Elements\Quad($bsize,$sizeY);
        $this->bg_num->setStyle(self::$bgStyle);
        $this->bg_num->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_num);
        
        $this->label_num = new \ManiaLib\Gui\Elements\Label($bsize-2.5,$sizeY-1);
        $this->label_num->setPosX($bsize/2);        
        $this->label_num->setPosY(-$sizeY/2);
        $this->label_num->setAlign("center","center");
        $this->label_num->setScale(1.5);
        $this->label_num->setText('$DDD'.$indexNumber);
        $this->addComponent($this->label_num);
        
        $this->bg_main = new \ManiaLib\Gui\Elements\Quad($this->getSizeX()-(4*$bsize)-1, $sizeY);
        $this->bg_main->setPosX($bsize+.5);
        $this->bg_main->setStyle(self::$bgStyle);
        $this->bg_main->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_main);
        
        $this->createMain($server, $sizeY, $bsize);
        $this->createMap($server, $sizeY, $bsize);
        
        $this->bg_jplayer = new \ManiaLib\Gui\Elements\Quad($bsize,$sizeY);
        $this->bg_jplayer->setStyle(self::$bgStyle);
        $this->bg_jplayer->setSubStyle(self::$bgStyles[$indexNumber%2]);
        
        $this->addComponent($this->bg_jplayer);
        
        $this->bg_jspec = new \ManiaLib\Gui\Elements\Quad($bsize,$sizeY/2-$YSpace);
        $this->bg_jspec->setStyle(self::$bgStyle);
        $this->bg_jspec->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_jspec);
        
        $this->bg_fav = new \ManiaLib\Gui\Elements\Quad($bsize,$sizeY/2-$YSpace);
        $this->bg_fav->setStyle(self::$bgStyle);
        $this->bg_fav->setSubStyle(self::$bgStyles[$indexNumber%2]);
        $this->addComponent($this->bg_fav);
        
        $this->bg_info = new \ManiaLib\Gui\Elements\Quad($bsize,$sizeY);
        $this->bg_info->setStyle(self::$bgStyle);
        $this->bg_info->setSubStyle(self::$bgStyles[$indexNumber%2]);      
        $this->addComponent($this->bg_info);
        
        $this->setSize($this->getSizeX(), $sizeY+$YSpace);
    }
    
    public function onResize($oldX, $oldY) {
        $sizeY = 10;
        $YSpace = 0.5;
        $bsize = $sizeY/2;
       
        $this->bg_main->setSizeX($this->getSizeX()-(4*$bsize)-2);
        
        $this->bg_info->setPosition($this->getSizeX() - $bsize -1, 0);
        $this->bg_fav->setPosition($this->getSizeX() - 2*$bsize -1, -$sizeY/2 - .5);
        $this->bg_jspec->setPosition($this->getSizeX() - 2*$bsize - 1, 0);
        $this->bg_jplayer->setPosition($this->getSizeX() - 3*$bsize -1 , 0);
        
        $this->icon_status->setPosX($this->getSizeX()-4);
        $this->icon_game->setPosX($this->getSizeX()-3*$bsize - $this->icon_game->getSizeX() - 2);
        
        $this->icons_frame->setPositionX($this->bg_main->getPosX() + $this->bg_main->getSizeX()/2 - $sizeY);
        $this->icon_specs->setPosX(($this->bg_main->getSizeX()/2-2)/2 +2 );
        $this->label_nbSpecs->setPosX($this->icon_specs->getPosX()+$this->icon_specs->getSizeX());
        $this->icon_ladder->setPosX($this->bg_main->getSizeX()/2 - 10);
        $this->label_ladder->setPosX($this->icon_ladder->getPosX()+$this->icon_specs->getSizeX());
        
        $this->label_sname->setSizeX($this->bg_main->getSizeX()/2- $sizeY);
        
        $this->icon_author->setPosX(( ($this->bg_main->getSizeX() -10)/16)*6);
        $this->label_author->setPosX($this->icon_author->getPosX()+$this->icon_author->getSizeX());
        
        $this->icon_envi->setPosX(( ($this->bg_main->getSizeX() -10)/16)*10);
        $this->label_envi->setPosX($this->icon_envi->getPosX()+$this->icon_envi->getSizeX());
        
        $this->icon_gtime->setPosX(( ($this->bg_main->getSizeX() -10)/16)*13);
        $this->label_gtime->setPosX($this->icon_gtime->getPosX()+$this->icon_gtime->getSizeX());
        
        parent::onResize($oldX, $oldY);
    }


    public function destroy() {
        parent::destroy();
    }
    
    private function createMain(\ManiaLivePlugins\oliverde8\ServerNeighborhood\Server $server, $sizeY, $bsize){
        
        $frame = new \ManiaLib\Gui\Elements\Frame();
        $frame->setPosY(-1);
        $iSize = $sizeY/2 - 1;
        
        
        $this->icon_status = new \ManiaLib\Gui\Elements\Quad($sizeY+0.6, $sizeY+0.6);
        $this->icon_status->setPosY(-.2);
        if($server->getServer_data()->server->login == \ManiaLive\Data\Storage::getInstance()->serverLogin){
            $this->icon_status->setStyle('Icons128x128_1');
            $this->icon_status->setSubStyle('Back');
            $this->addComponent($this->icon_status);
        }else if($server->getServer_data()->server->private == 'true'){
            $this->icon_status->setStyle('Icons128x128_1');
            $this->icon_status->setSubStyle('Padlock');
            $this->addComponent($this->icon_status);
        }
        
        $this->icon_game = new \ManiaLib\Gui\Elements\Quad($sizeY-3,$sizeY-3);
        $this->icon_game->setPosY(-1); 
        $this->icon_game->setStyle('Icons128x32_1');
        $this->icon_game->setSubStyle(\ManiaLivePlugins\oliverde8\ServerNeighborhood\ServerNeighborhood::$gamemodes[(int)$server->getServer_data()->server->gamemode]['icon']);
        $this->addComponent($this->icon_game);
        
        $this->icon_player = new \ManiaLib\Gui\Elements\Icons64x64_1($iSize,$iSize);
        $this->icon_player->setPosX($bsize+2);
        $this->icon_player->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::Buddy);
        $frame->add($this->icon_player);
        
        $this->label_nbPlayers = new \ManiaLib\Gui\Elements\Label($iSize*2, $sizeY*0.6+0.6);
        $this->label_nbPlayers->setPosX($this->icon_player->getPosX()+$this->icon_player->getSizeX());
        $this->label_nbPlayers->setPosY($this->icon_player->getPosY()-.5);
        $this->label_nbPlayers->setScale(.8);
        if((int)$server->getServer_data()->server->players->current == (int)$server->getServer_data()->server->players->maximum)
            $this->label_nbPlayers->setText ('$F00'.$server->getServer_data()->server->players->current.'/'.$server->getServer_data()->server->players->maximum);
        else 
            $this->label_nbPlayers->setText ('$FFF'.$server->getServer_data()->server->players->current.'/'.$server->getServer_data()->server->players->maximum);
        $frame->add($this->label_nbPlayers);
        
        $this->icon_specs = new \ManiaLib\Gui\Elements\Icons64x64_1($iSize,$iSize);
        $this->icon_specs->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::IconPlayers);
        $frame->add($this->icon_specs);
        
        $this->label_nbSpecs = new \ManiaLib\Gui\Elements\Label($iSize*2, $sizeY*0.6+0.6);
        $this->label_nbSpecs->setScale(.8);
        $this->label_nbSpecs->setPosY($this->icon_specs->getPosY()-.5);
        if((int)$server->getServer_data()->server->players->current == (int)$server->getServer_data()->server->players->maximum)
            $this->label_nbSpecs->setText ('$F00'.$server->getServer_data()->server->spectators->current.'/'.$server->getServer_data()->server->spectators->maximum);
        else
            $this->label_nbSpecs->setText ('$FFF'.$server->getServer_data()->server->spectators->current.'/'.$server->getServer_data()->server->spectators->maximum);
        $frame->add($this->label_nbSpecs);
        
        $this->icon_ladder = new \ManiaLib\Gui\Elements\Icons64x64_1($iSize,$iSize);
        $this->icon_ladder->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::ToolLeague1);
        $frame->add($this->icon_ladder);
        
        $this->label_ladder = new \ManiaLib\Gui\Elements\Label($iSize*2,$iSize);
        $this->label_ladder->setScale(.8);
        $this->label_ladder->setPosY($this->icon_ladder->getPosY()-.5);
        $this->label_ladder->setText('$FFF'.$server->getServer_data()->server->ladder->minimum.'/'.$server->getServer_data()->server->ladder->maximum);
        $frame->add($this->label_ladder);
        
        $this->addComponent($frame);
        $this->icons_frame = $frame;
        
        $this->label_sname = new \ManiaLib\Gui\Elements\Label($this->bg_main->getSizeX()/2-2, $sizeY/2);
        $this->label_sname->setPosX($bsize+2);
        $this->label_sname->setPosY(-1*.6);
        $this->label_sname->setAlign('left', 'top');
        $this->label_sname->setScale(1);
        $this->label_sname->setText('$AAA'.$server->getServer_data()->server->name);
        $this->addComponent($this->label_sname);
    }
    
    public function createMap(\ManiaLivePlugins\oliverde8\ServerNeighborhood\Server $server, $sizeY, $bsize){
        
        $iSize = $sizeY/2 - 1;
        $tscale = .8;
        
        $this->map_frame = new \ManiaLib\Gui\Elements\Frame();
        $this->map_frame->setPosY(-$sizeY/2);
        $this->map_frame->setPosX($bsize+2);
        $this->addComponent($this->map_frame);
        
        $this->icon_map = new \ManiaLib\Gui\Elements\Quad($iSize, $iSize);
        $this->icon_map->setStyle('Icons128x128_1');
        $this->icon_map->setSubStyle('NewTrack');
        $this->map_frame->add($this->icon_map);
        
        $this->label_map = new \ManiaLib\Gui\Elements\Label(20,$iSize);
        $this->label_map->setScale(.8);
        $this->label_map->setPosX($this->icon_map->getPosX()+$this->icon_map->getSizeX());
        $this->label_map->setPosY($this->icon_map->getPosY()-.5);
        $this->label_map->setText('$FFF'.$server->getServer_data()->current->map->name);
        $this->map_frame->add($this->label_map);
        
        $this->icon_author = new \ManiaLib\Gui\Elements\Quad($iSize, $iSize);
        $this->icon_author->setStyle('Icons128x128_1');
        $this->icon_author->setSubStyle('Solo');
        $this->map_frame->add($this->icon_author);
        
        $this->label_author = new \ManiaLib\Gui\Elements\Label(20,$iSize);
        $this->label_author->setScale(.7);
        $this->label_author->setPosY($this->icon_author->getPosY()-.5);
        $this->label_author->setText('$FFF'.$server->getServer_data()->current->map->author);
        $this->map_frame->add($this->label_author);
        
        $this->icon_envi = new \ManiaLib\Gui\Elements\Quad($iSize, $iSize);
        $this->icon_envi->setStyle('Icons128x128_1');
        $this->icon_envi->setSubStyle('Nations');
        $this->map_frame->add($this->icon_envi);
        
        $this->label_envi = new \ManiaLib\Gui\Elements\Label(20,$iSize);
        $this->label_envi->setScale(.7);
        $this->label_envi->setPosY($this->icon_author->getPosY()-.5);
        $this->label_envi->setText('$FFF'.$server->getServer_data()->current->map->environment);
        $this->map_frame->add($this->label_envi);
        
        $this->icon_gtime = new \ManiaLib\Gui\Elements\Quad($iSize, $iSize);
        $this->icon_gtime->setStyle('MedalsBig');
        $this->icon_gtime->setSubStyle('MedalGold');
        $this->map_frame->add($this->icon_gtime);
        
        $this->label_gtime = new \ManiaLib\Gui\Elements\Label(20,$iSize);
        $this->label_gtime->setScale(.7);
        $this->label_gtime->setPosY($this->icon_author->getPosY()-.5);
        $this->label_gtime->setText('$FFF'.$server->getServer_data()->current->map->goldtime);
        $this->map_frame->add($this->label_gtime);
        
    }
}

?>
