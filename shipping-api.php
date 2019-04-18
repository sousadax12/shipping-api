<?php
/**
 * Plugin Name: Shipping API
 */


function getShippingRates($request_data)
{
    $_pf = new WC_Product_Factory();
    $params = $request_data->get_params();
    $location = $params["location"];
    $total_weight = 0;
    foreach ($params["products"] as $id) {
        $product = $_pf->get_product($id);
        if (isset($product)) {
            $total_weight += $product->get_weight();
        }
    }
    $wbs = get_option("wbs_config");
    foreach ($wbs["rules"] as $rule) {
        $matchLocation = in_array($location, $rule["conditions"]["destination"]["locations"]);
        $matchWeight = (($rule["conditions"]["weight"]["range"]["min"] <= $total_weight) && ($rule["conditions"]["weight"]["range"]["max"] >= $total_weight));
        if ($matchLocation && $matchWeight) {
            return ["weight" => $total_weight, "cost" => $rule["charges"]["base"]];

        }
    }

    $products = [];


    return [];
}


add_action('rest_api_init', function () {
    register_rest_route('api-wbs', '/rates', array(
        'methods' => 'POST',
        'callback' => "getShippingRates"
    ));
});
