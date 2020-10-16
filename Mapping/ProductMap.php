<?

include_once "my_tools/Models/Product.php";

class ProductMap
{
    public static function MapFromBitrixElementToProduct($bitrixElement){
        $prod = new ProductAto();

        $arFields = $bitrixElement->GetFields();
        $arProperties = $bitrixElement->GetProperties();

        $prod->ProductId = $arFields["XML_ID"];
        $prod->VendorId = $arProperties["ATT_BRAND"]["VALUE"];
        $prod->VendorCode = $arProperties["CML2_ARTICLE"]["VALUE"];
        $prod->Name = $arFields["NAME"];
        $prod->Description = $arFields["DETAIL_TEXT"];
        $prod->Batteries = $arProperties["batteries"]["VALUE"];
        $prod->Pack = $arProperties["pack"]["VALUE"];
        $prod->Material = $arProperties["material"]["VALUE"];
        $prod->Length = $arProperties["lenght"]["VALUE"];
        $prod->Diameter = $arProperties["diameter"]["VALUE"];
        $prod->CategoryId = $arFields["IBLOCK_SECTION_ID"];
        $prod->Function = $arProperties["function"]["VALUE"];
        $prod->AddFunction = $arProperties["addfunction"]["VALUE"];
        $prod->Vibration = $arProperties["vibration"]["VALUE"];
        $prod->Volume = $arProperties["volume"]["VALUE"];
        $prod->ModelYear = $arProperties["modelyear"]["VALUE"];
        $prod->InfoPrice = $arProperties["MORE_PROPERTIES"]["VALUE"][0];
        $prod->IeId = $arFields["ID"];
        $prod->VendorCountry = $arProperties["country"]["VALUE"] != null ? $arProperties["country"]["VALUE"] : "0";
        $prod->NewAndBestseller = $arProperties["OFFERS"]["VALUE"][0] != null ? $arProperties["OFFERS"]["VALUE"][0] : "";

        $prod->ImagesURL->Img1 = $arFields["DETAIL_PICTURE"] != null ? $arFields["DETAIL_PICTURE"] : "";

        return $prod;
    }

    public static function MapFromProductToBitrixElement($product){
        $arrImages = (array)$product->ImagesURL;
        $bitrixElement = array(
            "XML_ID" => $product->ProductId,
            "IBLOCK_ID" =>  "17",//$product->BlockId,
            "CODE" => self::getCode($product->Name),
            "IBLOCK_SECTION_ID" => $product->CategoryId,
            "NAME" => $product->Name,
            "DETAIL_PICTURE" => CFile::MakeFileArray($arrImages["Img1"]),
            "DETAIL_TEXT" => $product->Description,
            "PROPERTY_VALUES" => array(
                "ATT_BRAND" => $product->VendorId,
                "CML2_ARTICLE" => $product->VendorCode,
                "batteries" => $product->Batteries,
                "pack" => $product->Pack,
                "material" => $product->Material,
                "lenght" => $product->Length,
                "diameter" => $product->Diameter,
                "function" => $product->Function,
                "addfunction" => $product->AddFunction,
                "vibration" => $product->Vibration,
                "volume" => $product->Volume,
                "modelyear" => $product->ModelYear,
                "MORE_PROPERTIES" => $product->InfoPrice,
                "country" => $product->VendorCountry,
                "OFFERS" => self::getOfferPropertyId($product->NewAndBestseller)
            )
        );
        $arrMorePhoto = array();
        unset($arrImages["Img1"]);
        foreach ($arrImages as $value) {
            if ($value != null && $value != "") $arrMorePhoto[] = self::getImageId($value);
        }
        if (count($arrMorePhoto) > 0) $bitrixElement["PROPERTY_VALUES"] += ["MORE_PHOTO" => $arrMorePhoto];

        return $bitrixElement;
    }

    private static function getCode($str){
        return CUtil::translit($str, "ru");
    }

    private static function getImageId($url){
//        https://dev.1c-bitrix.ru/api_help/main/reference/cfile/makefilearray.php
//        https://dev.1c-bitrix.ru/api_help/main/reference/cfile/savefile.php
        $arrImageProperty = CFile::MakeFileArray($url);
        $imgId = CFile::SaveFile($arrImageProperty, "Image");
        return $imgId;
    }

    private static function getOfferPropertyId($value){
        switch ($value) {
            case "Хит продаж": return 1836;
            case "Новинка" : return 1834;
            case "Новинка Хит продаж" : return 1851;
            default : return "";
        }
    }
}