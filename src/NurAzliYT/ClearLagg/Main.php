<?php

namespace NurAzliYT\ClearLagg;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use NurAzliYT\ClearLagg\manager\ClearLaggManager;
use NurAzliYT\ClearLagg\manager\StatsManager;
use NurAzliYT\ClearLagg\command\ClearLaggCommand;
use NurAzliYT\ClearLagg\command\subcommands\StatsCommand;

class Main extends PluginBase {

    private Config $config;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $this->getLogger()->info("ClearLagg plugin enabled!");
        $this->registerCommands();
        $this->startClearlagg();
    }

    private function registerCommands() {
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->register("clearlagg", new ClearLaggCommand($this));
        $commandMap->register("clearlaggstats", new StatsCommand($this));
    }

    private function startClearlagg() {
        $interval = $this->config->get("auto-clear-interval", 300);  // Default 300 detik (5 menit)
        $this->getScheduler()->scheduleRepeatingTask(new ClearLaggManager($this), 20 * $interval);
    }

    public function getClearLaggConfig(): Config {
        return $this->config;
    }

    public function notifyPlayers(string $message, int $countdown) {
        $task = new class($this, $message, $countdown) extends \pocketmine\scheduler\Task {
            private Main $plugin;
            private string $message;
            private int $countdown;

            public function __construct(Main $plugin, string $message, int $countdown) {
                $this->plugin = $plugin;
                $this->message = $message;
                $this->countdown = $countdown;
            }

            public function onRun(): void {
                $this->plugin->getServer()->broadcastMessage($this->message . " in " . $this->countdown . " seconds!");
                if ($this->countdown <= 0) {
                    $this->plugin->getScheduler()->cancelTask($this->getTaskId());
                }
                $this->countdown -= 10;
            }
        };
        $this->getScheduler()->scheduleRepeatingTask($task, 20 * 10);
    }
}