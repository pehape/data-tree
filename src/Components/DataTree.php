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
use Pehape\DataTree\Mappers;
use Pehape\DataTree\Plugins;
use Pehape\DataTree\Sources;
use Pehape\DataTree\Localization\Untranslation;


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

    /** @var Localization\ITranslator */
    private $translator;

    /** @var Application\UI\ITemplate */
    private $templatePath;

    /** @var Application\IPresenter */
    private $presenter = NULL;

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
    ];

    /** Events. */
    use Events\EventsTrait;

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

        $this->setDataSource($dataSource);
        if ($dataMapper === NULL) {
            $dataMapper = new Mappers\DatabaseMapper();
        }

        if ($translator === NULL) {
            $translator = new Untranslation();
        }

        $this->dataMapper = $dataMapper;
        $this->translator = $translator;
        $this->options = Utils\Arrays::mergeTree($this->options, $this->defaultOptions);
    }


    /** Render default template. */
    public function render()
    {
        if ($this->templatePath !== NULL) {
            $this->template->setFile($this->templatePath);
        } else {
            $this->template->setFile(__DIR__ . '/templates/default.latte');
        }

        $this->template->setTranslator($this->translator);
        $this->template->controlName = $this->getControlPath();
        $this->template->innerPlugins = $this->getPlugins(Plugins\BasePlugin::SCOPE_INNER);
        $this->template->outerPlugins = $this->getPlugins(Plugins\BasePlugin::SCOPE_OUTER);
        $this->template->plugins = array_merge($this->template->innerPlugins, $this->template->outerPlugins);
        $this->template->options = Utils\ArrayHash::from($this->options);
        $this->template->isAjax = $this->presenter->isAjax();
        $this->template->render();
    }


    /**
     * Handle various jsTree callbacks.
     * @param string $callback
     */
    public function handleCallback($callback)
    {
        $parameters = $this->processParameters($this->getParameters());
        if (count($this->$callback) === 0) {
            $defaultCallback = $callback . 'Callback';
            $this->$defaultCallback($this, $parameters);
        } else {
            $this->$callback($this, $parameters);
        }
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
    public function addPlugin($name, $class = NULL, $scope = Plugins\BasePlugin::SCOPE_INNER)
    {
        if ($class === NULL) {
            if (array_key_exists($name, $this->defaultPlugins) === FALSE) {
                throw new Exceptions\MissingPluginClassException();
            }

            $class = new $this->defaultPlugins[$name];
            $scope = Plugins\BasePlugin::SCOPE_INNER;
        } elseif (($class instanceof Plugins\IPlugin) === FALSE) {
            throw new Exception\UnvalidPluginClassException();
        }

        $class->setScope($scope);
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


    /** @return string */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }


    /**
     * Set custom template path.
     * @param string $templatePath
     * @return DataTree
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
        return $this;
    }


    /**
     * Get component name path.
     * @return string
     */
    private function getControlPath()
    {
        $names = [$this->name];
        $parent = $this;
        while (($parent = $parent->getParent()) !== NULL) {
            $names[] = $parent->name;
        }

        array_pop($names); // Remove presenter
        return join('-', array_reverse($names));
    }


    /** @inheritdoc */
    protected function attached($presenter)
    {
        parent::attached($presenter);
        $this->presenter = $this->lookup('Nette\Application\IPresenter');
    }


}
