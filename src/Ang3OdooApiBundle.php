<?php

namespace Ang3\Bundle\OdooApiBundle;

use Ang3\Bundle\OdooApiBundle\DBAL\Types\RecordType;
use Ang3\Bundle\OdooApiBundle\ORM\ModelRegistry;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;

class Ang3OdooApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        /* @var ModelRegistry */
        $modelRegistry = $this->container->get(ModelRegistry::class);

        /** @var RecordType */
        $recordType = Type::getType(RecordType::NAME);

        // Enregistrement du registre des modèles
        $recordType->setModelRegistry($modelRegistry);
    }
}
