<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Components;

use Nette\Application;
use Nette\Localization;
use Nette\Utils;
use Pehape\DataTree\Events;
use Pehape\DataTree\Exceptions;
use Pehape\DataTree\Localization\Untranslation;
use Pehape\DataTree\Mappers;
use Pehape\DataTree\Plugins;
use Pehape\DataTree\Rendering;
use Pehape\DataTree\Sources;


/**
 * DataTree.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DataTree extends Application\UI\Control
{

    /** @var Sources\IDataSource */
    private $dataSource;

    /** @var Mappers\IDataMapper */
    private $dataMapper;

    /** @var Rendering\IRenderer */
    private $renderer;

    /** @var Localization\ITranslator */
    private $translator;

    /** @var array */
    private $events = [];

    /** @var string */
    private $defaultOptions = [
        'elementType' => 'div',
        'elementId' => 'dataTree',
        'title' => 'Tree',
        'titleElementType' => 'div',
        'defaultState' => self::STATE_OPEN,
        'joinTree' => NULL,
    ];

    /** @var array */
    private $options = [];

    /** @var array List of default plugins */
    private $defaultPlugins = [
        'types' => '\Pehape\DataTree\Plugins\TypesPlugin',
        'contextmenu' => '\Pehape\DataTree\Plugins\ContextmenuPlugin',
        'dnd' => '\Pehape\DataTree\Plugins\DragAndDropPlugin',
        'checkbox' => '\Pehape\DataTree\Plugins\CheckboxPlugin',
        'search' => '\Pehape\DataTree\Plugins\SearchPlugin',
    ];

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

    /** @var array List of default selected nodes. */
    private $selectedNodes = [];

    /** @var array List of default opened nodes. */
    private $openedNodes = [];

    /** @var array List of default disabled nodes. */
    private $disabledNodes = [];

    /** State constants. */
    const STATE_OPEN = 1;
    const STATE_CLOSED = 0;

    /** Responses. */
    const RESPONSE_SUCCESS = 'success';
    const RESPONSE_ERROR = 'error';


    /**
     * Constructor.
     * @param Sources\IDataSource $dataSource
     * @param Mappers\IDataMapper $dataMapper
     * @param Localization\ITranslator $translator
     */
    public function __construct(Sources\IDataSource $dataSource, Mappers\IDataMapper $dataMapper = NULL, Localization\ITranslator $translator = NULL)
    {
        parent::__construct();

        $this->setDataSource(clone $dataSource);
        if ($dataMapper === NULL) {
            $dataMapper = new Mappers\DatabaseMapper();
        }

        if ($translator === NULL) {
            $translator = new Untranslation();
        }

        $this->dataMapper = $dataMapper;
        $this->renderer = new Rendering\DefaultRenderer();
        $this->translator = $translator;
        $this->options = Utils\Arrays::mergeTree($this->options, $this->defaultOptions);
        $this->registerDefaultEvents();
    }


    /** Render default template. */
    public function render()
    {
        $this->renderer->render($this);
    }


    /**
     * Handle various jsTree callbacks.
     * @param string $callback
     */
    public function handleCallback($callback)
    {
        $eventCallback = $this->getEvent($callback)->getCallback();
        $parameters = $this->processParameters($this->getParameters());
        Utils\Callback::invoke($eventCallback, $this, $parameters);
    }


    /**
     * Process parameters before sending them to callbacks.
     * @param array $parameters
     * @return Utils\ArrayHash
     */
    private function processParameters(array $parameters)
    {
        $processedParameters = [];
        foreach ($parameters as $key => $value) {
            if (isset($this->options['joinTree']) === TRUE &&
                substr($key, 0, (strlen($this->options['joinTree']) + 1)) === $this->options['joinTree'] . '_') {
                // If the paramater is from joinedTree
                if (array_key_exists($this->options['joinTree'], $processedParameters) === FALSE) {
                    $processedParameters[$this->options['joinTree']] = [];
                }

                $realKey = str_replace($this->options['joinTree'] . '_', '', $key);
                $processedParameters[$this->options['joinTree']][$realKey] = $value;
            } else {
                $processedParameters[$key] = $value;
            }
        }

        unset($parameters['callback']);
        return Utils\ArrayHash::from($processedParameters);
    }


    /**
     * Get source data for DataTree.
     * @param array $conditions
     * @return array
     */
    public function getData(array $conditions = [])
    {
        $rawData = $this->getRawData($conditions);
        $mappedData = [];
        if ($this->dataMapper !== NULL) {
            $mappedData = $this->dataMapper->applyMapping($rawData);
        }

        return $mappedData;
    }


    /**
     * Returns unmapped data for DataTree.
     * @param array $conditions
     * @return array
     */
    public function getRawData(array $conditions)
    {
        return $this->getDataSource()->getNodes($conditions);
    }


    /**
     * Send response to client.
     * @param array $data
     * @param int $type
     */
    public function sendResponse(array $data, $type = NULL)
    {
        if ($type !== NULL) {
            $responseData = [
                'type' => $type,
                'data' => $data,
            ];
        } else {
            $responseData = $data;
        }

        $jsonResponse = new Application\Responses\JsonResponse($responseData);
        $this->presenter->sendResponse($jsonResponse);
    }


    /**
     * Send succes response to client.
     * @param array $data
     */
    public function sendSuccessResponse(array $data)
    {
        $this->sendResponse($data, self::RESPONSE_SUCCESS);
    }


    /**
     * Send error response to client.
     * @param array $data
     */
    public function sendErrorResponse(array $data)
    {
        $this->sendResponse($data, self::RESPONSE_ERROR);
    }


    /** @return Sources\IDataSource */
    public function getDataSource()
    {
        return $this->dataSource;
    }


    /**
     * Set data source.
     * @param Sources\IDataSource $dataSource
     * @return DataTree
     */
    public function setDataSource(Sources\IDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }


    /** @return Mappers\IDataMapper */
    public function getDataMapper()
    {
        return $this->dataMapper;
    }


    /**
     * Set data mapper.
     * @param Mappers\IDataMapper $mapper
     */
    public function setDataMapper(Mappers\IDataMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }


    /** @return Renderin\IRenderer */
    public function getRenderer()
    {
        return $this->renderer;
    }


    /**
     * @param Rendering\IRenderer $renderer
     * @return DataTree
     */
    public function setRenderer(Rendering\IRenderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }


    public function getTranslator()
    {
        return $this->translator;
    }


    public function setTranslator(Localization\ITranslator $translator)
    {
        $this->translator = $translator;
        return $this;
    }


    /** @return array */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * @param array $options
     * @return ClosureTree
     */
    public function setOptions(array $options)
    {
        $this->options = Utils\Arrays::mergeTree($options, $this->options);
        return $this;
    }


    /**
     * Get option with given name.
     * @param string $name
     * @return mixed (int|string|object|NULL ...)
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : NULL;
    }


    /**
     * Set option with given name.
     * @param string $name
     * @param string $value
     * @return ClosureTree
     */
    public function setOption($name, $value)
    {
        if (is_array($value) === TRUE && array_key_exists($name, $this->options) === TRUE) {
            $this->options[$name] = Utils\Arrays::mergeTree($value, $this->options[$name]);
        } else {
            $this->options[$name] = $value;
        }

        return $this;
    }


    /**
     * Add plugin
     * @param string $name
     * @param Plugins\IPlugin|NULL
     * @param int $scope
     * @return IPlugin
     */
    public function addPlugin($name, $class = NULL)
    {
        if ($class === NULL) {
            if (array_key_exists($name, $this->defaultPlugins) === FALSE) {
                throw new Exceptions\MissingPluginClassException();
            }

            $class = new $this->defaultPlugins[$name]($this);
        } elseif (($class instanceof Plugins\IPlugin) === FALSE) {
            throw new Exception\UnvalidPluginClassException();
        }

        $this[Plugins\BasePlugin::PREFIX . $name] = $class;

        return $class;
    }


    /**
     * Get plugins.
     * @param int|NULL $scope
     * @return array
     */
    public function getPlugins($scope = NULL)
    {
        $plugins = array_filter((array) $this->getComponents(), function ($component) use ($scope) {
            if ($scope !== NULL) {
                return (substr($component->name, 0, strlen(Plugins\BasePlugin::PREFIX)) === Plugins\BasePlugin::PREFIX && $component->getScope() === $scope);
            } else {
                return (substr($component->name, 0, strlen(Plugins\BasePlugin::PREFIX)) === Plugins\BasePlugin::PREFIX);
            }
        });

        return $plugins;
    }


    private function registerDefaultEvents()
    {
        foreach ($this->defaultEvents as $eventName => $eventClass) {
            $this->addEvent($eventName, new $eventClass);
        }
    }


    /**
     * Add event.
     * @param string $name
     * @param Events\IEvent|NULL
     * @return IEvent
     */
    public function addEvent($name, $class)
    {
        if ($class === NULL) {
            if (array_key_exists($name, $this->defaultEvents) === FALSE) {
                throw new Exceptions\MissingEventClassException();
            }

            $class = new $this->defaultEvents[$name];
        } elseif (($class instanceof Events\IEvent) === FALSE) {
            throw new Exceptions\UnvalidEventClassException();
        }

        $this[Events\BaseEvent::PREFIX . $name] = $class;

        return $class;
    }


    /** @return Plugins\IPlugin */
    public function getEvent($name)
    {
        return $this->getComponent(Events\BaseEvent::PREFIX . $name);
    }


    /**
     * Get events.
     * @param int|NULL $type
     * @return array
     */
    public function getEvents($type = NULL)
    {
        $events = array_filter((array) $this->getComponents(), function ($component) use ($type) {
            if ($type !== NULL) {
                return (substr($component->name, 0, strlen(Events\BaseEvent::PREFIX)) === Events\BaseEvent::PREFIX && $component->getType() === $type);
            } else {
                return (substr($component->name, 0, strlen(Events\BaseEvent::PREFIX)) === Events\BaseEvent::PREFIX);
            }
        });

        return $events;
    }


    /**
     * @internal
     * Get component name path.
     * @return string
     */
    public function getControlPath()
    {
        $names = [$this->name];
        $parent = $this;
        while (($parent = $parent->getParent()) !== NULL) {
            $names[] = $parent->name;
        }

        array_pop($names); // Remove presenter
        return join('-', array_reverse($names));
    }


    /** @return array */
    public function getSelectedNodes()
    {
        return $this->selectedNodes;
    }


    /** @return array */
    public function getOpenedNodes()
    {
        return $this->openedNodes;
    }


    /** @return array */
    public function getDisabledNodes()
    {
        return $this->disabledNodes;
    }


    /**
     * @param array $selectedNodes
     * @return DataTree
     */
    public function setSelectedNodes(array $selectedNodes)
    {
        $this->selectedNodes = $selectedNodes;
        return $this;
    }


    /**
     * @param array $openedNodes
     * @return DataTree
     */
    public function setOpenedNodes(array $openedNodes)
    {
        $this->openedNodes = $openedNodes;
        return $this;
    }


    /**
     * @param array $disabledNodes
     * @return DataTree
     */
    public function setDisabledNodes(array $disabledNodes)
    {
        $this->disabledNodes = $disabledNodes;
        return $this;
    }


}
