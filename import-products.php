<?php
/**
 * Import Divi Engine sample products via WP-CLI
 * Run: wp eval-file import-products.php --allow-root
 */

// Define products inline to avoid CSV parsing issues
$products = [
    // Simple products
    [
        'type' => 'simple',
        'name' => 'Divi Engine String Bag (Big Logo)',
        'sku' => 'de-string-bag-big',
        'regular_price' => 19.99,
        'description' => '',
        'short_description' => 'This fashionable string bag is made of 100% cotton. It is the perfect size for carrying your everyday essentials.',
        'categories' => ['Accessories'],
        'stock_status' => 'instock',
        'image_url' => 'https://ajax-filters-bc.diviengine.com/sampledata/images/Bag1.jpg',
    ],
    [
        'type' => 'simple',
        'name' => 'Divi Engine String Bag (Small Logos)',
        'sku' => 'de-string-bag-small',
        'regular_price' => 19.99,
        'description' => '',
        'short_description' => 'This fashionable string bag is made of 100% cotton. It is the perfect size for carrying your everyday essentials.',
        'categories' => ['Accessories'],
        'stock_status' => 'instock',
        'image_url' => 'https://ajax-filters-bc.diviengine.com/sampledata/images/Bag2.jpg',
    ],
    [
        'type' => 'simple',
        'name' => 'Lanyard',
        'sku' => 'de-lanyard',
        'regular_price' => 9.99,
        'sale_price' => 7.99,
        'description' => '',
        'short_description' => 'Stop losing your important access keys with a lanyard that is ALMOST as reliable as Divi Engine plugins!',
        'categories' => ['Accessories'],
        'stock_status' => 'instock',
        'image_url' => 'https://ajax-filters-bc.diviengine.com/sampledata/images/Lanyard1.jpg',
    ],
    // Variable: Brand Buttons
    [
        'type' => 'variable',
        'name' => 'Brand Buttons',
        'sku' => 'de-brand-buttons',
        'description' => 'Represent your favorite CMS, eCommerce Platform, Website Builder, or Plugin Company in style with a cool pin.',
        'short_description' => '',
        'categories' => ['Accessories'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Brand', 'options' => ['Divi', 'Divi Engine', 'WooCommerce', 'WordPress']],
        ],
        'default_attributes' => ['Brand' => 'Divi Engine'],
        'variations' => [
            ['name' => 'Brand Buttons - Divi', 'sku' => 'de-brand-buttons-divi', 'regular_price' => 9.99, 'attributes' => ['Brand' => 'Divi'], 'image_url' => 'https://ajax-filters-bc.diviengine.com/sampledata/images/DE-Pins-1.jpg'],
            ['name' => 'Brand Buttons - Divi Engine', 'sku' => 'de-brand-buttons-de', 'regular_price' => 9.99, 'attributes' => ['Brand' => 'Divi Engine'], 'image_url' => 'https://ajax-filters-bc.diviengine.com/sampledata/images/DE-Pins-4.jpg'],
            ['name' => 'Brand Buttons - WooCommerce', 'sku' => 'de-brand-buttons-wc', 'regular_price' => 9.99, 'attributes' => ['Brand' => 'WooCommerce'], 'image_url' => 'https://ajax-filters-bc.diviengine.com/sampledata/images/DE-Pins-2.jpg'],
            ['name' => 'Brand Buttons - WordPress', 'sku' => 'de-brand-buttons-wp', 'regular_price' => 9.99, 'attributes' => ['Brand' => 'WordPress'], 'image_url' => 'https://ajax-filters-bc.diviengine.com/sampledata/images/DE-Pins-3.jpg'],
        ],
    ],
    // Variable: Divi Engine Tee
    [
        'type' => 'variable',
        'name' => 'Divi Engine Tee',
        'sku' => 'de-divi-engine-tee',
        'description' => 'This comfortable cotton t-shirt that features the Divi Engine logo on the front is perfect for any occasion. The shirt is available in three colors.',
        'short_description' => '',
        'categories' => ['Men', 'Men > Shirts'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Color', 'options' => ['Blue', 'White', 'Yellow']],
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Color' => 'Yellow', 'Size' => 'Large'],
        'variations' => [],
    ],
    // Variable: Divi Tee
    [
        'type' => 'variable',
        'name' => 'Divi Tee',
        'sku' => 'de-divi-tee',
        'description' => 'This comfortable cotton t-shirt features the Divi logo on the front and back. It is the perfect tee for any occasion.',
        'short_description' => '',
        'categories' => ['Men', 'Men > Shirts'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Large'],
        'variations' => [],
    ],
    // Variable: WordPress Tee
    [
        'type' => 'variable',
        'name' => 'WordPress Tee',
        'sku' => 'de-wordpress-tee',
        'description' => 'This comfortable cotton t-shirt features the WordPress logo on the front and back. It is the perfect tee for any occasion.',
        'short_description' => '',
        'categories' => ['Men', 'Men > Shirts'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Large'],
        'variations' => [],
    ],
    // Variable: Mens Divi Hoodie
    [
        'type' => 'variable',
        'name' => "Men's Divi Hoodie",
        'sku' => 'de-mens-divi-hoodie',
        'description' => 'This Divi hoodie is a must have for any Divi fan. It is made from a soft, comfortable, and durable cotton blend.',
        'short_description' => '',
        'categories' => ['Men', 'Men > Hoodies'],
        'stock_status' => 'outofstock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Large'],
        'variations' => [],
    ],
    // Variable: Dat Divi Engine Life Hoodie - Limited Edition
    [
        'type' => 'variable',
        'name' => "Dat Divi Engine Life Hoodie - Limited Edition",
        'sku' => 'de-de-life-hoodie-le',
        'description' => 'This Divi Engine hoodie is a must have for any Divi Engine fan.',
        'short_description' => '',
        'categories' => ['Men', 'Men > Hoodies'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Large'],
        'variations' => [],
    ],
    // Variable: Mens WordPress Hoodie
    [
        'type' => 'variable',
        'name' => "Men's WordPress Hoodie",
        'sku' => 'de-mens-wp-hoodie',
        'description' => 'This WordPress hoodie is a must have for any WordPress fan.',
        'short_description' => '',
        'categories' => ['Men', 'Men > Hoodies'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Large'],
        'variations' => [],
    ],
    // Variable: Divi Engine Logo Zipper Hoodie
    [
        'type' => 'variable',
        'name' => 'Divi Engine Logo Zipper Hoodie',
        'sku' => 'de-de-logo-zip-hoodie',
        'description' => 'This Divi Engine hoodie is a must have for any Divi Engine fan.',
        'short_description' => '',
        'categories' => ['Women', 'Women > Hoodies'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Medium'],
        'variations' => [],
    ],
    // Variable: Purple Divi Engine Text Zipper Hoodie
    [
        'type' => 'variable',
        'name' => 'Purple Divi Engine Text Zipper Hoodie',
        'sku' => 'de-purple-de-zip-hoodie',
        'description' => 'This Divi Engine hoodie is a must have for any Divi Engine fan.',
        'short_description' => '',
        'categories' => ['Women', 'Women > Hoodies'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Medium'],
        'variations' => [],
    ],
    // Variable: WooCommerce "Gimme the Money" Zipper Hoodie
    [
        'type' => 'variable',
        'name' => 'WooCommerce "Gimme the Money" Zipper Hoodie',
        'sku' => 'de-wc-gimme-hoodie',
        'description' => 'This WooCommerce hoodie is a must have for any WooCommerce fan.',
        'short_description' => '',
        'categories' => ['Women', 'Women > Hoodies'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Medium'],
        'variations' => [],
    ],
    // Variable: Divi Ninja Tee
    [
        'type' => 'variable',
        'name' => 'Divi Ninja Tee',
        'sku' => 'de-divi-ninja-tee',
        'description' => 'This comfortable cotton t-shirt features the Divi logo on the front and expresses your Ninja status.',
        'short_description' => '',
        'categories' => ['Women', 'Women > Shirts'],
        'stock_status' => 'onbackorder',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Large'],
        'variations' => [],
    ],
    // Variable: Divi Simplified Crop-top
    [
        'type' => 'variable',
        'name' => 'Divi Simplified Crop-top',
        'sku' => 'de-divi-crop-top',
        'description' => 'This comfortable cotton crop-top features the Divi and Divi Engine logos.',
        'short_description' => '',
        'categories' => ['Women', 'Women > Shirts'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
            ['name' => 'Color', 'options' => ['Blue', 'White', 'Yellow']],
        ],
        'default_attributes' => ['Size' => 'Large', 'Color' => 'White'],
        'variations' => [],
    ],
    // Variable: Dat Divi Engine Life Crop-top (3-Tone)
    [
        'type' => 'variable',
        'name' => "Dat Divi Engine Life Crop-top (3-Tone)",
        'sku' => 'de-de-life-crop-3tone',
        'description' => 'This comfortable cotton crop-top features the Divi Engine logo.',
        'short_description' => '',
        'categories' => ['Women', 'Women > Shirts'],
        'stock_status' => 'instock',
        'attributes' => [
            ['name' => 'Size', 'options' => ['Large', 'Medium', 'Small']],
        ],
        'default_attributes' => ['Size' => 'Large'],
        'variations' => [],
    ],
];

function ensure_category($name, $parent = 0) {
    $term = term_exists($name, 'product_cat', $parent);
    if ($term) return $term['term_id'];
    $result = wp_insert_term($name, 'product_cat', ['parent' => $parent, 'slug' => sanitize_title($name)]);
    return is_wp_error($result) ? 0 : $result['term_id'];
}

function ensure_categories($path) {
    $parts = explode(' > ', $path);
    $parent_id = 0;
    foreach ($parts as $part) {
        $parent_id = ensure_category(trim($part), $parent_id);
    }
    return $parent_id;
}

$imported = 0;
$errors = 0;

foreach ($products as $data) {
    try {
        echo "Creating: {$data['name']}... ";

        $product = null;
        $category_ids = [];
        foreach ($data['categories'] as $cat_path) {
            $cat_id = ensure_categories($cat_path);
            if ($cat_id) $category_ids[] = $cat_id;
        }

        if ($data['type'] === 'simple') {
            $product = new WC_Product_Simple();
            $product->set_name($data['name']);
            $product->set_regular_price((string)$data['regular_price']);
            if (!empty($data['sale_price'])) {
                $product->set_sale_price((string)$data['sale_price']);
            }
            $product->set_sku($data['sku']);
            $product->set_description($data['description'] ?? '');
            $product->set_short_description($data['short_description']);
            $product->set_category_ids($category_ids);
            $product->set_stock_status($data['stock_status']);
            $product->set_manage_stock(false);
            $product->save();
            echo "ID {$product->get_id()} OK\n";
            $imported++;
        } elseif ($data['type'] === 'variable') {
            $product = new WC_Product_Variable();
            $product->set_name($data['name']);
            if ($data['sku']) $product->set_sku($data['sku']);
            $product->set_description($data['description'] ?? '');
            $product->set_short_description($data['short_description'] ?? '');
            $product->set_category_ids($category_ids);
            $product->set_stock_status($data['stock_status']);
            $product->set_manage_stock(false);
            
            $attrs = [];
            $attr_idx = 0;
            foreach ($data['attributes'] as $attr_data) {
                $attribute = new WC_Product_Attribute();
                $attribute->set_name($attr_data['name']);
                $attribute->set_options($attr_data['options']);
                $attribute->set_position($attr_idx);
                $attribute->set_visible(true);
                $attribute->set_variation(true);
                $attrs[] = $attribute;
                $attr_idx++;
            }
            $product->set_attributes($attrs);
            
            $default_attrs = [];
            foreach ($data['default_attributes'] as $name => $value) {
                $default_attrs[$name] = $value;
            }
            if (!empty($default_attrs)) {
                $product->set_default_attributes($default_attrs);
            }
            
            $product->save();
            $parent_id = $product->get_id();
            echo "ID {$parent_id} (variable) OK\n";
            $imported++;

            // Create variations
            $colors = ['Blue', 'White', 'Yellow'];
            $sizes = ['Large', 'Medium', 'Small'];
            
            $variation_data_list = [];

            if ($data['name'] === 'Divi Engine Tee') {
                foreach ($colors as $color) {
                    foreach ($sizes as $size) {
                        $img = '';
                        $color_lower = strtolower($color);
                        $img = "https://ajax-filters-bc.diviengine.com/sampledata/images/Shirt-3-{$color_lower}-front.jpg";
                        $variation_data_list[] = [
                            'name' => "Divi Engine Tee - {$color}, {$size}",
                            'sku' => "de-de-tee-{$color_lower}-{$size}",
                            'regular_price' => 14.99,
                            'attributes' => ['Color' => $color, 'Size' => $size],
                            'image_url' => $img,
                        ];
                    }
                }
            } elseif ($data['name'] === 'Divi Tee') {
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "Divi Tee - {$size}",
                        'sku' => "de-divi-tee-{$size}",
                        'regular_price' => 14.99,
                        'sale_price' => 12.99,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === 'WordPress Tee') {
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "WordPress Tee - {$size}",
                        'sku' => "de-wp-tee-{$size}",
                        'regular_price' => 14.99,
                        'sale_price' => 12.99,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === "Men's Divi Hoodie") {
                foreach ($sizes as $size) {
                    $price = $size === 'Large' ? 39.99 : 34.99;
                    $variation_data_list[] = [
                        'name' => "Men's Divi Hoodie - {$size}",
                        'sku' => "de-mens-divi-hoodie-{$size}",
                        'regular_price' => $price,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === "Dat Divi Engine Life Hoodie - Limited Edition") {
                $stocks = ['Large' => 100, 'Medium' => 2, 'Small' => 33];
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "Dat Divi Engine Life Hoodie - Limited Edition - {$size}",
                        'sku' => "de-life-hoodie-le-{$size}",
                        'regular_price' => 44.99,
                        'attributes' => ['Size' => $size],
                        'stock_quantity' => $stocks[$size],
                    ];
                }
            } elseif ($data['name'] === "Men's WordPress Hoodie") {
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "Men's WordPress Hoodie - {$size}",
                        'sku' => "de-mens-wp-hoodie-{$size}",
                        'regular_price' => 34.99,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === 'Divi Engine Logo Zipper Hoodie') {
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "Divi Engine Logo Zipper Hoodie - {$size}",
                        'sku' => "de-logo-zip-hoodie-{$size}",
                        'regular_price' => 29.99,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === 'Purple Divi Engine Text Zipper Hoodie') {
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "Purple Divi Engine Text Zipper Hoodie - {$size}",
                        'sku' => "de-purple-zip-hoodie-{$size}",
                        'regular_price' => 29.99,
                        'sale_price' => 27.99,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === 'WooCommerce "Gimme the Money" Zipper Hoodie') {
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "WooCommerce \"Gimme the Money\" Zipper Hoodie - {$size}",
                        'sku' => "de-wc-gimme-hoodie-{$size}",
                        'regular_price' => 29.99,
                        'sale_price' => 27.99,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === 'Divi Ninja Tee') {
                foreach ($sizes as $size) {
                    $variation_data_list[] = [
                        'name' => "Divi Ninja Tee - {$size}",
                        'sku' => "de-divi-ninja-tee-{$size}",
                        'regular_price' => 12.99,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === 'Divi Simplified Crop-top') {
                foreach ($sizes as $size) {
                    foreach ($colors as $color) {
                        $variation_data_list[] = [
                            'name' => "Divi Simplified Crop-top - {$size}, {$color}",
                            'sku' => "de-divi-crop-{$size}-{$color}",
                            'regular_price' => 12.99,
                            'attributes' => ['Size' => $size, 'Color' => $color],
                        ];
                    }
                }
            } elseif ($data['name'] === "Dat Divi Engine Life Crop-top (3-Tone)") {
                foreach ($sizes as $size) {
                    $price = $size === 'Large' ? 14.99 : 12.99;
                    $variation_data_list[] = [
                        'name' => "Dat Divi Engine Life Crop-top (3-Tone) - {$size}",
                        'sku' => "de-life-crop-3tone-{$size}",
                        'regular_price' => $price,
                        'attributes' => ['Size' => $size],
                    ];
                }
            } elseif ($data['name'] === 'Brand Buttons') {
                // Variations already defined in the data
                foreach ($data['variations'] as $vd) {
                    $variation_data_list[] = $vd;
                }
            }

            foreach ($variation_data_list as $vd) {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($parent_id);
                $variation->set_name($vd['name']);
                if (!empty($vd['sku'])) $variation->set_sku($vd['sku']);
                if (!empty($vd['regular_price'])) $variation->set_regular_price((string)$vd['regular_price']);
                if (!empty($vd['sale_price'])) $variation->set_sale_price((string)$vd['sale_price']);
                $variation->set_manage_stock(!empty($vd['stock_quantity']));
                if (!empty($vd['stock_quantity'])) {
                    $variation->set_stock_quantity($vd['stock_quantity']);
                    $variation->set_stock_status('instock');
                } else {
                    $variation->set_stock_status('instock');
                }
                
                $attrs = [];
                foreach ($vd['attributes'] as $attr_name => $attr_value) {
                    $attrs[strtolower($attr_name)] = $attr_value;
                }
                $variation->set_attributes($attrs);
                $variation->save();
                echo "  Variation {$vd['name']} ID {$variation->get_id()} OK\n";
                $imported++;
            }
        }
    } catch (Exception $e) {
        echo "ERROR: {$e->getMessage()}\n";
        $errors++;
    }
}

echo "\nDone! Imported: {$imported}, Errors: {$errors}\n";
