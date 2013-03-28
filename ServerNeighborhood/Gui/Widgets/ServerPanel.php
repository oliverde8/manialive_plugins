<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Widgets;

use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Windows\ServerList;

/**
 * Description of ServerPanel
 *
 * @author oliverde8
 */
class ServerPanel extends \ManiaLive\Gui\Window {

    public static $xml_config;

    private $servers = array();
    private $config;
    private $lastStart;
    private $first = true;

    private $items = array();
    
    private $frame;
    
    private $bg;
    private $bg_title;
    private $label_title;
    private $icon_title;
    
    private $bg_more;
    private $icon_all;
    private $label_all;

    public function onConstruct() {
        $this->config = \ManiaLivePlugins\oliverde8\ServerNeighborhood\Config::getInstance();

        $this->bg = new \ManiaLib\Gui\Elements\Bgs1InRace();
        $this->bg->setSubStyle(\ManiaLib\Gui\Elements\Bgs1InRace::BgTitleGlow);
        $this->bg->setAlign("left", "top");
        $this->addComponent($this->bg);
        
        $this->bg_title = new \ManiaLib\Gui\Elements\BgsPlayerCard();
        $this->bg_title->setSubStyle('BgRacePlayerName');
        $this->bg_title->setAlign("left", "top");
        $this->bg_title->setPosition(0.5,-0.5);
        $this->bg_title->setSizeY(4*0.8);
        $this->addComponent($this->bg_title);
        
        $this->label_title = new \ManiaLib\Gui\Elements\Label();
        $this->label_title->setAlign("left", "top");
        $this->label_title->setPosition(3.5,-1);
        $this->label_title->setSizeY(4);
        $this->label_title->setScale(0.8);
        $this->label_title->setText("Server Neighborhood");
        $this->addComponent($this->label_title);

        $this->bg_more = new \ManiaLib\Gui\Elements\BgsPlayerCard(10, 4);
        $this->bg_more->setSubStyle(\ManiaLib\Gui\Elements\BgsPlayerCard::BgCardSystem);
        $this->bg_more->setPosX(2);
        $this->bg_more->setPosY(-(4*0.8)-1);
        $this->addComponent($this->bg_more);
        
        $this->icon_all = new \ManiaLib\Gui\Elements\Icons64x64_1(4, 4);
        $this->icon_all->setSubStyle(\ManiaLib\Gui\Elements\Icons64x64_1::ArrowNext);
        $this->icon_all->setPosY(-(4*0.8)-1);
        $this->addComponent($this->icon_all);
        
        $this->label_all = new \ManiaLib\Gui\Elements\Label(20,4);
        $this->label_all->setAlign("right", "top");
        $this->label_all->setPosY($this->icon_all->getPosY()-1);
        $this->label_all->setSizeY(4);
        $this->label_all->setScale(0.6);
        $this->label_all->setText('$FFFShow All');
        $this->addComponent($this->label_all);
        
        $action = $this->createAction(array($this, 'showList'));
        $this->label_all->setAction($action);
        $this->icon_all->setAction($action);
        
        
        $this->icon_title = new Icons128x128_1(5,5);
        $this->icon_title->setPosition($this->getSizeX()-2,0);
        $this->icon_title->setSubStyle(Icons128x128_1::ServersAll);
        $this->addComponent($this->icon_title);
        
        $this->frame = new \ManiaLive\Gui\Controls\Frame();
        $this->frame->setAlign("left", "top");
        $this->frame->setPosition(2, -(4*0.8)-5);
        $this->frame->setLayout(new \ManiaLib\Gui\Layouts\Column(-1));
        $this->addComponent($this->frame);
    }

    public function update($servers) {
        $this->servers = $servers;
        $this->populateList();
    }
    
    private function populateList(){

        //$this->items = array();
        $this->frame->clearComponents();

        $onlineServers = array();
        $nbOnline = 0;
        foreach($this->servers as $server){
            if($server->isOnline()){
                $onlineServers[] = $server;
                $nbOnline++;
            }
        }

        if(self::$xml_config->hud->nbElement >= $nbOnline)
            $i = 0;
        else{
            $i = $this->lastStart % $nbOnline;
        }
        $this->lastStart++;
        
        $nbShown = 0;
        while($nbShown < $nbOnline && $nbShown < self::$xml_config->hud->nbElement){
            
            if(!isset($this->items[$nbShown])){
                $className = '\\ManiaLivePlugins\\oliverde8\\ServerNeighborhood\\Gui\\Widget_Controls\\'.self::$xml_config->hud->style;
                $item = new $className($nbShown, $this, $onlineServers[$i % $nbOnline]);
            }else{
                $item = $this->items[$nbShown];
                $item->setData($onlineServers[$i % $nbOnline]);
            }
            if($this->first){
                $this->first = false;
                $this->setSizeY($item->getSizeY()*self::$xml_config->hud->nbElement + 9);
            }
            $item->setSizeX($this->getSizeX()-3);
            $this->items[] = $item;
            $this->frame->addComponent($item);
            $i++;
            $nbShown++;
        }
    }
    
    public function onResize($oldX, $oldY) {
        $this->bg->setSize($this->getSizeX(),$this->getSizeY());
        $this->bg_title->setSizeX($this->getSizeX()-2);
        $this->label_title->setSizeX($this->getSizeX()-4);
        $this->icon_title->setPosX($this->getSizeX()-4);
        
        $this->bg_more->setSize($this->getSizeX()-3);
        $this->icon_all->setPosX($this->getSizeX()-3-$this->icon_all->getSizeX());
        $this->label_all->setPosX($this->getSizeX()-3-$this->icon_all->getSizeX());
    }
    
    
    public function windowDetails($login, $server){
        \ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Windows\ServerList::Erase($login);
        $w = \ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Windows\ServerList::Create($login);
        //$w->setServers()
    }
    
    public function showList($login){
        ServerList::Erase($login);
        $w = ServerList::Create($login);
        $w->setTitle('ServerNeighborhood - Server List');
        $w->setSize(120, 105);
        $w->setServers($this->servers);
        $w->centerOnScreen();
		$w->show();
    }
    
}

?>
