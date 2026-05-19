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

    private $maxRadius = 50;
    private $maxBlocks = 50000;

    public function onEnable() {
        $this->getLogger()->info("§aAdvancedWorld активирован! (fixed by SantianDev)");
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if ($command->getName() !== "aw") {
            return false;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage("§c§lЭту команду можно использовать только в игре!");
            return true;
        }

        if (!$sender->hasPermission("aw.command")) {
            $sender->sendMessage("§c§lУ вас нет прав на использование этой команды.");
            return true;
        }

        if (count($args) < 3) {
            $this->sendUsage($sender);
            return true;
        }

        $shape = strtolower($args[0]);
        $radius = (int)$args[1];

        if ($radius <= 0 || $radius > $this->maxRadius) {
            $sender->sendMessage("§c§lРадиус должен быть от 1 до {$this->maxRadius}.");
            return true;
        }

        $blockData = explode(":", $args[2]);
        $id = (int)$blockData[0];
        $meta = isset($blockData[1]) ? (int)$blockData[1] : 0;

        $item = Item::get($id, $meta);
        if ($item->getId() !== $id || $item->getBlock()->getId() === 0 && $id !== 0) {
            $sender->sendMessage("§c§lНеверный ID блока: {$args[2]}.");
            return true;
        }
        $block = $item->getBlock();
        $pos = $sender->getPosition();
        $level = $sender->getLevel();

        $blocksSet = 0;

        switch ($shape) {
            case "circle":
                $blocksSet = $this->generateCircle($level, $pos, $radius, $block);
                $sender->sendMessage("§a§lКруг радиусом $radius создан! (блоков: $blocksSet)");
                break;

            case "sphere":
                $blocksSet = $this->generateSphere($level, $pos, $radius, $block);
                $sender->sendMessage("§a§lСфера радиусом $radius создана! (блоков: $blocksSet)");
                break;

            case "pyramid":
                $blocksSet = $this->generatePyramid($level, $pos, $radius, $block);
                $sender->sendMessage("§a§lПирамида высотой $radius создана! (блоков: $blocksSet)");
                break;

            case "oval":
                $blocksSet = $this->generateOval($level, $pos, $radius, $block);
                $sender->sendMessage("§a§lОвал создан! (блоков: $blocksSet)");
                break;

            default:
                $sender->sendMessage("§c§lНеизвестная фигура. §eИспользуйте: circle, sphere, pyramid, oval");
                break;
        }
        return true;
    }

    private function generateCircle($level, Vector3 $pos, int $radius, Block $block): int {
        $count = 0;
        $radiusSq = $radius * $radius;
        $cx = $pos->x;
        $cz = $pos->z;
        $y = $pos->y;

        for ($x = -$radius; $x <= $radius; $x++) {
            $dx = $x * $x;
            for ($z = -$radius; $z <= $radius; $z++) {
                if ($dx + $z * $z <= $radiusSq) {
                    $level->setBlock(new Vector3($cx + $x, $y, $cz + $z), $block);
                    $count++;
                    if ($count > $this->maxBlocks) {
                        $this->getLogger()->warning("Превышен лимит блоков ($this->maxBlocks) при генерации круга!");
                        return $count;
                    }
                }
            }
        }
        return $count;
    }

    private function generateSphere($level, Vector3 $pos, int $radius, Block $block): int {
        $count = 0;
        $radiusSq = $radius * $radius;
        $cx = $pos->x;
        $cy = $pos->y;
        $cz = $pos->z;

        for ($x = -$radius; $x <= $radius; $x++) {
            $dx = $x * $x;
            for ($y = -$radius; $y <= $radius; $y++) {
                $dy = $y * $y;
                for ($z = -$radius; $z <= $radius; $z++) {
                    if ($dx + $dy + $z * $z <= $radiusSq) {
                        $level->setBlock(new Vector3($cx + $x, $cy + $y, $cz + $z), $block);
                        $count++;
                        if ($count > $this->maxBlocks) {
                            $this->getLogger()->warning("Превышен лимит блоков ($this->maxBlocks) при генерации сферы!");
                            return $count;
                        }
                    }
                }
            }
        }
        return $count;
    }

    private function generatePyramid($level, Vector3 $pos, int $height, Block $block): int {
        $count = 0;
        $cx = $pos->x;
        $cy = $pos->y;
        $cz = $pos->z;

        for ($y = 0; $y <= $height; $y++) {
            $size = $height - $y;
            for ($x = -$size; $x <= $size; $x++) {
                for ($z = -$size; $z <= $size; $z++) {
                    $level->setBlock(new Vector3($cx + $x, $cy + $y, $cz + $z), $block);
                    $count++;
                    if ($count > $this->maxBlocks) {
                        $this->getLogger()->warning("Превышен лимит блоков ($this->maxBlocks) при генерации пирамиды!");
                        return $count;
                    }
                }
            }
        }
        return $count;
    }

    private function generateOval($level, Vector3 $pos, int $radius, Block $block): int {
        $count = 0;
        $rx = (int)($radius * 1.5);
        $rz = $radius;
        $cx = $pos->x;
        $cz = $pos->z;
        $y = $pos->y;

        for ($x = -$rx; $x <= $rx; $x++) {
            $dx = ($x * $x) / ($rx * $rx);
            for ($z = -$rz; $z <= $rz; $z++) {
                if ($dx + ($z * $z) / ($rz * $rz) <= 1) {
                    $level->setBlock(new Vector3($cx + $x, $y, $cz + $z), $block);
                    $count++;
                    if ($count > $this->maxBlocks) {
                        $this->getLogger()->warning("Превышен лимит блоков ($this->maxBlocks) при генерации овала!");
                        return $count;
                    }
                }
            }
        }
        return $count;
    }

    private function sendUsage(CommandSender $sender) {
        $sender->sendMessage("§e§lИспользование: §4/aw §d<shape> §a<radius> §b<id[:meta]>");
        $sender->sendMessage("§3§lФигуры: §ecircle, sphere, pyramid, oval");
        $sender->sendMessage("§7Пример: §6/aw sphere 10 35:14 §7(красная шерсть)");
        $sender->sendMessage("§c§lВнимание: радиус не более §e{$this->maxRadius}§c, блоков не более §e{$this->maxBlocks}");
    }
}
