<?php

declare(strict_types=1);

use IOL\Generic\v1\BitMasks\RequestMethod;
use IOL\Generic\v1\Request\APIResponse;

$response = APIResponse::getInstance();

$response->setAllowedRequestMethods(
    new RequestMethod(RequestMethod::GET)
);
$response->needsAuth(true);
$userId = $response->check();
$input = $response->getRequestData([
    [
        'name' => 'badgeId',
        'types' => ['string'],
        'required' => true,
        'errorCode' => 701101,
    ],
]);
$input['badgeId'] = str_replace([' ', '-', ':'], '', $input['badgeId']);
if (!ctype_xdigit($input['badgeId'])) {
    $response->addError(701201)->render();
}

$order = new \IOL\Clerk\v1\Entity\ClerkOrder();
$response->addData('balance', $order->getCardBalance($order->getUserFromBadge($input['badgeId'])));

