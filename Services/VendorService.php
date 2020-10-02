<?php


include_once "Mapping/VendorMap.php";

 class VendorService
 {
     public static function getVendorsId(){
         $arrVendors = array();
         $res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => 1), false, false, array("ID", "XML_ID"));
         while($ob = $res->GetNextElement())
         {
             $arrVendors[] = VendorMap::toVendorIdWithInternalId($ob);
         }

         return json_encode($arrVendors, JSON_UNESCAPED_UNICODE);
     }
 }