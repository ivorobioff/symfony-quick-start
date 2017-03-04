<?php
namespace ImmediateSolutions\SupportBundle\Api;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class JsonResponseFactory implements ResponseFactoryInterface
{
    /**
     * @param array $content
     * @param int $status
     * @return Response
     */
    public function create($content, $status)
    {
		return new JsonResponse($content, $status);
	}
}