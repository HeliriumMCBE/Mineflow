<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipePack;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class ExportForm {

    public function sendRecipeListByRecipe(Player $player, Recipe $recipe): void {
        $recipes = Main::getRecipeManager()->getWithLinkedRecipes($recipe, $recipe);
        $this->sendRecipeList($player, $recipes);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function sendRecipeList(Player $player, array $recipes, array $messages = []): void {
        $recipes = array_values($recipes);

        $buttons = [
            new Button("@form.export.execution", function () use($player, $recipes) { $this->sendExportMenu($player, $recipes); }),
            new Button("@form.add")
        ];
        foreach ($recipes as $i => $recipe) {
            $buttons[] = new Button($recipe->getGroup()."/".$recipe->getName(), function () use($player, $recipes, $i) {
                $this->sendRecipeMenu($player, array_values($recipes), $i);
            });
        }

        (new ListForm("@form.export.recipeList.title"))
            ->setContent("@form.selectButton")
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data, array $recipes) {
                (new MineflowForm)->selectRecipe($player, "@form.export.selectRecipe.title",
                    function (Player $player, Recipe $recipe) use ($recipes) {
                        $recipes = array_merge($recipes, Main::getRecipeManager()->getWithLinkedRecipes($recipe, $recipe));
                        $this->sendRecipeList($player, $recipes, ["@form.added"]);
                    },
                    function (Player $player) use ($recipes) {
                        $this->sendRecipeList($player, $recipes, ["@form.canceled"]);
                    }
                );
            })->addMessages($messages)->addArgs($recipes)->show($player);
    }

    public function sendRecipeMenu(Player $player, array $recipes, int $index): void {
        $recipe = $recipes[$index];
        (new ListForm($recipe->getName()))
            ->setContent("@form.selectButton")
            ->setButtons([
                new Button("@form.back"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, array $recipes, int $index) {
                if ($data === 0) {
                    $this->sendRecipeList($player, $recipes);
                } else {
                    unset($recipes[$index]);
                    $this->sendRecipeList($player, $recipes, ["@form.delete.success"]);
                }
            })->addArgs($recipes, $index)->show($player);
    }

    /**
     * @param Player $player
     * @param Recipe[] $recipes
     * @param array<string|int> $default
     * @param array<string|int>[] $errors
     */
    public function sendExportMenu(Player $player, array $recipes, array $default = [], array $errors = []): void {
        if (empty($recipes)) {
            $this->sendRecipeList($player, $recipes, ["@form.export.empty"]);
            return;
        }

        (new CustomForm("@mineflow.export"))
            ->setContents([
                new Input("@form.export.name", "", $default[0] ?? "", true),
                new Input("@form.export.author", "", $default[1] ?? $player->getName(), true),
                new Input("@form.export.detail", "", $default[2] ?? ""),
                new Toggle("@form.export.includeConfig", true),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, array $recipes) {
                if ($data[4]) {
                    $this->sendRecipeList($player, $recipes, ["@form.canceled"]);
                    return;
                }

                [$name, $author, $detail] = $data;

                /** @var array<string|int>[] $errors */
                $errors = [];
                if (preg_match("#[.¥/:?<>|*\"]#u", preg_quote($name, "/@#~"))) $errors = ["@form.recipe.invalidName", 0];

                if (!empty($errors)) {
                    $this->sendExportMenu($player, $recipes, $data, $errors);
                    return;
                }

                $pack = new RecipePack($name, $author, $detail, $recipes, $data[3] ? null : []);
                $pack->export(Main::getInstance()->getDataFolder()."/exports/");

                $player->sendMessage(Language::get("form.export.success", [$name.".json"]));
            })->addErrors($errors)->addArgs($recipes)->show($player);
    }

}