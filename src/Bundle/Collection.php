<?php

namespace Message\Mothership\Discount\Bundle;

use Message\Cog\ValueObject\Collection as BaseCollection;

/**
 * Class Collection
 * @package Message\Mothership\Discount\Bundle
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class Collection extends BaseCollection
{
	protected function _configure()
	{
		$this->addValidator(function ($item) {
			if (!$item instanceof Bundle) {
				throw new \InvalidArgumentException('Item must be an instance of Bundle');
			}
		});

		$this->setKey(function ($item) {
			return $item->getID();
		});
	}
}