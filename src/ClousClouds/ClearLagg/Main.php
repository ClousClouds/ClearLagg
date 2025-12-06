<?php

/*
 * This file is part of ClearLagg
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  |(__| / -_) _` | '_| |__/ _` / _` / _` |
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

	public function onEnable() : void{
		$this->saveDefaultConfig();
		$this->clearLaggManager = new ClearLaggManager($this);
		$this->statsManager = new StatsManager($this);

		$this->clearLaggManager->init();
		if($this->getConfig()->get("update-notify", true)){
			$this->checkUpdate();
		}
		if(class_exists("Ifera\ScoreHud\event\TagsResolveEvent")){
			$this->getServer()->getPluginManager()->registerEvents(
				new ScoreHudListener($this),
				$this
			);
		}
	}

	private function checkUpdate() : void{
		$currentVersion = $this->getDescription()->getVersion();
    
		$this->getServer()->getAsyncPool()->submitTask(new class($currentVersion) extends \pocketmine\scheduler\AsyncTask{
			private string $currentVersion;
        
			public function __construct(string $currentVersion){
				$this->currentVersion = $currentVersion;
			}
        
			public function onRun() : void{
				$url = "https://poggit.pmmp.io/releases.json?name=ClearLagg";
				$response = @file_get_contents($url, false, stream_context_create([
					'http' => [
						'timeout' => 5,
						'header' => "User-Agent: ClearLagg-UpdateChecker\r\n"
					]
				]));

				if($response !== false){
					$releases = json_decode($response, true);
					if(!empty($releases[0]['version'])){
						$this->setResult([
							'success' => true,
							'latest' => $releases[0]['version'],
							'current' => $this->currentVersion
						]);
						return;
					}
				}
				$this->setResult(['success' => false]);
			}

			public function onCompletion() : void{
				$result = $this->getResult();
				if(($result['success'] ?? false) && version_compare($result['latest'], $result['current'], '>')){
					$server = \pocketmine\Server::getInstance();
					$server->getLogger()->info("§e[ClearLagg] New version available: §f" . $result['latest'] . "§e(Current: §f" . $result['current'] . "§e)");
					$server->getLogger()->info("§eDownload: §fhttps://poggit.pmmp.io/p/ClousClouds/ClearLagg");
				}
			}
		});
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
