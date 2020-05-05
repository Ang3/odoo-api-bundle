<?php

namespace Ang3\Bundle\OdooApiBundle\Validator\Constraints;

use Ang3\Component\Odoo\Expression\DomainInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OdooRecord extends Constraint
{
    /**
     * @var string
     *
     * @required
     */
    public $model;

    /**
     * @var DomainInterface[]|DomainInterface|array|string|null
     */
    public $domains;

    /**
     * @var string
     */
    public $connection = 'default';

    /**
     * @var string
     */
    public $typeErrorMessage = 'This value must be a positive integer.';

    /**
     * @var string
     */
    public $message = 'The record of ID {{ model_id }} from model "{{ model_name }}" does not exist.';

    public function getDefaultOption(): string
    {
        return 'model';
    }
}
