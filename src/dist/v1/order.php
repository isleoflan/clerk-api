<?php

declare(strict_types=1);

use IOL\Dashboard\v1\BitMasks\RequestMethod;
use IOL\Dashboard\v1\Request\APIResponse;

$response = APIResponse::getInstance();

$response->setAllowedRequestMethods(
    new RequestMethod(RequestMethod::POST)
);
$response->needsAuth(true);
$userId = $response->check();
$input = $response->getRequestData([
    [
        'name' => 'badgeId',
        'types' => ['string'],
        'required' => true,
        'errorCode' => 701201,
    ],
    [
        'name' => 'products',
        'types' => ['array'],
        'required' => true,
        'errorCode' => 701202,
    ],
]);

