<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Validator;

/**
 * Class Validator Pool
 */
class Pool
{
    /**
     * @var Product\Type\AbstractType[]
     */
    protected $validators = [];

    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * Get Validators defined in di
     *
     * @param string $type
     * @return array
     */
    public function getValidators($type)
    {
        return isset($this->validators[$type]) ? $this->validators[$type] : $this->validators['abstract'];
    }
}
