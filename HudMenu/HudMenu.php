<?php

/**
 *
 * @name Oliverde8 HudMenu
 * @date 23-03-2013
 * @version 1.42
 * @website http://forum.maniaplanet.com/viewtopic.php?f=47&t=552
 * @package oliverd87
 *
 * @author Oliver "oliverde8" De Cramer <oliverde8@gmail.com>
 * @copyright 2011
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things of use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */

namespace ManiaLivePlugins\oliverde8\HudMenu;

use ManiaLive\Utilities\Console;
use ManiaLivePlugins\oliverde8\HudMenu\Gui\Controls\Button;
use ManiaLivePlugins\oliverde8\HudMenu\Gui\Windows\RootMenu;
use ManiaLive\Event\Dispatcher;

class HudMenu extends \ManiaLive\PluginHandler\Plugin {

    private static $buttons;
    private static $roots;
    private $bid;
    private $style;
    private $playerData = array();
    private $started = false;
    private $showTo = array();
    private $gameVersion;
    private $eventSent;
    
    private $loadedList = array();

    public function onInit() {
        $this->eventSent = false;
        $this->setPublicMethod("findButton");
        $this->setPublicMethod("addButton");
        $this->setPublicMethod("addRootMenu");

        $this->setVersion("1.42");
    }

    public function onReady() {
        //recovering config XML file
        $this->gameVersion = $this->connection->getVersion();

        //We need to enable the plugin events
        $this->enablePluginEvents();

        if ($this->gameVersion->name == "ManiaPlanet")
            $xml = file_get_contents('config/oliverde8.Hud.menu-mp.xml');
        else {
            $xml = file_get_contents('config/oliverde8.Hud.menu-tmf.xml');
            if (!$xml) {
                $xml = file_get_contents('config/oliverde8.Hud.menu.xml');
            }
        }

        if (!$xml) {
            //Recheck default file
            //If the file didn't exist, ERROR
            Console::println('[' . date('H:i:s') . '] [oliverde8.HudMenu] XML file unfound! (oliverde8.Hud.info_messages.xml)');
        } else {
            //The file exists We sould load it
            $this->LoadXML($xml);
        }

        //Checking if the AdminGroups plugin is there
        if ($this->isPluginLoadedMine("MLEPP\Core"))
            Button::$AdminGroups = \ManiaLivePlugins\MLEPP\Core\AdminGroups::getInstance();
        else if ($this->isPluginLoadedMine("ManiaLivePlugins\eXpansion\AdminGroups\AdminGroups")){
            Button::$AdminGroups = \ManiaLivePlugins\eXpansion\AdminGroups\AdminGroups::getInstance();
            Dispatcher::register(\ManiaLivePlugins\eXpansion\AdminGroups\Events\Event::getClass(), $this);
        }else
            Button::$AdminGroups = null;

        //Creating the Menu for the players already in the game
        foreach ($this->storage->players as $login => $player) {
            $this->createMenu($login);
        }

        //Creating the Menu for SPectators
        foreach ($this->storage->spectators as $login => $player) {
            $this->createMenu($login);
        }

        Console::println('[' . date('H:i:s') . '] [oliverde8.HudMenu] XML file Loaded. Total buttons : ' . $this->bid);

        //Enabling dedicated events for PlayerConnect and PlayerDisconnect.
        $this->enableDedicatedEvents(\ManiaLive\DedicatedApi\Callback\Event::ON_PLAYER_CONNECT);
        $this->enableDedicatedEvents(\ManiaLive\DedicatedApi\Callback\Event::ON_PLAYER_DISCONNECT);

        //Send event HudMenuReady to let know other plugins they can now add buttons to the menu.
        Dispatcher::dispatch(new onOliverde8HudMenuReady($this));

        $this->eventSent = true;

        Console::println('[' . date('H:i:s') . '] [oliverde8.HudMenu] All Buttons added. Total buttons : ' . $this->bid);

        //The menu is open all threads in different plugins has added the buttons needed.
        $this->started = true;

        //We show the Menu to all players in waiting List
        if (\is_array($this->showTo)) {
            foreach ($this->showTo as $login => $id) {
                $this->createMenu($login);
            }
        }
    }

    /**
     * Will create the menu for the player and will show it to him
     * If the Menu isn't yet ready it will add the player to the list of people waiting for th menu to be ready.
     *
     * @param <type> $login
     */
    protected function createMenu($login) {

        if (!$this->started) {
            //If the menu isn't ready yet, then we add the login to the list of players waiting
            $this->showTo[$login] = true;
        } else if (isset($this->playerData[$login])) {
            //If that player has already a Menu we will update;
            $this->playerData[$login]->closeSubs();
            $this->playerData[$login]->forceRefresh();
        } else {
            //We create a Menu for the player
            $menu = RootMenu::Create($login, false);
            $root = array();

            //Setting the root of the Menu
            $menu->setRoots(self::$roots);

            //Placing in position the Menu
            $menu->setPosition($this->style->Menu->PosX, $this->style->Menu->PosY);

            $menu->show();

            //Registering the menu in player data so that we can update it later.
            $this->playerData[$login] = $menu;
        }
    }

    /**
     * 	Shows the Menu to the player when he connects
     *
     * @param <type> $login
     * @param <type> $isSpectator
     */
    public function onPlayerConnect($login, $isSpectator) {
        if ($this->started)
            $this->createMenu($login);
        else
            $this->showTo[$login] = true;
        
        $this->memory();
    }

    /**
     * Destroys the Menu of the player ho deconnected.
     *
     * @param <type> $login
     */
    public function onPlayerDisconnect($login, $reason="") {
        if (isset($this->showTo[$login])) {
            unset($this->showTo[$login]);
        }
        if (isset($this->playerData[$login])) {
            $this->playerData[$login]->closeSubs();
            $this->playerData[$login]->destroySub();
            unset($this->playerData[$login]);
        }
        RootMenu::onPlayerDisconnect($login);
        RootMenu::Erase($login);
    }
    
    public function exp_admin_added($login){
        $this->createMenu($login);
    }
    
    public function exp_admin_removed($login){
        $this->createMenu($login);
    }
            
    function memory() {
        print "Memory Usage: " . memory_get_usage() / 1024 . "Mb\n";
    }

    public function onPluginLoaded($pluginId) {
        if ($this->eventSent && isset($this->loadedList[$pluginId])) {
            foreach ($this->storage->players as $login => $player) {
                $this->onPlayerDisconnect($login);
            }
            foreach ($this->storage->spectators as $login => $player) {
                $this->onPlayerDisconnect($login);
            }
            $this->bid = 0;
            foreach (self::$buttons as $button){
                $button->myDestroy();
            }
             foreach (self::$roots as $button){
                $button->myDestroy();
            }
            self::$buttons = array();
            self::$roots = array();
            $this->onReady();
        }
    }

    public function onPluginUnloaded($pluginId) {
        if ($this->eventSent) {
            foreach ($this->storage->players as $login => $player) {
                $this->onPlayerDisconnect($login);
            }
            foreach ($this->storage->spectators as $login => $player) {
                $this->onPlayerDisconnect($login);
            }
            $this->bid = 0;
            self::$buttons = array();
            self::$roots = array();
            $this->onReady();
        }
    }

    /**
     * Will parse the String that represents the XML
     * It will founbd all the Menu settings and will generate the buttons in it
     *
     * @param <String> $xml The XML file
     */
    public function LoadXML($xml) {
        //Parsing the XML file
        $xml = simplexml_load_string($xml);

        //Putting to 0 the id of the buttons
        $this->bid = 0;

        $this->style = $xml->style;

        //All the windows settings, the main settings for the menu
        $winSettings = new WindowSettings();
        $winSettings->sizeX = (float) $this->style->BackGround->SizeX;
        $winSettings->sizeY = (float) $this->style->BackGround->SizeY;
        $winSettings->marginX = (float) $this->style->BackGround->MarginX;
        $winSettings->marginY = (float) $this->style->BackGround->MarginY;
        $winSettings->VerticalDirection = (int) $this->style->VerticalDirection;
        $winSettings->HorizentalDirection = (int) $this->style->HorizentalDirection;
        $winSettings->closeOnClick = $this->stringToBool($this->style->closeOnClick);
        $winSettings->remeberOpenMenus = $this->stringToBool($this->style->remeberOpenMenus);
        $winSettings->openAtLevel = $this->stringToBool($this->style->openAtLevel);
        RootMenu::setSettings($winSettings);

        //Settings that are specific to buttons 
        $buttonSettings = new ButtonSettings();

        $buttonSettings->closeOnClick = $winSettings->closeOnClick;

        $buttonSettings->sizeX = (float) $this->style->BackGround->SizeX;
        $buttonSettings->sizeY = (float) $this->style->BackGround->SizeY;

        $buttonSettings->bigIcons = $this->stringToBool($this->style->Icon->BigIcons);
        $winSettings->bigIcons = $buttonSettings->bigIcons;

        $buttonSettings->icon_sizeX = (float) $this->style->Icon->SizeX;
        $buttonSettings->icon_sizeY = (float) $this->style->Icon->SizeY;
        $buttonSettings->icon_posX = (float) $this->style->Icon->posX;
        $buttonSettings->icon_posY = (float) $this->style->Icon->posY;
        $buttonSettings->iconBig_sizeX = (float) $this->style->Icon->SizeBigX;
        $buttonSettings->iconBig_sizeY = (float) $this->style->Icon->SizeBigY;
        $buttonSettings->iconBig_posX = (float) $this->style->Icon->posBigX;
        $buttonSettings->iconBig_posY = (float) $this->style->Icon->posBigY;

        $buttonSettings->button_Style = $this->style->button->Style;
        $buttonSettings->button_SubStyle = $this->style->button->SubStyle;
        $buttonSettings->separator_Style = $this->style->separator->Style;
        $buttonSettings->separator_SubStyle = $this->style->separator->SubStyle;

        $buttonSettings->textColor = $this->style->Text->Color;
        $buttonSettings->textSeparatorColor = $this->style->Text->ColorSeparator;
        $buttonSettings->textRootSize = (float) $this->style->Text->RootSize;
        $buttonSettings->textSize = (float) $this->style->Text->Size;

        $buttonSettings->textAlign = $this->style->Text->HorizontalAlign;
        $buttonSettings->textPosX = (float) $this->style->Text->posX;
        $buttonSettings->textPosY = (float) $this->style->Text->posY;
        $buttonSettings->textSizeY = (float) $this->style->Text->SizeY;
        $buttonSettings->textSizeX = (float) $this->style->Text->SizeX;
        $buttonSettings->textStyle = (float) $this->style->Text->style;
        Button::setStyle($buttonSettings);

        /* Loading first menu itmes here going recursive on the next level */
        foreach ($xml->buttons->button as $Xbutton) {

            //Adding the button to the main button list
            $plugin_id = isset($Xbutton['plugin_id']) ? $Xbutton['plugin_id'] : null;
            if ($this->addFromXmlButton($Xbutton, 0, $plugin_id)) {

                //IF done then add if as a root
                self::$roots[] = self::$buttons[$this->bid - 1];
                //Load the sub menus of ths button
                $this->loadSub(0, $Xbutton, $this->bid - 1);
            }
        }
    }

    public function addRootMenu($name, $params = array(), $plugin_id = null) {
        $params["caption"] = $name;
        if ($this->addFromXmlButton($params, $parent->getLevel() + 1)) {
            self::$roots[] = self::$buttons[$this->bid - 1];
            return self::$buttons[$this->bid - 1];
        }
    }

    /**
     *
     * @param <type> $level The level of the sub menu
     * @param <type> $button The XMLObject button
     * @param <type> $pid The id of it's parent button
     */
    private function loadSub($level, $button, $pid) {
        $level++;
        foreach ($button->button as $Xbutton) {
            $plugin_id = isset($Xbutton['plugin_id']) ? $Xbutton['plugin_id'] : null;
            //Adding the button to the main button list
            if ($this->addFromXmlButton($Xbutton, $level)) {
                //Add the button to it's father.
                self::$buttons[$pid]->addSubButton(self::$buttons[$this->bid - 1]);

                //Se if this button has any sub Buttons
                $this->loadSub($level, $Xbutton, ($this->bid - 1));
            }
        }
    }

    /**
     *
     * @param <type> $xml The XMLObject button
     * @param <type> $level The level of the sub menu it is in
     * @return boolean
     */
    private function addFromXmlButton($xml, $level, $plugin_id = null) {

        //We may find an error, if we do will put this to false;
        $added = true;


        if (isset($xml["Link"])) {
            //If it is a link ot a web site
            self::$buttons[$this->bid] = new Button($this->style->BackGround->SizeX, $this->style->BackGround->SizeY);
            self::$buttons[$this->bid]->setButton($this->bid, $level, $xml["caption"], $this);

            self::$buttons[$this->bid]->setLink($xml["Link"]);
            if (isset($xml["plugin"]) && !$this->isPluginLoadedMine((string) $xml["plugin"])) {	
				$pname = 
				$added = false;
            }
        } elseif (isset($xml["ManiaLink"])) {
            //If there is a link to a ManiaLink
            self::$buttons[$this->bid] = new Button($this->style->BackGround->SizeX, $this->style->BackGround->SizeY);
            self::$buttons[$this->bid]->setButton($this->bid, $level, $xml["caption"], $this);

            self::$buttons[$this->bid]->setManiaLink($xml["ManiaLink"]);
            if (isset($xml["plugin"]) && !$this->isPluginLoadedMine((string) $xml["plugin"])) {
                $added = false;
            }
        } elseif (isset($xml["seperator"])) {
            //If it is a separator

            self::$buttons[$this->bid] = new Button($this->style->BackGround->SizeX, $this->style->BackGround->SizeY);
            self::$buttons[$this->bid]->setButton($this->bid, $level, $xml["caption"], $this, true);
            if (isset($xml["plugin"]) && !$this->isPluginLoadedMine((string) $xml["plugin"])) {
                $added = false;
            }
        } elseif (isset($xml["chat"])) {
            self::$buttons[$this->bid] = new Button($this->style->BackGround->SizeX, $this->style->BackGround->SizeY);
            self::$buttons[$this->bid]->setButton($this->bid, $level, $xml["caption"], $this);

            self::$buttons[$this->bid]->setFunctionCall($this, 'interpreterTunnel', array((string) $xml["chat"]));
            if (isset($xml["plugin"]) && !$this->isPluginLoadedMine((string) $xml["plugin"])) {
                $added = false;
            }
        } elseif (isset($xml["plugin"]) && isset($xml["function"])) {
            //If it will interact with a Plugon
            //Check if the plugin exists and has that function
            if ($this->checkPlugin($xml["plugin"], (String) $xml["function"])) {

                if (\get_class($xml["plugin"]) == "SimpleXMLElement") {
                    //If the plugin is a XMLElement convert to string
                    $plugin = (String) $xml["plugin"];
                } else {
                    //Xe keep it as it is. We probably have a the plugn Object
                    $plugin = $xml["plugin"];
                }

                //Creating the button
                self::$buttons[$this->bid] = new Button($this->style->BackGround->SizeX, $this->style->BackGround->SizeY);
                self::$buttons[$this->bid]->setButton($this->bid, $level, $xml["caption"], $this);

                //Creating the parameters for the function that will be called
                if (isset($xml["params"]))
                    $params = explode(";", $xml["params"]);
                else
                    $params = array();

                $pcpt = 0;
                while (isset($params[$pcpt])) {
                    if ($params[$pcpt] == "null") {
                        $params[$pcpt] = null;
                    }
                    $pcpt++;
                }

                //Setting FUnction Call of the butto,
                self::$buttons[$this->bid]->setFunctionCall($plugin, (String) $xml["function"], $params);

                //If there is any checkFunction
                if (isset($xml["checkFunction"]) && $this->checkPlugin($plugin, (String) $xml["checkFunction"])) {
                    self::$buttons[$this->bid]->setCheckFunction($plugin, (String) $xml["checkFunction"]);
                }

                //If we need to force refresh
                if (isset($xml["forceRefresh"]) && $this->stringToBool((String) $xml["forceRefresh"])) {
                    self::$buttons[$this->bid]->setForceRefresh(true);
                }

                //If it is a switch button
                if (isset($xml["switchFunction"]) && $this->checkPlugin($plugin, (String) $xml["switchFunction"])) {
                    self::$buttons[$this->bid]->setSwitchFunction($plugin, (String) $xml["switchFunction"]);
                }
            } else {
                //If the plugin don't exist we can't add it
                $added = false;
            }
        } else {
            if (!isset($xml["plugin"]) || $this->checkPlugin($xml["plugin"])) {
                self::$buttons[$this->bid] = new Button($this->style->BackGround->SizeX, $this->style->BackGround->SizeY);
                self::$buttons[$this->bid]->setButton($this->bid, $level, $xml["caption"], $this);
            } else {
                $added = false;
            }
        }

        if ($added) {
            //If we can add it letys look for other settings

            if (isset($xml["style"]) && isset($xml["substyle"])) {
                //Setting the ICon style
                self::$buttons[$this->bid]->setIcon($xml["style"], $xml["substyle"]);
            }

            if (isset($xml["image"])) {
                //ssetting an Icon image
                self::$buttons[$this->bid]->setImage($xml["image"]);
            }

            if (Button::$AdminGroups && isset($xml["permission"])) {
                self::$buttons[$this->bid]->setPermission((String) $xml["permission"]);
            }

            if (isset($xml["needToBeAdmin"])) {
                self::$buttons[$this->bid]->setNeedToBeAdmin(true);
            }

            self::$buttons[$this->bid]->done();
            $this->bid++;
        } else {
            unset(self::$buttons[$this->bid]);
        }

        return $added;
    }

	public function isPluginLoadedMine($pluginName){ 
		if(!$this->isPluginLoaded($pluginName)){
			$exploded = explode("\\", $pluginName);
			$newName = "ManiaLivePlugins\\".$pluginName."\\".$exploded[sizeof($exploded)-1];
			
			if($this->isPluginLoaded($newName))			
				echo $pluginName . "FALSE TRUE \n";
			else 
				echo $pluginName . "FALSE FALSE \n";
			
			return $this->isPluginLoaded($newName);
		}
		echo $pluginName . "TRUE \n";
		return true;
	}
	
	public function getPluginNameFromOld($pluginName){
		if(!$this->isPluginLoaded($pluginName)){
			$exploded = explode("\\", $pluginName);
			$newName = "ManiaLivePlugins\\".$pluginName."\\".$exploded[sizeof($exploded)-1];
			return $newName;
		}else{
			return $pluginName;
		}
	}
	
    /**
     * Check if a function of the gicen plugin can be called bt the Menu
     *
     * @param <type> $plugin Name or Object of the plugin to check
     * @param <type> $function The name of the method we need to see if exists.
     * @return <type>  True or False
     */
    private function checkPlugin($plugin, $function = null) {

        //If we got a SimpleXMLElement converting to strng
        if (\get_class($plugin) == "SimpleXMLElement") {
            $plugin2 = (String) $plugin;
        } elseif (!empty($function)) {
            return method_exists($plugin, $function);
        } else {
            return true;
        }

        if ($this->isPluginLoadedMine($plugin2)) {
            if ($function == null)
                return true;

			$plugin2 = $this->getPluginNameFromOld($plugin2);
			
            $methods = \ManiaLive\PluginHandler\PluginHandler::getInstance()->getPublicMethods($plugin2);
            //print_r($methods);
            if (\is_array($methods)) {
                foreach ($methods as $method) {
                    if ($method["name"] == $function)
                        return true;
                }
            }
        }
        return false;
    }

    /**
     * Converys a string to a Boolean
     * @param <type> $string
     * @return <Boolean>
     */
    private function stringToBool($string) {
        if (strtoupper($string) == "FALSE" || $string == "0" || strtoupper($string) == "NO" || empty($string))
            return false;
        return true;
    }

    public function callMyPublicMethod($plugin, $function, $login, $params = array()) {
        return $this->callPublicMethod($plugin, $function, $login, $params);
    }

    public function onClick($login, Button $button) {
        $this->playerData[$login]->onClick($button);
    }

    public function closeSubs($login) {
        $this->playerData[$login]->closeSubs();
    }

    public function forceRefresh($login, $level = null) {
        $this->playerData[$login]->forceRefresh($level);
    }

    public function interpreterTunnel($login, $text) {
        \ManiaLive\Features\ChatCommand\Interpreter::getInstance()->onPlayerChat(0, $login, "/" . $text, true);
    }

    static function getButton($id) {
        if (isset(self::$buttons[$id])) {
            return self::$buttons[$id];
        }else
            return false;
    }

    public function addButton($parent, $name, $params = array(), $plugin_id = null) {
        if (\is_string($parent)) {
            $parent = \explode('\\', $parent);
            $parent = $this->findButton($parent);
            if ($parent == false)
                return false;
        }elseif ($parent == false)
            return false;

        $params["caption"] = $name;
        if ($this->addFromXmlButton($params, $parent->getLevel() + 1, $plugin_id)) {
            $parent->addSubButton(self::$buttons[$this->bid - 1]);
            return self::$buttons[$this->bid - 1];
        }else
            return false;
    }

    public function findButton($path, $level = 0, $buttons = null) {

        if ($buttons == null) {
            $level = 0;
            $buttons = self::$roots;
        }

        foreach ($buttons as $button) {
            if (\strtoupper($button->getCaption()) == \strtoupper($path[$level])) {
                if (isset($path[$level + 1])) {
                    return $this->findButton($path, ($level + 1), $button);
                }
                return $button;
            }
        }

        return false;
    }

    public function onUnload() {
        parent::onUnload();
        RootMenu::EraseAll();
    }

}

?>
