<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Forms;

use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;
use Tester\Assert;
use Tester\TestCase;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryBuilder;
use Venne\Forms\FormFactory;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DoctrineFormFactoryBuilderTest extends TestCase
{

	/** @var EntityFormMapper */
	private $entityFormMapper;


	public function setUp()
	{
		$this->entityFormMapper = new EntityFormMapper;
	}


	public function testBuildFactory()
	{
		$entity = new FooEntity;

		$factoryBuilder = new FormFactoryBuilder($this->entityFormMapper);
		$form = $factoryBuilder
			->createNew(new FormFactory)
			->setEntity($entity)
			->buildFactory()
			->create();

		Assert::null($this->entityFormMapper->load);
		Assert::null($this->entityFormMapper->save);

		$form['_eventControl']->onAttached();

		Assert::null($this->entityFormMapper->save);
		Assert::same(array($entity, $form), $this->entityFormMapper->load);

		$form->onValidate();

		Assert::same(array($entity, $form), $this->entityFormMapper->load);
		Assert::same(array($entity, $form), $this->entityFormMapper->save);

	}


	public function testBuildFactorySaveSuccess()
	{
		$entity = new FooEntity;

		$this->entityFormMapper->em = $em = new EntityManager;
		$factoryBuilder = new FormFactoryBuilder($this->entityFormMapper);
		$form = $factoryBuilder
			->createNew(new FormFactory(function () {
				return new MyForm;
			}))
			->setEntity($entity)
			->buildFactory()
			->create();

		$form->s = $form['_submit'];

		Assert::null($this->entityFormMapper->load);
		Assert::null($this->entityFormMapper->save);
		Assert::null($em->begin);
		Assert::null($em->persist);
		Assert::null($em->flush);
		Assert::null($em->commit);
		Assert::null($em->rollback);

		$form['_eventControl']->onAttached();

		Assert::null($this->entityFormMapper->save);
		Assert::same(array($entity, $form), $this->entityFormMapper->load);

		$form->onValidate($form);

		Assert::same(array($entity, $form), $this->entityFormMapper->load);
		Assert::same(array($entity, $form), $this->entityFormMapper->save);
		Assert::true($em->begin);
		Assert::true($em->persist);
		Assert::true($em->flush);
		Assert::null($em->commit);
		Assert::null($em->rollback);

		$form->onSuccess($form);

		Assert::same(array($entity, $form), $this->entityFormMapper->load);
		Assert::same(array($entity, $form), $this->entityFormMapper->save);
		Assert::true($em->begin);
		Assert::true($em->persist);
		Assert::true($em->flush);
		Assert::true($em->commit);
		Assert::null($em->rollback);

	}


	public function testBuildFactorySaveError()
	{
		$entity = new FooEntity;

		$this->entityFormMapper->em = $em = new EntityManager;
		$factoryBuilder = new FormFactoryBuilder($this->entityFormMapper);
		$form = $factoryBuilder
			->createNew(new FormFactory(function () {
				return new MyForm;
			}))
			->setEntity($entity)
			->buildFactory()
			->create();

		$form->s = $form['_submit'];

		Assert::null($this->entityFormMapper->load);
		Assert::null($this->entityFormMapper->save);
		Assert::null($em->begin);
		Assert::null($em->persist);
		Assert::null($em->flush);
		Assert::null($em->commit);
		Assert::null($em->rollback);

		$form['_eventControl']->onAttached();

		Assert::null($this->entityFormMapper->save);
		Assert::same(array($entity, $form), $this->entityFormMapper->load);

		$form->onValidate($form);

		Assert::same(array($entity, $form), $this->entityFormMapper->load);
		Assert::same(array($entity, $form), $this->entityFormMapper->save);
		Assert::true($em->begin);
		Assert::true($em->persist);
		Assert::true($em->flush);
		Assert::null($em->commit);
		Assert::null($em->rollback);

		$form->onError($form);

		Assert::same(array($entity, $form), $this->entityFormMapper->load);
		Assert::same(array($entity, $form), $this->entityFormMapper->save);
		Assert::true($em->begin);
		Assert::true($em->persist);
		Assert::true($em->flush);
		Assert::null($em->commit);
		Assert::true($em->rollback);

	}

}

class MyForm extends Form
{

	public $s = TRUE;


	public function __construct()
	{
		$this->addSubmit('_submit', 'Submit');
	}


	public function isSubmitted()
	{
		return $this->s;
	}

}

class FooEntity extends BaseEntity
{

	public $text;

}

class EntityManager
{

	public $begin;

	public $persist;

	public $flush;

	public $commit;

	public $rollback;


	public function beginTransaction()
	{
		$this->begin = TRUE;
	}


	public function persist()
	{
		$this->persist = TRUE;
	}


	public function flush()
	{
		$this->flush = TRUE;
	}


	public function commit()
	{
		$this->commit = TRUE;
	}


	public function rollback()
	{
		$this->rollback = TRUE;
	}

}

$testCache = new DoctrineFormFactoryBuilderTest;
$testCache->run();

namespace Kdyby\DoctrineForms;

class EntityFormMapper
{

	public $em;

	public $load;

	public $save;


	public function load($entity, $form)
	{
		$this->load = array($entity, $form);
	}


	public function save($entity, $form)
	{
		$this->save = array($entity, $form);
	}


	public function getEntityManager()
	{
		return $this->em;
	}

}

namespace Kdyby\Doctrine\Entities;

class BaseEntity
{

}
