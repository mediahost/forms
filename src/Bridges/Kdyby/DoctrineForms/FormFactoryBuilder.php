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

use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;
use Nette\Forms\ISubmitterControl;
use Nette\Object;
use Venne\Forms\Controls\EventControl;
use Venne\Forms\FormFactory;
use Venne\Forms\IFormFactory;
use Venne\Forms\IFormFactoryBuilder;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FormFactoryBuilder extends Object implements IFormFactoryBuilder
{

	/** @var EntityFormMapper */
	private $entityMapper;

	/** @var IFormFactory */
	private $formFactory;

	/** @var BaseEntity */
	private $entity;

	/** @var array */
	private $saveEntity;

	/** @var bool */
	private $inTransaction = FALSE;


	/**
	 * @param EntityFormMapper $entityMapper
	 */
	public function __construct(EntityFormMapper $entityMapper)
	{
		$this->entityMapper = $entityMapper;
		$this->saveEntity = function(Form $form) {
			return isset($form['_submit']) && $form->isSubmitted() === $form['_submit'];
		};
	}


	/**
	 * @param IFormFactory $formFactory
	 * @return $this
	 */
	public function createNew(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
		return $this;
	}


	/**
	 * @param BaseEntity $entity
	 * @return $this
	 */
	public function setEntity(BaseEntity $entity)
	{
		$this->entity = $entity;
		return $this;
	}


	/**
	 * @param $saveEntity
	 * @return $this
	 */
	public function setSaveEntity($saveEntity)
	{
		$this->saveEntity = $saveEntity;
		return $this;
	}


	/**
	 * @return IFormFactory
	 */
	public function buildFactory()
	{
		return new FormFactory(function () {
			$form = $this->formFactory->create();
			$form['_eventControl'] = $eventControl = new EventControl('_eventControl');
			$entity = $this->entity;
			$saveEntity = $this->saveEntity;
			$eventControl->onAttached[] = function () use ($form, $entity) {
				$this->entityMapper->load($entity, $form);
				unset($form['_eventControl']);
			};
			$form->onValidate[] = function () use ($form, $entity, $saveEntity) {
				$this->entityMapper->save($entity, $form);
				if ($saveEntity($form)) {
					try {
						$this->entityMapper->getEntityManager()->beginTransaction();
						$this->inTransaction = TRUE;
						$this->entityMapper->getEntityManager()->persist($entity);
						$this->entityMapper->getEntityManager()->flush();
					} catch (\Exception $e) {
						$this->entityMapper->getEntityManager()->rollback();
						$this->inTransaction = FALSE;
						$form->addError($e->getMessage());
					}
				}
			};
			$form->onSuccess[] = function ($form) {
				if ($this->inTransaction) {
					try {
						$this->entityMapper->getEntityManager()->commit();
						$this->inTransaction = FALSE;
					} catch (\Exception $e) {
						$this->entityMapper->getEntityManager()->rollback();
						$this->inTransaction = FALSE;
						$form->addError($e->getMessage());
					}
				}
			};
			$form->onError[] = function () {
				if ($this->inTransaction) {
					$this->entityMapper->getEntityManager()->rollback();
				}
			};
			return $form;
		});
	}

}
