<?php
namespace ImmediateSolutions\SupportBundle\Pagination;
use ImmediateSolutions\Support\Core\Options\PaginationOptions;
use ImmediateSolutions\Support\Pagination\AdapterInterface;

/**
 * @author Igor Vorobiov <igor.vorobioff@gmail.com>
 */
class DefaultPaginatorAdapter implements AdapterInterface
{
	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @param array|object $configOrProvider
	 */
	public function __construct($configOrProvider)
	{
		if (is_array($configOrProvider)){
			$this->callback['getAll'] = $configOrProvider['getAll'];
			$this->callback['getTotal'] = $configOrProvider['getTotal'];
		} else {
			$this->callback['getAll'] = function($page, $perPage) use ($configOrProvider){
				return $configOrProvider->getAll(new PaginationOptions($page, $perPage));
			};

			$this->callback['getTotal'] = function() use ($configOrProvider){
				return $configOrProvider->getTotal();
			};
		}
	}

	/**
	 * Gets all items according to the provided parameters
	 *
	 * @param int $page
	 * @param int $perPage
	 * @return object[]
	 */
	public function getAll($page, $perPage)
	{
		return call_user_func($this->callback['getAll'], $page, $perPage);
	}

	/**
	 * Counts the total number of items
	 *
	 * @return int
	 */
	public function getTotal()
	{
		return call_user_func($this->callback['getTotal']);
	}
}