<?php
class CommonService{
    public static function GetIeIdByProvidedExId($blockId, $exId){
        $result = CIBlockElement::GetList(Array(), array("IBLOCK_ID" => $blockId, "XML_ID" => $exId), false, false, Array());
        $obj = $result->GetNextElement();
        $arFields = $obj->GetFields();
        return $arFields["ID"];
    }
}
