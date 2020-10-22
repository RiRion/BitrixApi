<?


class OfferAto{
    public $Id = ""; // offer ID
    public $ProductId = ""; // prodid -> CML2_LINK (Property)
    public $XmlId = ""; // sku -> B_IBLOCK_ELEMENT.XML_ID
    public $Barcode = ""; // barcode -> barcode (Property)
    public $Name = ""; // name -> B_IBLOCK_ELEMENT.NAME
    public $Quantity = ""; // qty -> B_CATALOG_PRODUCT.QUANTITY
    public $ShippingDate = ""; // shippingdate -> shippingdate (Property)
    public $Weight = ""; // weight -> B_CATALOG_PRODUCT.WEIGHT
    public $Color = ""; // color -> COLOR (Property)
    public $Size = ""; // size -> SIZE (Property)
    public $Currency = ""; // currency -> B_CATALOG_PRICE.CURRENCY & B_CATALOG_PRODUCT.PURCHASING_CURRENCY
    public $Price = ""; // price -> B_CATALOG_PRICE.PRICE
    public $BaseWholePrice = ""; // basewoleprice -> B_CATALOG_PRODUCT.PURCHASING_PRICE
    public $P5SStock = ""; // p5s_stock -> p5sstock
    public $SuperSale = ""; // SuperSale -> SuperSale
    public $StopPromo = ""; // StopPromo -> StopPromo
}