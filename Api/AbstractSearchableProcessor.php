<?php
namespace ImmediateSolutions\SupportBundle\Api;

use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
abstract class AbstractSearchableProcessor extends \ImmediateSolutions\Support\Api\Searchable\AbstractSearchableProcessor
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request = $container->make(Request::class);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->data === null){
            $this->data = parse_url_query($this->request->query->all());
        }

        return $this->data;
    }
}