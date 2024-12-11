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

namespace KnosTx\ClearLagg\manager;

use KnosTx\ClearLagg\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use function count;

class StatsManager{

	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function sendStats(CommandSender $sender) : void{
		$worldCount = count($this->plugin->getServer()->getWorldManager()->getWorlds());
		$entityCount = 0;
		foreach ($this->plugin->getServer()->getWorldManager()->getWorlds() as $world){
			$entityCount += count($world->getEntities());
		}

		$sender->sendMessage(TextFormat::YELLOW . "Server Stats:");
		$sender->sendMessage(TextFormat::GOLD . "Worlds: " . TextFormat::WHITE . $worldCount);
		$sender->sendMessage(TextFormat::GOLD . "Entities: " . TextFormat::WHITE . $entityCount);
	}
}
