<?php

/*
 * This file is part of
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  | (__| / -_) _` | '_| |__/ _` / _` / _` |
 *   \___|_\___\__,_|_| |____\__,_\__, \__, |
 *                                |___/|___/
 * @license GPL-3.0
 * @author KnosTx
 * @link https://github.com/KnosTx/ClearLagg
 * Copyright is protected by the Law of the country.
 *
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
use function str_replace;
use function strtolower;

class Main extends PluginBase
{
	private ClearLaggManager $clearLaggManager;
	private StatsManager $statsManager;
	private ?TaskHandler $clearTaskHandler = null;
	private ?TaskHandler $broadcastTaskHandler = null;
	private int $timeRemaining;

	public function onEnable() : void{
		$this->saveDefaultConfig();
		$this->clearLaggManager = new ClearLaggManager($this);
		$this->statsManager = new StatsManager($this);

		$this->timeRemaining = $this->getConfig()->get("clear-interval", 300);
		$this->clearTaskHandler = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
			function() : void{
				$this->onTick();
			}
		), 20);

		$broadcastInterval = $this->getConfig()->get("broadcast-interval", 150);
		$this->broadcastTaskHandler = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
			function() : void{
				$this->broadcastTime();
			}
		), $broadcastInterval * 20);
	}

	public function onDisable() : void{
		if($this->clearTaskHandler !== null){
			$this->clearTaskHandler->cancel();
		}
		if($this->broadcastTaskHandler !== null){
			$this->broadcastTaskHandler->cancel();
		}
	}

	private function notifyPlayers() : void{
		if($this->getConfig()->getNested("notify-players.enable", true)) {
			$countdown = $this->getConfig()->getNested("notify-players.countdown", 299);
			if($this->timeRemaining == $countdown || 
			   ($this->timeRemaining <= $countdown && $this->timeRemaining % 60 == 0) ||
			   $this->timeRemaining <= 10) {
				$message = $this->getConfig()->getNested("notify-players.message", "All dropped items will be cleared in {time} seconds!");
				$formattedMessage = str_replace("{time}", (string)$this->timeRemaining, $message);
				$this->getServer()->broadcastMessage($formattedMessage);
			}
		}
	}

	/**
	 * Retrieves the ClearLaggManager instance.
	 */
	public function getClearLaggManager() : ClearLaggManager{
		return $this->clearLaggManager;
	}

	/**
	 * Retrieves the StatsManager instance.
	 */
	public function getStatsManager() : StatsManager{
		return $this->statsManager;
	}

	/**
	 * Handles commands related to ClearLagg.
	 *
	 * @param CommandSender $sender  The sender of the command
	 * @param Command       $command The command executed
	 * @param string        $label   The command label
	 * @param array         $args    Command arguments
	 *
	 * @return bool Whether the command was successful
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(strtolower($command->getName()) === "clearlagg"){
			if(!empty($args) && strtolower($args[0]) === "stats"){
				(new StatsCommand($this))->execute($sender);
			}else{
				$this->clearLaggManager->clearItems();
			}
			return true;
		}
		return false;
	}

	/**
	 * Handles the auto-clear tick countdown and execution.
	 */
	private function onTick() : void{
		$this->notifyPlayers();
		if($this->timeRemaining <= 0){
			$this->clearLaggManager->clearItems();
			$this->statsManager->incrementItemsCleared();
			$this->timeRemaining = $this->getConfig()->get("auto-clear-interval", 300);
		}else{
			$this->timeRemaining--;
		}
	}

	/**
	 * Broadcasts the remaining time before auto-clear.
	 */
	private function broadcastTime() : void{
		$messageTemplate = $this->getConfig()->get("broadcast-message", "§bThe items will be deleted in {time} seconds.");
		$message = str_replace("{time}", (string) $this->timeRemaining, $messageTemplate);
		$this->getServer()->broadcastMessage($message);
	}
}
