<?php

namespace LinkSlots;

use linkslotsapi\API;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Server;

/**
 * Class LinkSlots
 * @package LinkSlots
 */
class LinkSlots extends PluginBase implements Listener {

    /** @var  string[] $servers */
    public static $servers;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if(!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        if(!is_file($this->getDataFolder()."/config.yml")) {
            $this->saveResource("/config.yml");
        }
        self::$servers = (array)$this->getConfig()->get("servers");
        $this->loadServers();
        $this->loadUpdateTask();
    }

    private function loadServers() {
        foreach (self::$servers as $server) {
            $d = explode(":",$server);
            API::addServer($d[0], $d[1]);
        }
    }

    private function loadUpdateTask() {
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new class extends Task {
            public function onRun(int $currentTick) {
                foreach (LinkSlots::$servers as $server) {
                    $d = explode(":", $server);
                    $sr = API::getServer($d[0], $d[1]);
                    Server::getInstance()->getQueryInformation()->setMaxPlayerCount(Server::getInstance()->getQueryInformation()->getMaxPlayerCount()+$sr->getSlots());
                    Server::getInstance()->getQueryInformation()->setPlayerCount(Server::getInstance()->getQueryInformation()->getPlayerCount()+$sr->getOnlinePlayers());
                }
            }
        }, 20*10);
    }
}