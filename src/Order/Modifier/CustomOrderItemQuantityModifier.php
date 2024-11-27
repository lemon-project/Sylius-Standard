<?php

declare(strict_types=1);

namespace App\Order\Modifier;

use Sylius\Component\Order\Factory\OrderItemUnitFactoryInterface;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifier;

/**
 * CustomOrderItemQuantityModifier class
 *
 * @author    Rafal Wachstiel <rafal.wachstiel@gmail.com>
 */
class CustomOrderItemQuantityModifier extends OrderItemQuantityModifier
{
    protected const STEP = 10;
    
    /** @var int|null $previousQuantity Previous quantity stored */
    protected ?int $previousQuantity = null;
    
    /**
     * CustomOrderItemQuantityModifier constructor
     *
     * @param OrderItemUnitFactoryInterface $orderItemUnitFactory
     */
    public function __construct(OrderItemUnitFactoryInterface $orderItemUnitFactory)
    {
        parent::__construct($orderItemUnitFactory);
    }
    
    /**
     * Override modify method to adjust quantity to nearest multiple of self::STEP.
     * It is mainly used to adjust quantity in cart on the load to override the default quantity.
     *
     * @param OrderItemInterface $orderItem
     * @param int $targetQuantity
     *                           
     * @return void
     */
    public function modify(OrderItemInterface $orderItem, int $targetQuantity): void
    {
        if (is_null($this->previousQuantity)) {
            // we don't have previous quantity, so we can't compare
            $targetQuantity = max(self::STEP, $this->getCeil($targetQuantity));
        } else {
            if ($this->previousQuantity < $targetQuantity) {
                // we are increasing quantity
                $targetQuantity = max(self::STEP, $this->getCeil($targetQuantity));
            } else {
                // we are decreasing quantity
                $targetQuantity = max(self::STEP, $this->getFloor($targetQuantity));
            }
        }
        
        // store as previous quantity
        $this->previousQuantity = $targetQuantity;
        
        // continue with original method
        parent::modify($orderItem, $targetQuantity);
    }
    
    /**
     * Get the nearest lower multiple of self::STEP
     *
     * @param int $targetQuantity
     *
     * @return int
     */
    protected function getCeil(int $targetQuantity): int
    {
        return (int) ceil($targetQuantity / self::STEP) * self::STEP;
    }
    
    /**
     * Get the nearest higher multiple of self::STEP
     *
     * @param int $targetQuantity
     *
     * @return int
     */
    protected function getFloor(int $targetQuantity): int
    {
        return (int) floor($targetQuantity / self::STEP) * self::STEP;
    }
}