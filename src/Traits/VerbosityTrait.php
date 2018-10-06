<?php

namespace Traits;

/**
 * Verbosity trait.
 */
trait VerbosityTrait
{
    /**
     * $verbosity.
     *
     * @var bool
     */
    private $verbosity = true;

    /**
     * Get $verbosity.
     *
     * @return bool
     */
    public function getVerbosity()
    {
        return $this->verbosity;
    }

    /**
     * Set $verbosity.
     *
     * @param bool $verbosity $verbosity
     *
     * @return self
     */
    public function setVerbosity(bool $verbosity)
    {
        $this->verbosity = $verbosity;

        return $this;
    }
}
