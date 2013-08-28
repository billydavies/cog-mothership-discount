<?php

namespace Message\Mothership\Discount\Discount;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\DB;

use Message\User\UserInterface;

class Create
{
	protected $_query;
	protected $_locale;
	protected $_currentUser;

	public function __construct(DB\Query $query, UserInterface $currentUser)
	{
		$this->_query		= $query;
		$this->_currentUser	= $currentUser;
	}

	public function create(Discount $discount)
	{
		if (!$discount->authorship->createdAt()) {
			$discount->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$result = $this->_query->run(
			'INSERT INTO
				discount
			SET
				code          = ?s,
				created_at 	  = ?d,
				created_by    = ?i,
				name 		  = ?s,
				description   = ?s,
				start   	  = ?dn,
				end   		  = ?dn',
			array(
				$discount->code,
				$discount->authorship->createdAt(),
				$discount->authorship->createdBy(),
				$discount->name,
				$discount->description,
				$discount->start,
				$discount->end,
			)
		);

		$discount->id = $result->id();
		
		return $discount;
	}
}
