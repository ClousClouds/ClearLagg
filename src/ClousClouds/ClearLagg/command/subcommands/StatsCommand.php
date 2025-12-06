<?php

/*
 * This file is part of
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  | (__| / -_) _` | '_| |__/ _` / _` / _` |
 *   \___|_\___\__,_|_| |____\__,_\__, \__, |
 *                                |___/|___/
 * @license GPL-3.0
 * @author ClousClouds
 * @link https://github.com/ClousClouds/ClearLagg
 * Copyright is protected by the Law of the country.
 *
 */

declare(strict_types=1);

namespace ClousClouds\ClearLagg\command\subcommands;

use ClousClouds\ClearLagg\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class StatsCommand{

	private Main $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender) : bool{
		try{
			$statsManager = $this->plugin->getStatsManager();
			$itemsCleared = $statsManager->getItemsCleared();
			$sender->sendMessage(TextFormat::GREEN . "Total items cleared: " . TextFormat::YELLOW . $itemsCleared);
		}catch(\Exception $e){
			$sender->sendMessage(TextFormat::RED . "An error occurred: " . $e->getMessage());
			$this->plugin->getLogger()->error("Error in StatsCommand: " . $e->getMessage());
			return false;
		}

		return true;
	}
}
