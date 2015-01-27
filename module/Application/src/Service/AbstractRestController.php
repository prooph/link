<?php
/*
 * This file is part of Ginger Workflow Framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 12/14/14 - 9:37 PM
 */
namespace Application\Service;

use Zend\Mvc\Controller\AbstractRestfulController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Class AbstractRestController
 *
 * @package Application\Service
 * @author Alexander Miertsch <alexander.miertsch.extern@sixt.com>
 */
class AbstractRestController extends AbstractRestfulController
{
    /**
     * Create a new resource
     *
     * @param  mixed $data
     * @return mixed
     */
    public function create($data)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Delete an existing resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function delete($id)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Delete the entire resource collection
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @return mixed
     */
    public function deleteList()
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Return single resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function get($id)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Return list of resources
     *
     * @return mixed
     */
    public function getList()
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Retrieve HEAD metadata for the resource
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  null|mixed $id
     * @return mixed
     */
    public function head($id = null)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Respond to the OPTIONS method
     *
     * Typically, set the Allow header with allowed HTTP methods, and
     * return the response.
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @return mixed
     */
    public function options()
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Respond to the PATCH method
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  $id
     * @param  $data
     */
    public function patch($id, $data)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Replace an entire resource collection
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  mixed $data
     * @return mixed
     */
    public function replaceList($data)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Modify a resource collection without completely replacing it
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.2.0); instead, raises an exception if not implemented.
     *
     * @param  mixed $data
     * @return mixed
     */
    public function patchList($data)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Update an existing resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return mixed
     */
    public function update($id, $data)
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * Basic functionality for when a page is not available
     *
     * @return array
     */
    public function notFoundAction()
    {
        return new ApiProblemResponse(new ApiProblem(405, 'Method not allowed'));
    }

    /**
     * @param $status
     * @param $detail
     * @param null|string $type
     * @param null|string $title
     * @param array $additional
     * @return ApiProblemResponse
     */
    protected function getApiProblemResponse($status, $detail, $type = null, $title = null, array $additional = array())
    {
        return new ApiProblemResponse(new ApiProblem($status, $detail, $type, $title, $additional));
    }
} 