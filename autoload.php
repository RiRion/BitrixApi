<?php
// BitrixApi autoload.

// Mapping
include_once __DIR__ . '/Mapping/OfferMap.php';
include_once __DIR__ . '/Mapping/ProductMap.php';
include_once __DIR__ . '/Mapping/VendorMap.php';

// Models
include_once __DIR__ . '/Models/Images.php';
include_once __DIR__ . '/Models/OfferAto.php';
include_once __DIR__ . '/Models/ProductAto.php';
include_once __DIR__ . '/Models/Vendor.php';
include_once __DIR__ . '/Models/VendorIdWithInternalId.php';

// Services
include_once __DIR__ . '/Services/VendorService.php';
include_once __DIR__ . '/Services/ProductService.php';
include_once __DIR__ . '/Services/OfferService.php';