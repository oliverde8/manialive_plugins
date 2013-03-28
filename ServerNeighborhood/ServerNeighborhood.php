<?php

/**
 *
 * @name Oliverde8 Server Switch
 * @date 23-03-2013
 * @version 1.0
 * @website ...
 * @package oliverd8
 *
 * @author Oliver "oliverde8" De Cramer <oliverde8@gmail.com>
 * @copyright 2013
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

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood;

use ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Widgets\ServerPanel;

class ServerNeighborhood extends \ManiaLive\PluginHandler\Plugin {
    
    public static $gamemodes = array(
		0 => array('name' => 'SCRIPT',		'icon' => 'RT_Script'),
		1 => array('name' => 'ROUNDS',		'icon' => 'RT_Rounds'),
		2 => array('name' => 'TIME_ATTACK',	'icon' => 'RT_TimeAttack'),
		3 => array('name' => 'TEAM',		'icon' => 'RT_Team'),
		4 => array('name' => 'LAPS',		'icon' => 'RT_Laps'),
		5 => array('name' => 'CUP',         'icon' => 'RT_Cup'),
		6 => array('name' => 'STUNTS',		'icon' => 'RT_Stunts'),
	);
    
    private $server;
    private $servers = array();
    private $lastSent = 0;
    private $db_active = false;
    private $config;
    private $xml;
    private $widget_players = array();

    public function onInit() {
        $this->setVersion("1.0");
        $this->config = Config::getInstance();
    }

    public function onReady() {
        $this->server = new Server();
        $this->server->create_fromConnection($this->connection, $this->storage);

        $this->registerChatCommand('servers', 'showServerList', 0, true);
        //Creating database
        /* try {
          $this->enableDatabase();
          if (!$this->db->tableExists("ode8_servers")) {
          $q = "CREATE TABLE `ode8_servers` (
          `server_id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
          `server_login` VARCHAR( 50 ) NOT NULL,
          `server_lastmodified` INT( 9 ),
          `server_data` TEXT,
          KEY(`server_login`)
          ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM ;";
          $this->db->query($q);
          }
          $this->db_active = true;
          } catch (\Exception $ex) {

          } */
        
        $this->xml = simplexml_load_file($this->config->config_file);
        ServerPanel::$xml_config = $this->xml;

        $this->enableTickerEvent();
    }

    public function onTick() {
        parent::onTick();

        if ((time() - $this->lastSent) > $this->xml->refresh_interval) {
            $this->lastSent = time();
            $this->saveData($this->server->createXML($this->connection, $this->storage));
            $this->getData();
            $this->showWidget($this->servers);
        }
    }

    public function saveData($data) {
        $filename = $this->xml->storing_path . $this->storage->serverLogin . '_serverinfo.xml';

        // Opens the file for writing and truncates it to zero length
        // Try min. 40 times to open if it fails (write block)
        $tries = 0;

        try{
            $fh = fopen($filename, "w", 0, stream_context_create(array('ftp' => array('overwrite' => true))));
        }catch(\Exception $ex){$fh = false;}
        while ($fh === false) {
            if ($tries > 40) {
                break;
            }
            $tries++;
            try{
                $fh = fopen($filename, "w", 0, $this->stream_context);
            }catch(\Exception $ex){$fh = false;}
        }
        if ($tries >= 40) {
            \ManiaLive\Utilities\Console::println('[server_neighborhood] Could not open file " '.$filename.'" to store the Server Information!');
        } else {
            fwrite($fh, $data);
            fclose($fh);
        }
    }
    
    public function getData(){

        $i = 0;
        foreach ($this->xml->server_accounts->server_neighbor as $server_xml){

            try{
                $data = file_get_contents($server_xml->path);

                if(isset($this->servers[$i])){
                    $server = $this->servers[$i];
                }else{
                    $server = new Server();
                    $this->servers[$i] = $server;
                }
                $server->setServer_data(simplexml_load_string($data));
                $i++;
            }catch(\Exception $ex){
                //\ManiaLive\Utilities\Console::println('[server_neighborhood] Error loading : '.$server_xml->path);
            }
        }
    }

    public function showWidget($servers) {
        $windows = ServerPanel::GetAll();
        if (empty($windows)) {
            $window = ServerPanel::Create(null);
            $windows[] = $window;
            $window->setSize($this->xml->hud->sizeX, 25);
        }
        foreach ($windows as $window) {
            $window->setPosition($this->xml->hud->posX, $this->xml->hud->posY);
            $window->update($servers);
            $window->show();
        }
    }
    
    public function showServerList($login){
        Gui\Windows\ServerList::Erase($login);
        $w = Gui\Windows\ServerList::Create($login);
        $w->setTitle('ServerNeighborhood - Server List');
        $w->setSize(120, 105);
        $w->setServers($this->servers);
        $w->centerOnScreen();
		$w->show();
        
    }

}

?>
