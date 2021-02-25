<?
/**
 * Class ProductService
 */
class ProductService{
    public static function Test(){
        print_r(self::GetProductExIdByIeId(337730));

        return "";
    }

    public static function GetAllProducts()
    {
        $arrProducts = array();
        $arFilter = Array("IBLOCK_ID"=>17);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, Array());
        while($ob = $res->GetNextElement())
        {
            $arrProducts[] = ProductMap::MapFromBitrixElementToProduct($ob);
        }
        return json_encode($arrProducts, JSON_UNESCAPED_UNICODE);
    }

    public static function GetProductExIdByIeId($ieId){
        //$obj = CIBlockElement::GetList(Array(), array("IBLOCK_ID" => 17, "ID" => $ieId), false, false, Array());
        $obj = CIBlockElement::GetByID($ieId);
        if($product = $obj->GetNextElement()){
            $arFields = $product->GetFields();
            return $arFields["XML_ID"];
        }
        return 0;
    }

    public static function GetProductIeIdByExId($exId){
        $result = CIBlockElement::GetList(Array(), array("IBLOCK_ID" => 17, "XML_ID" => $exId), false, false, Array());
        $obj = $result->GetNextElement();
        $arFields = $obj->GetFields();
        return $arFields["ID"];
    }

    public static function GetProductIdWithIeId(){
        $arFilter = Array("IBLOCK_ID"=>17);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, Array());
        $arrResult = array();
        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $ids = new ProductIds();
            $ids->ProductIeId = $arFields["ID"];
            $ids->ProductExId = $arFields["XML_ID"];
            $arrResult[] = $ids;
        }
        return json_encode($arrResult, JSON_UNESCAPED_UNICODE);
    }

    public static function GetCategories(){
        $arrResult = array();
        $groupList = CIBlockSection::GetList(array("SORT"=>"ASC"), array("ID", "IBLOCK_ID" => 17),
            false, array("ID","IBLOCK_ID","IBLOCK_SECTION_ID","NAME"));
        while ($group = $groupList->GetNext()){
            $arrResult[] = array(
                "Id" => $group["ID"],
                "ParentId" => $group["IBLOCK_SECTION_ID"] == null ? 0 : $group["IBLOCK_SECTION_ID"],
                "Name" => $group["NAME"]
            );
        }
        return json_encode($arrResult, JSON_UNESCAPED_UNICODE);
    }

    public static function AddProductsRange()
    {
        set_time_limit(120);
//        https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/add.php - CIBlockElement::Add
//        https://dev.1c-bitrix.ru/api_help/search/classes/csearch/reindexall.php - CSearch::ReIndexAll
        $arNewProducts = json_decode(file_get_contents('php://input'));
        $arrId = array();
        $arrIdImportFalse = array();
        $el = new CIBlockElement();
        foreach ($arNewProducts as $item) {
            $elementForImport = ProductMap::MapFromProductToBitrixElement($item);
            if ($id = $el->Add($elementForImport, false, true, false)) {
                $arrId[] = $id;
            }
            else $arrIdImportFalse[] = $item->ProductExId;
//            else echo $el->LAST_ERROR."\n";
        }
        unset($item);

        $response = array(
            "arrId" => $arrId,
            "arrIdImportFalse" => $arrIdImportFalse,
            "arrIdCount" => count($arrId)
        );
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public static function UpdateProductsRange()
    {
        set_time_limit(120);
        $productsToUpdate = json_decode(file_get_contents('php://input'));
        $successUpdateProducts = array();
        $failureUpdateProducts = array();
        $el = new CIBlockElement();
        foreach ($productsToUpdate as $product) {
            $bitrixElement = ProductMap::MapFromProductToBitrixElement($product);
            $result = $el->Update($product->ProductIeId, $bitrixElement);
            if ($result) $successUpdateProducts[] = $bitrixElement["XML_ID"];
            else $failureUpdateProducts[] = $bitrixElement["XML_ID"];
        }

        $response  = array(
            "success" => $successUpdateProducts,
            "failure" => $failureUpdateProducts
        );

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public static function DeleteProductsRange()
    {
        set_time_limit(120);
        $request = file_get_contents('php://input');
        $deleteData = json_decode($request);
        $file = fopen('deleteLog.txt', 'a+');
        $arrResult = array();

        foreach ($deleteData as $value) {
            if (!CIBlockElement::Delete($value)) {
                $mes = "Error";
            }else {
                $mes = "Success";
            }
            fwrite($file, "DELETE: ".$mes." ID: ".$value." ".date("d.m.Y H:i:s").".\n" );
            $arrResult[] = array(
                "Id" => (int)$value,
                "Status" => $mes
            );
        }
        unset($value);
        fwrite($file, "\n");
        fclose($file);
        return json_encode($arrResult, JSON_UNESCAPED_UNICODE);
    }
}