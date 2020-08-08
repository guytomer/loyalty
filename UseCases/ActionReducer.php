<?php


namespace Loyalty\UseCases;


trait ActionReducer
{
    private function reduceActions(array $actions, int $points): array
    {
        return array_reduce($actions, function ($currentState, $action) {
            $points = $currentState["points"];
            $reductions = $currentState["reductions"];
            $actions = $currentState["actions"];
            $actionReduction = $points >= $action["activePoints"] ? $action["activePoints"] : $points;
            if ($actionReduction) {
                $action["activePoints"] -= $actionReduction;
                $reductions[] = ["actionId" => $action["id"], "usedPoints" => $actionReduction];
                $actions[] = $action;
            }
            return [
                "points" => $points - $actionReduction,
                "reductions" => $reductions,
                "actions" => $actions
            ];
        }, ["points" => $points, "reductions" => [], "actions" => []]);
    }
}