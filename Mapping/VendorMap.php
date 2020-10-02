<?php

include_once "Models/VendorIdWithInternalId.php";

class VendorMap{
    public static function toVendorIdWithInternalId($object)
    {
        $fields = $object->GetFields();
        $vendorIdWithInternalId = new VendorIdWithInternalId();
        $vendorIdWithInternalId->InternalId = $fields["ID"];
        $vendorIdWithInternalId->ExternalId = $fields["XML_ID"];
        return $vendorIdWithInternalId;
    }
}
