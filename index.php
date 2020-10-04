<?php
//Errors output. Debug.
//error_reporting(-1);
//ini_set('display_errors', 1);
//end errors output.//

use Slim\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ .'/vendor/autoload.php';
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

include_once __DIR__ . '/Services/VendorService.php';
include_once __DIR__ . '/Services/ProductService.php';
include_once __DIR__ . '/Services/OfferService.php';

//Authentication.
global $USER;

if (!$USER->IsAdmin()) return http_response_code(403);
//End authentication.

//Initialise App.
$app = AppFactory::create();
$app->setBasePath('/my_tools');
//End initialise.

// Login.
$app->get("/Login", function (Request $request, Response $response, $args){
    $response->withStatus(200);
    return $response;
});

// VENDORS /////////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->get('/GetVendors', function (Request $request, Response $response, $args) {
    $response->getBody()->write(VendorService::getVendorsId());
    return $response;
});

// PRODUCTS ////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Returns a list of products in Json format.
$app->get('/GetAllProducts', function (Request $request, Response $response, $args) {
    $response->getBody()->write(ProductService::getAllProducts());
    return $response;
});

//Returns a list of internal id by product id in Json format.
$app->get("/ProductIdWithIeId", function (Request $request, Response $response, $args){
   $response->getBody()->write(ProductService::getProductIdWithIeId());
   return $response;
});

//Returns categories with their Id.
$app->get("/GetCategories", function (Request $request, Response $response, $args){
    $response->getBody()->write(ProductService::getCategories());
    return $response;
});

//Adds products to the database.
$app->post("/AddProductsRange", function (Request $request, Response $response, $args){
    getPostWithoutResponse($request, $response, ProductService::addProductsRange());
    return $response;
});

$app->post("/UpdateProductsRange", function (Request $request, Response $response, $args){
    getPostWithoutResponse($request, $response, ProductService::updateProductsRange());
    return $response;
});

$app->post("/DeleteProductsRange", function (Request $request, Response $response, $args){
   getPostWithoutResponse($request, $response, ProductService::deleteProductsRange());
   return $response;
});

// OFFERS //////////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->get("/GetAllOffers", function (Request $request, Response $response, $args){
    $response->getBody()->write(OfferService::getAllOffers());
    return $response;
});

$app->post("/AddOffersRange", function (Request $request, Response $response, $args){
    getPostWithoutResponse($request, $response, OfferService::addOffersRange());
    return $response;
});

$app->post("/UpdateOffers", function (Request $request, Response $response, $args){
    getPostWithoutResponse($request, $response, OfferService::updateOffers());
    return $response;
});

$app->post("/DeleteOffers", function (Request $request, Response $response, $args){
    getPostWithoutResponse($request, $response, OfferService::deleteOffers());
    return $response;
});

// Functions ///////////////////////////////////////////////////////////////////////////////////////////////////////////

function postAndPutObject(Request $request, Response $response, $func){
    $contentType = $request->getHeaderLine("Content-Type");
    if (strstr($contentType, "application/json")){
        $response->getBody()->write($func);
    }
    else{
        $response->withStatus(400);
    }
}

function getPostWithoutResponse(Request $request, Response $response, $func){
    $contentType = $request->getHeaderLine("Content-Type");
    if (!strstr($contentType, "application/json")){
        $func;
    }
    else{
        $response->withStatus(400);
    }
}

// Run /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$app->run();