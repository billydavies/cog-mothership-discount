<?php

namespace Message\Mothership\Discount\Discount;

use Message\Cog\Service\Container;
use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Order;

class Discount
{
	public $id;
	public $authorship;
	public $code;
	public $name;
	public $description;

	public $start;
	public $end;

	public $freeShipping;

	public $percentage;
	public $thresholds = array();
	public $discountAmounts = array();

	public $appliesToOrder = true;
	public $products = array();

	public function __construct()
	{
		$this->authorship = new Authorship;
	}

	public function addProduct(Product $product)
	{
		$this->products[$product->id] = $product;
		$appliesToOrder = true;
	}

	public function addThreshold(Threshold $threshold)
	{
		$threshold->discount = $this;
		$this->thresholds[$threshold->currencyID] = $threshold;
	}

	public function addDiscountAmount(DiscountAmount $amount)
	{
		$amount->discount = $this;
		$this->discountAmounts[$amount->currencyID] = $amount;
	}

	public function getThresholdForCurrencyID($currencyID)
	{
		return array_key_exists($currencyID, $this->thresholds) ? $this->thresholds[$currencyID]->threshold : null;
	}

	public function getDiscountAmountForCurrencyID($currencyID)
	{
		return array_key_exists($currencyID, $this->discountAmounts) ? $this->discountAmounts[$currencyID]->amount : null;
	}

	public function isActive()
	{
		$curTime = new \DateTime;
		return (!$this->start || $this->start < $curTime) && (!$this->end || $this->end > $curTime);
	}

	public function hasValidStartEnd()
	{
		return !($this->start !== null && $this->end !== null && $this->start > $this->end);
	}

	public function hasBenefit()
	{
		return $this->percentage !== null || count($this->thresholds) > 0 || $this->freeShipping;
	}

	public function hasValidBenefit()
	{
		return !($this->percentage !== null && count($this->discountAmounts) > 0) && $this->hasBenefit();
	}

	public function isValid()
	{
		return $this->hasValidStartEnd() && $this->hasValidBenefit();
	}

	public function createOrderDiscount(Order\Order $order)
	{
		$orderDiscount = new Order\Entity\Discount\Discount;
		$orderDiscount->code 		= $this->code;
		$orderDiscount->name 		= $this->name;
		$orderDiscount->description = $this->description;

		$orderDiscount->percentage 	= $this->percentage;

		$orderDiscount->order 		= $order;

		if($this->appliesToOrder) {
			$orderDiscount->items = $order->items->all();
		} else {
			foreach($order->items->all() as $item) {
				foreach($this->products as $product) {
					if($item->productID === $product->id) {
						$orderDiscount->addItem($item);
						continue;
					}
				}
			}
		}
	}
}