<?php
$url = 'http://localhost/webhamburger/api/cart/add_item.php';
$data = json_encode(["product_id" => 1, "cart_id" => 5, "quantity" => 1, "unit_price" => 25]);
$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                    "Content-Length: " . strlen($data) . "\r\n",
        'content' => $data,
        'ignore_errors' => true,
    ]
];
$ctx = stream_context_create($opts);
$res = file_get_contents($url, false, $ctx);
echo $res;

?>
