<?php

namespace App\Dorcas\Common;



trait APITransformerTrait
{
    /**
     * Combines the entries in the availableIncludes, and defaultIncludes arrays to create a list of embeddable
     * resources for the transformer.
     *
     * @return array
     */
    protected function getEmbeds(): array
    {
        $embeds = [];
        if (property_exists($this, 'availableIncludes')) {
            $embeds = array_merge($embeds, $this->availableIncludes);
        }
        if (property_exists($this, 'defaultIncludes')) {
            $embeds = array_merge($embeds, $this->defaultIncludes);
        }
        return array_unique($embeds);
    }
    
    /**
     * Returns the include field definitions for the transformer.
     *
     * @return array
     */
    public function getIncludeDefinitions(): array
    {
        if (property_exists($this, 'includeDefinitions')) {
            return $this->includeDefinitions;
        }
        return [];
    }
}