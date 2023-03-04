<?php

namespace Minestrap\Rankers\Events;

use Minestrap\Rankers\Main;
use pocketmine\event\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\utils\Config;
use pocketmine\player\Player;

class PlayerStats implements Listener {

    /** @var Main */
    private $main;

    /** @var Config */
    private $config;

    /** @var Config */
    private $players;

    // Listener constructor class
    public function __construct(Main $main, Config $config, Config $players) {
        $this->main = $main;
        $this->config = $config;
        $this->players = $players;
    }

    // Block Break function checker
    public function onBreak(BlockBreakEvent $event) {
        
        // Check if the function is enabled
        if(!$this->config->get("break-counter", true)) {
            return;
        }

        // Check if the world is the selected on config.yml
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();

        if(!in_array($world, $this->config->get("break-counter-worlds", []))) {
            return;
        }

        // Checks if player already exists in database
        $name = strtolower($player->getName());

        if(!$this->players->exists($name)) {
            $this->players->setNested($name.".blocks_broken", 1);
            $this->players->setNested($name.".blocks_placed", 0);
            $this->players->setNested($name.".total_kills", 0);
            $this->players->setNested($name.".total_deaths", 0);
        
        } else {

            // If player already exists in database
            $bb = $this->players->getNested($name.".blocks_broken");
            $this->players->setNested($name.".blocks_broken", $bb + 1);
        }

        // Save the update
        $this->players->save();
    }

    // Block Place function checker
    public function onPlace(BlockPlaceEvent $event) {
        
        // Check if the function is enabled
        if(!$this->config->get("place-counter", true)) {
            return;
        }

        // Check if the world is the selected on config.yml
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();

        if(!in_array($world, $this->config->get("place-counter-worlds", []))) {
            return;
        }

        // Checks if player already exists in database
        $name = strtolower($player->getName());

        if(!$this->players->exists($name)) {
            $this->players->setNested($name.".blocks_broken", 0);
            $this->players->setNested($name.".blocks_placed", 1);
            $this->players->setNested($name.".total_kills", 0);
            $this->players->setNested($name.".total_deaths", 0);
        
        } else {

            // If player already exists in database
            $bb = $this->players->getNested($name.".blocks_placed");
            $this->players->setNested($name.".blocks_placed", $bb + 1);
        }

        // Save the update
        $this->players->save();
    }

    public function onDeath(PlayerDeathEvent $event) {

        // Check if the function is enabled
        if($this->config->get("death-counter", true)) {
    
            // Check if the world is the selected on config.yml
            $player = $event->getPlayer();
            $world = $player->getWorld()->getFolderName();
            
            if(in_array($world, $this->config->get("death-counter-worlds", []))) {
                
                // If player already exists in database
                $name = strtolower($player->getName());
                
                if(!$this->players->exists($name)) {
                    $this->players->setNested($name.".blocks_broken", 0);
                    $this->players->setNested($name.".blocks_placed", 0);
                    $this->players->setNested($name.".total_kills", 0);
                    $this->players->setNested($name.".total_deaths", 1);
                } else {
                    
                    // Update player death count
                    $deaths = $this->players->getNested($name.".total_deaths");
                    $this->players->setNested($name.".total_deaths", $deaths + 1);
                }

                // Save updates
                $this->players->save();
            }
        }
    
        if($this->config->get("kill-counter", true)) {
            
            // Check if the world is the selected on config.yml
            $player = $event->getPlayer();
            $world = $player->getWorld()->getFolderName();            
            
            if(in_array($world, $this->config->get("kill-counter-worlds", []))) {
                
                // Check if there's a killer and the killer is a player
                $killer = $event->getEntity()->getLastDamageCause()->getDamager();
                if($killer instanceof Player) {
                    $killerName = strtolower($killer->getName());
                    if(!$this->players->exists($killerName)) {
                        $this->players->setNested($killerName.".blocks_broken", 0);
                        $this->players->setNested($killerName.".blocks_placed", 0);
                        $this->players->setNested($killerName.".total_kills", 1);
                        $this->players->setNested($killerName.".total_deaths", 0);
                    
                    } else {
                        
                        // Update killer kill count
                        $kills = $this->players->getNested($killerName.".total_kills");
                        $this->players->setNested($killerName.".total_kills", $kills + 1);
                    }

                    // Save updates
                    $this->players->save();
                }
            }
        }
    }
}