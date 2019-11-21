<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\FormAPI\element\Button;

class HomeForm {

    public function sendMenu(Player $player) {
        (new ListForm("@form.home.title"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@mineflow.recipe"),
                new Button("@mineflow.command"),
                new Button("@form.exit")
            ])->onRecive(function (Player $player, ?int $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendMenu($player);
                        break;
                    case 1:
                        (new CommandForm)->sendMenu($player);
                        break;
                }
            })->show($player);
    }

    public function sendConfirmRename(Player $player, string $name, string $newName, callable $callback) {
        (new ModalForm("@form.home.rename.title"))
            ->setContent(Language::get("form.home.rename.content", [$name, $newName]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, string $name, string $newName, callable $callback) {
                if ($data === null) return;
                call_user_func_array($callback, [$data, $name, $newName]);
            })->addArgs($name, $newName, $callback)->show($player);
    }
}