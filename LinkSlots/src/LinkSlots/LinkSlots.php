<?php

namespace LinkSlots;

use LinkSlotsAPI\LinkSlotsAPI;
use pocketmine\event\Listener;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\plugin\PluginBase;

/**
 * Class LinkSlots
 * @package LinkSlots
 */
class LinkSlots extends PluginBase implements Listener {

    /** @var  string[] $servers */
    public $servers;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if(!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        if(!is_file($this->getDataFolder()."/config.yml")) {
            $this->saveResource("/config.yml");
        }
        $this->servers = (array)$this->getConfig()->get("servers");
    }

    public function getSlots() {
        $api = LinkSlotsAPI::getInstance();
        $slots = 0;
        foreach ($this->servers as $server) {
            $args = explode(":", $server);
            $players = (int)$api->getPlayers(strval($args[0]), intval($args[1]));
            $slots = $slots+$players;
        }
        return $slots;
    }

    /**
     * @param QueryRegenerateEvent $event
     */
    public function onQuery(QueryRegenerateEvent $event) {
        $event->setMaxPlayerCount($this->getSlots());
    }
}