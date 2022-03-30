<?php

declare(strict_types=1);

use IOL\Generic\v1\BitMasks\RequestMethod;
use IOL\Generic\v1\Request\APIResponse;

$response = APIResponse::getInstance();

$response->setAllowedRequestMethods(
    new RequestMethod(RequestMethod::GET)
);
$response->needsAuth(true);

$response->setData(\IOL\Clerk\v1\Entity\Product::getAllClerkProducts());

