<?php

declare(strict_types=1);

use IOL\Generic\v1\BitMasks\RequestMethod;
use IOL\Generic\v1\Request\APIResponse;

$response = APIResponse::getInstance();

$response->setAllowedRequestMethods(
    new RequestMethod(RequestMethod::GET)
);
$response->needsAuth(true);

$response->addData('products', \IOL\Clerk\v1\Entity\Product::getAllClerkProducts());
$response->addData('topUp', '4c0cf233-4987-48fa-8282-65098bff4c3d');

