<?php
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

    //Productos
            //Descripción
    $items = $xml2->xpath("//cac:Item/cbc:Description");
    $items_array = array_map(function($node) {
        return (string) $node;
    }, $items);
    $items_prod = implode(',', $items_array);

            //Referencia
    $ref = $xml2->xpath("//cac:StandardItemIdentification/cbc:ID");
    $ref_array = array_map(function($node) {
        return (string) $node;
    }, $ref);
    $ref_items = implode(',', $ref_array);

                //Cantidad
    $qty = $xml2->xpath("//cac:Price/cbc:BaseQuantity");
    $qty_array = array_map(function($node) {
        return (string) $node;
    }, $qty);   
    $qty_items = implode(',', $qty_array);      


                            //valor Descuento Items o productos
    $des_prod = $xml2->xpath("//cbc:AllowanceTotalAmount");
    $des_prod_array = array_map(function($node) {
        return (string) $node;
    }, $des_prod);
    $des_items = implode(',', $des_prod_array);


 
                //valor Unitario antes de iva
    $price_unt = $xml2->xpath("//cac:Price/cbc:PriceAmount");
    $price_unt_array = array_map(function($node) {
        return (string) $node;
    }, $price_unt);
    $precio_items = implode(',', $price_unt_array);

                    //valor valor iva Items o productos
    $iva_prod = $xml2->xpath("//cbc:TaxAmount");
    $iva_prod_array = array_map(function($node) {
        return (string) $node;
    }, $iva_prod);
    $iva_items = implode(',', $iva_prod_array);



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
        'iva_items' => $iva_items,
        'subtotal' => $subtotal,
        'iva' => $iva,
        'total' => $total
    );
             

}



?>

