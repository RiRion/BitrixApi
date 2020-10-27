<?php

namespace Services;

use Mapping\VendorMap;
use Models\Vendor;

 class VendorService
 {
     public static function GetVendorsId(){
         $arrVendors = array();
         $res = \CIBlockElement::GetList(array(), array("IBLOCK_ID" => 1), false, false, array("ID", "XML_ID"));
         while($ob = $res->GetNextElement())
         {
             $arrVendors[] = VendorMap::MapToVendorIdWithInternalId($ob);
         }

         return json_encode($arrVendors, JSON_UNESCAPED_UNICODE);
     }

     public static function GetVendors(){
         $vendors = array();
         $res = \CIBlockElement::GetList(array(), array("IBLOCK_ID" => 1), false, false, array());
         while ($ob = $res->GetNextElement())
         {
             $vendors[] = VendorMap::MapToVendorFromBitrixElement($ob);
         }

         return json_encode($vendors, JSON_UNESCAPED_UNICODE);
     }

     public static function AddVendorsRange()
     {
         set_time_limit(120);
         $data = json_decode(file_get_contents('php://input'));
         $vendors = self::CastToVendorAto($data);
         $el = new \CIBlockElement();
         foreach ($vendors as $vendor) {
             $bitrixElement = VendorMap::MapToBitrixElementFromVendor($vendor);
             $el->Add($bitrixElement, false, true, false);
         }
         unset($vendor);
     }

     public static function DeleteVendors()
     {
         set_time_limit(120);
         $vendorsId = json_decode(file_get_contents('php://input'));
         foreach ($vendorsId as $id) \CIBlockElement::Delete($id);
         unset($id);
     }

     private static function CastToVendorAto($arr){
         $arrVendors = array();
         foreach ($arr as $obj){
             $vendor = new Vendor();
             foreach ($obj as $key => $value)
             {
                 $vendor->$key = $value;
             }
             $arrVendors[] = $vendor;
         }
         return $arrVendors;
     }
 }