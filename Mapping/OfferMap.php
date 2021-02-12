<?
    include_once "my_tools/Models/Offer.php";

    class OfferMap{
        public static function mapToOfferAtoFromBitrixElement($bitrixElement){
            $offer = new OfferAto();

            $arrFields = $bitrixElement->GetFields();
            $arrProperties = $bitrixElement->GetProperties();
            $arrPrice = self::getCPriceByProductId($arrFields["ID"]);
            $arrProduct = CCatalogProduct::GetByID($arrFields["ID"]);

            $offer->Id = $arrFields["ID"];
            $offer->Name = $arrFields["NAME"];
            $offer->XmlId = $arrFields["EXTERNAL_ID"];

            $offer->Barcode = $arrProperties["barcode"]["VALUE"];
            $offer->ProductIeId = $arrProperties["CML2_LINK"]["VALUE"] != null ? $arrProperties["CML2_LINK"]["VALUE"] : "0";
            $offer->ShippingDate = $arrProperties["shippingdate"]["VALUE"];
            $offer->Color = $arrProperties["COLOR"]["VALUE"];
            $offer->Size = $arrProperties["SIZE"]["VALUE"];
            $offer->P5SStock = $arrProperties["p5sstock"]["VALUE"] != null ? $arrProperties["p5sstock"]["VALUE"] : "0";
            $offer->SuperSale = $arrProperties["SuperSale"]["VALUE"];
            $offer->StopPromo = $arrProperties["StopPromo"]["VALUE"];

            $offer->Currency = $arrPrice["CURRENCY"];
            $offer->Price = $arrPrice["PRICE"] != null ? $arrPrice["PRICE"] : "0";

            $offer->Quantity = $arrProduct["QUANTITY"] != null ? $arrProduct["QUANTITY"] : "0";
            $offer->Weight = $arrProduct["WEIGHT"];
            $offer->BaseWholePrice = $arrProduct["PURCHASING_PRICE"] != null ? $arrProduct["PURCHASING_PRICE"] : "0";

            return $offer;
        }

        public static function mapToBitrixElementFromOfferAto(OfferAto $offerAto){
            $bitrixElement = array(
                "IBLOCK_ID" => 22,
                "XML_ID" => $offerAto->XmlId,
                "EXTERNAL_ID" => $offerAto->XmlId,
                "NAME" => $offerAto->Name,
                "CODE" => CUtil::translit($offerAto->Name, "ru", array("replace_space"=>"-","replace_other"=>"-")),
                "PROPERTY_VALUES" => array(
                    "barcode" => $offerAto->Barcode,
                    "CML2_LINK" => $offerAto->ProductIeId,
                    "shippingdate" => $offerAto->ShippingDate,
                    "COLOR" => $offerAto->Color,
                    "SIZE" => $offerAto->Size,
                    "p5sstock" => $offerAto->P5SStock,
                    "SuperSale" => $offerAto->SuperSale,
                    "StopPromo" => $offerAto->StopPromo
                ),
            );

            return $bitrixElement;
        }

        public static function mapToCatalogProduct(OfferAto $offerAto, $offerId){
            return array(
                "ID" => $offerId,
                "QUANTITY" => $offerAto->Quantity,
                "WEIGHT" => $offerAto->Weight, // Not null!
                "PURCHASING_PRICE" => $offerAto->BaseWholePrice,
                "PURCHASING_CURRENCY" => $offerAto->Currency
            );
        }

        public static function mapToCatalogPrice(OfferAto $offerAto, $offerId){
            return array(
                "CURRENCY" => $offerAto->Currency,
                "PRICE" => $offerAto->Price,
                "CATALOG_GROUP_ID" => 1,
                "PRODUCT_ID" => $offerId,
            );
        }

        private static function getCPriceByProductId($productId){
            $arrFilter = array("ID", "CURRENCY", "PRICE");
            $priceResultObj =  CPrice::GetListEx(array(), array("PRODUCT_ID" => $productId), false, false, $arrFilter);
            return $priceResultObj->Fetch();
        }
    }
?>