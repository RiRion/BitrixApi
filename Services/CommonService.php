<?php
class CommonService{
    public static function GetIeIdByProvidedExId($blockId, $exId){
        $result = CIBlockElement::GetList(Array(), array("IBLOCK_ID" => $blockId, "XML_ID" => $exId), false, false, Array());
        if ($obj = $result->GetNextElement()){
            $arFields = $obj->GetFields();
            return $arFields["ID"];
        }
        else return -1;
    }

    public static function GetExIdByIeId($ieId){
        $obj = CIBlockElement::GetByID($ieId);
        if($product = $obj->GetNextElement()){
            $arFields = $product->GetFields();
            return $arFields["XML_ID"];
        }
        return 0;
    }
}
