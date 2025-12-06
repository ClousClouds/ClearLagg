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

namespace ClousClouds\ClearLagg\manager;

use ClousClouds\ClearLagg\Main;

class StatsManager{

	public Main $plugin;
	private int $itemsCleared;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->itemsCleared = 0;
	}

	public function incrementItemsCleared(int $count = 1) : void{
		$this->itemsCleared += $count;
	}

	public function getItemsCleared() : int{
		return $this->itemsCleared;
	}
}
