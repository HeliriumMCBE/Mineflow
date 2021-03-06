<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class ItemVariableDropdown extends VariableDropdown {

    protected $variableType = DummyVariable::ITEM;

    protected $actions = [
        FlowItemIds::CREATE_ITEM_VARIABLE,
    ];

    public function __construct(array $variables = [], string $default = "", string $text = "@action.form.target.item") {
        parent::__construct($text, $variables, [DummyVariable::ITEM], $default);
    }
}