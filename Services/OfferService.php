<?
use \Bitrix\Catalog\Model\Product as ClassProduct;
use \Bitrix\Catalog\Model\Price as ClassPrice;

/**
 * Class OfferService
 */
class OfferService{
    public static function GetAllOffers(){
        set_time_limit(120);
        $arrOffers = array();
        $arrFilter = array("IBLOCK_ID" => 22);
        $res = CIBlockElement::GetList(array(), $arrFilter, false, false, array());
        while($ob = $res->GetNextElement())
        {
            $arrOffers[] = OfferMap::mapToOfferAtoFromBitrixElement($ob);
        }

        return json_encode($arrOffers, JSON_UNESCAPED_UNICODE);
    }

    public static function AddOffersRange(){
        set_time_limit(120);
        $listToUpdate = json_decode(file_get_contents('php://input'));
        $el = new CIBlockElement();
        $arrId = array();
        $offers = self::castToOfferAto($listToUpdate);
        foreach ($offers as $offerAto) {
            $bitrixOffer = OfferMap::mapToBitrixElementFromOfferAto($offerAto);
            if ($offerId = $el->Add($bitrixOffer, false, true, false)){
                $catalogProduct = OfferMap::mapToCatalogProduct($offerAto, $offerId);
                $catalogPrice = OfferMap::mapToCatalogPrice($offerAto, $offerId);

//                $catalogProductId = CCatalogProduct::Add($catalogProduct); // Рабочий, но устаревший метод (по документации).
                ClassProduct::add($catalogProduct); // Works faster.
//                $catalogPriceId = CPrice::Add($catalogPrice); // Рабочий, но устаревший метод (по документации).
                ClassPrice::add($catalogPrice); // Works faster.
                // Новые методы ничего не возвращают - проверить их результат не представляется возможным.
            }

            $arrId[] = array(
                "ProductId" => $offerAto->ProductId,
                "XmlId" => $offerAto->XmlId,
                "OfferId" => $offerId == false ? 0 : $offerId
            );
        }
        return json_encode($arrId, JSON_UNESCAPED_UNICODE);
    }

    public static function UpdateOffers(){
        set_time_limit(120);
        $offersToUpdate = json_decode(file_get_contents('php://input'));
        $el = new CIBlockElement();
        $offers = self::castToOfferAto($offersToUpdate);
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

    private static function castToOfferAto($arr){
        $arrOffers = array();
        foreach ($arr as $obj){
            $offer = new OfferAto();
            foreach ($obj as $key => $value)
            {
                $offer->$key = $value;
            }
            $arrOffers[] = $offer;
        }
        return $arrOffers;
    }
}