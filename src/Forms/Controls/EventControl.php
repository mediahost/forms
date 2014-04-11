<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Controls;

use Nette\Forms\Controls\HiddenField;

/**
 * @author     Josef Kříž
 */
class EventControl extends HiddenField
{

	/** @var array */
	public $onAttached;


	public function __construct($caption = NULL)
	{
		parent::__construct($caption);

		$this->monitor('Nette\Application\UI\Presenter');
		$this->setOmitted(TRUE);
	}


	protected function attached($form)
	{
		$this->onAttached($this);
	}

}
