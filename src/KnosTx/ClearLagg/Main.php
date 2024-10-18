<?php

namespace KnosTx\ClearLagg;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use KnosTx\ClearLagg\manager\ClearLaggManager;
use KnosTx\ClearLagg\manager\StatsManager;
use KnosTx\ClearLagg\command\ClearLaggCommand;
use KnosTx\ClearLagg\command\subcommands\StatsCommand;

class Main extends PluginBase {

    private $clearLaggManager;
    private $statsManager;
    private $clearTaskHandler;
    private $broadcastTaskHandler;
    private $timeRemaining;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->clearLaggManager = new ClearLaggManager($this);
        $this->statsManager = new StatsManager($this);

        $this->timeRemaining = $this->getConfig()->get("auto-clear-interval", 300);

        $this->clearTaskHandler = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
            $this->onTick();
        }), 20);

        $broadcastInterval = $this->getConfig()->get("broadcast-interval", 15);
        $this->broadcastTaskHandler = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
            $this->broadcastTime();
        }), $broadcastInterval * 20);
    }

    public function onDisable(): void {
        if ($this->clearTaskHandler instanceof TaskHandler) {
            $this->clearTaskHandler->cancel();
        }
        if ($this->broadcastTaskHandler instanceof TaskHandler) {
            $this->broadcastTaskHandler->cancel();
        }
    }

    public function getClearLaggManager(): ClearLaggManager {
        return $this->clearLaggManager;
    }

    public function getStatsManager(): StatsManager {
        return $this->statsManager;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (strtolower($command->getName()) === "clearlagg") {
            if (count($args) > 0 && strtolower($args[0]) === "stats") {
                (new StatsCommand($this))->execute($sender);
            } else {
                $this->clearLaggManager->clearItems();
                $sender->sendMessage(TextFormat::GREEN . "Items cleared!");
            }
            return true;
        }
        return false;
    }

    private function onTick(): void {
        if ($this->timeRemaining <= 5 && $this->timeRemaining > 0) {
            $this->getServer()->broadcastMessage($this->clearLaggManager->getWarningMessage($this->timeRemaining));
        }

        if ($this->timeRemaining <= 0) {
            $this->clearLaggManager->clearItems();
            $this->statsManager->incrementItemsCleared();
            $this->timeRemaining = $this->getConfig()->get("auto-clear-interval", 300);
        } else {
            $this->timeRemaining--;
        }
    }

    private function broadcastTime(): void {
        $this->getServer()->broadcastMessage(str_replace("{time}", (string)$this->timeRemaining, $this->getConfig()->get("broadcast-message", "§bThe items will be deleted in {time} seconds.")));
    }
}
