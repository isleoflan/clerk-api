<?php

declare(strict_types=1);

use IOL\Generic\v1\BitMasks\RequestMethod;
use IOL\Generic\v1\Request\APIResponse;

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

if (count($input['products']) <= 0) {
    $response->addError(701202)->render();
}
$input['badgeId'] = str_replace([' ', '-', ':'], '', $input['badgeId']);
if (!ctype_xdigit($input['badgeId'])) {
    $response->addError(701201)->render();
}

$order = new \IOL\Clerk\v1\Entity\ClerkOrder();
$balance = $order->createNew(
    $order->getUserFromBadge($input['badgeId']),
    $input['products'],
    $userId
);

$response->addData('balance', $balance);
$response->setResponseCode(201);

