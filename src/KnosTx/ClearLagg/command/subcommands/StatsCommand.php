<?php

/*
 * This file part of
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  | (__| / -_) _` | '_| |__/ _` / _` / _` |
 *   \___|_\___\__,_|_| |____\__,_\__, \__, |
 *                                |___/|___/
 * @license GPL-3.0
 * @author KnosTx
 * @link https://github.com/KnosTx/ClearLagg
 * ©Copyright 2024 KnosTx
 *
 *
 */

declare(strict_types=1);

namespace KnosTx\ClearLagg\command\subcommands;

use KnosTx\ClearLagg\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class StatsCommand{

	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender) : bool{
		try{
			$statsManager = $this->plugin->getStatsManager();
			if ($statsManager === null){
				$sender->sendMessage(TextFormat::RED . "Stats manager is not initialized.");
				return false;
			}

			$itemsCleared = $statsManager->getItemsCleared();
			$sender->sendMessage(TextFormat::GREEN . "Total items cleared: " . TextFormat::YELLOW . $itemsCleared);
		} catch (\Exception $e){
			$sender->sendMessage(TextFormat::RED . "An error occurred: " . $e->getMessage());
			$this->plugin->getLogger()->error("Error in StatsCommand: " . $e->getMessage(), $e);
			return false;
		}

		return true;
	}
}
