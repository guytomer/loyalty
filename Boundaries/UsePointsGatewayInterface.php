<?php


namespace Loyalty\Boundaries;


interface UsePointsGatewayInterface
{
    public function createPointsUsage(array $reductions);
    public function updateActions(array $actions);
    public function commit();
}