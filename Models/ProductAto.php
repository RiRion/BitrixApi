<?

    class ProductAto {
        public $ProductId = "";
        public $VendorId = "";
        public $VendorCode = "";
        public $Name = "";
        public $Description = "";
        public $ImagesURL; //class Images
        public $Batteries = "";
        public $Pack = "";
        public $Material = "";
        public $Length = "";
        public $Diameter = "";
        public $Collection = "";
        public $CategoryId;
        public $Bestseller = "";
        public $New = "";
        public $Function = "";
        public $AddFunction = "";
        public $Vibration = "";
        public $Volume = "";
        public $ModelYear = "";
        public $InfoPrice = "";
        public $ImgStatus = "";
        public $IeId = "";
        public $VendorCountry = "";
        public $Offers = "";
        public $Sale = "";

        function __construct()
        {
            $this->ImagesURL = new Images();
        }
    }