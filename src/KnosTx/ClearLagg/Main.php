<?php

/*
 * This file is part of
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  |(__| / -_) _` | '_| |__/ _` / _` / _` |
 *   \___|_\___\__,_|_| |____\__,_\__, \__, |
 *                                |___/|___/
 * @license GPL-3.0
 * @author KnosTx
 * @link https://github.com/KnosTx/ClearLagg
 * ©Copyright 2024 KnosTx
 */

declare(strict_types=1);

namespace KnosTx\ClearLagg;

use KnosTx\ClearLagg\command\subcommands\StatsCommand;
use KnosTx\ClearLagg\manager\ClearLaggManager;
use KnosTx\ClearLagg\manager\StatsManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;
use function count;
use function str_replace;
use function strtolower;

/**
 * Main class for the ClearLagg plugin.
 */
class Main extends PluginBase{

    /** @var ClearLaggManager Manages the clearing of items on the server. */
    private $clearLaggManager;

    /** @var StatsManager Tracks and manages plugin statistics. */
    private $statsManager;

    /** @var TaskHandler|null Handles the task for automatic item clearing. */
    private $clearTaskHandler;

    /** @var TaskHandler|null Handles the task for broadcasting time remaining. */
    private $broadcastTaskHandler;

    /** @var int Time remaining before the next automatic item clear. */
    private $timeRemaining;

    /**
     * Called when the plugin is enabled.
     *
     * Initializes the ClearLaggManager, StatsManager, and scheduled tasks.
     */
    public function onEnable() : void{
        $this->saveDefaultConfig();
        $this->clearLaggManager = new ClearLaggManager($this);
        $this->statsManager = new StatsManager($this);

        $this->timeRemaining = $this->getConfig()->get("auto-clear-interval", 300);

        $this->clearTaskHandler = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
            $this->onTick();
        }), 20);

        $broadcastInterval = $this->getConfig()->get("broadcast-interval", 15);
        $this->broadcastTaskHandler = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
            $this->broadcastTime();
        }), $broadcastInterval * 20);
    }

    /**
     * Called when the plugin is disabled.
     *
     * Cancels all running tasks.
     */
    public function onDisable() : void{
        if($this->clearTaskHandler instanceof TaskHandler){
            $this->clearTaskHandler->cancel();
        }
        if($this->broadcastTaskHandler instanceof TaskHandler){
            $this->broadcastTaskHandler->cancel();
        }
    }

    /**
     * Returns the ClearLaggManager instance.
     *
     * @return ClearLaggManager
     */
    public function getClearLaggManager() : ClearLaggManager{
        return $this->clearLaggManager;
    }

    /**
     * Returns the StatsManager instance.
     *
     * @return StatsManager
     */
    public function getStatsManager() : StatsManager{
        return $this->statsManager;
    }

    /**
     * Handles commands for the plugin.
     *
     * @param CommandSender $sender The sender of the command.
     * @param Command $command The command being executed.
     * @param string $label The alias of the command.
     * @param string[] $args The arguments passed to the command.
     * @return bool True if the command was handled, false otherwise.
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if(strtolower($command->getName()) === "clearlagg"){
            if(count($args) > 0 && strtolower($args[0]) === "stats"){
               (new StatsCommand($this))->execute($sender);
            }else{
                $this->clearLaggManager->clearItems();
                $sender->sendMessage(TextFormat::GREEN . "Items cleared!");
            }
            return true;
        }
        return false;
    }

    /**
     * Called every tick by the clear task.
     *
     * Handles automatic item clearing and broadcasting warnings.
     */
    private function onTick() : void{
        if($this->timeRemaining <= 5 && $this->timeRemaining > 0){
            $this->getServer()->broadcastMessage($this->clearLaggManager->getWarningMessage($this->timeRemaining));
        }

        if($this->timeRemaining <= 0){
            $this->clearLaggManager->clearItems();
            $this->statsManager->incrementItemsCleared();
            $this->timeRemaining = $this->getConfig()->get("auto-clear-interval", 300);
        }else{
            $this->timeRemaining--;
        }
    }

    /**
     * Broadcasts the remaining time to all players on the server.
     */
    private function broadcastTime() : void{
        $this->getServer()->broadcastMessage(str_replace("{time}",(string) $this->timeRemaining, $this->getConfig()->get("broadcast-message", "§bThe items will be deleted in {time} seconds.")));
    }
}