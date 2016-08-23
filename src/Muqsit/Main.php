<?php
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
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->cfg = new Config($this->getDataFolder()."sell.yml", Config::YAML);
  }
  
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
    switch(strtolower($cmd->getName())){
      case "sell":
        if($sender->hasPermission("sell") || $sender->hasPermission("sell.hand")){
          if(!isset($args[0]) || count($args) > 1){
            $sender->sendMessage(TF::RED.TF::BOLD."SELL: ".TF::RESET.TF::DARK_RED."Use /sell hand");
            return false;
          }
          if($args[0] === "hand"){
            if($sender->isCreative()){
              $sender->sendMessage(TF::RED.TF::BOLD."SELL: ".TF::RESET.TF::DARK_RED."You cannot sell in creative mode.");
              return false;
            }
            $i = $sender->getInventory()->getItemInHand();
            if($i->getId() === 0){
              $sender->sendMessage(TF::RED . TF::BOLD ."SELL: ". TF::RESET . TF::DARK_RED ."You haven't equipped any item in your hand.");
              return false;
            }
            if($this->cfg->get($i->getId()) == null){
              $sender->sendMessage(TF::RED . TF::BOLD ."SELL: ". TF::RESET . TF::DARK_RED ."This item cannot be sold.");
              return false;
            }
            EconomyAPI::getInstance()->addMoney($sender, $this->cfg->get($i->getId()) * $i->getCount());
            $sender->getInventory()->removeItem($i);
            $price = $this->cfg->get($i->getId()) * $i->getCount();
            $sender->sendMessage(TF::GREEN . TF::BOLD . "SELL: " . TF::RESET . TF::GREEN . "$" . $price . " has been added to your account.");
            $sender->sendMessage(TF::GREEN . TF::BOLD . "SELL: ".TF::RESET . TF::GREEN . "Sold for " . TF::RED . "$" . $price . TF::GREEN . " (" . $i->getCount() . " " . $i->getName() . " at $" . $this->cfg->get($i->getId()) . " each).");
          }
        }else{
          $sender->sendMessage(TF::RED . TF::BOLD . "SELL :".TF::RESET . TF::DARK_RED . "You don't have permission to quick sell!";
          return false;
        }
      break;
      }
    }
  }
