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
            $arrOffers[] = OfferMap::mapToOfferAtoFromBitrixElement($ob);
        }

        return json_encode($arrOffers, JSON_UNESCAPED_UNICODE);
    }

    public static function AddOffer($obj){
        $response = new AddResponse();
        $el = new CIBlockElement();
        $offer = self::castToOfferAto($obj);
        $response->ObjectType = "Offer";
        $response->ExId = $offer->XmlId;
        $bitrixEl = OfferMap::mapToBitrixElementFromOfferAto($offer);
        if ($offerId = $el->Add($bitrixEl, false, true, false)){
            $catalogProduct = OfferMap::mapToCatalogProduct($offer, $offerId);
            $catalogPrice = OfferMap::mapToCatalogPrice($offer, $offerId);
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

    public static function AddOffersRange($objs){
        $response = array();
        foreach ($objs as $offer) {
            $response[] = self::AddOffer($offer);
        }
        return $response;
    }

    public static function UpdateOffers(){
        set_time_limit(120);
        $offersToUpdate = json_decode(file_get_contents('php://input'));
        $el = new CIBlockElement();
        $offers = self::castToListOfferAto($offersToUpdate);
        foreach ($offers as $offerAto) {
            $offerId = $offerAto->Id;
            $bitrixElement = OfferMap::mapToBitrixElementFromOfferAto($offerAto);
            $catalogProduct = OfferMap::mapToCatalogProduct($offerAto, $offerId);
            $catalogPrice = OfferMap::mapToCatalogPrice($offerAto, $offerId);
            $result = $el->Update($offerId, $bitrixElement);
            if (!CCatalogProduct::GetByID($offerId)){
                $catalogProduct = OfferMap::mapToCatalogProduct($offerAto, $offerId);
                ClassProduct::add($catalogProduct);
            }
            else ClassProduct::update($offerId, $catalogProduct);
            ClassPrice::update(self::getCPriceIdByOfferId($offerId), $catalogPrice);
//            $resProdUp = CCatalogProduct::update($offerId, $catalogProduct);
//            $resPriceUp = CPrice::update(self::getCPriceIdByOfferId($offerId), $catalogPrice);
        }
    }

    public static function DeleteOffers(){
        set_time_limit(120);
        $request = file_get_contents('php://input');
        $deleteData = json_decode($request);
        foreach ($deleteData as $id){
            $price = self::getCPriceIdByOfferId($id);
            CPrice::Delete($price);
            CCatalogProduct::Delete($id);
            CIBlockElement::Delete($id);
        }
    }

    private static function getCPriceIdByOfferId($offerId){
        $arrFilter = array("ID");
        $priceResultObj =  CPrice::GetListEx(array(), array("PRODUCT_ID" => $offerId), false, false, $arrFilter);
        $arr = $priceResultObj->Fetch();
        return $arr["ID"];
    }

    private static function castToOfferAto($obj){
        $offer = new OfferAto();
        foreach ($obj as $key => $value) {
            $offer->$key =  $value;
        }
        return $offer;
    }

    private static function castToListOfferAto($arr){
        $arrOffers = array();
        foreach ($arr as $obj){
            $offer = self::castToOfferAto($obj);
            $arrOffers[] = $offer;
        }
        return $arrOffers;
    }
}