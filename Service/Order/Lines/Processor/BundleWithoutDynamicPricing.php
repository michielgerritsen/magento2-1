<?php
/**
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mollie\Payment\Service\Order\Lines\Processor;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Mollie\Payment\Helper\General;

class BundleWithoutDynamicPricing implements ProcessorInterface
{
    /**
     * @var General
     */
    private $mollieHelper;

    public function __construct(
        General $mollieHelper
    ) {
        $this->mollieHelper = $mollieHelper;
    }

    public function process($orderLine, OrderInterface $order, OrderItemInterface $orderItem = null): array
    {
        if (
            !$orderItem ||
            $orderItem->getProductType() !== Type::TYPE_BUNDLE ||
            !$orderItem->getProduct() ||
            $orderItem->getProduct()->getPriceType() != Price::PRICE_TYPE_FIXED
        ) {
            return $orderLine;
        }

        $forceBaseCurrency = (bool)$this->mollieHelper->useBaseCurrency($order->getStoreId());
        $currency = $forceBaseCurrency ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode();

        $discountAmount = $this->getDiscountAmountWithTax($orderItem, $forceBaseCurrency);
        if (!$discountAmount) {
            return $orderLine;
        }

        // Magento provides us with a discount amount without tax, but calculates with tax is this case. So calculate
        // the correct amount with tax and recalculate the unit price, total amount vat amount and discount amount.

        $taxPercent = $orderItem->getTaxPercent();
        $discountAmount = $discountAmount + (($discountAmount / 100) * $taxPercent);
        $unitPrice = $orderLine['totalAmount']['value'] / $orderItem->getQtyOrdered();
        $newVatAmount = (($unitPrice - $discountAmount) / (100 + $taxPercent)) * $taxPercent;

        $orderLine['unitPrice'] = $this->mollieHelper->getAmountArray($currency, $unitPrice);
        $orderLine['totalAmount'] = $this->mollieHelper->getAmountArray($currency, $unitPrice - $discountAmount);
        $orderLine['vatAmount'] = $this->mollieHelper->getAmountArray($currency, $newVatAmount);
        $orderLine['discountAmount'] = $this->mollieHelper->getAmountArray($currency, $discountAmount);

        return $orderLine;
    }

    private function getDiscountAmountWithTax(OrderItemInterface $item, bool $forceBaseCurrency)
    {
        if ($forceBaseCurrency) {
            return abs($item->getBaseDiscountAmount() + $item->getBaseDiscountTaxCompensationAmount());
        }

        return abs($item->getDiscountAmount() + $item->getDiscountTaxCompensationAmount());
    }
}
