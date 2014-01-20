<?php

namespace ManiaLivePlugins\oliverde8\HudMenu\Gui\Windows;

use ManiaLive\Gui\Window;

use ManiaLivePlugins\oliverde8\HudMenu\WindowSettings;

class RootMenu extends Window {

    /**
     * @var Array of The Root Menu items.
     */
    private $roots;

    /**
     * @var Array of open Menus
     */
    public static $open = array();
    public static $openH = array();
    private $actual;

    private $sub;
    public $parent = null;

    private $level;
    private $buttonId;

    //The Settings
    private static $settings;

	
    public function onConstruct() {
        $settings = new WindowSettings();
        $this->level = -1;
    }

    public function onDraw(){
        $this->clearComponents();
        $posY=0;
		$forLater = null;
		$forLaterY = 0;
        
        foreach ($this->roots as $root){
            if($root->needToShow() && $root->hasRights($this->getRecipient())){

				if(self::$settings->bigIcons
					&& isset(self::$open[$this->getRecipient()])
					&& isset(self::$open[$this->getRecipient()][$this->level+1])
					&& self::$open[$this->getRecipient()][$this->level+1] == $root->getId()){
					$forLater = $root;
					$forLaterY = $posY;
				}else{
                    

                    $root->beforeDraw($this->getRecipient());
					$this->addComponent($root);
					$root->setPositionY($posY);
					$root->setPositionZ(-1);
				}
				if(self::$settings->VerticalDirection == 0)
					$posY-=self::$settings->sizeY + self::$settings->marginY;
				else
					$posY+=self::$settings->sizeY + self::$settings->marginY;
            }
        }

		if($forLater != null){
            $forLater->beforeDraw($this->getRecipient());
			$this->addComponent($forLater);
			$forLater->setPositionY($forLaterY);
			$forLater->setPositionZ(-1);
		}
    }
    
    public function afterDraw(){
        foreach ($this->roots as $root){
            $root->afterDraw();
        }
    }


    public function setRoots($roots){
        $this->roots = $roots;
    }

    public function setLevel($level){
        $this->level = $level;
    }

    public function getLevel($level){
        return $this->level;
    }

    public function setbuttonId($id){
        $this->buttonId = $id;
    }

    public function getbuttonId(){
        return $this->buttonId;
    }

    public static function setSettings(WindowSettings $settings){
        self::$settings = $settings;
    }

    public function onClick(\ManiaLivePlugins\oliverde8\HudMenu\Gui\Controls\Button $button){
		
		//If the list of open menus isn't created;
		if(!isset(self::$open[$this->getRecipient()])){
			self::$open[$this->getRecipient()] = array();
			self::$openH[$this->getRecipient()] = array();
		}

		if($this->level == ($button->getLevel()-1)){
			//If this is the window handling this level then gogo

			if(isset(self::$open[$this->getRecipient()][$button->getLevel()]) 
                    && self::$open[$this->getRecipient()][$button->getLevel()] == $button->getId()){
              
				//We need to close the Sub Level
                self::$openH[$this->getRecipient()] = self::$open[$this->getRecipient()];
                
				unset(self::$open[$this->getRecipient()][$button->getLevel()]);
				if(isset($this->sub) && !empty($this->sub)){
					//Closing the Sub Window and destroying it
					$this->sub->hide();
					$this->sub->closeSubs();
					$this->sub->destroy();
					if(self::$settings->bigIcons){
						$this->show();
                        $this->afterDraw();
                    }
				}
				unset($this->sub);
			}else{
				//We need to open a new sub Level
				if(isset(self::$open[$this->getRecipient()][$button->getLevel()]) && isset($this->sub)){
					//There is aalready one open closing it
					$this->sub->setRoots($button->getSubButtons());
					$this->sub->setPosY($button->getPosY()+$this->getPosY());
					$this->sub->show();
                    $this->sub->afterDraw();
					$this->sub->closeSubs();

					if(self::$settings->bigIcons){
						$this->show();
                        $this->afterDraw();
                    }
				}else{
					//Opening nex sub Menu
					$this->sub = RootMenu::Create($this->getRecipient(),false);
					$this->sub->setLevel($button->getLevel());
					$this->sub->setRoots($button->getSubButtons());

					//calculating its X position
					if(self::$settings->HorizentalDirection == 1)
						$posX = $this->getPosX()- $button->getSizeX() + self::$settings->marginX;
					else
						$posX = $this->getPosX()+ $button->getSizeX() - self::$settings->marginX;

					$posY = $button->getPosY();
					$this->sub->setPosition($posX, $posY+$this->getPosY());
					$this->sub->show();
                    $this->sub->afterDraw();
					if(self::$settings->bigIcons){
						$this->show();
                        $this->afterDraw();
                    }
				}
				self::$open[$this->getRecipient()][$button->getLevel()] = $button->getId();
                
                //Checking for History opening, 
                if(isset(self::$openH[$this->getRecipient()][$button->getLevel()]) 
                        && self::$openH[$this->getRecipient()][$button->getLevel()] == $button->getId()
                        && isset(self::$openH[$this->getRecipient()][$button->getLevel()+1])){
                    
                    foreach($button->getSubButtons() as $b){
                        if(self::$openH[$this->getRecipient()][$button->getLevel()+1] == $b->getId()){
                           $this->sub->onClick($b);                             
                        }                    
                    }
                }
			}
		}elseif($this->level < ($button->getLevel()-1)){
			//Pass the action to the sub level to handle it
			if($this->sub == null){
				
			}else
				$this->sub->onClick($button);
		}
    }

	/**
	 * Forces the Menus form a certain level to Refresh
	 *
	 * @param <type> $level
	 */
    public function forceRefresh($level=null){
        if($level==null){
            $this->show();
            if(isset($this->sub) && isset(self::$open[$this->getRecipient()][$this->level+1])){
                $this->sub->forceRefresh();
            }
        }else if($this->level == $level){
            $this->show();
        }else if(isset($this->sub) && isset(self::$open[$this->getRecipient()][$this->level+1]) && self::$open[$this->getRecipient()][$this->level+1] == $this->sub->getbuttonId()){
            $this->sub->forceRefresh($level);
        }
    }

	/**
	 * Closes all sub Menus
	 */
    public function closeSubs(){
		if(isset($this->sub) && !empty($this->sub)){
			$this->sub->hide();
			$this->sub->closeSubs();
			$this->sub->destroy();
            unset(self::$open[$this->getRecipient()][$this->level+1]);
		}
		unset($this->sub);
    }
    
    public function destroySub(){
        /*foreach ($this->roots as $key => $button) {
            $button->destroy();
            $this->roots[$key] = null;
        }*/
        $this->roots = null;
    }
    
    public function destroy() {
        parent::destroy();
        $this->closeSubs();
        $this->actual = null;
        $this->sub = null;
        $this->parent = null;
        $this->level = null;
    }

    static function onPlayerDisconnect($login){
		unset(self::$open[$login]);
		unset(self::$openH[$login]);
	}
}

?>