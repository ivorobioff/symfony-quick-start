<?php
namespace ImmediateSolutions\SupportBundle\Api;

use ImmediateSolutions\Support\Api\AbstractSerializer;
use ImmediateSolutions\Support\Api\Verify\VerifiableInterface;
use ImmediateSolutions\Support\Pagination\AdapterInterface;
use ImmediateSolutions\Support\Pagination\PaginationProviderInterface;
use ImmediateSolutions\Support\Pagination\Paginator;
use ImmediateSolutions\SupportBundle\Pagination\Describer;
use ImmediateSolutions\Support\Permissions\ProtectableInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use InvalidArgumentException;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 *
 * @property Reply $reply
 * @property Request $request
 */
abstract class AbstractController implements ContainerAwareInterface, ProtectableInterface, VerifiableInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @param ContainerInterface|null $container
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
		$this->initialize();
	}

	protected function initialize()
	{
		//
	}


	/**
	 * @param string $class
	 * @return AbstractSerializer
	 */
	public function getSerializer($class)
	{
		return new $class($this->container);
	}

	/**
	 * @param string $class
	 * @return AbstractProcessor
	 */
	public function getProcessor($class)
	{
		/**
		 * @var AbstractProcessor $processor
		 */
		$processor = new $class($this->container);

		$processor->validate();

		return $processor;
	}

    /**
     * @param AdapterInterface $adapter
     * @return object[]|PaginationProviderInterface
     */
    public function getPaginator(AdapterInterface $adapter)
    {
        return new Paginator($adapter, new Describer($this->request));
    }

    /**
     * @throws NotFoundHttpException
     */
    public function show404()
    {
        throw new NotFoundHttpException(VerifiableInterface::NOT_FOUND);
    }

    /**
     * @return bool
     */
    public function shouldVerify()
    {
        return true;
    }

	/**
	 * @return Reply
	 */
    protected function getReply()
	{
		return new Reply($this->container->get(ResponseFactoryInterface::class));
	}

	/**
	 * @return Request
	 */
    protected function getRequest()
	{
		return $this->container->get(Request::class);
	}

	public function __get($property)
	{
		if ($property == 'reply'){
			return $this->getReply();
		}

		if ($property == 'request'){
			return $this->getRequest();
		}

		throw new InvalidArgumentException('The "'.$property.'" has not been found');
	}
}