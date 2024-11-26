<?php

/*
 * This file part of
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  |(__| / -_) _` | '_| |__/ _` / _` / _` |
 *   \___|_\___\__,_|_| |____\__,_\__, \__, |
 *                                |___/|___/
 * @license GPL-3.0
 * @author KnosTx
 * @link https://github.com/KnosTx/ClearLagg
 */

declare(strict_types=1);

namespace KnosTx\ClearLagg\manager;

use KnosTx\ClearLagg\Main;
use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use function str_replace;

/**
 * Manages the ClearLagg plugin's main functionality, including automatic item clearing and player notifications.
 */
class ClearLaggManager{

    /** @var Main Plugin instance. */
    private $plugin;

    /** @var int Interval in seconds between automatic clears. */
    private $clearInterval;

    /** @var string Message sent to players when items are cleared. */
    private $clearMessage;

    /** @var string Warning message sent before items are cleared. */
    private $warningMessage;

    /** @var int Interval in seconds between broadcasts to notify players. */
    private $broadcastInterval;

    /** @var string Broadcast message to notify players of remaining time. */
    private $broadcastMessage;

    /** @var int Time remaining until the next item clear. */
    private $timeRemaining;

    /**
     * Constructs a new ClearLaggManager instance.
     *
     * @param Main $plugin The main plugin instance.
     */
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * Initializes the ClearLaggManager by loading configuration and scheduling tasks.
     */
    public function init() : void{
        $config = $this->plugin->getConfig();
        $this->clearInterval = $config->get("clear-interval", 300);
        $this->clearMessage = $config->get("clear-message", "§aGarbage collected correctly.");
        $this->warningMessage = $config->get("warning-message", "§cPicking up trash in{time}...");
        $this->broadcastInterval = $config->get("broadcast-interval", 15);
        $this->broadcastMessage = $config->get("broadcast-message", "§bThe items will be deleted in {time} seconds.");
        $this->timeRemaining = $config->getNested("notify-players.countdown", 299);

        $this->plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
            $this->onTick();
        }), 20);

        $this->plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
            $this->broadcastTime();
        }), $this->broadcastInterval * 20);
    }

    /**
     * Handles logic for each tick, including decrementing the countdown and sending warnings.
     */
    private function onTick() : void{
        if($this->timeRemaining <= 5 && $this->timeRemaining > 0){
            Server::getInstance()->broadcastMessage(str_replace("{time}",(string) $this->timeRemaining, $this->warningMessage));
        }

        if($this->timeRemaining <= 0){
            $this->clearItems();
            $this->timeRemaining = $this->clearInterval;
        }else{
            $this->timeRemaining--;
        }
    }

    /**
     * Clears all dropped items(ItemEntity) from all worlds.
     */
    public function clearItems() : void{
        foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world){
            foreach($world->getEntities() as $entity){
                if($entity instanceof ItemEntity){
                    $entity->flagForDespawn();
                }
            }
        }
        Server::getInstance()->broadcastMessage($this->clearMessage);
    }

    /**
     * Broadcasts the remaining time to all players.
     */
    private function broadcastTime() : void{
        Server::getInstance()->broadcastMessage(str_replace("{time}",(string) $this->timeRemaining, $this->broadcastMessage));
    }
}