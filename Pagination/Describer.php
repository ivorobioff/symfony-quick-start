<?php
namespace ImmediateSolutions\SupportBundle\Pagination;

use ImmediateSolutions\Support\Pagination\DescriberInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class Describer implements DescriberInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        $query = parse_url_query($this->request->query->all());

        $page = array_get($query, 'page', 1);

        return (is_numeric($page) && $page > 0) ? (int) $page : 1;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        $query = parse_url_query($this->request->query->all());

        $perPage = array_get($query, 'perPage', 10);

        return (is_numeric($perPage) && $perPage > 0) ? (int) $perPage : 10;
    }
}