<?
use \Bitrix\Catalog\Model\Product as ClassProduct;
use \Bitrix\Catalog\Model\Price as ClassPrice;

/**
 * Class OfferService
 */
class OfferService{
    public static function GetAllOffers(){
        set_time_limit(240);
        $arrOffers = array();
        $arrFilter = array("IBLOCK_ID" => 22);
        $res = CIBlockElement::GetList(array(), $arrFilter, false, false, array());
        while($ob = $res->GetNextElement())
        {
            $arrOffers[] = OfferMap::MapToOfferAtoFromBitrixElement($ob);
        }

        return json_encode($arrOffers, JSON_UNESCAPED_UNICODE);
    }

    public static function AddOffer($obj){
        $response = new ApiResponse();
        $el = new CIBlockElement();
        $offer = self::CastToOfferAto($obj);
        $response->ObjectType = "Offer";
        $response->Method = "Add";
        $response->ExId = $offer->XmlId;
        $bitrixEl = OfferMap::MapToBitrixElementFromOfferAto($offer);
        if ($offerId = $el->Add($bitrixEl, false, true, false)){
            $catalogProduct = OfferMap::MapToCatalogProduct($offer, $offerId);
            $catalogPrice = OfferMap::MapToCatalogPrice($offer, $offerId);
            // $catalogProductId = CCatalogProduct::Add($catalogProduct); // Рабочий, но устаревший метод (по документации).
            ClassProduct::add($catalogProduct); // Works faster.
            // $catalogPriceId = CPrice::Add($catalogPrice); // Рабочий, но устаревший метод (по документации).
            ClassPrice::add($catalogPrice); // Works faster.
            // Новые методы ничего не возвращают - проверить их результат не представляется возможным.
            $response->Status = $offerId;
        }
        else {
            $response->Status = -1;
            $response->ErrorMessage = $el->LAST_ERROR;
        }
        return $response;
    }

    public static function AddOffersRange($arrObj){
        $response = array();
        foreach ($arrObj as $offer) {
            $response[] = self::AddOffer($offer);
        }
        return $response;
    }

    public static function UpdateOffer($obj){
        $response = new ApiResponse();
        $response->ObjectType = "Offer";
        $response->Method = "Update";
        $el = new CIBlockElement();
        $offer = self::CastToOfferAto($obj);
        $response->ExId = $offer->XmlId;
        $offerId = OfferMap::GetOfferIeIdByExId($offer->XmlId);
        $bitrixElement = OfferMap::MapToBitrixElementFromOfferAto($offer);
        $catalogProduct = OfferMap::MapToCatalogProduct($offer, $offerId);
        $catalogPrice = OfferMap::MapToCatalogPrice($offer, $offerId);
        if($result = $el->Update($offerId, $bitrixElement)){
            if (!CCatalogProduct::GetByID($offerId)){
                ClassProduct::add($catalogProduct);
            }
            else ClassProduct::update($offerId, $catalogProduct);
            ClassPrice::update(self::GetCPriceIdByOfferId($offerId), $catalogPrice);

            $response->Status = 1;
        }
        else {
            $response->Status = -1;
            $response->ErrorMessage = $el->LAST_ERROR;
        }
        return $response;
    }

    public static function UpdateOfferRange($arrObj){
        $response = array();
        foreach ($arrObj as $obj) {
            $response[] = self::UpdateOffer($obj);
        }
        return $response;
    }

    public static function  DeleteOffer($exId){
        $response = new ApiResponse();
        $response->ObjectType = "Offer";
        $response->Method = "Delete";
        $response->ExId = $exId;

        $id = OfferMap::GetOfferIeIdByExId($exId);
        $price = self::GetCPriceIdByOfferId($id);
        CPrice::Delete($price);
        CCatalogProduct::Delete($id);
        if(CIBlockElement::Delete($id)) $response->Status = 1;
        else $response->Status = -1;
        return $response;
    }

    public static function DeleteOfferRange($exIdList){
        $response = array();
        foreach ($exIdList as $id){
            $response[] = self::DeleteOffer($id);
        }
        return $response;
    }

    private static function GetCPriceIdByOfferId($offerId){
        $arrFilter = array("ID");
        $priceResultObj =  CPrice::GetListEx(array(), array("PRODUCT_ID" => $offerId), false, false, $arrFilter);
        $arr = $priceResultObj->Fetch();
        return $arr["ID"];
    }

    private static function CastToOfferAto($obj){
        $offer = new OfferAto();
        foreach ($obj as $key => $value) {
            $offer->$key =  $value;
        }
        return $offer;
    }

    private static function CastToListOfferAto($arr){
        $arrOffers = array();
        foreach ($arr as $obj){
            $offer = self::CastToOfferAto($obj);
            $arrOffers[] = $offer;
        }
        return $arrOffers;
    }
}