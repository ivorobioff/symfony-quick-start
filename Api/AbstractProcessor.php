<?php
namespace ImmediateSolutions\SupportBundle\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
abstract class AbstractProcessor extends \ImmediateSolutions\Support\Api\AbstractProcessor
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    private $data;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
		$this->container = $container;
        $this->request = $container->get(Request::class);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->data === null){
            $data = $this->request->getContent();

            $data = json_decode($data, true);

            if ($data === null){
                $this->data = [];
            } else {
                $this->data = $data;
            }
        }

        return $this->data;
    }
}