<?php

namespace Jane\OpenApi\Guesser\OpenApiSchema;

use Jane\JsonSchema\Guesser\JsonSchema\ObjectGuesser;
use Jane\OpenApi\JsonSchema\Version3\Model\Discriminator;
use Jane\OpenApi\JsonSchema\Version3\Model\Schema;
use Jane\JsonSchema\Guesser\Guess\ClassGuess as BaseClassGuess;
use Jane\OpenApi\Guesser\Guess\ClassGuess;
use Jane\OpenApi\Guesser\Guess\MultipleClass;

class SchemaGuesser extends ObjectGuesser
{
    /**
     * {@inheritdoc}
     */
    public function supportObject($object)
    {
        return ($object instanceof Schema) && ('object' === $object->getType() || null === $object->getType()) && null !== $object->getProperties();
    }

    /**
     * @param Schema $object
     */
    protected function createClassGuess($object, $reference, $name, $extensions): BaseClassGuess
    {
        $classGuess = new ClassGuess($object, $reference, $this->naming->getClassName($name), $extensions);

        if ($object->getDiscriminator() instanceof Discriminator &&
            \is_array($object->getEnum()) && \count($object->getEnum()) > 0) {
            $classGuess = new MultipleClass($classGuess, $object->getDiscriminator()->getPropertyName());

            foreach ($object->getEnum() as $subClassName) {
                $subReference = preg_replace('#components/schemas\/.+$#', sprintf('components/schemas/%s', $subClassName), $reference);
                $classGuess->addReference($subClassName, $subReference);
            }
        }

        return $classGuess;
    }

    /**
     * @return string
     */
    protected function getSchemaClass()
    {
        return Schema::class;
    }
}
