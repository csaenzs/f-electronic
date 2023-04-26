<?php
session_start();
$facturas = array();

$user_id = $_SESSION['user_id'];

foreach (glob("archivosXML/$user_id/*.xml") as $archivo) {
    $factura = simplexml_load_file($archivo);
    $namespaces = $factura->getNamespaces(true);
    $factura->registerXPathNamespace('cac', $namespaces['cac']);
    $factura->registerXPathNamespace('cbc', $namespaces['cbc']);

    // Obtener los datos de la factura

    //Array invaice o factura
    $description_nodes = $factura->xpath('//cac:ExternalReference/cbc:Description');
    $description_array = array_filter($description_nodes, function($node) {
        return !empty((string) $node);
    });

    $xml2 = simplexml_load_string($description_array[0]);

        //Datos Generales de la factura   
    $cufe = (string)$factura->xpath('//cbc:UUID')[0];
    $fecha_emision = (string)$factura->xpath('//cbc:IssueDate')[0];
    $hora_emision = (string)$factura->xpath('//cbc:IssueTime')[0];
    $factura_numero = (string)$factura->xpath('cbc:ParentDocumentID')[0];
    $fecha_vencimiento =$xml2->xpath('//cbc:DueDate')[0];
    $forma_pago =$xml2->xpath('//cac:PaymentMeans/cbc:ID')[0];

    //Emisor
    $emisor_nombre = (string)$factura->xpath('//cbc:RegistrationName')[0];
    $emisor_nit = (string)$factura->xpath('//cac:PartyTaxScheme/cbc:CompanyID')[0];
    $emisor_dv = $xml2->xpath("//cbc:CompanyID/@schemeID")[0];
    $emisor_ciudad = $xml2->xpath("//cbc:CityName")[0];
    $emisor_codigopostal = $xml2->xpath("//cbc:PostalZone")[0];
    $emisor_departamento = $xml2->xpath("//cbc:CountrySubentity")[0];
    $emisor_direccion = $xml2->xpath("//cac:AddressLine/cbc:Line")[0];
    $emisor_codigopais = $xml2->xpath("//cbc:IdentificationCode")[0];
    $emisor_tel = $xml2->xpath("//cbc:Telephone")[0];
    $emisor_correo = $xml2->xpath("//cbc:ElectronicMail")[0];

    //Receptor
    $receptor_nombre = (string)$factura->xpath('//cac:ReceiverParty/cac:PartyTaxScheme/cbc:RegistrationName')[0];
    $receptor_nit = (string)$factura->xpath('//cac:PartyTaxScheme/cbc:CompanyID')[1];
    $receptor_dv = $xml2->xpath("//cbc:CompanyID/@schemeID")[2];
    $receptor_ciudad = $xml2->xpath("//cbc:CityName")[2];
    $receptor_codigopostal = $xml2->xpath("//cbc:PostalZone")[2];
    $receptor_departamento = $xml2->xpath("//cbc:CountrySubentity")[2];
    $receptor_direccion = $xml2->xpath("//cac:AddressLine/cbc:Line")[2];
    $receptor_codigopais = $xml2->xpath("//cbc:IdentificationCode")[2];
    $receptor_tel = $xml2->xpath("//cbc:Telephone")[2];
    $receptor_correo = $xml2->xpath("//cbc:ElectronicMail")[1];


        // Productos
        // Descripción
        $items = $xml2->xpath("//cac:Item/cbc:Description");
        $items_array = array_map(function($node) {
            return (string) $node;
        }, $items);
        $items_prod = $items_array;

        // Referencia
        $ref = $xml2->xpath("//cac:StandardItemIdentification/cbc:ID");
        $ref_array = array_map(function($node) {
            return (string) $node;
        }, $ref);
        $ref_items = $ref_array;

        // Cantidad
        $qty = $xml2->xpath("//cac:Price/cbc:BaseQuantity");
        $qty_items = array_map(function($node) {
            return floatval((string) $node);
        }, $qty);

        // Valor Descuento Items o productos
        $des_prod = $xml2->xpath("//cbc:AllowanceTotalAmount");
        $des_items = array_map(function($node) {
            return floatval((string) $node);
        }, $des_prod);

        // Valor Unitario antes de iva
        $price_unt = $xml2->xpath("//cac:Price/cbc:PriceAmount");
        $precio_items = array_map(function($node) {
            return floatval((string) $node);
        }, $price_unt);

        // Valor Total Items antes de iva subtotal
        $subtotal_prod = $xml2->xpath("//cac:InvoiceLine/cbc:LineExtensionAmount");
        $subtotal_items = array_map(function($node) {
            return floatval((string) $node);
        }, $subtotal_prod);

        // Porcentaje de IVA
        $porcIVA_prod = $xml2->xpath("//cbc:Percent");
        $porcIVA_items = array_map(function($node) {
            return floatval((string) $node);
        }, $porcIVA_prod);

        $iva_items = array_map(function($precio, $porcIVA) {
            return ($precio * $porcIVA) / 100;
        }, $precio_items, $porcIVA_items);

        $precio_items_incl_IVA = array_map(function($precio, $porcIVA) {
            return ($precio * $porcIVA / 100) + $precio;
        }, $precio_items, $porcIVA_items);

        $precio_total_incl_IVA = array_map(function($precio, $qty) {
            return $precio * $qty;
        }, $precio_items_incl_IVA, $qty_items);


       
    $subtotal = $xml2->xpath("//cbc:LineExtensionAmount")[0];
    $iva = $xml2->xpath("//cbc:TaxAmount")[0];
    $total = $xml2->xpath("//cbc:TaxInclusiveAmount")[0];   

       // Agregar los datos a la tabla

       $facturas[] = array(
        'cufe' => $cufe,
        'fecha_emision' => $fecha_emision,
        'hora_emision' => $hora_emision,
        'factura_numero' => $factura_numero,
        'fecha_vencimiento' => $fecha_vencimiento,
        'forma_pago' => $forma_pago,
        'emisor_nombre' => $emisor_nombre,
        'emisor_nit' => $emisor_nit,
        'emisor_dv' => $emisor_dv,
        'emisor_ciudad' => $emisor_ciudad,
        'emisor_codigopostal' => $emisor_codigopostal,
        'emisor_departamento' => $emisor_departamento,
        'emisor_direccion' => $emisor_direccion,
        'emisor_codigopais' => $emisor_codigopais,
        'emisor_tel' => $emisor_tel,
        'emisor_correo' => $emisor_correo,
        'receptor_nombre' => $receptor_nombre,
        'receptor_nit' => $receptor_nit,
        'receptor_dv' => $receptor_dv,
        'receptor_ciudad' => $receptor_ciudad,
        'receptor_codigopostal' => $receptor_codigopostal,
        'receptor_departamento' => $receptor_departamento,
        'receptor_direccion' => $receptor_direccion,
        'receptor_codigopais' => $receptor_codigopais,
        'receptor_tel' => $receptor_tel,
        'receptor_correo' => $receptor_correo,
        'items_prod' => $items_prod,
        'ref_items' => $ref_items,
        'qty_items' => $qty_items,
        'des_items' => $des_items, 
        'precio_items' => $precio_items,
        'precio_items_incl_IVA' => $precio_items_incl_IVA,
        'iva_items' => $iva_items,
        'porcIVA_items' => $porcIVA_items,
        'subtotal_items' => $subtotal_items,
        'precio_total_incl_IVA' => $precio_total_incl_IVA,
        'subtotal' => $subtotal,
        'iva' => $iva,
        'total' => $total
    );
    

   // var_dump($precio_total_incl_IVA);
                
}



?>

