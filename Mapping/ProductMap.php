<?

class ProductMap
{
    public static function MapFromBitrixElementToProduct($bitrixElement){
        $prod = new ProductAto();

        $arFields = $bitrixElement->GetFields();
        $arProperties = $bitrixElement->GetProperties();

        $prod->ProductIeId = $arFields["ID"];
        $prod->ProductExId = $arFields["XML_ID"];
        $prod->VendorId = CommonService::GetExIdByIeId($arProperties["ATT_BRAND"]["VALUE"]);
        $prod->VendorCode = $arProperties["CML2_ARTICLE"]["VALUE"];
        $prod->Name = $arFields["NAME"];
        // $prod->Description = $arFields["DETAIL_TEXT"];
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
        $prod->VendorCountry = $arProperties["country"]["VALUE"] != null ? $arProperties["country"]["VALUE"] : "0";
        $prod->Offers = $arProperties["OFFERS"]["VALUE"][0] != null ? self::getIdArrayFromOfferProperty($arProperties["OFFERS"]["VALUE"]) : "";
        $prod->ImagesURL->Img1 = $arFields["DETAIL_PICTURE"] != null ? $arFields["DETAIL_PICTURE"] : "";
        $prod->Sale = $arProperties["sale"]["VALUE"] != null ? $arProperties["sale"]["VALUE"] : "0";

        return $prod;
    }

    public static function MapFromProductToBitrixElement(ProductAto $product, bool $makeFileArray){
        $arrImages = (array)$product->ImagesURL;
        $bitrixElement = array(
            "XML_ID" => $product->ProductExId,
            "IBLOCK_ID" =>  "17",//$product->BlockId,
            "CODE" => self::getCode($product->Name),
            "IBLOCK_SECTION_ID" => $product->CategoryId,
            "NAME" => $product->Name,
            "DETAIL_PICTURE" => $makeFileArray ? CFile::MakeFileArray($arrImages["Img1"]) : null,
            "DETAIL_TEXT" => $product->Description,
            "PROPERTY_VALUES" => array(
                "ATT_BRAND" => VendorMap::GetVendorIeIdByExId($product->VendorId),
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
                //"OFFERS" => self::getOfferPropertyId($product->NewAndBestseller)
                "OFFERS" => $product->Offers,
                "sale" => $product->Sale
            )
        );
        if ($makeFileArray){
            $arrMorePhoto = array();
            unset($arrImages["Img1"]);
            foreach ($arrImages as $value) {
                if ($value != null && $value != "") $arrMorePhoto[] = self::getImageId($value);
            }
            if (count($arrMorePhoto) > 0) $bitrixElement["PROPERTY_VALUES"] += ["MORE_PHOTO" => $arrMorePhoto];
        }

        return $bitrixElement;
    }

    public static function GetProductIeIdByExId($exId){
        return CommonService::GetIeIdByProvidedExId(17, $exId);
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

    private static function getIdArrayFromOfferProperty($item){
        $result = array();
        foreach ($item as $i){
            $result[] = self::getOfferPropertyId($i);
        }
        return $result;
    }

    private static function getOfferPropertyId($value){
        switch ($value) {
            case "Новинка" : return 1834;
            case "Распродажа" : return 1835;
            case "Хит продаж": return 1836;
            case "Новинка Хит продаж" : return 1851;
            case "Скидка 10%" : return 1847;
            case "Скидка 20%" : return 1848;
            case "Скидка 30%" : return 1849;
            case "Скидка 40%" : return 1850;
            default : return "";
        }
    }
}