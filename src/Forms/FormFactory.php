<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms;

use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Object;
use Venne\System\Administration\Forms\Bootstrap3Renderer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FormFactory extends Object implements IFormFactory
{

	/** @var NULL|IFormFactory|callable */
	private $formFactory;


	/**
	 * @param NULL|IFormFactory|callable $formFactory
	 * @throws InvalidArgumentException
	 */
	public function __construct($formFactory = NULL)
	{
		if ($formFactory && !$formFactory instanceof IFormFactory && !is_callable($formFactory)) {
			throw new InvalidArgumentException("Form factory must be instance of 'Venne\Forms\IFormFactory' OR callable.");
		}

		$this->formFactory = $formFactory;
	}


	/**
	 * @return Form
	 * @throws \Nette\InvalidStateException
	 */
	public function create()
	{
		$form = $this->formFactory
			? ($this->formFactory instanceof IFormFactory ? $this->formFactory->create() : call_user_func($this->formFactory))
			: new Form;

		if (!$form instanceof Form) {
			throw new InvalidStateException("Created object is not instance of 'Nette\Application\UI\Form'.");
		}

		return $form;
	}

}
