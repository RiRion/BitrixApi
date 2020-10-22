<?php

namespace Mapping;

use Models\Vendor;
use Models\VendorIdWithInternalId;

class VendorMap
{
    public static function MapToVendorIdWithInternalId($object)
    {
        $fields = $object->GetFields();
        $vendorIdWithInternalId = new VendorIdWithInternalId();
        $vendorIdWithInternalId->InternalId = $fields["ID"];
        $vendorIdWithInternalId->ExternalId = $fields["XML_ID"];
        return $vendorIdWithInternalId;
    }

    public static function MapToVendorFromBitrixElement($bitrixElement)
    {
        $vendor = new Vendor();
        $fields = $bitrixElement->GetFields();
        $properties = $bitrixElement->GetProperties();

        $vendor->VendorId = $fields["XML_ID"];
        $vendor->Title = $fields["NAME"];
        $vendor->Description = $fields["DETAIL_TEXT"];
        $vendor->Country = $properties[132]["VALUE"];
        $vendor->DescType = $fields["DETAIL_TEXT_TYPE"];

        return $vendor;
    }

    public static function MapToBitrixElementFromVendor(Vendor $vendor)
    {
        return array(
            "XML_ID" => $vendor->VendorId,
            "NAME" => $vendor->Title,
            "DETAIL_TEXT" => $vendor->Description,
            "DETAIL_TEXT_TYPE" => $vendor->DescType,
            "PROPERTY_VALUES" => array(
                132 => $vendor->Country,
                133 => $vendor->VendorId
            )
        );
    }
}
