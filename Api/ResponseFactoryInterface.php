<?php
namespace ImmediateSolutions\SupportBundle\Api;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
interface ResponseFactoryInterface
{
    /**
     * @param mixed $content
     * @param int $status
     * @return Response
     */
    public function create($content, $status);
}