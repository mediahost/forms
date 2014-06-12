<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Bridges\Kdyby\DoctrineForms;

use Kdyby\DoctrineForms\EntityFormMapper;
use Venne\Forms\IFormFactory;
use Venne\Forms\IFormFactoryFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FormFactoryFactory implements IFormFactoryFactory
{

	/** @var EntityFormMapper */
	private $entityMapper;


	/**
	 * @param EntityFormMapper $entityMapper
	 */
	public function __construct(EntityFormMapper $entityMapper)
	{
		$this->entityMapper = $entityMapper;
	}


	/**
	 * @param IFormFactory|NULL $formFactory
	 * @return FormFactory
	 */
	public function create(IFormFactory $formFactory = NULL)
	{
		return new FormFactory($this->entityMapper, $formFactory);
	}


	/**
	 * @return EntityFormMapper
	 */
	public function getEntityMapper()
	{
		return $this->entityMapper;
	}

}
