<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\player\PlayerToggleFlightEvent;

class PlayerToggleFlightEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerToggleFlightEvent::class, $subKey);
    }

    /**
     * @param PlayerToggleFlightEvent $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["state"] = new StringVariable($event->isFlying() ? "true" : "false", "state");
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable("target", DummyVariable::PLAYER),
            "state" => new DummyVariable("state", DummyVariable::STRING),
        ];
    }
}