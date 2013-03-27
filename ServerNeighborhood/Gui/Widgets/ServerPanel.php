<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Widgets;

use \ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Widget_Controls\Small;

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
        
        $this->frame = new \ManiaLive\Gui\Controls\Frame();
        $this->frame->setAlign("left", "top");
        $this->frame->setPosition(2, -(4*0.8)-3);
        $this->frame->setLayout(new \ManiaLib\Gui\Layouts\Column(-1));
        $this->addComponent($this->frame);
    }

    public function update($servers) {
        $this->servers = $servers;
        $this->populateList();
    }
    
    private function populateList(){
        foreach ($this->items as $item)
            $item->destroy();

        $this->items = array();
        $this->frame->clearComponents();

        $onlineServers = array();
        $nbOnline = 0;
        foreach($this->servers as $server){
            if($server->isOnline()){
                $onlineServers[] = $server;
                $nbOnline++;
            }
        }

        if(self::$xml_config->hud->nbElement > $nbOnline)
            $i = 0;
        else{
            $i = $this->lastStart % $nbOnline;
        }
        $nbShown = 0;
        while($nbShown < $nbOnline && $nbShown < self::$xml_config->hud->nbElement){
            $item = new Small($onlineServers[$i % $nbOnline]);
            if($this->first){
                $this->first = false;
                $this->setSizeY($item->getSizeY()*self::$xml_config->hud->nbElement + 6);
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
    }

}

?>
