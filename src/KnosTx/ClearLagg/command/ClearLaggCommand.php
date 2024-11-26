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

namespace KnosTx\ClearLagg\command;

use KnosTx\ClearLagg\command\subcommands\StatsCommand;
use KnosTx\ClearLagg\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

/**
 * Command handler for the ClearLagg plugin.
 * Provides functionality for clearing items and showing stats.
 */
class ClearLaggCommand extends Command implements PluginOwned{
    use PluginOwnedTrait;

    /** @var Main The main plugin instance. */
    private $plugin;

    /**
     * Constructs the ClearLaggCommand instance.
     *
     * @param Main $plugin The main plugin instance.
     */
    public function __construct(Main $plugin){
        parent::__construct("clearlagg", "Clear lag by removing items", "/clearlagg [stats]", ["cl"]);
        $this->plugin = $plugin;
        $this->setPermission("clearlagg.command");
    }

    /**
     * Executes the ClearLagg command.
     *
     * @param CommandSender $sender The sender of the command.
     * @param string $commandLabel The label of the command.
     * @param array $args Arguments passed to the command.
     * @return bool Whether the command was successfully executed.
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$this->testPermission($sender)){
            $sender->sendMessage("You don't have permission to use this command.");
            return false;
        }

        try{
            if(isset($args[0]) && $args[0] === "stats"){
                $statsCommand = new StatsCommand($this->plugin);
                return $statsCommand->execute($sender);
            }else{
                $this->plugin->getClearLaggManager()->clearItems();
                $sender->sendMessage("Items cleared successfully.");
            }
        }catch(\Exception $e){
            $sender->sendMessage("An error occurred: " . $e->getMessage());
            $this->plugin->getLogger()->error("Error in ClearLaggCommand: " . $e->getMessage(), $e);
            return false;
        }

        return true;
    }

    /**
     * Returns the owning plugin for this command.
     *
     * @return Main The main plugin instance.
     */
    public function getOwningPlugin() : Main{
        return $this->plugin;
    }
}
