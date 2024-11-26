<?php

/*
 * This file part of
 *    ___ _              _
 *   / __| |___ __ _ _ _| |   __ _ __ _ __ _
 *  |(__| / -_) _` | '_| |__/ _` / _` / _` |
 *   \___|_\___\__,_|_| |____\__,_\__, \__, |
 *                                |___/|___/
 * @license GPL-3.0
 * @author KnosTx
 * @link https://github.com/KnosTx/ClearLagg
 */

declare(strict_types=1);

namespace KnosTx\ClearLagg\manager;

use KnosTx\ClearLagg\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use function count;

/**
 * Manages the collection and display of server statistics.
 */
class StatsManager{

    /** @var Main The main plugin instance. */
    private $plugin;

    /**
     * Constructs a new StatsManager instance.
     *
     * @param Main $plugin The main plugin instance.
     */
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * Sends server statistics, including the number of worlds and entities, to the specified CommandSender.
     *
     * @param CommandSender $sender The sender to receive the stats.
     */
    public function sendStats(CommandSender $sender) : void{
        // Count the number of worlds loaded on the server
        $worldCount = count($this->plugin->getServer()->getWorldManager()->getWorlds());
        
        // Count the total number of entities across all worlds
        $entityCount = 0;
        foreach($this->plugin->getServer()->getWorldManager()->getWorlds() as $world){
            $entityCount += count($world->getEntities());
        }

        // Send stats to the sender
        $sender->sendMessage(TextFormat::YELLOW . "Server Stats:");
        $sender->sendMessage(TextFormat::GOLD . "Worlds: " . TextFormat::WHITE . $worldCount);
        $sender->sendMessage(TextFormat::GOLD . "Entities: " . TextFormat::WHITE . $entityCount);
    }
}