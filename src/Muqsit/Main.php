<?php
/*
*   _____      _ _ 
*  / ____|    | | |
* | (___   ___| | |
*  \___ \ / _ \ | |
*  ____) |  __/ | |
* |_____/ \___|_|_|
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*/
 
namespace Muqsit;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\{Command, CommandSender}; //Best PHP7 feature.
use pocketmine\utils\{TextFormat as TF, Config};
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {

  public function onEnable(){
    if(!file_exists($this->getDataFolder() . "sell.yml")){
      @mkdir($this->getDataFolder());
      file_put_contents($this->getDataFolder() . "sell.yml",$this->getResource("sell.yml"));
    }
    $this->getLogger()->info(TF::GREEN.TF::BOLD."
   _____      _ _ 
  / ____|    | | |
 | (___   ___| | |
  \___ \ / _ \ | |
  ____) |  __/ | |
 |_____/ \___|_|_|
 Loaded Sell by Muqsit.");
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->cfg = new Config($this->getDataFolder()."sell.yml", Config::YAML);
  }
  
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
    switch(strtolower($cmd->getName())){
      case "sell":
          if(!isset($args[0]) || count($args) > 1){
            $sender->sendMessage(TF::RED.TF::BOLD."SELL: ".TF::RESET.TF::GOLD ."Use /sell hand");
            return false;
          }
          if($args[0] === "all"){
            if($sender->isCreative()){
              $sender->sendMessage("§l§aSell: " . TF::RESET . TF::DARK_GREEN . "You cannot sell in creative mode.");
              return false;
            }
            if(count($sender->getInventory()->getContents()) ==  0) {
              $sender->sendMessage("§l§aSell: " . TF::RESET . TF::DARK_GREEN . "Your inventory has no contents.");
              return false;
            }
            foreach($sender->getInventory()->getContents() as $item) {
               if($this->cfg->get($item->getId()) == null) continue;
              $price += $this->cfg->get($item->getId()) * $item->getCount();
              EconomyAPI::getInstance()->addMoney($sender, $this->cfg->get($item->getId()) * $item->getCount());
              $sender->getInventory()->removeItem($i);
            }
            $sender->sendMessage("§l§aSell: " . TF::RESET . TF::DARK_GREEN . "$" . $price . " has been added to your account.");
            $sender->sendMessage("§l§aSell: " . TF::RESET . TF::DARK_GREEN . "Sold all your inventory's contents.");
          }
          if($args[0] === "hand"){
            if($sender->isCreative()){
              $sender->sendMessage("§l§aSell: " . TF::RESET . TF::DARK_GREEN . "You cannot sell in creative mode.");
              return false;
            }
            $i = $sender->getInventory()->getItemInHand();
            if($i->getId() === 0){
              $sender->sendMessage("§l§aSell: " . TF::RESET . TF::RED . "You haven't equipped any item in your hand.");
              return false;
            }
            if($this->cfg->get($i->getId()) == null){
              $sender->sendMessage("§l§aSell: " . TF::RESET . TF::RED . "This item cannot be sold.");
              return false;
            }
            EconomyAPI::getInstance()->addMoney($sender, $this->cfg->get($i->getId()) * $i->getCount());
            $sender->getInventory()->removeItem($i);
            $price = $this->cfg->get($i->getId()) * $i->getCount();
            $sender->sendMessage("§l§aSell: " . TF::RESET . TF::DARK_GREEN . "$" . $price . " has been added to your account.");
            $sender->sendMessage("§l§aSell: ".TF::RESET . TF::DARK_GREEN . "Sold for " . TF::GOLD . "$" . $price . TF::GREEN . " (" . $i->getCount() . " " . $i->getName() . " at $" . $this->cfg->get($i->getId()) . " each).");
          }
        }
      }
    }
