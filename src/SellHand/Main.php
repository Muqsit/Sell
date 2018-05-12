<?php

/*
*   _____      _ _ 
*  / ____|    | | |
* | (___   ___| | |
*  \___ \ / _ \ | |
*  ____) |  __/ | |
* |_____/ \___|_|_|
 *
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*/

namespace SellHand;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener
{
	/** @var Config */
	private $messages , $sell;

	public function onEnable()
	{
		$this->getLogger()->info("Plugin Enabled by @Muqsit.");
		$files = array("sell.yml" , "messages.yml");
		foreach ($files as $file) {
			if (!file_exists($this->getDataFolder() . $file)) {
				@mkdir($this->getDataFolder());
				file_put_contents($this->getDataFolder() . $file , $this->getResource($file));
			}
		}
		$this->getServer()->getPluginManager()->registerEvents($this , $this);
		$this->sell = new Config($this->getDataFolder() . "sell.yml" , Config::YAML);
		$this->messages = new Config($this->getDataFolder() . "messages.yml" , Config::YAML);
	}

	public function onDisable()
	{
		$this->getLogger()->debug(TF::RED . "Plugin Disabled!");
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $cmd
	 * @param string        $label
	 * @param array         $args
	 * @return bool
	 */
	public function onCommand(CommandSender $sender , Command $cmd , string $label , array $args) : bool
	{
		switch (strtolower($cmd->getName())) {
			case "sell":

				/* Checks if command is executed by console. */
				/* It further solves the crash problem. */
				if (!($sender instanceof Player)) {
					$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::DARK_RED . "Please use this command in game!");
				}

				/* Check if the player is permitted to use the command */
				if ($sender->hasPermission("sell") || $sender->hasPermission("sell.hand") || $sender->hasPermission("sell.all")) {

					/* Disallow non-survival mode abuse */
					if (!$sender->isSurvival()) {
						$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::DARK_RED . "Please switch back to survival mode.");
					}

					/* Sell Hand */
					if (isset($args[0]) && strtolower($args[0]) == "hand") {

						if (!$sender->hasPermission("sell.hand")) {
							$error_handPermission = $this->messages->get("error-nopermission-sellHand");
							$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::RED . $error_handPermission);
						}

						$item = $sender->getInventory()->getItemInHand();
						$itemId = $item->getId();
						/* Check if the player is holding a block */
						if ($item->getId() === 0) {
							$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::DARK_RED . "You aren't holding any blocks/items.");
						}

						/* Recheck if the item the player is holding is a block */
						if ($this->sell->get($itemId) == null) {
							$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::GREEN . $item->getName() . TF::DARK_RED . " cannot be sold.");
						}

						/* Sell the item in the player's hand */
						EconomyAPI::getInstance()->addMoney($sender , $this->sell->get($itemId) * $item->getCount());
						$sender->getInventory()->removeItem($item);
						$price = $this->sell->get($item->getId()) * $item->getCount();
						$sender->sendMessage(TF::GREEN . TF::BOLD . "(!) " . TF::RESET . TF::GREEN . "$" . $price . " has been added to your account.");
						$sender->sendMessage(TF::GREEN . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $item->getCount() . " " . $item->getName() . " at $" . $this->sell->get($itemId) . " each).");

						/* Sell All */
					} elseif (isset($args[0]) && strtolower($args[0]) == "all") {

						if (!$sender->hasPermission("sell.all")) {
							$error_allPermission = $this->messages->get("error-nopermission-sellAll");
							$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::RED . $error_allPermission);
						}

						$items = $sender->getInventory()->getContents();
						foreach ($items as $item) {
							if ($this->sell->get($item->getId()) !== null && $this->sell->get($item->getId()) > 0) {
								$price = $this->sell->get($item->getId()) * $item->getCount();
								EconomyAPI::getInstance()->addMoney($sender , $price);
								$sender->sendMessage(TF::GREEN . TF::BOLD . "(!) " . TF::RESET . TF::GREEN . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $item->getCount() . " " . $item->getName() . " at $" . $this->sell->get($item->getId()) . " each).");
								$sender->getInventory()->remove($item);
							}
						}

					} elseif (isset($args[0]) && strtolower($args[0]) == "about") {
						$sender->sendMessage(TF::RED . TF::BOLD . "(!) " . TF::RESET . TF::GRAY . "This server uses the plugin, Sell Hand, by Muqsit Rayyan with minor tweeks by JackMD.");
					} else {
						$sender->sendMessage(TF::RED . TF::BOLD . "(!) " . TF::RESET . TF::DARK_RED . "Sell Online Market");
						$sender->sendMessage(TF::RED . "- " . TF::DARK_RED . "/sell hand " . TF::GRAY . "- Sell the item that's in your hand.");
						$sender->sendMessage(TF::RED . "- " . TF::DARK_RED . "/sell all " . TF::GRAY . "- Sell every possible thing in inventory.");
					}
				} else {
					$error_permission = $this->messages->get("error-permission");
					$sender->sendMessage(TF::RED . TF::BOLD . "Error: " . TF::RESET . TF::RED . $error_permission);
				}
		}

		return true;
	}
}
