<?
/**
 * Class ProductService
 */

//        https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/add.php - CIBlockElement::Add
//        https://dev.1c-bitrix.ru/api_help/search/classes/csearch/reindexall.php - CSearch::ReIndexAll
class ProductService{
    public static function Test(){
        print_r(ProductMap::GetProductIeIdByExId(862));
//        return $id;
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

    public static function AddProduct($obj){
        $productAto = self::CastToProductAto($obj);
        $el = new CIBlockElement();
        $productBitrix = ProductMap::MapFromProductToBitrixElement($productAto, true);
        $response = new ApiResponse();
        $response->ObjectType = "Product";
        $response->Method = "Add";
        $response->ExId = $productAto->ProductExId;
        if ($productId = $el->Add($productBitrix, false, true, false)){
            $response->Status = $productId;
        }
        else {
            $response->Status = -1;
            $response->ErrorMessage = $el->LAST_ERROR;
        }
        return $response;
    }

    public static function AddProductsRange($arrObj)
    {
        $result = array();
        foreach ($arrObj as $product){
            $result[] = self::AddProduct($product);
        }
        return $result;
    }

    public static function UpdateProduct($obj){
        $productAto = self::CastToProductAto($obj);
        $productAto->ProductIeId = ProductMap::GetProductIeIdByExId($productAto->ProductExId);
        $el = new CIBlockElement();
        $response = new ApiResponse();
        $response->ObjectType = "Offer";
        $response->Method = "Update";
        $response->ExId = $productAto->ProductExId;
        $productBitrix = ProductMap::MapFromProductToBitrixElement($productAto, false);
        if ($el->Update($productAto->ProductIeId, $productBitrix)){
            $response->Status = 1;
        }
        else {
            $response->Status = -1;
            $response->ErrorMessage = $el->LAST_ERROR;
        }
        return $response;
    }

    public static function UpdateProductsRange($arrObj)
    {
        $result = array();
        foreach ($arrObj as $obj){
            $result[] = self::UpdateProduct($obj);
        }
        return $result;
    }

    public static function DeleteProduct($exId){
        $response = new ApiResponse();
        $response->ObjectType = "Product";
        $response->Method = "Delete";
        $response->ExId = $exId;
        $ieId = ProductMap::GetProductIeIdByExId($exId);
        if(CIBlockElement::Delete($ieId)) $response->Status = 1;
        else $response->Status = -1;
        return $response;
    }

    public static function DeleteProductsRange($arrObj)
    {
        $result = array();
        foreach ($arrObj as $exId){
            $result[] = self::DeleteProduct($exId);
        }
        return $result;
    }

    private static function CastToProductAto($obj){
        $product = new ProductAto();
        foreach ($obj as $key => $value) {
            $product->$key =  $value;
        }
        return $product;
    }
}