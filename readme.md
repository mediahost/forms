# Venne:Forms [![Build Status](https://secure.travis-ci.org/Venne/forms.png)](http://travis-ci.org/Venne/forms)

Create forms as nested factories:

```
+- IFormFactory ---+    +- IFormFactory --+    +- IFormFactory -+
|   Base factory   | -> |   Adds inputs   | -> |  Adds mapping  | -> create() : Form
+------------------+    +-----------------+    +----------------+
```

**Benefits:**

- Change form configuration around the application on one place.
- Each factory can be used independently.
- It is not necessary to inherit forms.


## Installation

The best way to install Venne/Forms is using Composer:

```sh
composer require venne/forms:@dev
```


## Usage

### Create basic factory

example:

```yml
services:
	basicFormFactory:
		class: Nette\Application\UI\Form
		arguments: [NULL, NULL]
		implement: Venne\Forms\IFormFactory
		setup:
			- setRenderer(@system.formRenderer)
			- setTranslator(@translator.translator)
		autowired: no
```


### Create forms as factory

Implement `Venne\Forms\IFormFactory`:

```php
class FooFormFactory implements Venne\Forms\IFormFactory
{

	private $formFactory;


	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	public function create()
	{
		$form = $this->formFactory->create();
		$form->addText('foo', 'Foo');
		$form->addSubmit('_submit', 'Save');
		return $form;
	}

}
```


### Register forms

Define nested formFactory in constructor

```yml
services:
	fooFormFactory:
		class: FooFormFactory(@basicFormFactory)
```


### Use it in presenter

```php
class ExamplePresenter extends Nette\Application\UI\Presenter
{

	private $fooFormFactory;

	public function __construct(FooFormFactory $fooFormFactory)
	{
		$this->fooFormFactory = $fooFormFactory;
	}

	public function createComponentFooForm()
	{
		$form = $this->fooFormFactory->create();
		$form->onSuccess[] = $this->fooFormSuccess;
		return $form;
	}

	public function fooFormSuccess($form)
	{
		...
	}

}
```


## Connect forms with `kdyby\doctrine-forms`

```php
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;

class ExamplePresenter extends Nette\Application\UI\Presenter
{

	private $fooFormFactory;
    private $formFactoryFactory;

	public function __construct(FooFormFactory $fooFormFactory, FormFactoryFactory $formFactoryFactory)
	{
		$this->fooFormFactory = $fooFormFactory;
		$this->formFactoryFactory = $formFactoryFactory;
	}

	public function createComponentFooForm()
	{
		$entity = ....;

		$form = $this->formFactoryFactory
					->create($this->fooFormFactory)
					->setEntity($entity)
					->create();

		$form->onSuccess[] = $this->fooFormSuccess;
		return $form;
	}

	public function fooFormSuccess($form)
	{
		...
	}

}
