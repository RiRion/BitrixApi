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

     public static function AddVendor($obj){
         $response = new \ApiResponse();
         $response->ObjectType = "Vendor";
         $response->Method = "Add";
         $vendor = self::CastToVendorAto($obj);
         $el = new \CIBlockElement();
         $bitrixElement = VendorMap::MapToBitrixElementFromVendor($vendor);
         if ($vendorId = $el->Add($bitrixElement, false, true, false)){
             $response->Status = $vendorId;
         }
         else {
             $response->Status = -1;
             $response->ErrorMessage = $el->LAST_ERROR;
         }
         return $response;
     }

     public static function AddVendorsRange($arrObj)
     {
         $result = array();
         foreach ($arrObj as $vendor){
             $result[] = self::AddVendor($vendor);
         }
         return $result;
     }

     public static function DeleteVendor($id){
         $response = new \ApiResponse();
         $response->ObjectType = "Vendor";
         $response->Method = "Delete";
         $response->ExId = $id;

         if(\CIBlockElement::Delete($id)) $response->Status = 1;
         else $response->Status = -1;
         return $response;
     }

     public static function DeleteVendorsRange($arrId)
     {
         $result = array();
         foreach ($arrId as $id) {
             $result[] = self::DeleteVendor($id);
         }
         return $result;
     }

     private static function CastToVendorAto($obj){
         $vendor = new Vendor();
         foreach ($obj as $key => $value)
         {
             $vendor->$key = $value;
         }
         return $vendor;
     }

     private static function CastToVendorAtoRange($arr){
         $arrVendors = array();
         foreach ($arr as $obj){
             $arrVendors[] = self::CastToVendorAto($obj);
         }
         return $arrVendors;
     }
 }