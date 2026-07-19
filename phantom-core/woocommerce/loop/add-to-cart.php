<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
	return;
}

$product_id = $product->get_id();
$permalink  = $product->get_permalink();
?>
<a href="<?php echo esc_url( $permalink ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>" class="phantom-woo-atc-button" style="display:inline-block;padding:8px 16px;background:#333;color:#fff;text-decoration:none;border-radius:4px;">
	<?php esc_html_e( 'View Product', 'phantom-core' ); ?>
</a>
