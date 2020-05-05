<?php

namespace Ang3\Bundle\OdooApiBundle\Validator\Constraints;

use Ang3\Bundle\OdooApiBundle\ClientRegistry;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OdooRecordValidator extends ConstraintValidator
{
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * @param mixed $value
     *
     * @throws LogicException           when the connection was not found
     * @throws RuntimeException         when the domains expression is not valid
     * @throws InvalidArgumentException when the value of constraint domains is not valid
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!($constraint instanceof OdooRecord)) {
            throw new UnexpectedTypeException($constraint, OdooRecord::class);
        }

        if (null === $value || '' === $value || false === $value) {
            return;
        }

        $client = $this->clientRegistry->get($constraint->connection ?: 'default');
        $value = is_scalar($value) ? (int) $value : $value;

        if (!is_int($value) || $value < 1) {
            $this->context
                ->buildViolation($constraint->typeErrorMessage)
                ->addViolation()
            ;
        }

        $expressionBuilder = $client->expr();
        $domains = $constraint->domains ?: $expressionBuilder->eq('id', $value);

        if (is_string($domains)) {
            try {
                $domains = $this->expressionLanguage->evaluate(
                    $domains,
                    [
                        'expr' => $expressionBuilder,
                        'context' => [
                            'object' => $this->context->getObject(),
                            'model' => $constraint->model,
                            'id' => $value,
                        ],
                    ]
                );
            } catch (\Throwable $e) {
                throw new RuntimeException(sprintf('The domains expression "%s" is not valid', $domains), 0, $e);
            }
        }

        if (!$client->findOneBy($constraint->model, $value, $domains)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ model_name }}', (string) $constraint->model)
                ->setParameter('{{ model_id }}', (string) $value)
                ->addViolation()
            ;
        }
    }
}
