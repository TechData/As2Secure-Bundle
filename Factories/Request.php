<?php
/**
 * Created by PhpStorm.
 * User: westin
 * Date: 3/15/2015
 * Time: 11:30 AM
 */

namespace TechData\AS2SecureBundle\Factories;

use TechData\AS2SecureBundle\Models\Request as RequestModel;

class Request
{
    function __construct()
    {

    }

    public function build($content, $headers)
    {
        $request = new RequestModel($data, $params);
        $this->buildAbstract($request);
        return $request;

    }
}