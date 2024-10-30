<?php
/*
Plugin Name: WP e-Commerce qty rate shipping plugin
Plugin URI: http://www.jmds.co.uk
Description: WP e-Commerce qty rate shipping plugin.
Version: 1.1
Author: John Messingham
Author URI: http://www.jmds.co.uk
License: GPLv2

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class jm_wpsc_qtyrate_shipping {
    
    var $internal_name, $name;

    /**
     * Constructor
     *
     * @return boolean Always returns true.
     */
    function jm_wpsc_qtyrate_shipping() {
        $this->internal_name = "Qty Rate";
        $this->name = __( "Qty Rate", 'wpsc' );
        $this->is_external=false;
        return true;
    }

    /**
     * Returns i18n-ized name of shipping module.
     *
     * @return string
     */
    function getName() {
        return $this->name;
    }

    /**
     * Returns internal name of shipping module.
     *
     * @return string
     */
    function getInternalName() {
        return $this->internal_name;
    }

    /**
     * generates row of table rate fields
     */
    private function output_row( $key = '', $shipping = '' ) {
        $currency = wpsc_get_currency_symbol();
        $class = ( $this->alt ) ? ' class="alternate"' : '';
        $this->alt = ! $this->alt;
        ?>
            <tr>
                <td<?php echo $class; ?>>
                    <div class="cell-wrapper">
                        <input type="text" name="wpsc_shipping_qtyrate_breakpoint[]" value="<?php echo esc_attr( $key ); ?>" size="4" />
                        <small><?php _e( ' items and above', 'wpsc' ); ?></small>
                    </div>
                </td>
                <td<?php echo $class; ?>>
                    <div class="cell-wrapper">
                        <small><?php echo esc_html( $currency ); ?></small>
                        <input type="text" name="wpsc_shipping_qtyrate_rate[]" value="<?php echo esc_attr( $shipping ); ?>" size="4" />
                        <span class="actions">
                                <a tabindex="-1" title="<?php _e( 'Delete Layer', 'wpsc' ); ?>" class="button-secondary wpsc-button-round wpsc-button-minus" href="#"><?php echo _x( '&ndash;', 'delete item', 'wpsc' ); ?></a>
                                <a tabindex="-1" title="<?php _e( 'Add Layer', 'wpsc' ); ?>" class="button-secondary wpsc-button-round wpsc-button-plus" href="#"><?php echo _x( '+', 'add item', 'wpsc' ); ?></a>
                        </span>
                    </div>
                </td>
            </tr>
        <?php
    }


    /**
     * Returns HTML settings form. Should be a collection of <tr> elements containing two columns.
     *
     * @return string HTML snippet.
     */
    function getForm() {
        $this->alt = false;
        $breakpoints = get_option( 'jm_wpsc_qtyrate_shipping_breakpoints', array() );
        ob_start();
        ?>
        <tr>
            <td colspan='2'>
                <table>
                    <thead>
                        <tr>
                            <th class="item-count"><?php _e( 'Item Count', 'wpsc' ); ?></th>
                            <th class="shipping"><?php _e( 'Shipping Price', 'wpsc' ); ?></th>
                        </tr>
                    </thead>
                    <tbody class="table-rate">
                        <?php if ( ! empty( $breakpoints ) ): ?>
                            <?php
                                foreach( $breakpoints as $key => $rate ){
                                    $this->output_row( $key, $rate );
                                }
                            ?>
                        <?php else: ?>
                            <?php $this->output_row(); ?>
                        <?php endif ?>
                    </tbody>
                </table>
                </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * Saves shipping module settings.
     *
     * @return boolean Always returns true.
     */
    function submit_form() {
       
        if ( empty( $_POST['wpsc_shipping_qtyrate_rate'] ) || empty( $_POST['wpsc_shipping_qtyrate_breakpoint'] ) ) {
            return false;
        }
        $new_breakpoints = array();
  
        $breakpoints = (array)$_POST['wpsc_shipping_qtyrate_breakpoint'];
        $rates = (array)$_POST['wpsc_shipping_qtyrate_rate'];

        if ( !empty($rates) ) {
            foreach ($rates as $key => $price) {
                $new_breakpoints[$breakpoints[$key]] = $price;
            }
        }

        ksort( $new_breakpoints );
        update_option( 'jm_wpsc_qtyrate_shipping_breakpoints', $new_breakpoints );
        return true;
    }

    /**
     * returns shipping quotes using this shipping module.
     *
     * @return array collection of rates applicable.
     */
    function getQuote() {

        global $wpsc_cart;

        $itemCount = 0;
        $rate_amount = 0;

        foreach ($wpsc_cart->cart_items as $cart_item) {
                $itemCount = $itemCount + $cart_item->quantity;
        }

        $breakpoints = get_option('jm_wpsc_qtyrate_shipping_breakpoints');

        if ($breakpoints != '') {

            ksort($breakpoints);

            foreach ($breakpoints as $key => $rate) {
                if ($itemCount >= (float)$key) {
                    $rate_amount = $rate;
                }
            }
            return array( __( "$itemCount Items", 'wpsc' ) => (float)$rate_amount);
        }

    }
}

$jm_wpsc_qtyrate_shipping = new jm_wpsc_qtyrate_shipping();
$wpsc_shipping_modules[$jm_wpsc_qtyrate_shipping->getInternalName()] = $jm_wpsc_qtyrate_shipping;
