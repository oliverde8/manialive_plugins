<?php

namespace ManiaLivePlugins\oliverde8\HudMenu\Gui\Controls;

use ManiaLib\Gui\Elements\Label;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Gui\ActionHandler;
use \ManiaLivePlugins\oliverde8\HudMenu\Gui\Windows\RootMenu;

/**
 * @author oliver
 */
class Button extends \ManiaLive\Gui\Control implements \Iterator {
	
	public $caption;
	public $id;
	public $level;
	public $subButtons;
	public $plugin;
	public $isSubDir = true;
	public $action;
	public $switch;
	public $permission;
	public $needToBeAdmin;
	public $forceRefresh = false;
	public $isSeparator = false;
	public $check = array();

	public $blink = "";
	public $mllink = "";

	static public $AdminGroups = false;
	public $done = false;

	/* ML items */
	public $background;
	private $label;
	public $icon = null;
	
	private $bigIcon = false;
    private $paramsAsArray = false;
	static private $style;

	/* Iterator */
	private $iterator_index = 0;
	
	
	public function __construct($sizeX, $sizeY) {
		$this->sizeX = $sizeX;
		$this->sizeY = $sizeY;
	}

	public function createMe(Button $orginal){
		$this->caption = $orginal->caption;

		$this->id = $orginal->id;
		$this->level = $orginal->level;

		foreach($orginal->subButtons as $id => $subBut){
			$new = new Button($this->getSizeX(), $this->getSizeY());
			$new->createMe($subBut);
			$this->subButtons[] = $new;
		}
		$this->paramsAsArray = $orginal->paramsAsArray;
		$this->plugin = $orginal-> plugin;
		$this->isSubDir = $orginal->isSubDir;
		$this->action = $orginal->action;
		$this->switch = $orginal->switch;
		$this->permission = $orginal->permission;
		$this->needToBeAdmin = $orginal->needToBeAdmin;
		$this->forceRefresh = $orginal->forceRefresh;
		$this->isSeparator = $orginal->isSeparator;
		$this->check = $orginal->check;

		$this->start();

		if(\is_object($orginal->icon))
			$this->icon = clone $orginal->icon;
		else
			$this->icon = $orginal->icon;

		if($this->icon != null){
			$this->addComponent($this->icon);
		}

		if(!empty($orginal->blink)){;
			$this->background->setUrl($orginal->blink);
			$this->setIcon("Icons64x64_1", "ToolLeague1");
			$this->isSubDir = false;
		}elseif(!empty($orginal->mllink)){
			$this->background->setManialink($orginal->mllink);
			$this->setIcon("Icons128x128_1", "Manialink");
			$this->isSubDir = false;
		}
		$this->background->setAction($this->ml_action);
     
	}
	
	public function destroy() {
		if($this->isSubDir && \is_array($this->subButtons)){
			foreach($this->subButtons as $id => $button){
				$button->destroy();
			}
		}
        $this->subButtons = null;
        $this->plugin = null;
        $this->action = null;
		$this->clearComponents();
		ActionHandler::getInstance()->deleteAction($this->ml_action);
        $this->ml_action = null;
        $this->switch = null;
        $this->permission = null;
        $this->check = null;
        $this->blink = null;
        $this->mllink = null;
		unset($this->icon);
		unset($this->background);
		unset($this->label);
		parent::destroy();
	}
	
	public function checkPlugin($pluginId){
		$deleted = false;
		if($this->isSubDir && \is_array($this->subButtons)){
			foreach($this->subButtons as $id => $button){
				if($button->plugin == $pluginId){
					$button->destroy();
					$deleted = true;
				}else{
					$deleted = $deleted || $button->checkPlugin($pluginId);
				}
			}
		}
		return $deleted;
	}
	
	public function showAll(){
		$i=0;
		while($i<= $this->level){
			$i++;
		}
		if($this->isSubDir && \is_array($this->subButtons)){
			foreach($this->subButtons as $button){
				$button->showAll();
			}
		}
	}

	public function setButton($id, $level, $caption, $plugin, $separator=false) {
		$this->caption = $caption;
		$this->id = $id;
		$this->level = $level;
		$this->subButtons = array();
		$this->plugin = $plugin;
		$this->isSeparator = $separator;

		$this->permission = null;
		$this->needToBeAdmin = null;

		$this->start();
	}

	static public function setStyle($style) {
		self::$style = $style;
	}

	function beforeDraw($login) {
        parent::onDraw();
		if ($this->switch != null) {
			$value = -1;

			if (\is_string($this->switch["class"])) {
				$value = $this->plugin->callMyPublicMethod($this->switch["class"], $this->switch["function"], $login, array());
			} elseif (method_exists($this->action["class"], $this->action["function"])) {
				$value = call_user_func_array(array($this->switch["class"], $this->switch["function"]), array($login));
			}
			switch ((int)$value) {
				case 0 :
					$this->icon->setSubstyle("LvlRed");
					break;
				case 1 :
					$this->icon->setSubstyle("LvlGreen");
					break;
				case 2 :
					$this->icon->setSubstyle("LvlYellow");
					break;
			}
		}

		if(isset(RootMenu::$open[$login])
				&& isset(RootMenu::$open[$login][$this->level])
				&& RootMenu::$open[$login][$this->level] == $this->id
				&& self::$style->bigIcons){
			$this->icon->setSize(self::$style->iconBig_sizeX, self::$style->iconBig_sizeY);
			$this->icon->setPosition(self::$style->iconBig_posX, self::$style->iconBig_posY);
		}
		
	}
    
	protected function afterDraw() {
		parent::afterDraw();
		if (isset($this->icon) && $this->icon != NULL) {
			$this->icon->setSize(self::$style->icon_sizeX, self::$style->icon_sizeX);
			$this->icon->setPosition(self::$style->icon_posX, self::$style->icon_posY);
		}
	}

	private function start() {
		$this->ml_action = $this->createAction(array($this, 'onClick'));
		if ($this->isSeparator) {
            $style = "ManiaLib\Gui\Elements\\" . self::$style->separator_Style;
            $subStyle = self::$style->separator_SubStyle;
        } else {
            $style = "ManiaLib\Gui\Elements\\" . self::$style->button_Style;
            $subStyle = self::$style->button_SubStyle;
        }

        $this->background = new $style();
        $this->background->setSize($this->getSizeX(), $this->getSizeY());
        $this->background->setSubStyle($subStyle);
        $this->addComponent($this->background);
        if($this->background->getManialink() == "" && $this->background->getUrl() == ""){
            $this->background->setAction($this->ml_action);
        }

        
		$this->label = new Label();
		$this->label->setSize(self::$style->textSizeX, self::$style->textSizeY);
		$this->label->setValign('center');
		$this->label->setHalign(self::$style->textAlign);
		$this->label->setPosition(((self::$style->icon_sizeX * 3) / 4) + self::$style->textPosX, self::$style->textPosY);
		$this->label->setStyle(self::$style->textStyle);
        

		if ($this->isSeparator) {
			$this->label->setText(self::$style->textSeparatorColor . $this->caption);
		} else if($this->isSubDir){
			$this->label->setText(self::$style->textColor . $this->caption . '...');
		}else{
			$this->label->setText(self::$style->textColor . $this->caption);
		}

		if ($this->level == 0) {
			$this->label->setTextSize(self::$style->textRootSize);
		} else {
			$this->label->setTextSize(self::$style->textSize);
		}

		$this->addComponent($this->label);
	}

	public function onClick($login) {
		$params = array();

		if($this->isSubDir){
		
			call_user_func_array(array($this->action["class"], $this->action["function"]), array($login, $this));

		}else if (\is_string($this->action["class"])) {

			$params[] = $this->action["class"];
			$params[] = $this->action["function"];
			$params[] = $login;
            if($this->paramsAsArray){
                 $params[] = $this->action["params"];
            }else{
                foreach ($this->action["params"] as $p) {
                    $params[] = $p;
                }
            }
			call_user_func_array(array($this->plugin, "callMyPublicMethod"), $params);
		} elseif (method_exists($this->action["class"], $this->action["function"])) {
			$params[] = $login;
			if($this->paramsAsArray){
                 $params[] = $this->action["params"];
            }else{
                foreach ($this->action["params"] as $p) {
                    $params[] = $p;
                }
            }
			$params[] = "";
			call_user_func_array(array($this->action["class"], $this->action["function"]), $params);
		}

		//After Click Actions
		if ($this->forceRefresh) {
			$this->plugin->forceRefresh($login);
		}
		if ($this->switch != null) {
			$this->plugin->forceRefresh($login, $this->level + 1);
		}

		if(!$this->forceRefresh && $this->switch == null && !$this->isSubDir && self::$style->closeOnClick){
			$this->plugin->closeSubs($login);
		}
	}

	public function getId() {
		return $this->id;
	}

	public function addSubButton(Button $button) {
		$this->action = array("class" => $this->plugin, "function" => "onClick", "params" => array('id' => $this->id, "level" => $this->level));
		$this->subButtons[] = $button;
		if ($this->icon == null && $this->done) {
			$this->setIcon("Icons128x128_1", "Browse");
			$this->addComponent($this->icon);
		}
		$this->isSubDir = true;
	}

	public function getSubButtons() {
		return $this->subButtons;
	}

	public function getCaption() {
		return $this->caption;
	}

	public function getLevel() {
		return $this->level;
	}

	/* Button Proprietes */

	public function setIcon($style, $subStyle) {
		$style = "ManiaLib\Gui\Elements\\" . $style;
		$this->icon = new $style();
		$this->icon->setSubstyle($subStyle);
		$this->icon->setSize(self::$style->icon_sizeX, self::$style->icon_sizeX);
		$this->icon->setPosition(self::$style->icon_posX, self::$style->icon_posY);
	}

	public function setImage($url) {
		$this->icon = new \ManiaLib\Gui\Elements\Quad(self::$style->icon_sizeX, self::$style->icon_sizeX);
		$this->icon->setImage($url, true);
	}

	public function setLink($url) {
		$this->background->setUrl($url);
		$this->setIcon("Icons64x64_1", "ToolLeague1");
		$this->isSubDir = false;
		$this->blink = $url;
        $this->background->setAction(null);
        $this->label->setText(self::$style->textColor . $this->caption);
	}

	public function setManiaLink($ml) {
		$this->background->setManialink($ml);
		$this->setIcon("Icons128x128_1", "Manialink");
		$this->isSubDir = false;
		$this->mllink = $ml;
        $this->background->setAction(null);
        $this->label->setText(self::$style->textColor . $this->caption);
	}

	public function setFunctionCall($class, $function, $params=array()) {
		$this->action = array("class" => $class, "function" => $function, "params" => $params);
		$this->isSubDir = false;
	}

	public function setCheckFunction($class, $function) {
		$this->check = array("class" => $class, "function" => $function);
	}

	public function setForceRefresh($force) {
		$this->forceRefresh = $force;
	}

	public function setSwitchFunction($class, $function) {
		$this->switch = array("class" => $class, "function" => $function);
		$this->setIcon("Icons64x64_1", "LvlRed");
	}

	public function setPermission($permissionName) {
		$this->permission = $permissionName;
	}

	public function setNeedToBeAdmin($needToBeAdmin) {
		$this->needToBeAdmin = $needToBeAdmin;
	}

	public function done() {
		if (isset($this->icon) && !empty($this->icon)) {
			$this->addComponent($this->icon);
		}

		$this->done = true;
	}

	public function needToShow() {

        if($this->isSeparator){
            return true;
        }
        
		if ($this->isSubDir) {
			if (empty($this->subButtons))
				return false;
		}
		
        if (!isset($this->check) || empty($this->check))
			return true;
		if (\is_string($this->check["class"])) {
			return $this->plugin->callMyPublicMethod($this->check["class"], $this->check["function"], array());
		} elseif (method_exists($this->action["class"], $this->action["function"])) {
			return call_user_func_array(array($this->check["class"], $this->check["function"]), array());
		}
	}

	public function hasRights($login) {
		if ($this->permission != NULL && self::$AdminGroups != null) {
			return self::$AdminGroups->hasPermission($login, $this->permission);

		} else if ($this->needToBeAdmin) {
			if (self::$AdminGroups != null) {
				return self::$AdminGroups->hasPermission($login, 'oliverde8_SeeAdminHudMenu');
			} else {
				if (!AdminGroup::contains($login)){
					return false;
				}else
					return true;
			}
		}
		return true;
	}

	public function bigIcon() {
		$this->bigIcon = true;
	}
    
    public function setParamsAsArray($paramsAsArray) {
        $this->paramsAsArray = $paramsAsArray;
    }

    
	/* Iterator */

	public function rewind() {
		$this->iterator_index = 0;
	}

	public function current() {
		return $this->subButtons[$this->iterator_index];
	}

	public function key() {
		return $this->iterator_index;
	}

	public function next() {
		++$this->iterator_index;
	}

	public function valid() {
		return isset($this->subButtons[$this->iterator_index]);
	}
}
?>
