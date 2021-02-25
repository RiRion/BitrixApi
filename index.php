<?php
//Errors output. Debug.
error_reporting(-1);
ini_set('display_errors', 1);
//end errors output.//

use Slim\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Services\VendorService;

require __DIR__ .'/vendor/autoload.php';
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require __DIR__ .'/autoload.php';

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

$app->get('/GetVendorsId', function (Request $request, Response $response, $args) {
    $response->getBody()->write(VendorService::GetVendorsId());
    return $response;
});

$app->get('/GetVendors', function (Request $request, Response $response, $args) {
    $response->getBody()->write(VendorService::GetVendors());
    return $response;
});

$app->post("/AddVendors", function (Request $request, Response $response, $args){
    GetPostWithoutResponse($request, $response, VendorService::AddVendorsRange());
    return $response;
});

$app->post("/DeleteVendors", function (Request $request, Response $response, $args){
    GetPostWithoutResponse($request, $response, VendorService::DeleteVendors());
    return $response;
});

// PRODUCTS ////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Returns a list of products in Json format.
$app->get('/GetAllProducts', function (Request $request, Response $response, $args) {
    $response->getBody()->write(ProductService::GetAllProducts());
    return $response;
});

//Returns a list of internal id by product id in Json format.
$app->get("/ProductIdWithIeId", function (Request $request, Response $response, $args){
   $response->getBody()->write(ProductService::GetProductIdWithIeId());
   return $response;
});

//Returns categories with their Id.
$app->get("/GetCategories", function (Request $request, Response $response, $args){
    $response->getBody()->write(ProductService::GetCategories());
    return $response;
});

//Adds products to the database.
$app->post("/AddProductsRange", function (Request $request, Response $response, $args){
    GetPostWithoutResponse($request, $response, ProductService::AddProductsRange());
    return $response;
});

$app->post("/UpdateProductsRange", function (Request $request, Response $response, $args){
    GetPostWithoutResponse($request, $response, ProductService::UpdateProductsRange());
    return $response;
});

$app->post("/DeleteProductsRange", function (Request $request, Response $response, $args){
   GetPostWithoutResponse($request, $response, ProductService::DeleteProductsRange());
   return $response;
});

// OFFERS //////////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->get("/GetAllOffers", function (Request $request, Response $response, $args){
    $response->getBody()->write(OfferService::GetAllOffers());
    return $response;
});

$app->post("/AddOffersRange", function (Request $request, Response $response, $args){
    GetPostWithoutResponse($request, $response, OfferService::AddOffersRange());
    return $response;
});

$app->post("/UpdateOffers", function (Request $request, Response $response, $args){
    GetPostWithoutResponse($request, $response, OfferService::UpdateOffers());
    return $response;
});

$app->post("/DeleteOffers", function (Request $request, Response $response, $args){
    GetPostWithoutResponse($request, $response, OfferService::DeleteOffers());
    return $response;
});

// Tests ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->get("/Test", function (Request $request, Response $response, $args){
    $response->getBody()->write(ProductService::Test());
    return $response;
});

// Functions ///////////////////////////////////////////////////////////////////////////////////////////////////////////

function PostAndPutObject(Request $request, Response $response, $func){
    $contentType = $request->getHeaderLine("Content-Type");
    if (strstr($contentType, "application/json")){
        $response->getBody()->write($func);
    }
    else{
        $response->withStatus(400);
    }
}

function GetPostWithoutResponse(Request $request, Response $response, $func){
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