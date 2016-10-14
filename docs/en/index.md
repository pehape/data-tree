Documentation
======

Tools, scripts and assets for Pehape libraries.

![alt tag](https://raw.githubusercontent.com/pehape/data-tree/assets/static/images/tree-example.png)

Prerequisities
------------

### Database initialization ###

Open your mysql client and run the script **assets/static/sql/ini.sql**. This script will create 2 tables (default names: datatree, datatree_closure). You can change these names in the ini script.

### Client side ###

*CSS*

```html
<link href="{$basePath}/datatree/css/themes/default/style.min.css" rel="stylesheet" type="text/css">
```

*JS*

```html
<script src="{$basePath}/datatree/js/jquery-3.1.0.min.js" type="text/javascript"></script>
<script src="{$basePath}/datatree/js/jstree-3.3.1.min.js" type="text/javascript"></script>
<script src="{$basePath}/datatree/js/datatree.js" type="text/javascript"></script>
```

*Please be sure you have the valid path set.*

That's all!

Register component as service
------------

```yml
services:
	# Register data source for the DataTree component
	dataTreeSource: Pehape\DataTree\Sources\DatabaseSource
	# Create DataTree component using generated factory 
	dataTree: Pehape\DataTree\Components\IDataTree
``` 

Now you can inject your **dataTree** service to presenters and just use it.

First use
------------

For example in your TreePresenter.php write:

```php

	/** @var \Pehape\DataTree\Components\IDataTree */
	public $dataTree;

	...

	/** @return \Pehape\DataTree\Components\DataTree */
	protected function createComponentDataTree()
	{
		$dataTree = $this->dataTree->create();
	
		// Configure the tree

		return $dataTree;
	}


```

Configuration
------------

### Data sources ###

Each DataTree source must implement interface **IDataSource**. You can use DI and register this source in config.neon. Then this source will be injected to the DataTree component's constructor.

**DatabaseSource**

By default, the DatabaseSource works with tables "datatree" and "datatree_closure". You can change these options by the following code:

```php
	$dataTree->getDataSource()->setBaseTableName('datatree');
	$dataTree->getDataSource()->setClosureTableName('datatree_closure');
```

### Data mapping ###
Each DataTree mapper must implement interface **IDataMapper**. You can use DI and register this mapper in config.neon. Then this source will be injected to the DataTree component's constructor.

**DatabaseMapper**

DatabaseMapper maps database table columns to json object used by jsTree library. See [jsTree JSON structure](https://www.jstree.com/docs/json/) for more info.

You can change mapping by:

```php
	$dataTree->getDataMapper()->setMapping($mappingArray);
```

or you can even set your own data mapper:

```php
	$dataMapper = new YourDataMapper();
	$dataMapper->setMapping($mappingArray);
	$dataTree->setDataMapper($dataMapper);
```

### Rendering ###

DataTree library offers 3 various renderers:

* DefaultRenderer (default)
* BootstrapRenderer
* AdminlteRenderer

You can set your own renderer with custom template:

```php
	$dataTree->setRenderer(new YourRenderer());
```

*DefaultRenderer*
![alt tag](https://raw.githubusercontent.com/pehape/data-tree/assets/static/images/render-default.png)

*BootstrapRenderer*
![alt tag](https://raw.githubusercontent.com/pehape/data-tree/assets/static/images/render-bootstrap.png)

*AdminlteRenderer*
![alt tag](https://raw.githubusercontent.com/pehape/data-tree/assets/static/images/render-adminlte.png)

### Events ###

DataTree component has it's own eventing system. The most of events coresponds with jsTree events.

**Default events are defined in DataTree component class:**

```php
	/** @var array List of default events */
    private $defaultEvents = [
        'load_nodes' => '\Pehape\DataTree\Events\LoadNodesEvent',
        'select_node' => '\Pehape\DataTree\Events\SelectNodeEvent',
        'create_node' => '\Pehape\DataTree\Events\CreateNodeEvent',
        'rename_node' => '\Pehape\DataTree\Events\RenameNodeEvent',
        'delete_node' => '\Pehape\DataTree\Events\DeleteNodeEvent',
        'move_node' => '\Pehape\DataTree\Events\MoveNodeEvent',
        'paste' => '\Pehape\DataTree\Events\PasteNodeEvent',
    ];
```

Each of these events has default implementation of callback. You can set your own callback by:

```php
	$dataTree->getEvent('select_node')->setCallback([$this, 'selectNodeCallback']);
	
	...

	public function selectNodeCallback(\Pehape\DataTree\Components\DataTree $dataTree, \Nette\Utils\ArrayHash $parameters)
	{
		...
		$dataTree->sendSuccessResponse($data);
	}
```

As you can see, each callback accepts 2 parameters:

* \Pehape\DataTree\Components\DataTree $dataTree
* \Nette\Utils\ArrayHash $parameters


### Plugins ###

DataTree component has it's own plugin system. The most of plugins coresponds with jsTree plugins.

```php
	/** @var array List of default plugins */
    private $defaultPlugins = [
        'contextmenu' => '\Pehape\DataTree\Plugins\ContextmenuPlugin',
        'dnd' => '\Pehape\DataTree\Plugins\DragAndDropPlugin',
        'checkbox' => '\Pehape\DataTree\Plugins\CheckboxPlugin',
        'search' => '\Pehape\DataTree\Plugins\SearchPlugin',
        'types' => '\Pehape\DataTree\Plugins\TypesPlugin',
    ];
```

By default, no plugin is enabled. You can enable plugin by:

```php
	$dataTree->addPlugin('contextmenu');
```

Each plugin has common configuration, which can be used to post-configuration of jsTree JavaScript object. Configuration property is type of [\Pehape\Tools\Objects\JObject](https://github.com/pehape/tools/blob/master/docs/en/index.md#3-javascript-object-jobject).
