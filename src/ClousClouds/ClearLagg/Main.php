<?php

/*
 * This file is part of ClearLagg
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  | (__| / -_) _` | '_| |__/ _` / _` / _` |
 *   \___|_\___\__,_|_| |____\__,_\__, \__, |
 *                                 |___/|___/
 *
 * ClearLagg - A lag-reduction tool for PocketMine-MP.
 * @license GPL-3.0
 * @copyright ClousClouds
 * @link https://github.com/ClousClouds/ClearLagg
 */

declare(strict_types=1);

namespace ClousClouds\ClearLagg;

use ClousClouds\ClearLagg\manager\ClearLaggManager;
use ClousClouds\ClearLagg\manager\StatsManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use function class_exists;

class Main extends PluginBase{

	private ClearLaggManager $clearLaggManager;
	private StatsManager $statsManager;
	private ?TaskHandler $clearTaskHandler = null;
	private ?TaskHandler $broadcastTaskHandler = null;
	private int $timeRemaining;

	public function onEnable() : void{
		$this->saveDefaultConfig();
		$this->clearLaggManager = new ClearLaggManager($this);
		$this->statsManager = new StatsManager($this);

		$this->clearLaggManager->init();
		if($this->getConfig()->get("update-notify", true)){
			$this->checkUpdate();
		}
		if(class_exists("Ifera\ScoreHud\event\TagsResolveEvent")) {
			$this->getServer()->getPluginManager()->registerEvents(
				new ScoreHudListener($this),
				$this
			);
		}
	}

	public function onDisable() : void{
		if($this->clearTaskHandler !== null){
			$this->clearTaskHandler->cancel();
		}
		if($this->broadcastTaskHandler !== null){
			$this->broadcastTaskHandler->cancel();
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
}
