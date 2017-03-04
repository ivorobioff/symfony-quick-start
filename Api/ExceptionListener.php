<?php
namespace ImmediateSolutions\SupportBundle\Api;
use ImmediateSolutions\Support\Validation\Error;
use ImmediateSolutions\Support\Validation\ErrorsThrowableCollection;
use ImmediateSolutions\Support\Validation\PresentableException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class ExceptionListener
{
	/**
	 * @var ResponseFactoryInterface
	 */
	private $responseFactory;

	/**
	 * @var bool
	 */
	private $isDebug = false;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->responseFactory = $container->get(ResponseFactoryInterface::class);
		$this->isDebug = $container->getParameter('kernel.environment') == 'dev';
	}

	/**
	 * @param GetResponseForExceptionEvent $event
	 */
	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		$exception = $event->getException();

		if ($response = $this->write($exception)){
			$event->setResponse($response);
		}
	}

	/**
	 * @param Exception $exception
	 * @return Response
	 */
	private function write(Exception $exception)
	{
		if ($exception instanceof HttpException){
			return $this->writeHttpException($exception);
		}

		if ($exception instanceof ErrorsThrowableCollection){

			$data = [];

			/**
			 * @var Error[] $e
			 */
			foreach ($exception as $property => $error){
				$data[$property] = $this->prepareError($error);
			}

			return $this->responseFactory->create(['errors' => $data], Response::HTTP_UNPROCESSABLE_ENTITY);
		}

		if ($exception instanceof PresentableException){
			return $this->writeException(Response::HTTP_BAD_REQUEST, $exception->getMessage());
		}

		if ($this->isDebug){
			return null;
		}

		return $this->writeException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal Server Error');
	}

	/**
	 * @param HttpException $exception
	 * @return Response
	 */
	private function writeHttpException(HttpException $exception)
	{
		return $this->writeException($exception->getStatusCode(), $this->getHttpExceptionMessage($exception));
	}

	/**
	 * @param HttpException $exception
	 * @return string
	 */
	private function getHttpExceptionMessage(HttpException $exception)
	{
		$message = $exception->getMessage();

		if ($message){
			return $message;
		}

		if ($exception instanceof NotFoundHttpException){
			return VerifiableInterface::NOT_FOUND;
		}

		return 'Request Failed';
	}

	/**
	 * @param int $code
	 * @param string $message
	 * @return Response
	 */
	private function writeException($code, $message)
	{
		return $this->responseFactory->create([
			'code' => $code,
			'message' => $message
		], $code);
	}


	/**
	 * @param Error $error
	 * @return array
	 */
	private function prepareError(Error $error)
	{
		$data = [
			'identifier' => $error->getIdentifier(),
			'message' => $error->getMessage(),
			'extra' => []
		];

		if ($error->hasExtra()){
			foreach ($error->getExtra() as $name => $extra){
				$data['extra'][$name] = $this->prepareError($extra);
			}
		}

		return $data;
	}
}