<?php

namespace Jane\OpenApi\Generator\Model;

use Jane\JsonSchema\Generator\Model\ClassGenerator as BaseClassGenerator;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

trait ClassGenerator
{
    use BaseClassGenerator {
        createModel as baseCreateModel;
    }

    /**
     * Return a model class.
     *
     * @param string $name
     * @param Node[] $properties
     * @param Node[] $methods
     * @param bool   $hasExtensions
     * @param string $extends
     *
     * @return Stmt\Class_
     */
    protected function createModel(string $name, $properties, $methods, bool $hasExtensions = false, $extends = null): Stmt\Class_
    {
        $classExtends = null;
        if (null !== $extends) {
            $classExtends = new Name($extends);
        } elseif ($hasExtensions) {
            $classExtends = new Name('\ArrayObject');
        }

        $jsonProps = [];

        foreach($properties as $property){
          if($property instanceof Stmt\Property){
            $jsonProps[] = new Node\Expr\ArrayItem(
                new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $property->props[0]->name),
                new Node\Scalar\String_($property->props[0]->name));
          }
        }

        $methods[] = new Stmt\ClassMethod(
            'jsonSerialize',
            [
                'type'  => Stmt\Class_::MODIFIER_PUBLIC,
                'stmts' => [
                    new Stmt\Return_(
                        new Node\Expr\Array_($jsonProps,['kind' => Node\Expr\Array_::KIND_SHORT])
                    ),
                ],
            ]
        );


        return new Stmt\Class_(
            new Name($this->getNaming()->getClassName($name)),
            [
                'stmts' => array_merge($properties, $methods),
                'extends' => $classExtends,
                'implements' => [new Node\Identifier('\JsonSerializable')]
            ]
        );
    }
}
