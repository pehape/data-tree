<?php

/**
 * This file is part of the Pehape libraries (http://pehape.cz)
 * Copyright (c) 2016 Tomas Rathouz <trathouz at gmail.com>
 */

namespace Pehape\DataTree\Components;

use Nette\Application\Responses;
use Nette\Application\UI;
use Nette\Localization;
use Nette\Utils\Arrays;
use Nette\Utils\ArrayHash;
use Pehape\DataTree\Events;
use Pehape\DataTree\Exceptions;
use Pehape\DataTree\Mappers;
use Pehape\DataTree\Sources;
use Pehape\DataTree\Localization\Untranslation;


/**
 * DataTree.
 *
 * @author Tomas Rathouz <trathouz at gmail.com>
 */
class DataTree extends UI\Control
{

    /** @var Sources\IDataSource */
    private $dataSource;

    /** @var Mappers\IDataMapper */
    private $dataMapper;

    /** @var Localization\ITranslator */
    private $translator;

    /** @var UI\ITemplate */
    private $defaultTemplate;

    /** @var UI\ITemplate */
    private $interactionTemplate;

    /** @var UI\ITemplate */
    private $themeTemplate;

    /** @var string */
    private $defaultOptions = [
        'elementType' => 'div',
        'elementId' => 'dataTree',
        'title' => 'Tree',
        'titleElementType' => 'div',
        'defaultState' => self::STATE_OPEN,
        'interaction' => [
            'include' => TRUE,
            'includePath' => '/datatree/js',
            'applyWrap' => TRUE,
            'applyReady' => TRUE,
        ],
        'theme' => [
            'include' => TRUE,
            'includePath' => '/datatree/css/themes',
            'name' => 'default',
        ],
    ];

    /** @var array */
    private $options = [];

    /** @var array List of default enabled plugins */
    private $defaultPlugins = ['contextmenu', 'types', 'dnd'];

    /** @var array List of enabled plugins */
    private $plugins;

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
        $this->options = Arrays::mergeTree($this->options, $this->defaultOptions);
        $this->plugins = $this->defaultPlugins;

        $this->setDefaultCallbacks();
    }


    /** Render default template. */
    public function render()
    {
        $template = NULL;
        if ($this->defaultTemplate !== NULL) {
            $template = $this->defaultTemplate;
        } else {
            $template = $this->template;
            $template->setFile(__DIR__ . '/templates/default.latte');
        }

        $template->setTranslator($this->translator);
        $template->options = ArrayHash::from($this->options);
        $template->render();
    }


    /** Render template with JavaScript interaction. */
    public function renderInteraction()
    {
        $template = NULL;
        if ($this->interactionTemplate !== NULL) {
            $template = $this->interactionTemplate;
        } else {
            $template = $this->template;
            $template->setFile(__DIR__ . '/templates/interaction.latte');
        }

        $template->setTranslator($this->translator);
        $template->controlName = $this->getControlPath();
        $template->plugins = $this->plugins;
        $template->options = ArrayHash::from($this->options);
        $template->render();
    }


    /** Render template with CSS styles. */
    public function renderTheme()
    {
        $template = NULL;
        if ($this->themeTemplate !== NULL) {
            $template = $this->themeTemplate;
        } else {
            $template = $this->template;
            $template->setFile(__DIR__ . '/templates/theme.latte');
        }

        $template->setTranslator($this->translator);
        $template->options = ArrayHash::from($this->options);
        $template->render();
    }


    /**
     * Handle various jsTree callbacks.
     * @param string $callback
     */
    public function handleCallback($callback)
    {
        $parameters = $this->processParameters($this->getParameters());
        $this->$callback($this, $parameters);
    }


    /**
     * Process parameters before sending them to callbacks.
     * @param array $parameters
     * @return ArrayHash
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
        return ArrayHash::from($processedParameters);
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

        $jsonResponse = new Responses\JsonResponse($responseData);
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
        $this->sendResponse($data, self::RESPONSE_SUCCESS);
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
        $this->options = Arrays::mergeTree($options, $this->options);
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
            $this->options[$name] = Arrays::mergeTree($value, $this->options[$name]);
        } else {
            $this->options[$name] = $value;
        }

        return $this;
    }


    /**
     * Set enabled plugins.
     * @param array $plugins
     * @return DataTree
     */
    public function setEnabledPlugins(array $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }


    /**
     * Enable plugin.
     * @param string $plugin
     * @return DataTree
     */
    public function enablePlugin($plugin)
    {
        if (in_array($plugin, $this->plugins) === FALSE) {
            $this->plugins[] = $plugin;
        }

        return $this;
    }


    /** @return @array */
    public function getEnabledPlugins()
    {
        return $this->plugins;
    }


    /** @return UI\ITemplate */
    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }


    /** @return UI\ITemplate */
    public function getInteractionTemplate()
    {
        return $this->interactionTemplate;
    }


    /** @return UI\ITemplate */
    public function getThemeTemplate()
    {
        return $this->themeTemplate;
    }


    /**
     * @param UI\ITemplate $defaultTemplate
     * @return DataTree
     */
    public function setDefaultTemplate(UI\ITemplate $defaultTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;
        return $this;
    }


    /**
     * @param UI\ITemplate $interactionTemplate
     * @return DataTree
     */
    public function setInteractionTemplate(UI\ITemplate $interactionTemplate)
    {
        $this->interactionTemplate = $interactionTemplate;
        return $this;
    }


    /**
     * @param UI\ITemplate $themeTemplate
     * @return DataTree
     */
    public function setThemeTemplate(UI\ITemplate $themeTemplate)
    {
        $this->themeTemplate = $themeTemplate;
        return $this;
    }


    /** Set default callbacks. */
    private function setDefaultCallbacks()
    {
        $this->onLoadNodes[] = [$this, 'onLoadNodesCallback'];
        $this->onCreateNode[] = [$this, 'onCreateNodeCallback'];
        $this->onRenameNode[] = [$this, 'onRenameNodeCallback'];
        $this->onMoveNode[] = [$this, 'onMoveNodeCallback'];
        $this->onDeleteNode[] = [$this, 'onDeleteNodeCallback'];
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onLoadNodesCallback(DataTree $tree)
    {
        $nodes = $this->dataSource->getNodes();
        $mappedData = $this->dataMapper->applyMapping($nodes);
        $tree->sendResponse($mappedData);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onCreateNodeCallback(DataTree $tree, ArrayHash $parameters)
    {
        try {
            $nodeId = $this->dataSource->createNode($parameters->id, [
                'name' => $parameters->text,
                'type' => $parameters->type,
            ]);
        } catch (Exceptions\DatabaseSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse(['id' => $nodeId]);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onRenameNodeCallback(DataTree $tree, ArrayHash $parameters)
    {
        try {
            $this->dataSource->updateNode($parameters->id, ['name' => $parameters->text]);
        } catch (Exceptions\DatabaseSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse([]);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onMoveNodeCallback(DataTree $tree, ArrayHash $parameters)
    {
        try {
            $this->dataSource->moveNode($parameters->id, $parameters->parent);
        } catch (Exceptions\DatabaseSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse([]);
    }


    /**
     * @internal
     * @param DataTree $tree
     * @param array $parameters
     */
    public function onDeleteNodeCallback(DataTree $tree, ArrayHash $parameters)
    {
        try {
            $this->dataSource->removeNode($parameters->id);
        } catch (Exceptions\DatabaseSourceException $e) {
            $this->sendErrorResponse([]);
        }

        $this->sendSuccessResponse([]);
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


}
