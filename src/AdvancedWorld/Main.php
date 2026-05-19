<?php

namespace AdvancedWorld;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\block\Block;

class Main extends PluginBase {

    public function onEnable() {
        $this->getLogger()->info("§aAdvancedWorld активирован! Версия для PHP 7.0");
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if ($command->getName() === "aw") {
            if (!$sender instanceof Player) {
                $sender->sendMessage("§c§lЭту команду можно использовать только в игре!");
                return true;
            }

            if (!$sender->isOp()) {
                $sender->sendMessage("§c§lу вас нет прав на использование этой команды.");
                return true;
            }

            if (count($args) < 3) {
                $sender->sendMessage("§e§lИспользование: §4/aw §d<shape> §a<radius> §b<id[:meta]>");
                $sender->sendMessage("§3§lФигуры: §ecircle, sphere, pyramid, oval");
                return true;
            }

            $shape = strtolower($args[0]);
            $radius = (int)$args[1];
            
            // Обработка ID и Мета (например 35:14)
            $itemData = explode(":", $args[2]);
            $id = (int)$itemData[0];
            $meta = isset($itemData[1]) ? (int)$itemData[1] : 0;
            
            $block = Item::get($id, $meta)->getBlock();
            $pos = $sender->getPosition();
            $level = $sender->getLevel();

            switch ($shape) {
                case "circle":
                    for ($x = -$radius; $x <= $radius; $x++) {
                        for ($z = -$radius; $z <= $radius; $z++) {
                            if (($x * $x) + ($z * $z) <= ($radius * $radius)) {
                                $level->setBlock(new Vector3($pos->x + $x, $pos->y, $pos->z + $z), $block);
                            }
                        }
                    }
                    $sender->sendMessage("§a§lКруг радиусом $radius создан!");
                    break;

                case "sphere":
                    for ($x = -$radius; $x <= $radius; $x++) {
                        for ($y = -$radius; $y <= $radius; $y++) {
                            for ($z = -$radius; $z <= $radius; $z++) {
                                if (($x * $x) + ($y * $y) + ($z * $z) <= ($radius * $radius)) {
                                    $level->setBlock(new Vector3($pos->x + $x, $pos->y + $y, $pos->z + $z), $block);
                                }
                            }
                        }
                    }
                    $sender->sendMessage("§a§lСфера радиусом $radius создана!");
                    break;

                case "pyramid":
                    for ($y = 0; $y <= $radius; $y++) {
                        $size = $radius - $y;
                        for ($x = -$size; $x <= $size; $x++) {
                            for ($z = -$size; $z <= $size; $z++) {
                                $level->setBlock(new Vector3($pos->x + $x, $pos->y + $y, $pos->z + $z), $block);
                            }
                        }
                    }
                    $sender->sendMessage("§a§lПирамида высотой $radius создана!");
                    break;

                case "oval":
                    // Овал, вытянутый по оси X
                    for ($x = -$radius * 1.5; $x <= $radius * 1.5; $x++) {
                        for ($z = -$radius; $z <= $radius; $z++) {
                            if ((($x * $x) / (1.5 * 1.5)) + ($z * $z) <= ($radius * $radius)) {
                                $level->setBlock(new Vector3($pos->x + $x, $pos->y, $pos->z + $z), $block);
                            }
                        }
                    }
                    $sender->sendMessage("§a§lОвал создан!");
                    break;

                default:
                    $sender->sendMessage("§c§lНеизвестная фигура. §eИспользуйте: circle, sphere, pyramid, oval");
                    break;
            }
            return true;
        }
        return false;
    }
}
