<?php

namespace KnosTx\ClearLagg\manager;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use KnosTx\ClearLagg\Main;

class StatsManager {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function sendStats(CommandSender $sender): void {
        $worldCount = count($this->plugin->getServer()->getWorldManager()->getWorlds());
        $entityCount = 0;
        foreach ($this->plugin->getServer()->getWorldManager()->getWorlds() as $world) {
            $entityCount += count($world->getEntities());
        }

        $sender->sendMessage(TextFormat::YELLOW . "Server Stats:");
        $sender->sendMessage(TextFormat::GOLD . "Worlds: " . TextFormat::WHITE . $worldCount);
        $sender->sendMessage(TextFormat::GOLD . "Entities: " . TextFormat::WHITE . $entityCount);
    }
}
