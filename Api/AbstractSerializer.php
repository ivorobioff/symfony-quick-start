<?php
namespace ImmediateSolutions\SupportBundle\Api;

use DateTime;
use ImmediateSolutions\Support\Other\Enum;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
abstract class AbstractSerializer
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $serializer
     * @param object $object
     * @param $initializer
     */
    protected function delegate($serializer, $object, callable $initializer = null)
    {
        $serializer = new $serializer($this->container);

        if ($initializer){
            $initializer($serializer);
        }

        return $serializer($object);
    }

    /**
     * @param DateTime $datetime
     * @return string
     */
    protected function datetime(DateTime $datetime = null)
    {
        if ($datetime === null){
            return $datetime;
        }

        return $datetime->format(DateTime::ATOM);
    }

    /**
     * @param Enum $enum
     * @return string|integer
     */
    protected function enum(Enum $enum = null)
    {
        if ($enum === null){
            return $enum;
        }

        return $enum->value();
    }
}