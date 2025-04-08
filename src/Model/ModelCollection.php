<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.9.6
 */

namespace Quantum\Model;

use InvalidArgumentException;
use IteratorAggregate;
use Countable;
use Generator;

/**
 * Class ModelCollection
 * @package Quantum\Model
 */
class ModelCollection implements Countable, IteratorAggregate
{

    /**
     * @var QtModel[]
     */
    private $models = [];

    /**
     * @var iterable
     */
    private $originalModels;

    /**
     * @var bool
     */
    private $modelsProcessed = false;

    /**
     * @param iterable $models
     */
    public function __construct(iterable $models = [])
    {
        $this->originalModels = $models;

        if (is_array($models)) {
            $this->processModels();
        }
    }

    /**
     * Add a model to the collection
     * @param QtModel $model
     * @return self
     */
    public function add(QtModel $model): self
    {
        $this->processModels();

        $this->models[] = $model;

        if (!is_array($this->originalModels)) {
            $this->originalModels = $this->models;
        } else {
            $this->originalModels[] = $model;
        }

        return $this;
    }

    /**
     * Remove a model from the collection
     * @param QtModel $model
     * @return self
     */
    public function remove(QtModel $model): self
    {
        $this->processModels();

        $this->models = array_filter($this->models, function ($m) use ($model) {
            return $m !== $model;
        });

        $this->originalModels = $this->models;

        return $this;
    }

    /**
     * Get all models as an array
     * @return QtModel[]
     */
    public function all(): array
    {
        $this->processModels();
        return $this->models;
    }

    /**
     * Get the count of models in the collection
     * @return int
     */
    public function count(): int
    {
        $this->processModels();
        return count($this->models);
    }

    /**
     * Get the first model in the collection
     * @return QtModel|null
     */
    public function first(): ?QtModel
    {
        foreach ($this->getIterator() as $model) {
            return $model;
        }

        return null;
    }

    /**
     * Get the last model in the collection
     * @return QtModel|null
     */
    public function last(): ?QtModel
    {
        $this->processModels();
        return empty($this->models) ? null : end($this->models);
    }

    /**
     * Check if the collection is empty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->first() === null;
    }

    /**
     * Get an iterator for the collection
     * @return Generator
     * @throws InvalidArgumentException
     */
    public function getIterator(): Generator
    {
        if ($this->modelsProcessed) {
            yield from $this->models;
        } else {
            foreach ($this->originalModels as $model) {
                $this->validateModel($model);
                yield $model;
            }

            $this->processModels();
        }
    }

    /**
     * Process models from original source into the internal array
     * @throws InvalidArgumentException
     */
    private function processModels()
    {
        if ($this->modelsProcessed) {
            return;
        }

        $this->models = [];

        foreach ($this->originalModels as $model) {
            $this->validateModel($model);
            $this->models[] = $model;
        }

        $this->modelsProcessed = true;
    }

    /**
     * Validate that an item is a QtModel instance
     * @param mixed $model
     * @throws InvalidArgumentException
     */
    private function validateModel($model): void
    {
        if (!$model instanceof QtModel) {
            throw new InvalidArgumentException('All items must be instances of QtModel.');
        }
    }
}