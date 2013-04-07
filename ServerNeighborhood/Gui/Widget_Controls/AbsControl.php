<?php

namespace ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Widget_Controls;

use ManiaLivePlugins\oliverde8\ServerNeighborhood\Server;

/**
 * Description of AbsControl
 *
 * @author oliverde8
 */
abstract class AbsControl extends \ManiaLive\Gui\Control {

    public $server;

    function __construct(Server $server) {
        $this->server = $server;
        $this->onSetData($server);
    }

    public function setData(Server $server) {
        $this->server = $server;
        $this->onSetData($server);
    }

    public abstract function onSetData(Server $server);

    public function destroy() {
        parent::destroy();
        $this->server = null;
    }

    public function showServerPlayers($login) {
        \ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Windows\PlayerList::Erase($login);
        $w = \ManiaLivePlugins\oliverde8\ServerNeighborhood\Gui\Windows\PlayerList::Create($login);
        $w->setTitle('ServerNeighborhood - Server Players');
        $w->setSize(120, 105);
        $w->setServer($this->server);
        $w->centerOnScreen();
        $w->show();
    }

}

?>
