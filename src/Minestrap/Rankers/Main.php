<?php

namespace Minestrap\Rankers;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use Minestrap\Rankers\Events\PlayerStats;

class Main extends PluginBase implements Listener {

    /** @var Config */
    private $config;

    /** @var Config */
    private $players;

    public function onEnable(): void {
        
        // Save default config
        $this->saveResource("config.yml");
        $this->saveResource("players.yml");

        // Var default configs
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->players = new Config($this->getDataFolder() . "players.yml", Config::YAML);

        // to do...
    }
}
