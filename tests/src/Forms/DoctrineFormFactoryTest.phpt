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
use Venne\Bridges\Kdyby\DoctrineForms\FormFactory;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DoctrineFormFactoryTest extends TestCase
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

		$form = (new FormFactory($this->entityFormMapper))
			->setEntity($entity)
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

		$form = (new FormFactory($this->entityFormMapper, new \Venne\Forms\FormFactory(function () {
			return new MyForm;
		})))
			->setEntity($entity)
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

		$form = (new FormFactory($this->entityFormMapper, new \Venne\Forms\FormFactory(function () {
			return new MyForm;
		})))
			->setEntity($entity)
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


	public function testBuildFactoryFlushError()
	{
		$entity = new FooEntity;
		$this->entityFormMapper->em = $em = new EntityManagerErrorOnFlush;

		$form = (new FormFactory($this->entityFormMapper, new \Venne\Forms\FormFactory(function () {
			return new MyForm;
		})))
			->setEntity($entity)
			->create();

		$form->s = $form['_submit'];

		Assert::null($em->rollback);
		Assert::same(0, count($form->getErrors()));

		$form['_eventControl']->onAttached();
		$form->onValidate($form);

		Assert::true($em->rollback);
		Assert::same(1, count($form->getErrors()));
	}


	public function testBuildFactoryCommitError()
	{
		$entity = new FooEntity;
		$this->entityFormMapper->em = $em = new EntityManagerErrorOnCommit;

		$form = (new FormFactory($this->entityFormMapper, new \Venne\Forms\FormFactory(function () {
			return new MyForm;
		})))
			->setEntity($entity)
			->create();

		$form->s = $form['_submit'];

		$form['_eventControl']->onAttached();
		$form->onValidate($form);

		Assert::null($em->rollback);
		Assert::same(0, count($form->getErrors()));

		$form->onSuccess($form);

		Assert::true($em->rollback);
		Assert::same(1, count($form->getErrors()));
	}


	public function testSetSaveEntity()
	{
		$formFactory = new FormFactory($this->entityFormMapper);

		Assert::exception(function () use ($formFactory) {
			$formFactory->setSaveEntity('aaa');
		}, 'Nette\InvalidArgumentException', 'Argument must be callable.');

		foreach (array(TRUE, FALSE) as $val) {
			$test = FALSE;
			$entity = new FooEntity;
			$this->entityFormMapper->em = $em = new EntityManagerErrorOnCommit;

			$form = (new FormFactory($this->entityFormMapper, new \Venne\Forms\FormFactory(function () {
				return new MyForm;
			})))
				->setEntity($entity)
				->setSaveEntity(function () use (&$test, $val) {
					$test = TRUE;
					return $val;
				})
				->create();

			$form['_eventControl']->onAttached();
			$form->onValidate($form);

			Assert::same($val ? : NULL, $em->begin);
		}
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

class EntityManagerErrorOnCommit extends EntityManager
{

	public function commit()
	{
		throw new \Exception;
	}

}

class EntityManagerErrorOnFlush extends EntityManager
{

	public function flush()
	{
		throw new \Exception;
	}

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

$testCache = new DoctrineFormFactoryTest;
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
