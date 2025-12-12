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

use ClousClouds\ClearLagg\command\ClearLaggCommand;
use ClousClouds\ClearLagg\manager\ClearLaggManager;
use ClousClouds\ClearLagg\manager\StatsManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
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
		$this->getServer()->getCommandMap()->register("clearlagg", new ClearLaggCommand($this));
		
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
					],
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
					]
				]));

				if($response !== false) {
					$releases = json_decode($response, true);
					if(is_array($releases) && count($releases) > 0) {
						$latestRelease = null;
						foreach($releases as $release) {
							if(($release['is_obsolete'] ?? true) || ($release['is_pre_release'] ?? false)) {
								continue;
							}
							if($latestRelease === null || version_compare($release['version'], $latestRelease['version'], '>')) {
								$latestRelease = $release;
							}
						}

						if($latestRelease === null && !empty($releases[0])) {
							$latestRelease = $releases[0];
						}

						if($latestRelease !== null && isset($latestRelease['version'])) {
							$this->setResult([
								'success' => true,
								'latest' => $latestRelease['version'],
								'current' => $this->currentVersion,
								'url' => $latestRelease['html_url'] ?? "https://poggit.pmmp.io/p/ClearLagg"
							]);
							return;
						}
					}
				}
				$this->setResult(['success' => false]);
			}

			public function onCompletion(): void {
				$result = $this->getResult();
				if(($result['success'] ?? false)) {
					$latestVersion = $result['latest'];
					$currentVersion = $result['current'];
					if(version_compare($latestVersion, $currentVersion, '>')) {
						$server = Server::getInstance();
						$server->getLogger()->info("§e[ClearLagg] New version available: §f" . $latestVersion . "§e (Current: §f" . $currentVersion . "§e)");
						$server->getLogger()->info("§eDownload: §f" . ($result['url'] ?? "https://poggit.pmmp.io/p/ClearLagg"));
					}
				}
			}
		});
	}

	public function getClearLaggManager() : ClearLaggManager{
		return $this->clearLaggManager;
	}

	public function getStatsManager() : StatsManager{
		return $this->statsManager;
	}
}
